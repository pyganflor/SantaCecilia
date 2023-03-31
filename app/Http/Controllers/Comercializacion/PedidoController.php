<?php

namespace yura\Http\Controllers\Comercializacion;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;
use DB;
use yura\Modelos\DetallePedido;
use yura\Modelos\ItemDetallePedido;
use yura\Modelos\Pedido;

class PedidoController extends Controller
{
    public function inicio(Request $request)
    {
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.nombre', 'c.id_cliente')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre', 'asc')
            ->get();

        $fincas = DB::table('configuracion_empresa')
            ->where('proveedor', 0)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.pedidos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'clientes' => $clientes,
            'fincas' => $fincas
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Pedido::join('detalle_cliente as c', 'c.id_cliente', '=', 'pedido.id_cliente')
            ->select('pedido.*')->distinct()
            ->where('pedido.fecha_pedido', $request->fecha);
        if ($request->cliente != '')
            $listado = $listado->where('pedido.id_cliente', $request->cliente);
        if ($request->finca != '')
            $listado = $listado->where('pedido.id_configuracion_empresa', $request->finca);
        $listado = $listado->where('c.estado', 1)
            ->orderBy('c.nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.pedidos.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function add_pedido(Request $request)
    {
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.nombre', 'c.id_cliente')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre', 'asc')
            ->get();

        $fincas = DB::table('configuracion_empresa')
            ->where('proveedor', 0)
            ->get();

        $longitudes = DB::table('clasificacion_ramo')
            ->select('nombre')
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();

        return view('adminlte.gestion.comercializacion.pedidos.forms.add_pedido', [
            'clientes' => $clientes,
            'fincas' => $fincas,
            'longitudes' => $longitudes,
        ]);
    }

    public function editar_pedido(Request $request)
    {
        $pedido = Pedido::find($request->ped);
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.nombre', 'c.id_cliente')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre', 'asc')
            ->get();
        $agencias_cliente = DB::table('cliente_agenciacarga as ca')
            ->join('agencia_carga as a', 'a.id_agencia_carga', '=', 'ca.id_agencia_carga')
            ->select('ca.id_agencia_carga', 'a.nombre')->distinct()
            ->where('ca.id_cliente', $pedido->id_cliente)
            ->get();
        $consignatarios_cliente = DB::table('cliente_consignatario as cc')
            ->join('consignatario as c', 'c.id_consignatario', '=', 'cc.id_consignatario')
            ->select('cc.id_consignatario', 'c.nombre')->distinct()
            ->where('cc.id_cliente', $pedido->id_cliente)
            ->get();
        $fincas = DB::table('configuracion_empresa')
            ->where('proveedor', 0)
            ->get();
        $plantas = DB::table('planta')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $longitudes = DB::table('clasificacion_ramo')
            ->select('nombre')
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        return view('adminlte.gestion.comercializacion.pedidos.forms.editar_pedido', [
            'pedido' => $pedido,
            'clientes' => $clientes,
            'plantas' => $plantas,
            'fincas' => $fincas,
            'longitudes' => $longitudes,
            'agencias_cliente' => $agencias_cliente,
            'consignatarios_cliente' => $consignatarios_cliente,
        ]);
    }

    public function buscar_inventario(Request $request)
    {
        $fincas = DB::table('configuracion_empresa')
            ->select('id_configuracion_empresa', 'nombre');
        if ($request->finca != '')
            $fincas = $fincas->where('id_configuracion_empresa', $request->finca);
        $fincas = $fincas->get();

        $listado = [];
        foreach ($fincas as $f) {
            $inventarios = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('configuracion_empresa as fd', 'fd.id_configuracion_empresa', '=', 'i.finca_destino')
                ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                ->select(
                    'i.id_inventario_frio',
                    'i.id_variedad',
                    'i.finca_destino',
                    'i.id_clasificacion_ramo',
                    'i.tallos_x_ramo',
                    'i.disponibles',
                    'i.fecha',
                    'fd.nombre as finca_destino_nombre',
                    'p.nombre as planta_nombre',
                    'v.nombre as variedad_nombre',
                    'c.nombre as longitud',
                )
                ->where('i.estado', 1)
                ->where('i.disponibilidad', 1)
                ->where('i.id_empresa', $f->id_configuracion_empresa)
                ->where('v.nombre', 'like', '%' . espacios(mb_strtoupper($request->buscar)) . '%');
            if ($request->longitud != '')
                $inventarios = $inventarios->where('c.nombre', $request->longitud);
            $inventarios = $inventarios->orderBy('i.finca_destino')
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->orderBy('i.fecha')
                ->get();
            if (count($inventarios) > 0)
                $listado[] = [
                    'finca' => $f,
                    'inventarios' => $inventarios,
                ];
        }
        return view('adminlte.gestion.comercializacion.pedidos.forms._buscar_inventario', [
            'listado' => $listado,
        ]);
    }

    public function store_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = new Pedido();
            $pedido->id_cliente = $request->cliente;
            $pedido->fecha_pedido = $request->fecha;
            $pedido->id_configuracion_empresa = $request->finca;
            $pedido->id_exportador = $request->finca;
            $pedido->id_agencia_carga = $request->agencia;
            $pedido->id_consignatario = $request->consignatario;
            $pedido->marcacion = mb_strtoupper(espacios($request->marcacion));
            $pedido->save();
            $pedido = Pedido::All()->last();

            foreach (json_decode($request->data) as $d) {
                $detalle = new DetallePedido();
                $detalle->id_pedido = $pedido->id_pedido;
                $detalle->cantidad = $d->cajas;
                $detalle->orden = 1;
                $detalle->id_agencia_carga = -1;
                $detalle->save();
                $detalle = DetallePedido::All()->last();

                foreach ($d->detalles as $item) {
                    $model = new ItemDetallePedido();
                    $model->id_finca = $item->finca;
                    $model->id_finca_origen = $item->finca_destino;
                    $model->id_variedad = $item->id_variedad;
                    $model->longitud = $item->longitud;
                    $model->tallos_x_ramo = $item->tallos_x_ramo;
                    $model->ramos_x_caja = $item->ramos_x_caja;
                    $model->precio = $item->precio != '' ? $item->precio : 0;
                    $model->id_detalle_pedido = $detalle->id_detalle_pedido;
                    $model->save();
                }
            }

            $success = true;
            $msg = 'Se ha <b>GUARDADO</b> el pedido correctamente';

            DB::commit();
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

    public function eliminar_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = Pedido::find($request->id_pedido);
            $pedido->delete();

            $success = true;
            $msg = 'Se ha <b>ELIMINADO</b> el pedido correctamente';

            DB::commit();
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

    public function seleccionar_cliente(Request $request)
    {
        $agencias = DB::table('cliente_agenciacarga as ca')
            ->join('agencia_carga as a', 'ca.id_agencia_carga', '=', 'a.id_agencia_carga')
            ->select('ca.id_agencia_carga', 'a.nombre')->distinct()
            ->where('ca.id_cliente', $request->cliente)
            ->where('ca.estado', 1)
            ->orderBy('a.nombre')
            ->get();
        $consignatarios = DB::table('cliente_consignatario as ca')
            ->join('consignatario as a', 'ca.id_consignatario', '=', 'a.id_consignatario')
            ->select('ca.id_consignatario', 'a.nombre')->distinct()
            ->where('ca.id_cliente', $request->cliente)
            //->where('ca.estado', 1)
            ->orderBy('a.nombre')
            ->get();
        return [
            'agencias' => $agencias,
            'consignatarios' => $consignatarios,
        ];
    }
}
