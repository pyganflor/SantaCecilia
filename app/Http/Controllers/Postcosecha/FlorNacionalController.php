<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\FlorNacional;
use yura\Modelos\MotivosNacional;
use yura\Modelos\Submenu;

class FlorNacionalController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.flor_nacional.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();

        $motivos_nacional = MotivosNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $variedades = DB::table('ciclo_cama as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->select('c.id_variedad', 'v.nombre')->distinct()
            ->where('v.estado', 1)
            ->where('c.activo', 1)
            ->where('c.id_empresa', $finca)
            ->orderBy('v.nombre')
            ->get();

        $listado = [];
        foreach ($variedades as $var) {
            $valores = [];
            foreach ($motivos_nacional as $mot) {
                $cant = DB::table('flor_nacional')
                    ->select(DB::raw('sum(tallos) as cant'))
                    ->where('fecha', $request->fecha)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_motivos_nacional', $mot->id_motivos_nacional)
                    ->where('id_empresa', $finca)
                    ->get()[0]->cant;
                $valores[] = $cant;
            }
            $cosechados = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cant'))
                ->where('r.fecha_ingreso', $request->fecha)
                ->where('dr.id_variedad', $var->id_variedad)
                ->where('dr.id_empresa', $finca)
                ->get()[0]->cant;
            $listado[] = [
                'variedad' => $var,
                'valores' => $valores,
                'cosechados' => $cosechados,
            ];
        }

        return view('adminlte.gestion.postcocecha.flor_nacional.partials.listado', [
            'listado' => $listado,
            'motivos_nacional' => $motivos_nacional,
            'variedades' => $variedades,
        ]);
    }

    public function store_flor_nacional(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            $model = FlorNacional::where('id_variedad', $request->variedad)
                ->where('id_motivos_nacional', $request->motivo)
                ->where('fecha', $request->fecha)
                ->get()
                ->first();
            if ($model == '') {
                $model = new FlorNacional();
                $model->id_variedad = $request->variedad;
                $model->id_motivos_nacional = $request->motivo;
                $model->fecha = $request->fecha;
                $model->tallos = $request->tallos;
                $model->id_empresa = $finca;
                $model->save();
            } else {
                if ($request->tallos > 0) {
                    $model->tallos = $request->tallos;
                    $model->save();
                } else {
                    $model->delete();
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> correctamente';
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
