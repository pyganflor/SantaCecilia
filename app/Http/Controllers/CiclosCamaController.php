<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use yura\Modelos\Cama;
use yura\Modelos\CicloCama;
use yura\Modelos\Modulo;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class CiclosCamaController extends Controller
{
    public function inicio(Request $request)
    {
        $sectores = Sector::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.ciclos_cama.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'sectores' => $sectores
        ]);
    }

    public function seleccionar_sector(Request $request)
    {
        $modulos = Modulo::where('estado', 1)
            ->where('id_sector', $request->sector)
            ->orderBy('nombre')
            ->get();
        $options = '<option value="">Seleccione</option>';
        foreach ($modulos as $m) {
            $options .= '<option value="' . $m->id_modulo . '">' . $m->nombre . '</option>';
        }
        return [
            'options' => $options
        ];
    }

    public function seleccionar_modulo(Request $request)
    {
        $camas = Cama::where('estado', 1)
            ->where('id_modulo', $request->modulo)
            ->orderBy('id_cama')
            ->get();
        $variedades = Variedad::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.ciclos_cama.partials.listado', [
            'camas' => $camas,
            'variedades' => $variedades,
        ]);
    }

    public function store_ciclos(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $d) {
                $existe = CicloCama::All()
                    ->where('id_cama', $d->id_cama)
                    ->where('cuadro', $d->cuadro)
                    ->where('activo', 1)
                    ->first();
                if ($existe == '') {
                    $model = new CicloCama();
                    $model->id_cama = $d->id_cama;
                    $model->cuadro = $d->cuadro;
                    $model->fecha_inicio = $d->fecha_inicio;
                    $model->plantas_iniciales = $d->plantas_iniciales;
                    $model->conteo = $d->conteo;
                    $model->id_variedad = $d->id_variedad;
                    $model->save();
                } else {
                    $existe->fecha_inicio = $d->fecha_inicio;
                    $existe->plantas_iniciales = $d->plantas_iniciales;
                    $existe->conteo = $d->conteo;
                    $existe->id_variedad = $d->id_variedad;
                    $existe->save();
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>CREADO</strong> los ciclos correctamente';
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

    public function update_ciclo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = CicloCama::find($request->ciclo);
            $model->id_variedad = $request->id_variedad;
            $model->fecha_inicio = $request->fecha_inicio;
            $model->plantas_iniciales = $request->plantas_iniciales;
            $model->conteo = $request->conteo;
            //$model->semana_cosecha = $request->semana_cosecha;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el ciclo correctamente';
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

    public function terminar_ciclo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = CicloCama::find($request->ciclo);
            $model->fecha_fin = $request->fecha_fin;
            $model->activo = 0;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>TERMINADO</strong> el ciclo correctamente';
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

    public function eliminar_ciclo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = CicloCama::find($request->ciclo);
            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> el ciclo correctamente';
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
