<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use DB;

class Semana extends Model
{
    protected $table = 'semana';
    protected $primaryKey = 'id_semana';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'anno',
        'codigo',
        'fecha_inicial',
        'fecha_final',
        'curva',
        'desecho',
        'semana_poda',
        'semana_siembra',
        'fecha_registro',
        'estado',
        'id_variedad',
        'tallos_planta_siembra',
        'tallos_planta_poda',
        'tallos_ramo_siembra',
        'tallos_ramo_poda',
        'mes',
        'curva_perenne',
        'last_4_semana',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function getSemanaEmpresa($empresa, $poda_siembra = 'S')
    {
        return SemanaEmpresa::All()
            ->where('id_semana', $this->id_semana)
            ->where('id_empresa', $empresa)
            ->where('poda_siembra', $poda_siembra)
            ->first();
    }

    public function getProyNoPerennesByEmpresa($empresa)
    {
        return ProyNoPerennes::All()
            ->where('id_semana', $this->id_semana)
            ->where('id_empresa', $empresa)
            ->first();
    }

    public function getSemanaProyPerenneByFinca($finca)
    {
        return SemanaProyPerenne::All()->where('id_empresa', $finca)->where('id_semana', $this->id_semana)->first();
    }

    public function getTotalesProyeccionVentaSemanal($idsCliente, $idVariedad = null, $calculaAnnoAnterior = false, $semanaActual = false, $idsClientes = false, $ramosxCajaEmpresa = false)
    {

        $existeSemana = ProyeccionVentaSemanalReal::where('codigo_semana', $this->codigo)
            ->where(function ($query) use ($idVariedad) {
                if (isset($idVariedad))
                    $query->where('id_variedad', $idVariedad);
            })->select('codigo_semana')->exists();

        if (!$existeSemana) {
            $primeraSemana = ProyeccionVentaSemanalReal::where(function ($query) use ($idVariedad) {
                if (isset($idVariedad))
                    $query->where('id_variedad', $idVariedad);
            })->select(DB::raw('MIN(codigo_semana) as codigo'))->first();
            $this->codigo = $primeraSemana->codigo;
        }

        $proyeccion = ProyeccionVentaSemanalReal::where([
            //['id_variedad',$idVariedad],
            ['codigo_semana', $this->codigo]
        ])->where(function ($query) use ($idsCliente, $idVariedad) {
            if ($idsCliente)
                $query->whereNotIn('id_cliente', $idsCliente);
            if (isset($idVariedad))
                $query->where('id_variedad', $idVariedad);
        })->select(
            DB::raw('sum(valor) as total_valor'),
            DB::raw('sum(cajas_fisicas) as total_cajas_fisicas'),
            DB::raw('sum(cajas_equivalentes) as total_cajas_equivalentes')
        )->groupBy('codigo_semana')->first();

        $valorAnnoAnterior = 0;
        $cajasEquivalentesAnnoAnterior = 0;
        $totalCajasFisicasAnnoAterior = 0;

        if ($calculaAnnoAnterior) { //TOMA EN CUENTA LAS CAJAS DEL AÑO PASADO PARRA LA AUTO PROYECCIÓN DEL ANO ACTUAL

            $proyeccionAnnoActual = ProyeccionVentaSemanalReal::where([
                // ['id_variedad', $idVariedad],
                ['codigo_semana', $this->codigo]
            ])->where(function ($query) use ($idsClientes, $idVariedad) {
                if ($idsClientes)
                    $query->whereIn('id_cliente', $idsClientes);
                if (isset($idVariedad))
                    $query->where('id_variedad', $idVariedad);
            })->select('cajas_fisicas_anno_anterior', 'id_cliente')->get();

            foreach ($proyeccionAnnoActual as $item) {
                if ($item->cajas_fisicas == 0 && $semanaActual < $this->codigo) {

                    $cajasFisicasAnnoAnterior = $item->cajas_fisicas_anno_anterior;
                    $cajasEquivalentesAnnoAnterior += $cajasFisicasAnnoAnterior * $item->cliente->factor;
                    $totalCajasFisicasAnnoAterior += $cajasFisicasAnnoAnterior;
                    $ramosTotales = $cajasFisicasAnnoAnterior * $item->cliente->factor * $ramosxCajaEmpresa;
                    $precioPromedio = isset($idVariedad) ? $item->cliente->precio_promedio($idVariedad) : $item->cliente->precio_promedio;
                    $valorAnnoAnterior += $ramosTotales * (isset($precioPromedio->precio) ? $precioPromedio->precio : 0);
                }
            }
        }

        return [
            'valor' => (isset($proyeccion) ? $proyeccion->total_valor : 0) + $valorAnnoAnterior,
            'cajasEquivalentes' => (isset($proyeccion) ? $proyeccion->total_cajas_equivalentes : 0) + $cajasEquivalentesAnnoAnterior,
            'cajasFisicas' => (isset($proyeccion) ? $proyeccion->total_cajas_fisicas : 0) + $totalCajasFisicasAnnoAterior
        ];

    }

    public function getSaldo($idVariedad)
    {
        $semanaActual = getSemanaByDate(now()->toDateString())->codigo;
        $cajasProyectadas = $this->getCajasProyectadas($idVariedad);
        $ramosxCajaEmpresa = getConfiguracionEmpresa()->ramos_x_caja;
        $cajasVendidas = $this->getTotalesProyeccionVentaSemanal(null, $idVariedad, true, $semanaActual, false, $ramosxCajaEmpresa);

        //dump("semana: ". $this->codigo." cajaProyectadas: ".$cajasProyectadas. "  cajasVendidas: ".$cajasVendidas);

        return $cajasProyectadas - $cajasVendidas['cajasEquivalentes'] - $this->desecho($idVariedad);
    }

    public function getCajasProyectadas($idVariedad)
    {
        $semanaActual = getSemanaByDate(now()->toDateString());

        $objResumenSemanaCosecha = ResumenSemanaCosecha::where([
            ['id_variedad', $idVariedad],
            ['codigo_semana', $this->codigo - 1]
        ])->select('cajas_proyectadas', 'cajas')->first();

        if (isset($objResumenSemanaCosecha)) {
            if ($this->codigo > $semanaActual->codigo) {
                $cajasProyectadas = $objResumenSemanaCosecha->cajas_proyectadas;
            } else {
                $cajasProyectadas = $objResumenSemanaCosecha->cajas;
            }
        } else {
            for ($x = $this->codigo; $x > 0001; $x--) {
                $objResumenSemanaCosecha = ResumenSemanaCosecha::where([
                    ['id_variedad', $idVariedad],
                    ['codigo_semana', $x - 1]
                ])->select('cajas_proyectadas', 'codigo_semana')->first();
                if (isset($objResumenSemanaCosecha)) {
                    $cajasProyectadas = $objResumenSemanaCosecha->cajas_proyectadas;
                    break;
                } else {
                    $cajasProyectadas = 0;
                }
            }
        }

        return $cajasProyectadas;
    }

    public function desecho($idVariedad)
    {
        $objResumenSemanaCosecha = ResumenSemanaCosecha::where([
            ['codigo_semana', $this->codigo],
            ['id_variedad', $idVariedad]
        ])->select('desecho')->first();

        return isset($objResumenSemanaCosecha) ? $objResumenSemanaCosecha->desecho : 0;
    }

    public function getLastSaldoInicial($idVariedad, $desde)
    {
        $firstSemana = $this->firstSemanaResumenSemanaCosechaByVariedad($idVariedad);
        if ($firstSemana <= $desde) {
            $z = 0;
            $saldoInicial = 0;
            for ($x = $firstSemana; $x < $desde; $x++) {
                $semana = Semana::where([['codigo', $x], ['id_variedad', $idVariedad]])->select('codigo')->exists();
                if ($semana) {
                    if ($z == 0)
                        $saldoInicial = $this->firstSaldoInicialByVariedad($idVariedad);

                    $saldoFinal = getObjSemana($x)->getSaldo($idVariedad) + $saldoInicial;
                    if ($x > 0)
                        $saldoInicial = $saldoFinal;
                    $z++;
                }
            }
            return $saldoInicial;
        } else {
            return 0;
        }
    }

    public function getLastSaldoFinal($idVariedad, $desde)
    {
        $firstSemana = $this->firstSemanaResumenSemanaCosechaByVariedad($idVariedad);
        if ($firstSemana <= $desde) {
            $z = 0;
            $saldoInicial = 0;
            for ($x = $firstSemana; $x <= $desde; $x++) {
                $semana = Semana::where([['codigo', $x], ['id_variedad', $idVariedad]])->select('codigo')->exists();
                if ($semana) {
                    if ($z == 0)
                        $saldoInicial = $this->firstSaldoInicialByVariedad($idVariedad);

                    $saldoFinal = getObjSemana($x)->getSaldo($idVariedad) + $saldoInicial;
                    if ($x > 0)
                        $saldoInicial = $saldoFinal;
                    $z++;
                }
            }
            return $saldoInicial;
        } else {
            return 0;
        }
    }

    public function firstSemanaResumenSemanaCosechaByVariedad($idVariedad)
    {
        return ResumenSemanaCosecha::where('id_variedad', $idVariedad)
            ->select(DB::raw('MIN(codigo_semana) as codigo'))->first()->codigo;
    }

    public function firstSaldoInicialByVariedad($idVariedad)
    {
        return Variedad::find($idVariedad)->saldo_inicial;
    }

    public function cuartaSemanaFutura($idVariedad)
    {
        $semanas = Semana::where([['codigo', '>', $this->codigo], ['id_variedad', $idVariedad]])
            ->select('codigo')->limit(4)->orderBy('codigo', 'asc');

        if ($semanas->count() > 0) {
            return $semanas->get()->last()->codigo;
        } else {
            return 0;
        }

    }

    public function cajasFisicasAnnoAnterior($idVariedad, $idCliente)
    {

        /*$arrSemana = str_split($this->codigo,2);
        $anoAnterior = (int)$arrSemana[0]-1;
        $semanaAnnoAnterior =  $anoAnterior.$arrSemana[1];*/

        return ProyeccionVentaSemanalReal::where([
            ['id_variedad', $idVariedad],
            ['id_cliente', $idCliente],
            ['codigo_semana', $this->codigo]
        ])->select('cajas_fisicas_anno_anterior')->first();

    }

    /*public function getTotalesAnnoAnterior($idVariedad,$idClientes,$calcular){
        $cajasFisicas = 0;

        if($calcular){
            $objProyeccionVentaSemanalReal = ProyeccionVentaSemanalReal::where('id_variedad',$idVariedad)
                ->whereIn('id_cliente',$idClientes)
                ->select(
                    'codigo_semana',
                    DB::raw('sum(cajas_fisicas_anno_anterior) as cajas_fisicas_anno_anterior')
                )->groupBy('codigo_semana')->first();
            $cajasFisicas= $objProyeccionVentaSemanalReal->cajas_fisicas_anno_anterior;
        }
        return $cajasFisicas;

    }*/

    public function firstSaldoInicialBusqueda($idVariedad, $desde)
    {
        return ResumenSaldoProyeccionVentaSemanal::where([
            ['id_variedad', $idVariedad],
            ['codigo_semana', $desde]
        ])->select('saldo_inicial', 'saldo_final')->first();
    }

}
