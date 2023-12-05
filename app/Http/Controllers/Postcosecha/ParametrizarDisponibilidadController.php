<?php

namespace yura\Http\Controllers\Postcosecha;

use Illuminate\Http\Request;
use Validator;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\ClasificacionRamoDisponibilidad;
use yura\Modelos\Submenu;

class ParametrizarDisponibilidadController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.param_disponibilidad.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $clasificaciones = ClasificacionRamo::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $listado = ClasificacionRamoDisponibilidad::get();

        return view('adminlte.gestion.postcocecha.param_disponibilidad.partials.listado', [
            'clasificaciones' => $clasificaciones,
            'listado' => $listado,
        ]);
    }

    public function store_model(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'clasificacion' => 'required',
            'ramos_x_caja' => 'required',
        ], [
            'clasificacion.required' => 'La longitud es obligatoria',
            'ramos_x_caja.required' => 'Los ramos x caja son obligatorios',
        ]);
        if (!$valida->fails()) {
            $model = new ClasificacionRamoDisponibilidad();
            $model->id_clasificacion_ramo = $request->clasificacion;
            $model->ramos_x_caja = $request->ramos_x_caja;
            $model->id_mezcla = $request->mezcla;
            $model->save();
            $model = ClasificacionRamoDisponibilidad::get()->last();
            bitacora('clasificacion_ramo_disponibilidad', $model->id_clasificacion_ramo_disponibilidad, 'I', 'Creacion del modelo');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> el parametro satisfactoriamente';
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

    public function update_model(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'clasificacion' => 'required',
            'ramos_x_caja' => 'required',
        ], [
            'clasificacion.required' => 'La clasificacion es obligatoria',
            'ramos_x_caja.required' => 'Los ramos x caja son obligatorios',
        ]);
        if (!$valida->fails()) {
            $model = ClasificacionRamoDisponibilidad::find($request->id);
            $model->id_clasificacion_ramo = $request->clasificacion;
            $model->ramos_x_caja = $request->ramos_x_caja;
            $model->id_mezcla = $request->mezcla;
            $model->save();

            if ($model->save()) {
                $success = true;
                $msg = 'Se ha <strong>MODIFICADO</strong> el parametro satisfactoriamente';
                bitacora('clasificacion_ramo_disponibilidad', $model->id_clasificacion_ramo_disponibilidad, 'U', 'Modifico el parametro');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                    . '</div>';
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
}
