<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\CicloCama;
use yura\Modelos\CicloPlaga;
use yura\Modelos\Plaga;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;

class MonitoreoPlagasController extends Controller
{
    public function inicio(Request $request)
    {
        $sectores = Sector::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.monitoreo_plagas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'sectores' => $sectores
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = [];
        $camas = DB::table('ciclo_cama as cc')
            ->join('cama as c', 'c.id_cama', '=', 'cc.id_cama')
            ->select('cc.id_cama', 'c.nombre', 'c.cuadros')->distinct()
            ->where('cc.activo', 1)
            ->where('c.estado', 1)
            ->where('c.id_modulo', $request->modulo)
            ->orderBy('c.nombre')
            ->get();
        $max_ciclos = 0;
        foreach ($camas as $c) {
            $ciclos = CicloCama::where('activo', 1)
                ->where('id_cama', $c->id_cama)
                ->orderBy('cuadro', 'asc')
                ->get();

            $valores_ciclos = [];
            foreach ($ciclos as $c) {
                $plagas = CicloPlaga::where('id_ciclo_cama', $c->id_ciclo_cama)
                    ->where('fecha', '<=', $request->fecha)
                    ->orderBy('id_plaga')
                    ->orderBy('fecha', 'desc')
                    ->get();
                $valores_ciclos[] = [
                    'ciclo' => $c,
                    'plagas' => $plagas,
                ];
            }

            $listado[] = [
                'cama' => $c,
                'ciclos' => $valores_ciclos
            ];
            if (count($ciclos) > $max_ciclos)
                $max_ciclos = count($ciclos);
        }
        $plagas = Plaga::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.monitoreo_plagas.partials.listado', [
            'listado' => $listado,
            'max_ciclos' => $max_ciclos,
            'plagas' => $plagas,
        ]);
    }

    public function store_incidencia(Request $request)
    {
        try {
            DB::beginTransaction();
            $existe = CicloPlaga::All()
                ->where('id_ciclo_cama', $request->ciclo)
                ->where('fecha', $request->fecha)
                ->where('incidencia', $request->incidencia)
                ->where('id_plaga', $request->plaga)
                ->first();
            if ($existe == '') {
                $model = new CicloPlaga();
                $model->id_ciclo_cama = $request->ciclo;
                $model->fecha = $request->fecha;
                $model->incidencia = $request->incidencia;
                $model->id_plaga = $request->plaga;
                $model->save();

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> la incidencia correctamente';
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-danger text-center">' .
                        'Ya existe una incidencia de esta plaga, en el cuadro seleccionado</div>',
                ];
            }
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
}
