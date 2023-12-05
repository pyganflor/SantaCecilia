<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionRamoDisponibilidad;
use yura\Modelos\Submenu;

class ReporteDisponibilidadController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.posco_disponibilidad.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $variedades = DB::table('inventario_frio as i')
            ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->select(
                'i.id_variedad',
                'v.nombre',
            )->distinct()
            ->where('i.estado', 1)
            ->where('i.disponibles', '>', 0)
            ->where('c.nombre', '!=', 'Baja')
            ->where('c.nombre', '!=', 'Nacional')
            ->orderBy('v.nombre')
            ->get();
        $parametrizaciones = ClasificacionRamoDisponibilidad::get();
        $listado = [];
        foreach ($variedades as $var) {
            $longitudes = DB::table('inventario_frio as i')
                ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select(
                    'c.id_clasificacion_ramo',
                    'c.nombre as longitud',
                    DB::raw('sum(i.disponibles) as cantidad')
                )->distinct()
                ->where('i.id_variedad', $var->id_variedad)
                ->where('i.estado', 1)
                ->where('i.disponibles', '>', 0)
                ->where('c.nombre', '!=', 'Baja')
                ->where('c.nombre', '!=', 'Nacional')
                ->groupBy(
                    'i.id_variedad',
                    'v.nombre',
                    'c.id_clasificacion_ramo',
                    'c.nombre',
                )
                ->orderBy('c.nombre', 'desc')
                ->get();
            $valores_var = [];
            foreach ($longitudes as $long) {
                $param = '';
                foreach ($parametrizaciones as $p)
                    if ($p->id_clasificacion_ramo == $long->id_clasificacion_ramo)
                        $param = $p;

                if ($param != '') {
                    $cajas = intval($long->cantidad / $p->ramos_x_caja);
                    $sobra = $long->cantidad % $p->ramos_x_caja;

                    $valores_var[] = [
                        'longitud' => $long,
                        'param' => $param,
                        'cajas' => $cajas,
                        'sobra' => $sobra,
                    ];
                }
            }
            $listado[] = [
                'variedad' => $var,
                'valores_var' => $valores_var,
            ];
        }
        return view('adminlte.gestion.postcocecha.posco_disponibilidad.partials.listado', [
            'listado' => $listado,
        ]);
    }
}
