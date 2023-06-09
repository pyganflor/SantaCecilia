<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Area;
use yura\Modelos\OtrosGastos;
use yura\Modelos\Semana;

class UpdateOtrosGastos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otros_gastos:update {desde=0} {hasta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar los otros gastos';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "otros_gastos:update" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "otros_gastos:update" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');

        if ($desde_par <= $hasta_par) {
            if ($desde_par != 0)
                $semana_desde = Semana::All()->where('estado', 1)->where('codigo', $desde_par)->first();
            else
                $semana_desde = getSemanaByDate(opDiasFecha('-', 21, hoy()));
            if ($hasta_par != 0)
                $semana_hasta = Semana::All()->where('estado', 1)->where('codigo', $hasta_par)->first();
            else
                $semana_hasta = getSemanaByDate(hoy());

            Log::info('SEMANA PARAMETRO DESDE: ' . $desde_par . ' => ' . $semana_desde->codigo);
            Log::info('SEMANA PARAMETRO HASTA: ' . $hasta_par . ' => ' . $semana_hasta->codigo);

            $array_semanas = DB::table('semana')
                ->select('codigo')->distinct()
                ->where('estado', 1)
                ->where('codigo', '>=', $semana_desde->codigo)
                ->where('codigo', '<=', $semana_hasta->codigo)
                ->get();

            $areas = Area::where('estado', 1)->get();
            foreach ($areas as $pos_a => $area) {
                foreach ($array_semanas as $pos_s => $sem) {
                    dump('area: ' . ($pos_a + 1) . '/' . count($areas) . ' - sem: ' . ($pos_s + 1) . '/' . count($array_semanas));
                    $gastos_anterior = $area->getOtrosGastosLastSemana($sem->codigo);
                    $model = $area->otrosGastosBySemana($sem->codigo);
                    if ($model == '') {
                        $model = new OtrosGastos();
                        $model->id_area = $area->id_area;
                        $model->codigo_semana = $sem->codigo;
                        $model->id_empresa = $area->id_empresa;
                        $model->gip = $gastos_anterior != '' ? $gastos_anterior->gip : 0;
                        $model->ga = $gastos_anterior != '' ? $gastos_anterior->ga : 0;
                        $model->regalias = $gastos_anterior != '' ? $gastos_anterior->regalias : 0;
                    } else {
                        if ($model->gip == 0)
                            $model->gip = $gastos_anterior != '' ? $gastos_anterior->gip : 0;
                        if ($model->ga == 0)
                            $model->ga = $gastos_anterior != '' ? $gastos_anterior->ga : 0;
                        if ($model->regalias == 0)
                            $model->regalias = $gastos_anterior != '' ? $gastos_anterior->regalias : 0;
                    }
                    $model->save();
                }
            }
        }
        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "otros_gastos:update" <<<<< * >>>>>');
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "otros_gastos:update" <<<<< * >>>>>');
    }
}