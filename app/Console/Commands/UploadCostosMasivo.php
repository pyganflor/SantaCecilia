<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\Actividad;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\ActividadProducto;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\ManoObra;
use yura\Modelos\Producto;

class UploadCostosMasivo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costos:importar_file {url=0} {concepto=0} {criterio=0} {sobreescribir=0} {finca=0}';

    /**
     * url = nombre completo del archivo
     * concepto => I, insumos _ M, mano de obra
     * criterio => V, dinero _ C, cantidad
     * finca => id de la empresa a la que pertenecen los costos
     *
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para subir los costos mediante un excel con el resumen de totales por semana';

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
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "costos:importar_file" <<<<< ! >>>>>');

        $url = $this->argument('url');
        $concepto_importar = $this->argument('concepto');
        $criterio_importar = $this->argument('criterio');
        $sobreescribir = $this->argument('sobreescribir') === '0' ? false : true;
        $finca = $this->argument('finca');

        $document = IOFactory::load($url);
        $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

        $this->importar($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir, $finca);

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "costos:importar_file" <<<<< * >>>>>');
    }

    public function importar($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir = false, $finca)
    {
        $titles = $activeSheetData[1];
        foreach ($activeSheetData as $pos_row => $row) {
            if ($pos_row > 1) {
                if ($row['A'] != '' && $row['B'] != '') {
                    $actividad = Actividad::All()->where('estado', 1)
                        ->where('nombre', str_limit(mb_strtoupper(espacios($row['A'])), 50))->first();
                    if ($concepto_importar == 'I') { // insumos
                        $producto = Producto::All()->where('estado', 1)
                            ->where('nombre', str_limit(mb_strtoupper(espacios($row['B'])), 250))->first();
                        /*if ($producto == '') {
                            $producto = new Producto();
                            $producto->nombre = str_limit(mb_strtoupper(espacios($row['B'])), 250);
                            $producto->fecha_registro = date('Y-m-d');
                            $producto->save();
                        }*/
                        $concepto = 'insumo';
                    } else {    // mano de obra
                        $producto = ManoObra::All()->where('estado', 1)
                            ->where('nombre', str_limit(mb_strtoupper(espacios($row['B'])), 250))->first();
                        $concepto = 'mano de obra';
                    }
                    if ($actividad != '' && $producto != '') {
                        if ($concepto_importar == 'I') // insumos
                            $act_prod = ActividadProducto::All()
                                ->where('estado', 1)
                                ->where('id_actividad', $actividad->id_actividad)
                                ->where('id_producto', $producto->id_producto)
                                ->first();
                        else    // mano de obra
                            $act_prod = ActividadManoObra::All()
                                ->where('estado', 1)
                                ->where('id_actividad', $actividad->id_actividad)
                                ->where('id_mano_obra', $producto->id_mano_obra)
                                ->first();

                        if ($act_prod == '') {
                            if ($concepto_importar == 'I') { // insumos
                                $model = new ActividadProducto();
                                $model->id_producto = $producto->id_producto;
                            } else {    // mano de obra
                                $model = new ActividadManoObra();
                                $model->id_mano_obra = $producto->id_mano_obra;
                            }
                            $model->id_actividad = $actividad->id_actividad;
                            $model->fecha_registro = date('Y-m-d H:i:s');
                            $model->save();
                            if ($concepto_importar == 'I') {   // insumos
                                $act_prod = ActividadProducto::All()->last();
                                bitacora('actividad_producto', $act_prod->id_actividad_producto, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_producto');
                            } else {    // mano de obra
                                $act_prod = ActividadManoObra::All()->last();
                                bitacora('actividad_mano_obra', $act_prod->id_actividad_mano_obra, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_mano_obra');
                            }
                        }

                        foreach ($titles as $pos_title => $t) {
                            if (!in_array($pos_title, ['A', 'B'])) {
                                $codigo_semana = intval($t);
                                if ($codigo_semana > 0) {
                                    $value = floatval(str_replace(',', '', $row[$pos_title]));
                                    if ($concepto_importar == 'I') // insumos
                                        $costos = CostosSemana::All()
                                            ->where('id_empresa', $finca)
                                            ->where('codigo_semana', $codigo_semana)
                                            ->where('id_actividad_producto', $act_prod->id_actividad_producto)
                                            ->first();
                                    else    // mano de obra
                                        $costos = CostosSemanaManoObra::All()
                                            ->where('id_empresa', $finca)
                                            ->where('codigo_semana', $codigo_semana)
                                            ->where('id_actividad_mano_obra', $act_prod->id_actividad_mano_obra)
                                            ->first();
                                    if ($costos == '') {    // es nuevo
                                        if ($concepto_importar == 'I') { // insumos
                                            $model = new CostosSemana();
                                            $model->id_actividad_producto = $act_prod->id_actividad_producto;
                                            $model->id_empresa = $finca;
                                        } else {    // mano de obra
                                            $model = new CostosSemanaManoObra();
                                            $model->id_actividad_mano_obra = $act_prod->id_actividad_mano_obra;
                                            $model->id_empresa = $finca;
                                        }
                                        $model->codigo_semana = $codigo_semana;
                                        $model->fecha_registro = date('Y-m-d H:i:s');
                                        if ($criterio_importar == 'V')  // dinero
                                            $model->valor = $value;
                                        else    // cantidad
                                            $model->cantidad = $value;

                                        $model->save();
                                        if ($concepto_importar == 'I') { // insumos
                                            $costos = CostosSemana::All()->last();
                                            bitacora('costos_semana', $costos->id_costos_semana, 'I', 'Inserción satisfactoria de un nuevo costos_semana');
                                        } else {    // mano de obra
                                            $costos = CostosSemanaManoObra::All()->last();
                                            bitacora('costos_semana_mano_obra', $costos->id_costos_semana_mano_obra, 'I', 'Inserción satisfactoria de un nuevo costos_semana_mano_obra');
                                        }
                                    } else {    // ya existe
                                        if ($sobreescribir == true) {
                                            if ($criterio_importar == 'V')  // dinero
                                                $costos->valor = $value;
                                            else    // cantidad
                                                $costos->cantidad = $value;

                                            $costos->save();
                                            if ($concepto_importar == 'I') // insumos
                                                bitacora('costos_semana', $costos->id_costos_semana, 'U', 'Modificación satisfactoria de un costos_semana');
                                            else    // mano de obra
                                                bitacora('costos_semana_mano_obra', $costos->id_costos_semana_mano_obra, 'U', 'Modificación satisfactoria de un costos_semana_mano_obra');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
