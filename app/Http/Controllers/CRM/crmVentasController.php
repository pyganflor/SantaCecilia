<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Indicador;
use yura\Modelos\Pedido;
use yura\Modelos\Planta;
use yura\Modelos\ProyeccionVentaSemanalReal;
use yura\Modelos\ResumenVentaDiaria;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class crmVentasController extends Controller
{
    public function inicio(Request $request)
    {
        /* ======= INDICADORES ======= */
        $semana_desde = getSemanaByDate(opDiasFecha('-', 28, hoy()));
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $semanas_indicadores = getSemanasByCodigos($semana_desde->codigo, $semana_hasta->codigo);
        $indicadores = [];
        foreach ($semanas_indicadores as $sem) {
            $query = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('caja_frio as c', 'c.id_caja_frio', '=', 'dp.id_caja_frio')
                ->join('detalle_caja_frio as dc', 'dc.id_caja_frio', '=', 'c.id_caja_frio')
                ->select(
                    DB::raw('sum(dc.ramos * dc.tallos_x_ramo * dc.precio) as monto'),
                    DB::raw('sum(dc.ramos) as ramos'),
                    DB::raw('sum(dc.ramos * dc.tallos_x_ramo) as tallos'),
                )
                ->where('p.estado', 1)
                ->where('p.fecha_pedido', '>=', $sem->fecha_inicial)
                ->where('p.fecha_pedido', '<=', $sem->fecha_final)
                ->get()[0];
            $indicadores[] = [
                'semana' => $sem,
                'monto' => $query->monto,
                'ramos' => $query->ramos,
                'tallos' => $query->tallos,
            ];
        }

        /* ======= GRAFICAS ======= */
        $annos = DB::table('pedido')
            ->select(DB::raw('year(fecha_pedido) as anno'))->distinct()
            ->where('estado', 1)
            ->orderBy('anno', 'desc')
            ->get();
        $variedades = Variedad::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.crm.ventas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'annos' => $annos,
            'indicadores' => $indicadores,
            'variedades' => $variedades,
            'clientes' => getClientes(),
        ]);
    }

    public function listar_graficas(Request $request)
    {
        if ($request->annos == '' || $request->rango == 'L') {
            $view = 'graficas_rango';

            if ($request->rango == 'D') {   // diario
                $labels = DB::table('pedido')
                    ->select('fecha_pedido')->distinct()
                    ->where('estado', 1)
                    ->where('fecha_pedido', '>=', $request->desde)
                    ->where('fecha_pedido', '<=', $request->hasta)
                    ->orderBy('fecha_pedido')
                    ->get()->pluck('fecha_pedido')->toArray();

                $data = [];
                foreach ($labels as $l) {
                    $query = DB::table('pedido as p')
                        ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                        ->join('caja_frio as c', 'c.id_caja_frio', '=', 'dp.id_caja_frio')
                        ->join('detalle_caja_frio as dc', 'dc.id_caja_frio', '=', 'c.id_caja_frio')
                        ->select(
                            DB::raw('sum(dc.ramos * dc.tallos_x_ramo * dc.precio) as monto'),
                            DB::raw('sum(dc.ramos) as ramos'),
                            DB::raw('sum(dc.ramos * dc.tallos_x_ramo) as tallos'),
                        )
                        ->where('p.estado', 1)
                        ->where('p.fecha_pedido', $l);
                    if ($request->variedad != 'T')
                        $query = $query->where('dc.id_variedad', $request->variedad);
                    if ($request->longitud != '')
                        $query = $query->where('dc.longitud', $request->longitud);
                    $query = $query->get()[0];

                    $data[] = $query;
                }
            } else if ($request->rango == 'M') {   // mensual
                $labels = DB::table('pedido')
                    ->select(DB::raw('DISTINCT DATE_FORMAT(fecha_pedido, "%Y-%m") AS mes'))
                    ->where('estado', 1)
                    ->where('fecha_pedido', '>=', $request->desde)
                    ->where('fecha_pedido', '<=', $request->hasta)
                    ->groupBy('mes', 'fecha_pedido')
                    ->orderBy('fecha_pedido')
                    ->get()->pluck('mes')->toArray();

                $data = [];
                foreach ($labels as $l) {
                    $query = DB::table('pedido as p')
                        ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                        ->join('caja_frio as c', 'c.id_caja_frio', '=', 'dp.id_caja_frio')
                        ->join('detalle_caja_frio as dc', 'dc.id_caja_frio', '=', 'c.id_caja_frio')
                        ->select(
                            DB::raw('sum(dc.ramos * dc.tallos_x_ramo * dc.precio) as monto'),
                            DB::raw('sum(dc.ramos) as ramos'),
                            DB::raw('sum(dc.ramos * dc.tallos_x_ramo) as tallos'),
                        )
                        ->where('p.estado', 1)
                        ->whereMonth('p.fecha_pedido', date('m', strtotime($l)))
                        ->whereYear('p.fecha_pedido', '=', date('Y', strtotime($l)));
                    if ($request->variedad != 'T')
                        $query = $query->where('dc.id_variedad', $request->variedad);
                    if ($request->longitud != '')
                        $query = $query->where('dc.longitud', $request->longitud);
                    $query = $query->get()[0];

                    $data[] = $query;
                }
            } else if ($request->rango == 'S') {    // semanal
                $labels = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('fecha_final', '>=', $request->desde)
                    ->where('fecha_final', '<=', $request->hasta)
                    ->orderBy('codigo')
                    ->get();

                $data = [];
                foreach ($labels as $pos => $l) {
                    $query = DB::table('pedido as p')
                        ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                        ->join('caja_frio as c', 'c.id_caja_frio', '=', 'dp.id_caja_frio')
                        ->join('detalle_caja_frio as dc', 'dc.id_caja_frio', '=', 'c.id_caja_frio')
                        ->select(
                            DB::raw('sum(dc.ramos * dc.tallos_x_ramo * dc.precio) as monto'),
                            DB::raw('sum(dc.ramos) as ramos'),
                            DB::raw('sum(dc.ramos * dc.tallos_x_ramo) as tallos'),
                        )
                        ->where('p.estado', 1)
                        ->where('p.fecha_pedido', '>=', $l->fecha_inicial)
                        ->where('p.fecha_pedido', '<=', $l->fecha_final);
                    if ($request->variedad != 'T')
                        $query = $query->where('dc.id_variedad', $request->variedad);
                    if ($request->longitud != '')
                        $query = $query->where('dc.longitud', $request->longitud);
                    $query = $query->get()[0];

                    $data[] = $query;
                }
            } else if ($request->rango == 'L') {  // longitud x variedad
                $view = 'graficas_multiple';
                $labels = DB::table('pedido')
                    ->select('fecha_pedido')->distinct()
                    ->where('estado', 1)
                    ->where('fecha_pedido', '>=', $request->desde)
                    ->where('fecha_pedido', '<=', $request->hasta)
                    ->orderBy('fecha_pedido')
                    ->get()->pluck('fecha_pedido')->toArray();

                $longitudes = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('detalle_caja_frio as dc', 'dc.id_caja_frio', '=', 'dp.id_caja_frio')
                    ->select('dc.longitud')->distinct()
                    ->where('p.estado', 1)
                    ->where('p.fecha_pedido', '>=', $request->desde)
                    ->where('p.fecha_pedido', '<=', $request->hasta);
                if ($request->variedad != 'T')
                    $longitudes = $longitudes->where('dc.id_variedad', $request->variedad);
                $longitudes = $longitudes->orderBy('dc.longitud')
                    ->get()->pluck('longitud')->toArray();

                $data = [];
                foreach ($longitudes as $long) {
                    $valores = [];
                    foreach ($labels as $l) {
                        $query = DB::table('pedido as p')
                            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                            ->join('caja_frio as c', 'c.id_caja_frio', '=', 'dp.id_caja_frio')
                            ->join('detalle_caja_frio as dc', 'dc.id_caja_frio', '=', 'c.id_caja_frio')
                            ->select(
                                DB::raw('sum(dc.ramos * dc.tallos_x_ramo * dc.precio) as monto'),
                                DB::raw('sum(dc.ramos) as ramos'),
                                DB::raw('sum(dc.ramos * dc.tallos_x_ramo) as tallos'),
                            )
                            ->where('p.estado', 1)
                            ->where('p.fecha_pedido', $l)
                            ->where('dc.longitud', $long);
                        if ($request->variedad != 'T')
                            $query = $query->where('dc.id_variedad', $request->variedad);
                        $query = $query->get()[0];

                        $valores[] = $query;
                    }
                    $data[] = [
                        'longitud' => $long,
                        'valores' => $valores,
                    ];
                }
            }
            if ($request->tipo_grafica == 'line' || $request->rango == 'L') {
                $tipo_grafica = 'line';
                $fill_grafica = 'false';
            } else if ($request->tipo_grafica == 'area') {
                $tipo_grafica = 'line';
                $fill_grafica = 'true';
            } else {
                $tipo_grafica = 'bar';
                $fill_grafica = 'true';
            }
            $datos = [
                'labels' => $labels,
                'data' => $data,
                'tipo_grafica' => $tipo_grafica,
                'fill_grafica' => $fill_grafica,
                'rango' => $request->rango,
            ];
        } else {
            $view = 'graficas_annos';
            $annos = explode(' - ', $request->annos);

            en_desarrollo();
        }

        return view('adminlte.crm.ventas.partials.' . $view, $datos);
    }

    public function listar_ranking(Request $request)
    {
        $query = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('caja_frio as c', 'c.id_caja_frio', '=', 'dp.id_caja_frio')
            ->join('detalle_caja_frio as dc', 'dc.id_caja_frio', '=', 'c.id_caja_frio')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->select(
                'dc.id_variedad',
                'v.nombre',
                DB::raw('sum(dc.ramos * dc.tallos_x_ramo * dc.precio) as monto'),
                DB::raw('sum(dc.ramos) as ramos'),
                DB::raw('sum(dc.ramos * dc.tallos_x_ramo) as tallos'),
            )
            ->where('p.estado', 1)
            ->where('p.fecha_pedido', '>=', $request->desde)
            ->where('p.fecha_pedido', '<=', $request->hasta)
            ->groupBy(
                'dc.id_variedad',
                'v.nombre'
            );
        if ($request->criterio_ranking == 'T')
            $query = $query->orderBy('tallos', 'desc');
        if ($request->criterio_ranking == 'R')
            $query = $query->orderBy('ramos', 'desc');
        if ($request->criterio_ranking == 'M')
            $query = $query->orderBy('monto', 'desc');
        $query = $query->limit(4)
            ->get();

        return view('adminlte.crm.ventas.partials.listar_ranking', [
            'query' => $query,
            'criterio' => $request->criterio_ranking,
        ]);
    }
}
