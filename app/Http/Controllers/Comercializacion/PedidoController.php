<?php

namespace yura\Http\Controllers\Comercializacion;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;
use DB;
use yura\Modelos\CajaFrio;
use yura\Modelos\DetalleCajaFrio;
use yura\Modelos\DetallePedido;
use yura\Modelos\Pedido;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\File;
use Picqer\Barcode\BarcodeGeneratorHTML;
use yura\Modelos\Aerolinea;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Storage as Almacenamiento;

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

        return view('adminlte.gestion.comercializacion.pedidos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'clientes' => $clientes,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Pedido::join('detalle_cliente as c', 'c.id_cliente', '=', 'pedido.id_cliente')
            ->select('pedido.*')->distinct()
            ->where('pedido.fecha_pedido', $request->fecha);
        if ($request->cliente != '')
            $listado = $listado->where('pedido.id_cliente', $request->cliente);
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

        $longitudes = DB::table('clasificacion_ramo')
            ->select('nombre')
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();

        return view('adminlte.gestion.comercializacion.pedidos.forms.add_pedido', [
            'clientes' => $clientes,
            'longitudes' => $longitudes,
        ]);
    }

    public function modal_exportar(Request $request)
    {
        $desde = opDiasFecha('-', 7, hoy());
        $hasta = hoy();
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
            ->join('pedido as p', 'p.id_cliente', '=', 'c.id_cliente')
            ->select('dc.nombre', 'c.id_cliente')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre', 'asc')
            ->get();

        return view('adminlte.gestion.comercializacion.pedidos.forms.modal_exportar', [
            'desde' => $desde,
            'hasta' => $hasta,
            'clientes' => $clientes,
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
            'longitudes' => $longitudes,
            'agencias_cliente' => $agencias_cliente,
            'consignatarios_cliente' => $consignatarios_cliente,
        ]);
    }

    public function buscar_inventario(Request $request)
    {
        $listado = CajaFrio::where('nombre', 'like', '%' . espacios(mb_strtoupper($request->buscar)) . '%')
            ->where('armada', 0)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.comercializacion.pedidos.forms._buscar_inventario', [
            'listado' => $listado,
        ]);
    }

    public function agregar_inventario(Request $request)
    {
        $caja = CajaFrio::find($request->id_caja);
        $caja_reservada = false;
        if ($caja != '') {
            if ($caja->reservado == 0) {
                $caja->reservado = 1;
                $caja->save();
            } else {
                $caja_reservada = true;
            }
        }
        return view('adminlte.gestion.comercializacion.pedidos.forms._agregar_inventario', [
            'caja' => $caja,
            'caja_reservada' => $caja_reservada,
            'cliente' => $request->cliente,
        ]);
    }

    public function regresar_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $caja = CajaFrio::find($request->id_caja);
            $caja->reservado = 0;
            $caja->save();

            $success = true;
            $msg = 'Se ha <b>REGRESADO</b> la caja al inventario correctamente';

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

    public function deshacer_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $caja = CajaFrio::find($d);
                $caja->reservado = 0;
                $caja->save();
            }

            $success = true;
            $msg = 'Se ha <b>CANCELADO</b> el pedido correctamente';

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

    public function store_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = new Pedido();
            $pedido->id_cliente = $request->cliente;
            $pedido->fecha_pedido = $request->fecha;
            $pedido->id_agencia_carga = $request->agencia;
            $pedido->id_consignatario = $request->consignatario;
            $pedido->marcacion = mb_strtoupper(espacios($request->marcacion));
            $pedido->save();
            $pedido = Pedido::All()->last();

            foreach (json_decode($request->data_caja) as $d) {
                $detalle = new DetallePedido();
                $detalle->id_pedido = $pedido->id_pedido;
                $detalle->id_caja_frio = $d->id;
                $detalle->marcacion_po = $d->marcacion_po;
                $detalle->orden = 1;
                $detalle->save();

                $caja = CajaFrio::find($d->id);
                $caja->armada = 1;
                $caja->save();
            }
            foreach (json_decode($request->data_precio) as $d) {
                $detalle_caja = DetalleCajaFrio::find($d->id);
                $detalle_caja->precio = $d->precio;
                $detalle_caja->save();
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
            if ($request->devolver)
                foreach ($pedido->detalles as $det) {
                    $caja = $det->caja_frio;
                    $caja->armada = 0;
                    $caja->reservado = 0;
                    $caja->save();
                }
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

    public function update_precio(Request $request)
    {
        DB::beginTransaction();
        try {
            $detalle = DetalleCajaFrio::find($request->det_caja);
            $detalle->precio = $request->precio;
            $detalle->save();

            $success = true;
            $msg = 'Se ha <b>CAMBIADO</b> el precio correctamente';

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

    public function update_marcacion_po(Request $request)
    {
        DB::beginTransaction();
        try {
            $detalle = DetallePedido::find($request->det_ped);
            $detalle->marcacion_po = $request->marcacion_po;
            $detalle->save();

            $success = true;
            $msg = 'Se ha <b>CAMBIADO</b> el PO correctamente';

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

    public function cambiar_caja(Request $request)
    {
        $detalle = DetalleCajaFrio::find($request->det);
        $cajas = CajaFrio::where('armada', 0)
            ->where('reservado', 0)
            ->where('id_caja_frio', '!=', $detalle->id_caja_frio)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.pedidos.forms.cambiar_caja', [
            'detalle' => $detalle,
            'cajas' => $cajas,
        ]);
    }

    public function eliminar_detalle_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $detalle_pedido = DetallePedido::find($request->det_ped);
            if ($request->devolver) {
                $caja = $detalle_pedido->caja_frio;
                $caja->armada = 0;
                $caja->reservado = 0;
                $caja->save();
            }
            $detalle_pedido->delete();

            $success = true;
            $msg = 'Se ha <b>ELIMINADO</b> la caja del pedido correctamente';

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

    public function add_caja(Request $request)
    {
        $listado = CajaFrio::where('armada', 0)
            ->orderBy('id_empresa')
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.pedidos.forms.add_caja', [
            'listado' => $listado
        ]);
    }

    public function agregar_caja(Request $request)
    {
        DB::beginTransaction();
        try {
            $detalle = new DetallePedido();
            $detalle->id_pedido = $request->pedido;
            $detalle->id_caja_frio = $request->id_caja;
            $detalle->marcacion_po = $request->marcacion_po;
            $detalle->orden = 1;
            $detalle->save();

            $caja = CajaFrio::find($request->id_caja);
            $caja->armada = 1;
            $caja->reservado = 1;
            $caja->save();

            $pedido = Pedido::find($request->pedido);
            foreach ($caja->detalles as $det_caja) {
                $precio = getPrecioByClienteLongitudVariedad($pedido->id_cliente, $det_caja->longitud, $det_caja->id_variedad);
                if ($precio != '') {
                    $det_caja->precio = $precio;
                    $det_caja->save();
                }
            }

            $success = true;
            $msg = 'Se ha <b>AGREGADO</b> la caja al pedido correctamente';

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

    public function update_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = Pedido::find($request->ped);
            $pedido->id_cliente = $request->cliente;
            $pedido->fecha_pedido = $request->fecha;
            $pedido->id_agencia_carga = $request->agencia;
            $pedido->id_consignatario = $request->consignatario;
            $pedido->marcacion = mb_strtoupper(espacios($request->marcacion));
            $pedido->save();

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

    public function generar_packing(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');
        $barCode = new BarcodeGeneratorHTML();
        $id_pedido = $request->ped;
        $pedido = Pedido::find($id_pedido);
        if ($pedido->codigo_dae != '' && $pedido->guia_madre != '' && $pedido->guia_hija != '' || 1) {
            if ($pedido->packing <= 0) {
                $last_packing = DB::table('pedido')
                    ->select(DB::raw('max(packing) as last'))
                    ->get()[0]->last;
                $packing = $last_packing;
                $packing++;
                $pedido->packing = $packing;
                $pedido->save();
            }
            if ($pedido->factura <= 0) {
                $last_factura = DB::table('pedido')
                    ->select(DB::raw('max(factura) as last'))
                    ->get()[0]->last;
                $factura = $last_factura;
                $factura++;
                $pedido->factura = $factura;
            }
            $datos = [
                'pedido' => $pedido,
            ];
            return PDF::loadView('adminlte.gestion.comercializacion.pedidos.partials.pdf_packing', compact('datos', 'barCode'))
                ->setPaper(array(0, 0, 450, 400), 'landscape')->stream();
        } else {
            return 'Es Necesario ingresar la DAE y las guias';
        }
    }

    public function generar_factura(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');
        $barCode = new BarcodeGeneratorHTML();
        $id_pedido = $request->ped;
        $pedido = Pedido::find($id_pedido);
        if ($pedido->packing <= 0) {
            $last_packing = DB::table('pedido')
                ->select(DB::raw('max(packing) as last'))
                ->get()[0]->last;
            $packing = $last_packing;
            $packing++;
            $pedido->packing = $packing;
        }
        if ($pedido->factura <= 0) {
            $last_factura = DB::table('pedido')
                ->select(DB::raw('max(factura) as last'))
                ->get()[0]->last;
            $factura = $last_factura;
            $factura++;
            $pedido->factura = $factura;
        }
        $pedido->save();
        $datos = [
            'pedido' => $pedido,
        ];

        $pdf = PDF::loadView('adminlte.gestion.comercializacion.pedidos.partials.pdf_factura', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 450, 400), 'landscape');

        return $pdf->stream();
    }

    public function exportar_factura(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_factura($spread, $request);

        $fileName = "Factura.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_factura($spread, $request)
    {
        $id_pedido = $request->ped;
        $pedido = Pedido::find($id_pedido);
        if ($pedido->packing <= 0) {
            $last_packing = DB::table('pedido')
                ->select(DB::raw('max(packing) as last'))
                ->get()[0]->last;
            $packing = $last_packing;
            $packing++;
            $pedido->packing = $packing;
        }
        if ($pedido->factura <= 0) {
            $last_factura = DB::table('pedido')
                ->select(DB::raw('max(factura) as last'))
                ->get()[0]->last;
            $factura = $last_factura;
            $factura++;
            $pedido->factura = $factura;
        }
        $pedido->save();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('FACTURA');

        $generator  = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode(str_pad($pedido->factura, 8, '0', STR_PAD_LEFT), $generator::TYPE_CODE_128, 2, 60);
        $nombre_archivo = 'codigo_de_barras.png';
        Almacenamiento::disk('file_loads')->put($nombre_archivo, $barcode);

        $barcodePath = public_path('storage/file_loads/codigo_de_barras.png'); // Ruta al archivo de la imagen del código de barras
        $drawing = new Drawing();
        $drawing->setPath($barcodePath);
        $drawing->setCoordinates('A1'); // Ubicación en la hoja de cálculo
        $drawing->setWorksheet($sheet);

        $sheet->mergeCells('A1:A3');
        setValueToCeldaExcel($sheet, 'B1', 'ECSTACECILIA-ROSES');
        $sheet->getStyle('B1')->getFont()->setSize(16);
        $sheet->mergeCells('B1:J4');
        setValueToCeldaExcel($sheet, 'A4', 'INVOICE: ' . str_pad($pedido->factura, 8, '0', STR_PAD_LEFT));
        setValueToCeldaExcel($sheet, 'A5', 'CUSTOMER:');
        setValueToCeldaExcel($sheet, 'B5', $pedido->cliente->detalle()->nombre);
        setValueToCeldaExcel($sheet, 'E5', 'M.A.W.B::');
        setValueToCeldaExcel($sheet, 'F5', $pedido->guia_madre);
        setValueToCeldaExcel($sheet, 'A6', 'COUNTRY:');
        setValueToCeldaExcel($sheet, 'B6', $pedido->consignatario->pais()->nombre);
        setValueToCeldaExcel($sheet, 'E6', 'H.A.W.B:');
        setValueToCeldaExcel($sheet, 'F6', $pedido->guia_hija);
        setValueToCeldaExcel($sheet, 'A7', 'DAE:');
        setValueToCeldaExcel($sheet, 'B7', $pedido->codigo_dae);
        setValueToCeldaExcel($sheet, 'E7', 'CARRIER:');
        setValueToCeldaExcel($sheet, 'F7', $pedido->agencia_carga->nombre);
        setValueToCeldaExcel($sheet, 'I7', 'DATE:');
        setValueToCeldaExcel($sheet, 'J7', $pedido->fecha_pedido);

        $row = 8;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#BOX');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'BOX.T');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FLOWER');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIETIES');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'NANDINA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'LENGHT');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'BUNCH');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'STEMS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'UNIT. $');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL');

        $total_tallos = 0;
        $total_ramos = 0;
        $total_monto = 0;
        foreach ($pedido->detalles as $pos_det => $det) {
            $caja_frio = $det->caja_frio;
            $detalles = $caja_frio->detalles;
            foreach ($detalles as $pos_item => $item) {
                $variedad = getVariedad($item->id_variedad);
                $planta = $variedad->planta;
                $total_tallos += $item->ramos * $item->tallos_x_ramo;
                $total_ramos += $item->ramos;
                $total_monto += $item->precio * $item->ramos * $item->tallos_x_ramo;

                $row++;
                $col = 0;
                if ($pos_item == 0) {
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pos_det + 1);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja_frio->tipo);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles) - 1));
                }
                $col = 2;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $planta->nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $variedad->nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $planta->nandina);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->ramos);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->ramos * $item->tallos_x_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->precio);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($item->precio * $item->ramos * $item->tallos_x_ramo, 2));
            }
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALS');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 5] . $row);
        $col = 6;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col += 2;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_monto, 2));

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A8:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_etiqueta(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $id_pedido = $request->ped;
        $pedido = Pedido::find($id_pedido);
        if ($pedido->packing <= 0) {
            $last_packing = DB::table('pedido')
                ->select(DB::raw('max(packing) as last'))
                ->get()[0]->last;
            $packing = $last_packing;
            $packing++;
            $pedido->packing = $packing;
            $pedido->save();
        }
        if ($pedido->factura <= 0) {
            $last_factura = DB::table('pedido')
                ->select(DB::raw('max(factura) as last'))
                ->get()[0]->last;
            $factura = $last_factura;
            $factura++;
            $pedido->factura = $factura;
        }
        $datos = [
            'pedido' => $pedido,
        ];
        return PDF::loadView('adminlte.gestion.comercializacion.pedidos.partials.pdf_etiqueta', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 360, 220), 'landscape')->stream();
    }

    public function generar_prefactura(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $id_pedido = $request->ped;
        $pedido = Pedido::find($id_pedido);
        if ($pedido->codigo_dae != '' && $pedido->guia_madre != '' && $pedido->guia_hija != '' || 1) {
            if ($pedido->packing <= 0) {
                $last_packing = DB::table('pedido')
                    ->select(DB::raw('max(packing) as last'))
                    ->get()[0]->last;
                $packing = $last_packing;
                $packing++;
                $pedido->packing = $packing;
                $pedido->save();
            }
            $aerolinea = Aerolinea::All()
                ->where('codigo', substr($pedido->guia_madre, 0, 3))
                ->first();
            $getResumenTipoCaja = $pedido->getResumenTipoCaja();
            $datos = [
                'pedido' => $pedido,
                'getResumenTipoCaja' => $getResumenTipoCaja,
                'aerolinea' => $aerolinea != '' ? $aerolinea->nombre : '',
            ];
            return PDF::loadView('adminlte.gestion.comercializacion.pedidos.partials.pdf_prefactura', compact('datos', 'barCode'))
                ->setPaper(array(0, 0, 600, 500), 'landscape')->stream();
        } else {
            return 'Es Necesario ingresar la DAE y las guias';
        }
    }

    public function exportar_pedidos(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_pedidos($spread, $request);

        $fileName = "PEDIDOS.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_pedidos($spread, $request)
    {
        $listado = Pedido::where('fecha_pedido', '>=', $request->desde)
            ->where('fecha_pedido', '<=', $request->hasta);
        if ($request->cliente != 'T')
            $listado = $listado->where('id_cliente', $request->cliente);
        $listado = $listado->orderBy('fecha_pedido')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('PEDIDOS');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Factura');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Consignatario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Carguera');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Pais');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Dae');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Guia Madre');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Guia Hija');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FB');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Valor Total');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Valor por Tallo');

        foreach ($listado as $item) {
            $getTotales = $item->getTotales();
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_pedido);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, str_pad($item->factura, 8, '0', STR_PAD_LEFT));
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cliente->detalle()->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->consignatario->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->agencia_carga->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->consignatario->pais()->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->codigo_dae);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->guia_madre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->guia_hija);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $getTotales['full_box']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $getTotales['tallos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . $getTotales['monto']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . round($getTotales['monto'] / $getTotales['tallos'], 2));
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_resumen_pedidos(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_resumen_pedidos($spread, $request);

        $fileName = "Cuarto_Frío.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_resumen_pedidos($spread, $request)
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
        $resumen_variedades = [];
        foreach ($listado as $pos_ped => $ped) {
            foreach ($ped->detalles as $pos_det => $det) {
                $caja_frio = $det->caja_frio;
                foreach ($caja_frio->detalles as $pos_item => $item) {
                    $variedad = $item->variedad;
                    $pos_en_resumen = -1;
                    foreach ($resumen_variedades as $pos => $r) {
                        if ($r['variedad']->id_variedad == $item->id_variedad && $r['longitud'] == $item->longitud) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_variedades[$pos_en_resumen]['tallos'] += $item->ramos * $item->tallos_x_ramo;
                        $resumen_variedades[$pos_en_resumen]['ramos'] += $item->ramos;
                        $resumen_variedades[$pos_en_resumen]['monto'] += $item->ramos * $item->tallos_x_ramo * $item->precio;
                    } else {
                        $resumen_variedades[] = [
                            'variedad' => $variedad,
                            'longitud' => $item->longitud,
                            'tallos' => $item->ramos * $item->tallos_x_ramo,
                            'ramos' => $item->ramos,
                            'monto' => $item->ramos * $item->tallos_x_ramo * $item->precio,
                        ];
                    }
                }
            }
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('RESUMEN_PEDIDOS');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Monto');

        $total_tallos = 0;
        $total_ramos = 0;
        $total_monto = 0;
        foreach ($resumen_variedades as $r) {
            $total_tallos += $r['tallos'];
            $total_ramos += $r['ramos'];
            $total_monto += $r['monto'];

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['longitud']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['tallos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['ramos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['monto']);
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $col += 2;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_monto, 2));

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_estado_cliente(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_estado_cliente($spread, $request);

        $fileName = "PEDIDOS CLIENTE.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_estado_cliente($spread, $request)
    {
        $listado = Pedido::where('fecha_pedido', '>=', $request->desde)
            ->where('fecha_pedido', '<=', $request->hasta)
            ->where('id_cliente', $request->cliente)
            ->orderBy('fecha_pedido')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('PEDIDOS');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Factura');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Consignatario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FB');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Valor Total');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Valor por Tallo');

        $total_fb = 0;
        $total_tallos = 0;
        $total_monto = 0;
        foreach ($listado as $item) {
            $getTotales = $item->getTotales();
            $total_fb += $getTotales['full_box'];
            $total_tallos += $getTotales['tallos'];
            $total_monto += $getTotales['monto'];
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_pedido);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, str_pad($item->factura, 8, '0', STR_PAD_LEFT));
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cliente->detalle()->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->consignatario->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $getTotales['full_box']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $getTotales['tallos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . $getTotales['monto']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . round($getTotales['monto'] / $getTotales['tallos'], 2));
        }

        $row++;
        setValueToCeldaExcel($sheet, 'A' . $row, 'TOTALES');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $col = 4;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_fb);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . $total_monto);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . round($total_monto / $total_tallos, 2));

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
