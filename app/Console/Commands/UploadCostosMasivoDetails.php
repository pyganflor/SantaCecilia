<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\Actividad;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\ActividadProducto;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\ManoObra;
use yura\Modelos\Producto;
use yura\Modelos\ControlPersonal;
use yura\Modelos\CostosDiarioManoObra;

use Carbon\Carbon;

class UploadCostosMasivoDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costos:importar_file_details {url=0} {concepto=0} {criterio=0} {sobreescribir=0} {finca=0}';

    /**
     * url = nombre completo del archivo
     * concepto => I, insumos _ M, mano de obra
     * criterio => V, dinero _ C, cantidad
     * sobreescribir => S, si _ I, sumar a lo anterior
     * finca => id de la empresa a la que pertenecen los costos
     *
     * @var string
     */
    protected $description = 'Comando para subir los costos mediante un excel con los detalles por fecha';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "costos:importar_file_details" <<<<< ! >>>>>');

        $url = $this->argument('url');
        $concepto_importar = $this->argument('concepto');
        $criterio_importar = $this->argument('criterio');
        $sobreescribir = $this->argument('sobreescribir');
        $finca = $this->argument('finca');

        // $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);
        if ($concepto_importar == 'I') {
            dump("mal");
            //$this->importar_insumos($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir, $finca);
        } else {
            dump("bien");
            $this->importar_mano_obra();

            // $this->importar_mano_obra($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir, $finca);
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "costos:importar_file_details" <<<<< * >>>>>');
    }

    public function importar_insumos($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir = false, $finca)
    {
        $titles = $activeSheetData[1];
        $lista = [];
        $faltantes = [];
        foreach ($activeSheetData as $pos_row => $row) {
            if ($pos_row > 1) {
                $finca = ConfiguracionEmpresa::All()->where('nombre', $row['A'])->first();
                if ($finca != '') {
                    dump($row);
                    $anno = explode('.', $row['B'])[2];
                    $mes = explode('.', $row['B'])[1];
                    $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                    $dia = explode('.', $row['B'])[0];
                    $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                    $fecha = $anno . '-' . $mes . '-' . $dia;
                    $semana = getSemanaByDate($fecha);
                    $actividad = Actividad::All()
                        ->where('nombre', espacios(mb_strtoupper($row['C'])))
                        ->where('id_empresa', $finca->id_configuracion_empresa)
                        ->first();  //query
                    if ($semana != '') {
                        if ($actividad != '') {
                            $producto = Producto::All()
                                ->where('nombre', espacios(mb_strtoupper($row['D'])))
                                ->where('id_empresa', $finca->id_configuracion_empresa)
                                ->first();  //query
                            if ($producto != '') {
                                dump('pos: ' . $pos_row . '/' . count($activeSheetData) . '-' . porcentaje($pos_row, count($activeSheetData), 1) . '% - fecha: ' . $fecha . ' - sem: ' . $semana->codigo . ' - act: ' . $actividad->nombre . ' - prod: ' . $producto->nombre);
                                $existe = false;
                                for ($i = 0; $i < count($lista); $i++) {
                                    if ($lista[$i]['semana'] == $semana->codigo && $lista[$i]['actividad']->id_actividad == $actividad->id_actividad && $lista[$i]['producto']->id_producto == $producto->id_producto && $lista[$i]['finca'] == $finca->id_configuracion_empresa) {
                                        $lista[$i]['cantidad'] += floatval(str_replace(',', '', $row['F']));
                                        $lista[$i]['valor'] += floatval(str_replace(',', '', $row['E']));
                                        $existe = true;
                                    }
                                }
                                if (!$existe) {
                                    array_push($lista, [
                                        'finca' => $finca->id_configuracion_empresa,
                                        'semana' => $semana->codigo,
                                        'actividad' => $actividad,
                                        'producto' => $producto,
                                        'cantidad' => floatval(str_replace(',', '', $row['F'])),
                                        'valor' => floatval(str_replace(',', '', $row['E'])),
                                    ]);
                                }

                                /* guardar en la tabla COSTOS_DIARIO_PRODUCTO */
                                //$model = Cos
                            } else {
                                dump('pos: ' . $pos_row . '****************** ERROR *******************');
                                dump('En la Finca: "' . $finca->nombre . '", no se ha encontrado el PRODUCTO: "' . espacios(mb_strtoupper($row['D'])) . '"');
                                if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado el PRODUCTO: ' . espacios(mb_strtoupper($row['D'])), $faltantes))
                                    array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado el PRODUCTO: ' . espacios(mb_strtoupper($row['D'])));
                            }
                        } else {
                            dump('pos: ' . $pos_row . '****************** ERROR *******************');
                            dump('En la finca: "' . $finca->nombre . '", no se ha encontrado la ACTIVIDAD: "' . espacios(mb_strtoupper($row['C'])) . '"');
                            if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['C'])), $faltantes))
                                array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['C'])));
                        }
                    } else {
                        dump('pos: ' . $pos_row . '****************** ERROR *******************');
                        dump('No se ha encontrado la SEMANA de la FECHA: "' . $row['B'] . '"');
                    }
                } else {
                    dump('pos: ' . $pos_row . '****************** ERROR *******************');
                    dump('No se ha encontrado la FINCA: "' . $row['A'] . '"');
                    if (!in_array('No se ha encontrado la FINCA: ' . $row['A'], $faltantes))
                        array_push($faltantes, 'No se ha encontrado la FINCA: ' . $row['A']);
                }
            }
        }
        dump('=========== GUARDAR DATOS ===========');
        foreach ($lista as $pos_item => $item) {
            $act_prod = ActividadProducto::All()
                ->where('id_actividad', $item['actividad']->id_actividad)
                ->where('id_producto', $item['producto']->id_producto)
                ->first();
            if ($act_prod == '') {
                $act_prod = new ActividadProducto();
                $act_prod->id_actividad = $item['actividad']->id_actividad;
                $act_prod->id_producto = $item['producto']->id_producto;
                $act_prod->save();
                $act_prod = ActividadProducto::All()->last();
            }
            $costo_semana = CostosSemana::All()
                ->where('id_empresa', $item['finca'])
                ->where('codigo_semana', $item['semana'])
                ->where('id_actividad_producto', $act_prod->id_actividad_producto)
                ->first();
            if ($costo_semana == '') {
                $costo_semana = new CostosSemana();
                $costo_semana->codigo_semana = $item['semana'];
                $costo_semana->id_actividad_producto = $act_prod->id_actividad_producto;
                $costo_semana->valor = 0;
                $costo_semana->cantidad = 0;
                $costo_semana->id_empresa = $item['finca'];
            }
            if ($sobreescribir == 'S') {
                $costo_semana->valor = $item['valor'];
            } else {
                $costo_semana->valor += $item['valor'];
            }
            dump($pos_item . '/' . count($lista) . '-' . porcentaje($pos_item, count($lista), 1) . '% - act_prod: ' . $act_prod->id_actividad_producto . ' - act: ' . $item['actividad']->nombre . ' - prod: ' . $item['producto']->nombre . ' -sem: ' . $item['semana'] . ' - valor: ' . $item['valor']);
            $costo_semana->save();
        }
        if (count($faltantes) > 0) {
            dump('=========== FALTANTES ===========');
            dump($faltantes);
            /* ------------ ACTUALIZR NOTIFICACION fallos_upload_insumos --------------- */
            NotificacionesSistema::fallos_upload_insumos($faltantes);
        }
    }

    public function importar_mano_obra()
    {
        $lista = [];
        $faltantes = [];
        $empresas = ConfiguracionEmpresa::All();
        
        foreach ($empresas as $p => $finca) 
        {
            if ($finca != '') 
            {
                $controles_personal = ControlPersonal::join('personal_detalle', 'control_personal.id_personal_detalle', '=', 'personal_detalle.id_personal_detalle')
                ->select('control_personal.*', 'personal_detalle.*')
                ->orderBy('personal_detalle.id_personal')
                ->get();

                foreach($controles_personal as $pos_cp => $control)
                {
                    $semana = getSemanaByDate($control->fecha);
                    $hora1 = Carbon::parse($control->desde);
                    $hora2 = Carbon::parse($control->hasta);
                    $diferencia = $hora1->diff($hora2);
                    $horas = $diferencia->h; // Obtener las horas completas
                    $minutos = $diferencia->i; // Obtener los minutos
                    // Convertir los minutos a una fracción de hora
                    $fraccion_minutos = $minutos / 60;
                    // Calcular la diferencia total en horas, incluyendo los minutos como decimales
                    $diferencia_horas = $horas + $fraccion_minutos;
                    $cantidad_horas_laboradas_full = $diferencia_horas - ($control->time_lunch / 60);

                    $cantidad_horas_laboradas = $cantidad_horas_laboradas_full > 8 ? 8 : $cantidad_horas_laboradas_full;
                    $cantidad_horas_extra = $cantidad_horas_laboradas_full > 8 ? $cantidad_horas_laboradas_full - 8 : 0;
                    
                    // $semana = getSemanaByDate("2023-04-17");
                    if ($semana != '') {
                        $actividad = Actividad::All()
                            ->where('id_actividad', $control->id_actividad)
                            ->where('id_empresa', $finca->id_configuracion_empresa)
                            ->first();  //query
                        if ($actividad != '') {
                            $mo = ManoObra::All()
                                ->where('id_mano_obra', $control->id_mano_obra)
                                ->where('id_empresa', $finca->id_configuracion_empresa)
                                ->first();  //query
                            if ($mo != '') {
                                /* CALCULAR TOTAL */
                                $fecha = Carbon::parse($control->fecha);
                                $anno = $fecha->format('Y'); // Obtener el año
                                $mes = $fecha->format('m'); // Obtener el mes
                                $dias_lab_mes = count(bussiness_days($anno . '-' . $mes . '-01', $anno . '-' . $mes . '-' . getUltimoDiaMes($anno, $mes))[$anno . '-' . $mes]);
                                $hor_efect_mes = $dias_lab_mes * 8; // CantDiasLaborables/Mes *8(hrs/dia)
                                $cost_hr_ord = 450 / $hor_efect_mes;  // Sueldo / HorasEfectivasMes // OJO
                                $cost_hr_supl = 450 / 240;    // Sueldo / 240(30dias * 8) // OJO
                                $cantidad_horas = getDiaLaboral($control->fecha) ? $cantidad_horas_laboradas : 0;
                                $cantidad_horas_50 = getDiaLaboral($control->fecha)? $cantidad_horas_extra : 0;
                                $cantidad_horas_100 = !getDiaLaboral($control->fecha)? $cantidad_horas_laboradas_full : 0;
                                $cost_dia_pers = (getDiaLaboral($control->fecha) ? $cantidad_horas_laboradas : 0) * $cost_hr_ord;   // HoraNormal * CostHrsOrdinaria
                                $cost_dia_50_pers = (getDiaLaboral($control->fecha)? $cantidad_horas_extra : 0) * (1.5 * $cost_hr_supl); // Hrs50  * (50% de CostHrOrdinaria)
                                $cost_dia_100_pers = (!getDiaLaboral($control->fecha)? $cantidad_horas_laboradas_full : 0) * (2 * $cost_hr_supl); // Hrs100  * (100% de CostHrOrdinaria)
                                $cost_dia_aus_pers = 0 * $cost_hr_ord;    // HrsAusentismo * CostHrsOrdinaria
                                $cost_total_dia_pers = $cost_dia_pers + $cost_dia_50_pers + $cost_dia_100_pers + $cost_dia_aus_pers;    // CostoTotalDiaPersona
                                if ($control->fecha_ingreso != "") {
                                    $fecha_ingreso = $control->fecha_ingreso;
                                    dump('fecha: ' . $fecha . ' || fecha_ingreso: ' . $fecha_ingreso);
                                    $cant_anno_activo = difFechas($fecha, $fecha_ingreso)->days / 365;  // CantidadAnnosActivo
                                    $prov_13th = $cost_total_dia_pers / 12;  //  Provision 13º
                                    $prov_14th = 450 / 261;  //  Sueldo Basico en el pais / ... // OJO
                                    $fondos_reserva = $cant_anno_activo > 1 ? $prov_13th : 0;
                                    $aporte_patronal = (12.15 * $cost_total_dia_pers) / 100;   //  12.15% de CostoTotalDiaPersona
                                    $total = $cost_total_dia_pers + $prov_13th + $prov_14th + $fondos_reserva + $aporte_patronal;   //  TOTAL

                                    // Crear una nueva instancia del modelo CostosDiarioManoObra
                                    $costoDiario = new CostosDiarioManoObra();

                                    $act_mo = ActividadManoObra::All()
                                    ->where('id_actividad', $control->id_actividad)
                                    ->where('id_mano_obra', $control->id_mano_obra)
                                    ->first();
                                    if ($act_mo == '') {
                                        $act_mo = new ActividadManoObra();
                                        $act_mo->id_actividad = $control->id_actividad;
                                        $act_mo->id_mano_obra = $control->id_mano_obra;
                                        $act_mo->save();
                                        $act_mo = ActividadManoObra::All()->last();
                                    }
                                    $costo_diario_mo = CostosDiarioManoObra::All()
                                    ->where('id_control_personal', $control->id_control_personal)
                                    ->first();
                                    if ($costo_diario_mo == '') {
                                        // Setear los valores   
                                        $costoDiario->id_actividad_mano_obra = $act_mo->id_actividad_mano_obra;
                                        $costoDiario->id_control_personal = $control->id_control_personal;
                                        $costoDiario->fecha = $control->fecha;
                                        $costoDiario->codigo_semana = $semana->codigo;
                                        $costoDiario->valor = $total;
                                        $costoDiario->cantidad = 1;
                                        $costoDiario->cantidad_horas = $cantidad_horas;
                                        $costoDiario->cantidad_horas_50 = $cantidad_horas_50;
                                        $costoDiario->cantidad_horas_100 = $cantidad_horas_100;
                                        $costoDiario->id_personal = $control->id_personal;
                                        $costoDiario->id_empresa = $finca->id_configuracion_empresa;
                                        $costoDiario->valor_50 = $cost_dia_50_pers;
                                        $costoDiario->valor_100 = $cost_dia_100_pers;

                                        // Guardar en la tabla
                                        $costoDiario->save();
                                    } else {
                                        // Actualizar los valores
                                        $costo_diario_mo->id_actividad_mano_obra = $act_mo->id_actividad_mano_obra;
                                        $costo_diario_mo->fecha = $control->fecha;
                                        $costo_diario_mo->codigo_semana = $semana->codigo;
                                        $costo_diario_mo->valor = $total;
                                        $costo_diario_mo->cantidad = 1;
                                        $costo_diario_mo->cantidad_horas = $cantidad_horas;
                                        $costo_diario_mo->cantidad_horas_50 = $cantidad_horas_50;
                                        $costo_diario_mo->cantidad_horas_100 = $cantidad_horas_100;
                                        $costo_diario_mo->id_personal = $control->id_personal;
                                        $costo_diario_mo->id_empresa = $finca->id_configuracion_empresa;
                                        $costo_diario_mo->valor_50 = $cost_dia_50_pers;
                                        $costo_diario_mo->valor_100 = $cost_dia_100_pers;
                                    
                                        // Guardar los cambios en la tabla
                                        $costo_diario_mo->update();
                                    }
                                   

                                    /*dump('$dias_lab_mes = ' . $dias_lab_mes);
                                    dump('$hor_efect_mes = ' . $hor_efect_mes);
                                    dump('$cost_hr_ord = ' . $cost_hr_ord);
                                    dump('$cost_hr_supl = ' . $cost_hr_supl);
                                    dump('$cost_dia_pers = ' . $cost_dia_pers);
                                    dump('$cost_dia_50_pers = ' . $cost_dia_50_pers);
                                    dump('$cost_dia_100_pers = ' . $cost_dia_100_pers);
                                    dump('$cost_dia_aus_pers = ' . $cost_dia_aus_pers);
                                    dump('$cost_total_dia_pers = ' . $cost_total_dia_pers);
                                    dump('$prov_13th = ' . $prov_13th);
                                    dump('$prov_14th = ' . $prov_14th);
                                    dump('$fondos_reserva = ' . $fondos_reserva);
                                    dump('fecha_ingreso: ' . $fecha_ingreso . ' || fecha: ' . $fecha . ' = dias: ' . difFechas($fecha, $fecha_ingreso)->days . ' || annos: ' . $cant_anno_activo);
                                    dump('$aporte_patronal = ' . $aporte_patronal);*/
                                    // dump('pos: ' . $p . '/' . count($activeSheetData) . '-' . porcentaje($p, count($activeSheetData), 1) . '% - fecha: ' . $fecha . ' - sem: ' . $semana->codigo . ' - act: ' . $actividad->nombre . ' - mo: ' . $mo->nombre . ' - TOTAL: ' . $total);
                                    //dump('$total: '. $total, '$cost_total_dia_pers: '.$cost_total_dia_pers, '$prov_13th: '.$prov_13th, '$prov_14th: '.$prov_14th, '$fondos_reserva: '.$fondos_reserva, '$aporte_patronal: '.$aporte_patronal);
                                    $existe = false;
                                    $aux_id_personal = 0;
                                    for ($i = 0; $i < count($lista); $i++) {
                                        if ($lista[$i]['semana'] == $semana->codigo && $lista[$i]['actividad']->id_actividad == $actividad->id_actividad && $lista[$i]['mano_obra']->id_mano_obra == $mo->id_mano_obra && $lista[$i]['finca'] == $finca->id_configuracion_empresa) {
                                            $lista[$i]['valor'] += $total;
                                            $lista[$i]['valor_50'] += $cost_dia_50_pers;
                                            $lista[$i]['valor_100'] += $cost_dia_100_pers;
                                            $lista[$i]['cantidad_horas'] += $cantidad_horas;
                                            $lista[$i]['cantidad_horas_50'] += $cantidad_horas_50;
                                            $lista[$i]['cantidad_horas_100'] += $cantidad_horas_100;
                                            $lista[$i]['cantidad'] += 1;
                                            if($control->id_personal != $aux_id_personal) {
                                               $aux_id_personal = $control->id_personal;
                                               $lista[$i]['cantidad_personal']++;
                                            }
                                            $existe = true;
                                        }
                                    }
                                    if (!$existe) {
                                        array_push($lista, [
                                            'finca' => $finca->id_configuracion_empresa,
                                            'semana' => $semana->codigo,
                                            'actividad' => $actividad,
                                            'mano_obra' => $mo,
                                            'valor' => $total,
                                            'valor_50' => $cost_dia_50_pers,
                                            'valor_100' => $cost_dia_100_pers,
                                            'cantidad_horas' => $cantidad_horas,
                                            'cantidad_horas_50' => $cantidad_horas_50,
                                            'cantidad_horas_100' => $cantidad_horas_100,
                                            'cantidad' => 1,
                                            'cantidad_personal' => 1
                                        ]);
                                    }
                                } else {
                                    dump('pos: ' . $p . '****************** ERROR *******************');
                                    dump('En la fila: "' . $p . '", la fecha de ingreso no es correcta');
                                    if (!in_array('En la fila: "' . $p . '", la fecha de ingreso no es correcta', $faltantes))
                                        array_push($faltantes, 'En la fila: ' . $p . ', la fecha de ingreso no es correcta');
                                }
                            } else {
                                dump('pos: ' . $p . '****************** ERROR *******************');
                                dump('En la Finca: "' . $finca->nombre . '", no se ha encontrado la MANO de OBRA: "' . $control->id_mano_obra . '"');
                                if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado la MANO de OBRA: ' . $control->id_mano_obra, $faltantes))
                                    array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado la MANO de OBRA: ' . $control->id_mano_obra);
                            }
                        } else {
                            dump('pos: ' . $p . '****************** ERROR *******************');
                            dump('En la finca: "' . $finca->nombre . '", no se ha encontrado la ACTIVIDAD: "' . espacios(mb_strtoupper($control->id_actividad)) . '"');
                            if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($control->id_actividad)), $faltantes))
                                array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($control->id_actividad)));
                        }
                    } else {
                        dump('pos: ' . $p . '****************** ERROR *******************');
                        dump('No se ha encontrado la SEMANA de la FECHA: "' . $control->fecha . '"');
                    }
                }
            } else {
                dump('pos: ' . $p . '****************** ERROR *******************');
                dump('No se ha encontrado la FINCA.');
                if (!in_array('No se ha encontrado la FINCA.', $faltantes))
                    array_push($faltantes, 'No se ha encontrado la FINCA.');
            }
        }

        dump('=========== GUARDAR DATOS ===========');
        $sobreescribir="";
        foreach ($lista as $pos_item => $item) {
            $act_mo = ActividadManoObra::All()
                ->where('id_actividad', $item['actividad']->id_actividad)
                ->where('id_mano_obra', $item['mano_obra']->id_mano_obra)
                ->first();
            if ($act_mo == '') {
                $act_mo = new ActividadManoObra();
                $act_mo->id_actividad = $item['actividad']->id_actividad;
                $act_mo->id_mano_obra = $item['mano_obra']->id_mano_obra;
                $act_mo->save();
                $act_mo = ActividadManoObra::All()->last();
            }
            $costo_semana = CostosSemanaManoObra::All()
                ->where('id_empresa', $item['finca'])
                ->where('codigo_semana', $item['semana'])
                ->where('id_actividad_mano_obra', $act_mo->id_actividad_mano_obra)
                ->first();
            if ($costo_semana == '') {
                $costo_semana = new CostosSemanaManoObra();
                $costo_semana->codigo_semana = $item['semana'];
                $costo_semana->id_actividad_mano_obra = $act_mo->id_actividad_mano_obra;
                $costo_semana->valor = 0;
                $costo_semana->cantidad = $item['cantidad'];
                $costo_semana->id_empresa = $item['finca'];
            }
            if ($sobreescribir == 'S') {
                $costo_semana->cantidad = $item['cantidad'];
                $costo_semana->valor = $item['valor'];
                $costo_semana->valor_50 = $item['valor_50'];
                $costo_semana->valor_100 = $item['valor_100'];
                $costo_semana->cantidad_horas = $item['cantidad_horas'];
                $costo_semana->cantidad_horas_50 = $item['cantidad_horas_50'];
                $costo_semana->cantidad_horas_100 = $item['cantidad_horas_100'];
                $costo_semana->cantidad_personal = $item['cantidad_personal'];
            } else {
                $costo_semana->cantidad += $item['cantidad'];
                $costo_semana->valor += $item['valor'];
                $costo_semana->valor_50 += $item['valor_50'];
                $costo_semana->valor_100 += $item['valor_100'];
                $costo_semana->cantidad_horas = $item['cantidad_horas'];
                $costo_semana->cantidad_horas_50 = $item['cantidad_horas_50'];
                $costo_semana->cantidad_horas_100 = $item['cantidad_horas_100'];
                $costo_semana->cantidad_personal = $item['cantidad_personal'];
            }
            dump(($pos_item + 1) . '/' . count($lista) . '-' . porcentaje($pos_item, count($lista), 1) . '% - act_mo: ' . $act_mo->id_actividad_mano_obra . ' - act: ' . $item['actividad']->nombre . ' - prod: ' . $item['mano_obra']->nombre . ' -sem: ' . $item['semana'] . ' - valor: ' . $item['valor']);
            $costo_semana->save();
        }
        if (count($faltantes) > 0) {
            dump('=========== FALTANTES ===========');
            dump($faltantes);
            /* ------------ ACTUALIZR NOTIFICACION fallos_upload_insumos --------------- */
            NotificacionesSistema::fallos_upload_mano_obra($faltantes);
        }
    }
}