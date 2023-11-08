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
        $listado = DB::table('flor_nacional as f')
            ->join('variedad as v', 'v.id_variedad', '=', 'f.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('modulo as m', 'm.id_modulo', '=', 'f.id_modulo')
            ->join('sector as s', 's.id_sector', '=', 'm.id_sector')
            ->select(
                'f.*',
                'v.id_planta',
                'm.id_sector',
            )->distinct()
            ->where('f.fecha', $request->fecha)
            ->where('f.id_empresa', $finca)
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('s.nombre')
            ->orderBy('m.nombre')
            ->get();
        $clasificaciones_ramos = ClasificacionRamo::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $motivos_nacional = MotivosNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $plantas = DB::table('ciclo_cama as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('p.estado', 1)
            ->where('c.activo', 1)
            ->where('c.id_empresa', $finca)
            ->orderBy('p.nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.flor_nacional.partials.listado', [
            'listado' => $listado,
            'clasificaciones_ramos' => $clasificaciones_ramos,
            'motivos_nacional' => $motivos_nacional,
            'plantas' => $plantas,
        ]);
    }

    public function add_flor_nacional(Request $request)
    {
        $finca = getFincaActiva();
        $clasificaciones_ramos = ClasificacionRamo::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $motivos_nacional = MotivosNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $plantas = DB::table('ciclo_cama as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('p.estado', 1)
            ->where('c.activo', 1)
            ->where('c.id_empresa', $finca)
            ->orderBy('p.nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.flor_nacional.forms.add_flor_nacional', [
            'clasificaciones_ramos' => $clasificaciones_ramos,
            'motivos_nacional' => $motivos_nacional,
            'plantas' => $plantas,
        ]);
    }

    public function buscar_modulos(Request $request)
    {
        $finca = getFincaActiva();
        $modulos = DB::table('ciclo_cama as c')
            ->join('cama as ca', 'ca.id_cama', '=', 'c.id_cama')
            ->join('modulo as m', 'm.id_modulo', '=', 'ca.id_modulo')
            ->join('sector as s', 's.id_sector', '=', 'm.id_sector')
            ->select('ca.id_modulo', 'm.nombre', 's.nombre as nombre_sector')->distinct()
            ->where('c.activo', 1)
            ->where('c.id_variedad', $request->variedad)
            ->where('c.id_empresa', $finca)
            ->orderBy('s.nombre')
            ->orderBy('m.nombre')
            ->get();

        $options = '';
        foreach ($modulos as $mod)
            $options .= '<option value="' . $mod->id_modulo . '">' . $mod->nombre_sector . ': ' . $mod->nombre . '</option>';

        return [
            'options' => $options,
        ];
    }

    public function store_flor_nacional(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            foreach (json_decode($request->data) as $d) {
                $model = new FlorNacional();
                $model->id_variedad = $d->variedad;
                $model->id_modulo = $d->modulo;
                $model->id_motivos_nacional = $d->motivo;
                $model->tallos = $d->tallos;
                $model->fecha = $request->fecha;
                $model->id_empresa = $finca;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los ingresos correctamente';
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

    public function eliminar_flor_nacional(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = FlorNacional::find($request->id);
            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la flor nacional correctamente';
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

    public function update_flor_nacional(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = FlorNacional::find($request->id);
            $model->id_variedad = $request->variedad;
            $model->id_modulo = $request->modulo;
            $model->id_motivos_nacional = $request->motivo;
            $model->tallos = $request->tallos;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la flor nacional correctamente';
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
