<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Submenu;

class ReporteFlorNacionalController extends Controller
{
    public function inicio(Request $request)
    {
        $motivos = DB::table('motivos_nacional')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $variedades = DB::table('flor_nacional as fn')
            ->join('variedad as v', 'v.id_variedad', '=', 'fn.id_variedad')
            ->select('fn.id_variedad', 'v.nombre')->distinct()
            ->where('v.estado', 1)
            ->orderBy('v.nombre')
            ->get();
        return view('adminlte.crm.reporte_flor_nacional.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'motivos' => $motivos,
            'variedades' => $variedades,
            'desde' => opDiasFecha('-', 7, hoy()),
            'hasta' => opDiasFecha('-', 1, hoy()),
        ]);
    }

    public function listar_reporte(Request $request)
    {
        if ($request->tipo == 'V') {    // reporte por variedades
            $variedades = DB::table('flor_nacional as fn')
                ->join('variedad as v', 'v.id_variedad', '=', 'fn.id_variedad')
                ->select('fn.id_variedad', 'v.nombre')->distinct()
                ->where('fn.fecha', '>=', $request->desde)
                ->where('fn.fecha', '<=', $request->hasta);
            if ($request->variedad != '')
                $variedades = $variedades->where('fn.id_variedad', $request->variedad);
            if ($request->motivo != '')
                $variedades = $variedades->where('fn.id_motivos_nacional', $request->motivo);
            $variedades = $variedades->orderBy('v.nombre')
                ->get();

            $fechas = DB::table('flor_nacional as fn')
                ->select('fn.fecha')->distinct()
                ->where('fn.fecha', '>=', $request->desde)
                ->where('fn.fecha', '<=', $request->hasta);
            if ($request->variedad != '')
                $fechas = $fechas->where('fn.id_variedad', $request->variedad);
            if ($request->motivo != '')
                $fechas = $fechas->where('fn.id_motivos_nacional', $request->motivo);
            $fechas = $fechas->orderBy('fn.fecha')
                ->get()->pluck('fecha')->toArray();

            $listado = [];
            $total_nacional = 0;
            $total_cosecha = 0;
            foreach ($variedades as $var) {
                $valores = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('flor_nacional as fn')
                        ->select(DB::raw('sum(tallos) as cantidad'))
                        ->where('fn.fecha', $f)
                        ->where('fn.id_variedad', $var->id_variedad);
                    if ($request->motivo != '')
                        $cantidad = $cantidad->where('fn.id_motivos_nacional', $request->motivo);
                    $cantidad = $cantidad->get()[0]->cantidad;

                    $valores[] = $cantidad;
                    $total_nacional += $cantidad;
                }
                $cosechados = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cant'))
                    ->where('r.fecha_ingreso', '>=', $request->desde)
                    ->where('r.fecha_ingreso', '<=', $request->hasta)
                    ->where('dr.id_variedad', $var->id_variedad);
                if ($request->motivo != '')
                    $cosechados = $cosechados->where('fn.id_motivos_nacional', $request->motivo);
                $cosechados = $cosechados->get()[0]->cant;
                $total_cosecha += $cosechados;
                $listado[] = [
                    'variedad' => $var,
                    'valores' => $valores,
                    'cosechados' => $cosechados,
                ];
            }

            return view('adminlte.crm.reporte_flor_nacional.partials.listado', [
                'listado' => $listado,
                'fechas' => $fechas,
                'total_nacional' => $total_nacional,
                'total_cosecha' => $total_cosecha,
            ]);
        } else {    // reporte por motivos
            $motivos = DB::table('flor_nacional as fn')
                ->join('motivos_nacional as m', 'm.id_motivos_nacional', '=', 'fn.id_motivos_nacional')
                ->select('fn.id_motivos_nacional', 'm.nombre')->distinct()
                ->where('fn.fecha', '>=', $request->desde)
                ->where('fn.fecha', '<=', $request->hasta);
            if ($request->variedad != '')
                $motivos = $motivos->where('fn.id_variedad', $request->variedad);
            if ($request->motivo != '')
                $motivos = $motivos->where('fn.id_motivos_nacional', $request->motivo);
            $motivos = $motivos->orderBy('m.nombre')
                ->get();

            $fechas = DB::table('flor_nacional as fn')
                ->select('fn.fecha')->distinct()
                ->where('fn.fecha', '>=', $request->desde)
                ->where('fn.fecha', '<=', $request->hasta);
            if ($request->variedad != '')
                $fechas = $fechas->where('fn.id_variedad', $request->variedad);
            if ($request->motivo != '')
                $fechas = $fechas->where('fn.id_motivos_nacional', $request->motivo);
            $fechas = $fechas->orderBy('fn.fecha')
                ->get()->pluck('fecha')->toArray();

            $listado = [];
            $total_nacional = 0;
            $total_cosecha = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cant'))
                ->where('r.fecha_ingreso', '>=', $request->desde)
                ->where('r.fecha_ingreso', '<=', $request->hasta);
            if ($request->variedad != '')
                $total_cosecha = $total_cosecha->where('fn.id_variedad', $request->variedad);
            $total_cosecha = $total_cosecha->get()[0]->cant;
            foreach ($motivos as $mot) {
                $valores = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('flor_nacional as fn')
                        ->select(DB::raw('sum(tallos) as cantidad'))
                        ->where('fn.fecha', $f)
                        ->where('fn.id_motivos_nacional', $mot->id_motivos_nacional);
                    if ($request->variedad != '')
                        $cantidad = $cantidad->where('fn.id_variedad', $request->variedad);
                    $cantidad = $cantidad->get()[0]->cantidad;

                    $valores[] = $cantidad;
                    $total_nacional += $cantidad;
                }
                $listado[] = [
                    'motivo' => $mot,
                    'valores' => $valores,
                ];
            }

            return view('adminlte.crm.reporte_flor_nacional.partials.listado_motivos', [
                'listado' => $listado,
                'fechas' => $fechas,
                'total_nacional' => $total_nacional,
                'total_cosecha' => $total_cosecha,
            ]);
        }
    }
}
