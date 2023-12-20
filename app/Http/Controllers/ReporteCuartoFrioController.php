<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use Validator;
use Illuminate\Support\Facades\Storage as Almacenamiento;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\InventarioFrio;

class ReporteCuartoFrioController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.reporte_cuarto_frio.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::where('estado', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $plantas = Planta::where('estado', 1);
        if ($request->planta != '')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('nombre')->get();

        $dias = [0, 1, 2, 3, 4, 5, 6, 7];
        $listado = [];
        foreach ($plantas as $p) {
            if ($request->tipo == 'F') { // cuarto frio
                $variedades = DB::table('inventario_frio as i')
                    ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.id_variedad', 'v.nombre', 'c.nombre as longitud')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.disponibles', '>', 0)
                    ->where('c.nombre', '!=', 'Baja')
                    ->where('c.nombre', '!=', 'Nacional')
                    ->where('v.id_planta', $p->id_planta);
                if ($request->variedad != '')
                    $variedades = $variedades->where('i.id_variedad', $request->variedad);
                $variedades = $variedades->orderBy('v.nombre')
                    ->get();
            } else {    // flor nacional
                $variedades = DB::table('inventario_frio as i')
                    ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.id_variedad', 'v.nombre', 'c.nombre as longitud')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.disponibles', '>', 0)
                    ->where('c.nombre', '!=', 'Baja')
                    ->where('c.nombre', '=', 'Nacional')
                    ->where('v.id_planta', $p->id_planta);
                if ($request->variedad != '')
                    $variedades = $variedades->where('i.id_variedad', $request->variedad);
                $variedades = $variedades->orderBy('v.nombre')
                    ->get();
            }

            $valores_var = [];
            foreach ($variedades as $v) {
                $valores_dias = [];
                $tiene = false;
                foreach ($dias as $pos => $dia) {
                    if ($request->tipo == 'F') {    // cuarto frio
                        $cantidad = DB::table('inventario_frio as i')
                            ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                            ->select(DB::raw('sum(i.disponibles) as cantidad'))
                            ->where('i.disponibilidad', 1)
                            ->where('i.basura', 0)
                            ->where('i.estado', 1)
                            ->where('i.id_variedad', $v->id_variedad)
                            ->where('c.nombre', $v->longitud);
                        if ($pos == count($dias) - 1)
                            $cantidad = $cantidad->where('i.fecha', '<=', opDiasFecha('-', $dia, hoy()));
                        else
                            $cantidad = $cantidad->where('i.fecha', opDiasFecha('-', $dia, hoy()));
                        $cantidad = $cantidad->get()[0]->cantidad;
                    } else {    // flor nacional
                        $cantidad = DB::table('inventario_frio as i')
                            ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                            ->select(DB::raw('sum(i.disponibles) as cantidad'))
                            ->where('i.disponibilidad', 1)
                            ->where('i.basura', 0)
                            ->where('i.estado', 1)
                            ->where('i.id_variedad', $v->id_variedad)
                            ->where('c.nombre', '=', 'Nacional');
                        if ($pos == count($dias) - 1)
                            $cantidad = $cantidad->where('i.fecha', '<=', opDiasFecha('-', $dia, hoy()));
                        else
                            $cantidad = $cantidad->where('i.fecha', opDiasFecha('-', $dia, hoy()));
                        $cantidad = $cantidad->get()[0]->cantidad;
                    }
                    $valores_dias[] = $cantidad;
                    if ($cantidad > 0)
                        $tiene = true;
                }
                if ($tiene)
                    $valores_var[] = [
                        'variedad' => $v,
                        'valores_dias' => $valores_dias,
                    ];
            }
            if (count($valores_var) > 0)
                $listado[] = [
                    'planta' => $p,
                    'valores_var' => $valores_var,
                ];
        }
        return view('adminlte.gestion.reporte_cuarto_frio.partials.listado', [
            'listado' => $listado,
            'dias' => $dias,
            'tipo' => $request->tipo
        ]);
    }

    public function importar_bajas(Request $request)
    {
        return view('adminlte.gestion.reporte_cuarto_frio.forms.bajas', []);
    }

    public function importar_file_bajas(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_bajas' => 'required',
        ]);
        $msg = '<div class="alert alert-success text-center">Se ha importado el archivo. Revise su contenido antes de procesarlo.</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_bajas;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = "upload_bajas_cuarto_frio." . $extension;
                $r1 = Almacenamiento::disk('file_loads')->put($nombre_archivo, \File::get($archivo));
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'DOMDocument::loadHTML(): Invalid char in CDATA') !== false)
                    $mensaje_error = 'Problema con el archivo excel';
                else
                    $mensaje_error = $e->getMessage();
                return [
                    'mensaje' => '<div class="alert alert-danger text-center">' .
                        '<p>¡Ha ocurrido un problema al subir el archivo, contacte al administrador del sistema!</p>' .
                        '<legend style="font-size: 0.9em; color: white; margin-bottom: 2px">mensaje de error</legend>' .
                        $mensaje_error .
                        '</div>',
                    'success' => false
                ];
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function get_importar_file_bajas(Request $request)
    {
        $url = public_path('storage\file_loads\upload_bajas_cuarto_frio.csv');
        $document = IOFactory::load($url);
        $sheet = $document->getActiveSheet()->toArray(null, true, true, true);
        $listado = [];
        $fallos = false;
        foreach ($sheet as $pos => $row) {
            if ($pos > 1) {
                $nombre_variedad = mb_strtoupper(espacios(explode(' (', $row['A'])[0]));
                $longitud = espacios(explode(' (', $row['A'])[1]);
                $longitud = explode('cm)', $longitud)[0];
                $variedad = Variedad::All()
                    ->where('estado', 1)
                    ->where('nombre', $nombre_variedad)
                    ->first();
                $listado[] = [
                    'model_variedad' => $variedad,
                    'nombre_variedad' => $nombre_variedad,
                    'fallos' => $variedad == '' ? true : false,
                    'longitud' => $longitud,
                    'ramos' => $row['B'],
                    'tallos' => $row['C'],
                ];
                if ($variedad == '' || $row['B'] <= 0 || $row['C'] <= 0)
                    $fallos = true;
            }
        }
        return view('adminlte.gestion.reporte_cuarto_frio.forms.file_bajas', [
            'listado' => $listado,
            'fallos' => $fallos,
        ]);
    }

    public function store_bajas(Request $request)
    {
        $finca = getFincaActiva();
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $inventarios = InventarioFrio::join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'inventario_frio.id_clasificacion_ramo')
                    ->select('inventario_frio.*')
                    ->where('inventario_frio.estado', '=', 1)
                    ->where('inventario_frio.disponibilidad', '=', 1)
                    ->where('inventario_frio.id_variedad', '=', $d->id_variedad)
                    ->where('c.nombre', $d->longitud)
                    ->where('inventario_frio.tallos_x_ramo', $d->tallos_x_ramo)
                    ->where('inventario_frio.id_empresa', $finca)
                    ->orderBy('inventario_frio.fecha_registro', 'asc')
                    ->get();
                $pedido = $d->ramos;
                foreach ($inventarios as $l) {
                    if ($pedido > 0) {
                        $disponible = $l->disponibles;
                        $disponibilidad = 1;
                        if ($pedido >= $disponible) {
                            $pedido = $pedido - $disponible;
                            $disponible = 0;
                        } else {
                            $disponible = $disponible - $pedido;
                            $pedido = 0;
                        }
                        if ($disponible == 0)
                            $disponibilidad = 0;

                        $model = InventarioFrio::find($l->id_inventario_frio);
                        $model->disponibles = $disponible;
                        $model->disponibilidad = $disponibilidad;
                        $model->save();
                        //bitacora('inventario_frio', $model->id_inventario_frio, 'U', 'Actualizacion de un inventario en frio');
                    }
                }
            }
            DB::commit();
            $success = true;
            $msg = 'Se ha guardado el registro correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-warning text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema </p>
                        <p><strong>Error:</strong> ' . $e->getMessage() . 'en la línea ' . $e->getLine() . ' del archivo ' . $e->getFile() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Cuarto_Frío.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $plantas = Planta::where('estado', 1);
        if ($request->planta != '')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('nombre')->get();

        $dias = [0, 1, 2, 3, 4, 5, 6, 7];
        $listado = [];
        foreach ($plantas as $p) {
            if ($request->tipo == 'F') { // cuarto frio
                $variedades = DB::table('inventario_frio as i')
                    ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.id_variedad', 'v.nombre', 'c.nombre as longitud')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.disponibles', '>', 0)
                    ->where('c.nombre', '!=', 'Baja')
                    ->where('c.nombre', '!=', 'Nacional')
                    ->where('v.id_planta', $p->id_planta);
                if ($request->variedad != '')
                    $variedades = $variedades->where('i.id_variedad', $request->variedad);
                $variedades = $variedades->orderBy('v.nombre')
                    ->get();
            } else {    // flor nacional
                $variedades = DB::table('inventario_frio as i')
                    ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                    ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                    ->select('i.id_variedad', 'v.nombre', 'c.nombre as longitud')->distinct()
                    ->where('i.estado', 1)
                    ->where('i.disponibles', '>', 0)
                    ->where('c.nombre', '!=', 'Baja')
                    ->where('c.nombre', '=', 'Nacional')
                    ->where('v.id_planta', $p->id_planta);
                if ($request->variedad != '')
                    $variedades = $variedades->where('i.id_variedad', $request->variedad);
                $variedades = $variedades->orderBy('v.nombre')
                    ->get();
            }

            $valores_var = [];
            foreach ($variedades as $v) {
                $valores_dias = [];
                $tiene = false;
                foreach ($dias as $pos => $dia) {
                    if ($request->tipo == 'F') {    // cuarto frio
                        $cantidad = DB::table('inventario_frio as i')
                            ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                            ->select(DB::raw('sum(i.disponibles) as cantidad'))
                            ->where('i.disponibilidad', 1)
                            ->where('i.basura', 0)
                            ->where('i.estado', 1)
                            ->where('i.id_variedad', $v->id_variedad)
                            ->where('c.nombre', $v->longitud);
                        if ($pos == count($dias) - 1)
                            $cantidad = $cantidad->where('i.fecha', '<=', opDiasFecha('-', $dia, hoy()));
                        else
                            $cantidad = $cantidad->where('i.fecha', opDiasFecha('-', $dia, hoy()));
                        $cantidad = $cantidad->get()[0]->cantidad;
                    } else {    // flor nacional
                        $cantidad = DB::table('inventario_frio as i')
                            ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                            ->select(DB::raw('sum(i.disponibles) as cantidad'))
                            ->where('i.disponibilidad', 1)
                            ->where('i.basura', 0)
                            ->where('i.estado', 1)
                            ->where('i.id_variedad', $v->id_variedad)
                            ->where('c.nombre', '=', 'Nacional');
                        if ($pos == count($dias) - 1)
                            $cantidad = $cantidad->where('i.fecha', '<=', opDiasFecha('-', $dia, hoy()));
                        else
                            $cantidad = $cantidad->where('i.fecha', opDiasFecha('-', $dia, hoy()));
                        $cantidad = $cantidad->get()[0]->cantidad;
                    }
                    $valores_dias[] = $cantidad;
                    if ($cantidad > 0)
                        $tiene = true;
                }
                if ($tiene)
                    $valores_var[] = [
                        'variedad' => $v,
                        'valores_dias' => $valores_dias,
                    ];
            }
            if (count($valores_var) > 0)
                $listado[] = [
                    'planta' => $p,
                    'valores_var' => $valores_var,
                ];
        }
        $tipo = $request->tipo;

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Cuarto Frío');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad/Medida');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $totales_dias = [];
        foreach ($dias as $pos => $l) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $l . $pos == count($dias) - 1 ? '...' : '');
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            $totales_dias[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            foreach ($item['valores_var'] as $var) {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['variedad']->nombre);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['variedad']->nombre);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $total_var = 0;
                foreach ($var['valores_dias'] as $pos => $val) {
                    $total_var += $val;
                    $totales_dias[$pos] += $val;
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_var);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $col++;
        $total = 0;
        foreach ($totales_dias as $v) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            $total += $v;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A' . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function ver_inventario(Request $request)
    {
        $clasificacion_ramo = ClasificacionRamo::All()
            ->where('nombre', $request->longitud)
            ->first();
        $listado = InventarioFrio::where('id_variedad', $request->variedad)
            ->where('fecha', $request->fecha)
            ->where('id_clasificacion_ramo', $clasificacion_ramo->id_clasificacion_ramo)
            ->where('disponibles', '>', 0)
            ->where('basura', 0)
            ->get();
        return view('adminlte.gestion.reporte_cuarto_frio.forms.ver_inventario', [
            'listado' => $listado,
            'tipo' => $request->tipo,
            'variedad' => $request->variedad,
            'fecha' => $request->fecha,
            'longitud' => $request->longitud,
        ]);
    }

    public function botar_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = InventarioFrio::find($request->id);
            if ($model->disponibles <= $request->cantidad) {
                $model->cantidad_basura = $model->disponibles;
                $model->disponibles = 0;
                $model->disponibilidad = 0;
                $model->basura = 1;
            } else {
                $model->disponibles -= $request->cantidad;
                $basura = new InventarioFrio();
                $basura->fecha = hoy();
                $basura->cantidad = $model->clasificacion_ramo->nombre == 'Nacional' ? 1 : $request->cantidad;
                $basura->disponibles = $request->cantidad;
                $basura->id_variedad = $model->id_variedad;
                $basura->id_modulo = $model->id_modulo;
                $basura->id_clasificacion_ramo = $model->id_clasificacion_ramo;
                $basura->id_motivos_nacional = $model->id_motivos_nacional;
                $basura->tallos_x_ramo = $model->clasificacion_ramo->nombre == 'Nacional' ? $request->cantidad : $model->tallos_x_ramo;
                $basura->disponibilidad = 0;
                $basura->basura = 1;
                $basura->cantidad_basura = $request->cantidad;
                $basura->id_empresa = getFincaActiva();
                $basura->save();
            }
            $model->save();

            $success = true;
            $msg = 'Se ha guardado el registro correctamente';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-warning text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema </p>
                        <p><strong>Error:</strong> ' . $e->getMessage() . 'en la línea ' . $e->getLine() . ' del archivo ' . $e->getFile() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }
}
