<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Validator;
use yura\Http\Controllers\Controller;
use yura\Modelos\Plaga;
use yura\Modelos\Producto;
use yura\Modelos\RotacionesPlaga;
use yura\Modelos\Submenu;

class PlagasController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.campo.plagas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Plaga::where('nombre', 'like', '%' . mb_strtoupper($request->busqueda) . '%')
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.campo.plagas.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_plaga(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500|unique:producto',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'El nombre ya existe',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = new Plaga();
            $model->nombre = espacios(mb_strtoupper($request->nombre));
            $model->save();
            $model = Plaga::All()->last();
            bitacora('plaga', $model->id_plaga, 'I', 'Creacion de la plaga');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> la plaga satisfactoriamente';
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_plaga(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $existe_nombre = Plaga::All()
                ->where('id_plaga', '!=', $request->id)
                ->where('nombre', espacios(mb_strtoupper($request->nombre)))
                ->first();
            if ($existe_nombre != '') {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El nombre de la plaga y existe</p>'
                    . '</div>';
            } else {
                $model = Plaga::find($request->id);
                $model->nombre = espacios(mb_strtoupper($request->nombre));
                $model->save();

                if ($model->save()) {
                    $success = true;
                    $msg = 'Se ha <strong>MODIFICADO</strong> la plaga satisfactoriamente';
                    bitacora('plaga', $model->id_plaga, 'U', 'Modifico la plaga');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function cambiar_estado_plaga(Request $request)
    {
        $model = Plaga::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>MODIFICADO</strong> la plaga satisfactoriamente';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function rotaciones_plaga(Request $request)
    {
        return view('adminlte.gestion.campo.plagas.forms.rotaciones_plaga', [
            'plaga' => $request->id,
        ]);
    }

    public function listar_incidencias(Request $request)
    {
        $listado = RotacionesPlaga::where('incidencia', $request->incidencia)
            ->where('id_plaga', $request->plaga)
            ->where('estado', 1)
            ->orderBy('rotacion')
            ->get();
        $productos = Producto::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.campo.plagas.forms.listar_incidencias', [
            'listado' => $listado,
            'productos' => $productos,
            'incidencia' => $request->incidencia
        ]);
    }

    public function store_rotacion(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'rotacion' => 'required',
            'producto' => 'required',
            'dosis' => 'required',
            'litros_x_cama' => 'required',
        ], [
            'rotacion.required' => 'La rotacion es obligatoria',
            'producto.required' => 'El producto es obligatorio',
            'dosis.required' => 'La dosis es obligatoria',
            'litros_x_cama.required' => 'Los litros por cama son obligatorios',
        ]);
        if (!$valida->fails()) {
            $model = new RotacionesPlaga();
            $model->id_plaga = $request->plaga;
            $model->incidencia = $request->incidencia;
            $model->rotacion = $request->rotacion;
            $model->id_producto = $request->producto;
            $model->dosis = $request->dosis;
            $model->litros_x_cama = $request->litros_x_cama;
            $model->save();
            $model = Plaga::All()->last();
            bitacora('rotaciones_plaga', $model->id_rotaciones_plaga, 'I', 'Creacion de la rotacion de plaga');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> la rotacion satisfactoriamente';
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_rotacion(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'rotacion' => 'required',
            'producto' => 'required',
            'dosis' => 'required',
            'litros_x_cama' => 'required',
        ], [
            'rotacion.required' => 'La rotacion es obligatoria',
            'producto.required' => 'El producto es obligatorio',
            'dosis.required' => 'La dosis es obligatoria',
            'litros_x_cama.required' => 'Los litros por cama son obligatorios',
        ]);
        if (!$valida->fails()) {
            $model = RotacionesPlaga::find($request->id);
            $model->rotacion = $request->rotacion;
            $model->id_producto = $request->producto;
            $model->dosis = $request->dosis;
            $model->litros_x_cama = $request->litros_x_cama;
            $model->save();
            $model = Plaga::All()->last();
            bitacora('rotaciones_plaga', $model->id_rotaciones_plaga, 'U', 'Modificar la rotacion de plaga');

            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> la rotacion satisfactoriamente';
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function delete_rotacion(Request $request)
    {

        $model = RotacionesPlaga::find($request->id);
        $model->estado = 0;
        $model->save();
        bitacora('rotaciones_plaga', $model->id_rotaciones_plaga, 'E', 'Eliminar la rotacion de plaga');

        $success = true;
        $msg = 'Se ha <strong>ELIMINADO</strong> la rotacion satisfactoriamente';

        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }
}
