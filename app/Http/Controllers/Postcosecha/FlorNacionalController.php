<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionRamo;
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
            foreach (json_decode($request->data) as $d) {
                dd($d, $request->fecha);
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
}
