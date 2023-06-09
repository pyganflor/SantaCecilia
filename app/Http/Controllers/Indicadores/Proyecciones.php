<?php

namespace yura\Http\Controllers\Indicadores;

use Carbon\Carbon;
use http\Env\Request;
use yura\Console\Commands\VentaSemanalReal;
use yura\Http\Controllers\Controller;
use yura\Modelos\Pedido;
use yura\Modelos\ProyeccionVentaSemanalReal;
use yura\Modelos\ResumenSemanaCosecha;
use yura\Modelos\Indicador;
use DB;

class Proyecciones extends Controller
{
    public static function sumCajasFuturas4Semanas($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ResumenSemanaCosecha::whereBetween('codigo_semana',[$intervalos['primeraSemanaFutura'],$intervalos['cuartaSemanaFutura']])
            ->select(DB::Raw('sum(cajas_proyectadas) as cajas'))->first();

        $objInidicardor = Indicador::where('nombre','DP1');
        $objInidicardor->update(['valor'=>number_format($dato->cajas,2,".","")]);
    }

    public static function sumTallosFuturos4Semanas($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ResumenSemanaCosecha::whereBetween('codigo_semana',[$intervalos['primeraSemanaFutura'],$intervalos['cuartaSemanaFutura']])
            ->select(DB::Raw('sum(tallos_proyectados) as tallos'))->first();
        $objInidicardor = Indicador::where('nombre','DP2');
        $objInidicardor->update(['valor'=>number_format($dato->tallos,2,".","")]);
    }

    public static function sumCajasVendidas($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ProyeccionVentaSemanalReal::whereBetween('codigo_semana',[$intervalos['primeraSemanaFutura'],$intervalos['cuartaSemanaFutura']])
            ->select(DB::Raw('sum(cajas_equivalentes) as cajas'))->first();
        $objInidicardor = Indicador::where('nombre','DP3');
        $objInidicardor->update(['valor'=>number_format($dato->cajas,2,".","")]);
    }

    public static function sumDineroGeneradoVentas($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ProyeccionVentaSemanalReal::whereBetween('codigo_semana',[$intervalos['primeraSemanaFutura'],$intervalos['cuartaSemanaFutura']])
            ->select(DB::Raw('sum(valor) as valor'))->first();
        $objInidicardor = Indicador::where('nombre','DP4');
        $objInidicardor->update(['valor'=>number_format($dato->valor,2,".","")]);
    }

    public static function sumTallosCosechadosFuturo1Semana($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ResumenSemanaCosecha::where('codigo_semana',$intervalos['primeraSemanaFutura'])
            ->select(DB::Raw('sum(tallos_proyectados) as tallos'))->first();
        $objInidicardor = Indicador::where('nombre','DP6');
        $objInidicardor->update(['valor'=>number_format($dato->tallos,2,".","")]);
    }

    public static function sumCajasVendidasFuturas1Semana($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ProyeccionVentaSemanalReal::where('codigo_semana',$intervalos['primeraSemanaFutura'])
            ->select(DB::Raw('sum(cajas_equivalentes) as cajas'))->first();

        $objInidicardor = Indicador::where('nombre','DP7');
        $objInidicardor->update(['valor'=>number_format($dato->cajas,2,".","")]);
    }

    public static function sumCajasCosechadasFuturas1Semana($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ResumenSemanaCosecha::where('codigo_semana',$intervalos['primeraSemanaFutura'])
            ->select(DB::Raw('sum(cajas_proyectadas) as cajas'))->first();

        $objInidicardor = Indicador::where('nombre','DP8');
        $objInidicardor->update(['valor'=>number_format($dato->cajas,2,".","")]);
    }

    public static function sumDineroGeneradoFuturo1Semana($indicador_par){
        $intervalos = self::intervalosTiempo();
        $dato = ProyeccionVentaSemanalReal::where('codigo_semana',$intervalos['primeraSemanaFutura'])
            ->select(DB::Raw('sum(valor) as valor'))->first();

        $objInidicardor = Indicador::where('nombre','DP9');
        $objInidicardor->update(['valor'=>number_format($dato->valor,2,".","")]);
    }

    public static function proyeccionVentaFutura3Meses($indicador_par, $returnData=false){
        $primerMesSiguiente = Carbon::parse(now())->addMonth()->toDateString();
        $SegundoMesSiguiente = Carbon::parse($primerMesSiguiente)->addMonth()->toDateString();
        $tercerMesSiguiente = Carbon::parse($SegundoMesSiguiente)->addMonth()->toDateString();
        $data=[];

        //-------------PRIMER MES SIGUIENTE--------------//
        $inicio = Carbon::parse($primerMesSiguiente)->startOfMonth()->toDateString();
        $fin = Carbon::parse($primerMesSiguiente)->endOfMonth()->toDateString();
        $valor=0;
        $pedidos = Pedido::where('estado',1)->whereBetween('fecha_pedido',[$inicio,$fin])->get();
        foreach($pedidos as $pedido)
            $valor+= $pedido->getPrecioByPedido();

        $nombreMes= getMeses()[Carbon::parse($primerMesSiguiente)->format('n')-1];
        $data['primer_mes']=['mes'=>$nombreMes,'valor'=>$valor];

        //-------------SEGUNDO MES SIGUIENTE--------------//
        $inicio =Carbon::parse($SegundoMesSiguiente)->startOfMonth()->toDateString();
        $fin =Carbon::parse($SegundoMesSiguiente)->endOfMonth()->toDateString();
        $valor=0;
        $pedidos = Pedido::where('estado',1)->whereBetween('fecha_pedido',[$inicio,$fin])->get();
        foreach($pedidos as $pedido)
            $valor+= $pedido->getPrecioByPedido();

        $nombreMes= getMeses()[Carbon::parse($SegundoMesSiguiente)->format('n')-1];
        $data['segundo_mes']=['mes'=>$nombreMes,'valor'=>$valor];;

        //-------------TERCER MES SIGUIENTE--------------//
        $inicio =Carbon::parse($tercerMesSiguiente)->startOfMonth()->toDateString();
        $fin =Carbon::parse($tercerMesSiguiente)->endOfMonth()->toDateString();
        $valor=0;
        $pedidos = Pedido::where('estado',1)->whereBetween('fecha_pedido',[$inicio,$fin])->get();
        foreach($pedidos as $pedido)
            $valor+= $pedido->getPrecioByPedido();

        $nombreMes= getMeses()[Carbon::parse($tercerMesSiguiente)->format('n')-1];
        $data['tercer_mes']=['mes'=>$nombreMes,'valor'=>$valor];

        if($returnData){
            return $data;
        }else{
            $objInidicardor = Indicador::where('nombre','DP5');
            $objInidicardor->update(['valor'=>$data['primer_mes']['mes'].":".$data['primer_mes']['valor']."|".$data['segundo_mes']['mes'].":".$data['segundo_mes']['valor']."|".$data['tercer_mes']['mes'].":".$data['tercer_mes']['valor']]);
        }
    }

    public static function intervalosTiempo(){
        $fechaActual =now()->toDateString();
        Info('Intervalo de busqueda, desde: '.Carbon::Parse($fechaActual)->addDays(7)->toDateString(). " Hasta: ".opDiasFecha('+', 28,  $fechaActual));
        return [
            'primeraSemanaFutura' =>getSemanaByDate(Carbon::Parse($fechaActual)->addDays(7)->toDateString())->codigo,
            'cuartaSemanaFutura' =>getSemanaByDate(opDiasFecha('+', 28,  $fechaActual))->codigo
        ];
    }

}
