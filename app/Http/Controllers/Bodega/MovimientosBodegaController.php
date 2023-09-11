<?php

namespace yura\Http\Controllers\Bodega;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\IngresoBodega;
use yura\Modelos\Modulo;
use yura\Modelos\Producto;
use yura\Modelos\SalidaBodega;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;

class MovimientosBodegaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bodega.movimientos_bodega.inicio', [
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

        return view('adminlte.gestion.bodega.movimientos_bodega.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function add_ingresos(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Producto::where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.bodega.movimientos_bodega.forms.add_ingresos', [
            'listado' => $listado,
        ]);
    }

    public function store_ingresos(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $d) {
                $model = Producto::find($d->id_prod);
                $model->disponibles += $d->unidades;
                $model->save();
                bitacora('producto', $model->id_producto, 'U', 'INGRESO A BODEGA de ' . $d->unidades . ' UNIDADES');

                /* INGRESO_BODEGA */
                $ingreso = new IngresoBodega();
                $ingreso->id_producto = $d->id_prod;
                $ingreso->fecha = $request->fecha;
                $ingreso->cantidad = $d->unidades;
                $ingreso->precio = $d->precio_compra != '' ? $d->precio_compra : 0;
                $ingreso->save();
                $ingreso = IngresoBodega::All()->last();
                bitacora('ingreso_bodega', $ingreso->id_ingreso_bodega, 'I', 'INGRESO A BODEGA de ' . $d->unidades . ' UNIDADES de ' . $model->nombre);
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los ingresos correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function add_salidas(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Producto::where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        $sectores = DB::table('ciclo_cama as cc')
            ->join('cama as c', 'c.id_cama', '=', 'cc.id_cama')
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->join('sector as s', 's.id_sector', '=', 'm.id_sector')
            ->select('m.id_sector', 's.nombre')->distinct()
            ->where('s.estado', 1)
            ->where('m.estado', 1)
            ->where('c.estado', 1)
            ->where('cc.activo', 1)
            ->orderBy('s.nombre')
            ->get();
        return view('adminlte.gestion.bodega.movimientos_bodega.forms.add_salidas', [
            'listado' => $listado,
            'sectores' => $sectores,
        ]);
    }

    public function store_salidas(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $d) {
                $producto = Producto::find($d->id_prod);
                if ($producto->disponibles >= $d->unidades) {
                    $producto->disponibles -= $d->unidades;
                    $producto->save();
                    bitacora('producto', $producto->id_producto, 'U', 'SALIDA DE BODEGA de ' . $d->unidades . ' UNIDADES');

                    /* SALIDA_BODEGA */
                    $ingreso = new SalidaBodega();
                    $ingreso->id_producto = $d->id_prod;
                    $ingreso->fecha = $request->fecha;
                    $ingreso->cantidad = $d->unidades;
                    $ingreso->id_modulo = $d->modulo;
                    $ingreso->save();
                    $ingreso = SalidaBodega::All()->last();
                    bitacora('salida_bodega', $ingreso->id_salida_bodega, 'I', 'SALIDA A BODEGA de ' . $d->unidades . ' UNIDADES de ' . $producto->nombre);
                } else {
                    DB::rollBack();
                    $success = false;
                    $msg = '<div class="alert alert-danger text-center">La cantidad a sacar del producto: <b>' . $producto->nombre . '</b> es <b>MAYOR</b> que el <strong>DISPONIBLE</strong> en bodega</div>';

                    return [
                        'success' => $success,
                        'mensaje' => $msg,
                    ];
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> las salidas correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function seleccionar_sector(Request $request)
    {
        $modulos = DB::table('ciclo_cama as cc')
            ->join('cama as c', 'c.id_cama', '=', 'cc.id_cama')
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->select('c.id_modulo', 'm.nombre')->distinct()
            ->where('m.id_sector', $request->sector)
            ->where('m.estado', 1)
            ->where('c.estado', 1)
            ->where('cc.activo', 1)
            ->orderBy('m.nombre')
            ->get();
        $options = '<option value="">Seleccione</option>';
        foreach ($modulos as $s)
            $options .= '<option value="' . $s->id_modulo . '">' . $s->nombre . '</option>';
        return [
            'modulos' => $options,
        ];
    }
}
