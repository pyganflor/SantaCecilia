<?php

namespace yura\Http\Controllers\Bodega;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Producto;
use yura\Modelos\Submenu;
use Validator;

class ProductosController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bodega.productos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Producto::Where(function ($q) use ($request) {
            $q->Where('nombre', 'like', '%' . mb_strtoupper($request->busqueda) . '%')
                ->orWhere('codigo', 'like', '%' . mb_strtoupper($request->busqueda) . '%');
        })->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.bodega.productos.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500|unique:producto',
            'codigo' => 'required|max:500|unique:producto',
            'unidad_medida' => 'required',
            'stock_minimo' => 'required',
            'disponibles' => 'required',
            'conversion' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'El nombre ya existe',
            'unidad_medida.required' => 'La unidad de medida es obligatoria',
            'nombre.max' => 'El nombre es muy grande',
            'codigo.required' => 'El codigo es obligatorio',
            'codigo.unique' => 'El codigo ya existe',
            'codigo.max' => 'El codigo es muy grande',
            'stock_minimo.required' => 'El stock minimo es obligatorio',
            'disponibles.required' => 'Los disponibles son obligatorios',
            'conversion.required' => 'La conversion es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = new Producto();
            $model->codigo = $request->codigo;
            $model->nombre = espacios(mb_strtoupper($request->nombre));
            $model->unidad_medida = $request->unidad_medida;
            $model->stock_minimo = $request->stock_minimo;
            $model->disponibles = 0;
            $model->conversion = $request->conversion;
            $model->precio_compra = $request->precio_compra;
            $model->save();
            $model = Producto::All()->last();
            bitacora('producto', $model->id_producto, 'I', 'Creacion del producto');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> el producto satisfactoriamente';
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

    public function update_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500',
            'codigo' => 'required|max:500',
            'unidad_medida' => 'required',
            'stock_minimo' => 'required',
            'conversion' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'codigo.required' => 'El codigo es obligatorio',
            'codigo.max' => 'El codigo es muy grande',
            'unidad_medida.required' => 'La unidad de medida es obligatoria',
            'stock_minimo.required' => 'El stock minimo es obligatorio',
            'conversion.required' => 'La conversion es obligatoria',
        ]);
        if (!$valida->fails()) {
            $existe_nombre = Producto::All()
                ->where('id_producto', '!=', $request->id)
                ->where('nombre', espacios(mb_strtoupper($request->nombre)))
                ->first();
            if ($existe_nombre != '') {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El nombre del producto y existe</p>'
                    . '</div>';
            } else {
                $existe_codigo = Producto::All()
                    ->where('id_producto', '!=', $request->id)
                    ->where('codigo', espacios(mb_strtoupper($request->codigo)))
                    ->first();
                if ($existe_codigo != '') {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p>El codigo del producto y existe</p>'
                        . '</div>';
                } else {
                    $model = Producto::find($request->id);
                    $model->codigo = $request->codigo;
                    $model->nombre = espacios(mb_strtoupper($request->nombre));
                    $model->stock_minimo = $request->stock_minimo;
                    $model->unidad_medida = $request->unidad_medida;
                    $model->conversion = $request->conversion;
                    $model->precio_compra = $request->precio_compra;
                    $model->save();

                    if ($model->save()) {
                        $success = true;
                        $msg = 'Se ha <strong>MODIFICADO</strong> el producto satisfactoriamente';
                        bitacora('producto', $model->id_producto, 'U', 'Modifico el producto');
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                            . '</div>';
                    }
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

    public function cambiar_estado_producto(Request $request)
    {
        $model = Producto::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>MODIFICADO</strong> el producto satisfactoriamente';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }
}
