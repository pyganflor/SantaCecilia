<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use yura\Modelos\Cama;
use yura\Modelos\Modulo;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;

class MapeoCultivoController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.mapeo_cultivo.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_sectores(Request $request)
    {
        $listado = Sector::orderBy('nombre')->get();
        return view('adminlte.gestion.mapeo_cultivo.partials.listado_sectores', [
            'listado' => $listado
        ]);
    }

    public function store_sector(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            $existe = Sector::All()
                ->where('nombre', $request->nombre)
                ->first();
            if ($existe == '') {
                $model = new Sector();
                $model->nombre = $request->nombre;
                $model->area = $request->area;
                $model->id_empresa = $finca;
                $model->save();

                $success = true;
                $msg = 'Se ha <strong>CREADO</strong> el sector correctamente';
            } else {
                $success = false;
                $msg = '<div class="alert alert-info text-center">El <strong>NOMBRE</strong> ya <b>EXISTE</b></div>';
            }

            DB::commit();
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

    public function update_sector(Request $request)
    {
        try {
            DB::beginTransaction();
            $existe = Sector::All()
                ->where('nombre', $request->nombre)
                ->where('id_sector', '!=', $request->id)
                ->first();
            if ($existe == '') {
                $model = Sector::find($request->id);
                $model->nombre = $request->nombre;
                $model->area = $request->area;
                $model->save();

                $success = true;
                $msg = 'Se ha <strong>MODIFICADO</strong> el sector correctamente';
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">El <strong>NOMBRE</strong> ya <b>EXISTE</b></div>';
            }

            DB::commit();
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

    public function cambiar_estado_sector(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = Sector::find($request->id);
            $model->estado = !$model->estado;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el sector correctamente';

            DB::commit();
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

    public function listar_modulos(Request $request)
    {
        $listado = Modulo::where('id_sector', $request->sector)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.mapeo_cultivo.partials.listado_modulos', [
            'listado' => $listado,
            'sector' => $request->sector,
        ]);
    }

    public function store_modulo(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            $existe = Modulo::All()
                ->where('nombre', $request->nombre)
                ->where('id_sector', $request->sector)
                ->first();
            if ($existe == '') {
                $model = new Modulo();
                $model->nombre = $request->nombre;
                $model->area = $request->area;
                $model->id_sector = $request->sector;
                $model->id_empresa = $finca;
                $model->save();

                $success = true;
                $msg = 'Se ha <strong>CREADO</strong> el bloque correctamente';
            } else {
                $success = false;
                $msg = '<div class="alert alert-info text-center">El <strong>NOMBRE</strong> ya <b>EXISTE</b></div>';
            }

            DB::commit();
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

    public function update_modulo(Request $request)
    {
        try {
            DB::beginTransaction();
            $existe = Modulo::All()
                ->where('nombre', $request->nombre)
                ->where('id_sector', $request->sector)
                ->where('id_modulo', '!=', $request->id)
                ->first();
            if ($existe == '') {
                $model = Modulo::find($request->id);
                $model->nombre = $request->nombre;
                $model->area = $request->area;
                $model->save();

                $success = true;
                $msg = 'Se ha <strong>MODIFICADO</strong> el bloque correctamente';
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">El <strong>NOMBRE</strong> ya <b>EXISTE</b></div>';
            }

            DB::commit();
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

    public function cambiar_estado_modulo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = Modulo::find($request->id);
            $model->estado = !$model->estado;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el bloque correctamente';

            DB::commit();
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

    public function listar_camas(Request $request)
    {
        $listado = Cama::where('id_modulo', $request->modulo)
            ->orderBy('id_cama')
            ->get();
        return view('adminlte.gestion.mapeo_cultivo.partials.listado_camas', [
            'listado' => $listado,
            'modulo' => $request->modulo,
        ]);
    }

    public function store_camas(Request $request)
    {
        try {
            $finca = getFincaActiva();
            /* NUEVAS CAMAS */
            foreach (json_decode($request->data_new) as $d) {
                $existe = Cama::All()
                    ->where('nombre', $d->nombre)
                    ->where('id_modulo', $request->modulo)
                    ->first();
                if ($existe == '') {
                    $model = new Cama();
                    $model->nombre = $d->nombre;
                    $model->area = $d->area != '' ? $d->area : 0;
                    $model->cuadros = $d->cuadro > 0 ? $d->cuadro : 1;
                    $model->id_modulo = $request->modulo;
                    $model->id_empresa = $finca;
                    $model->save();
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-danger text-center">La <strong>CAMA: ' . $d->nombre . '</strong> ya <b>EXISTE</b> en este bloque</div>';

                    return [
                        'success' => $success,
                        'mensaje' => $msg,
                    ];
                }
            }

            /* EDITAR CAMAS */
            foreach (json_decode($request->data_edit) as $d) {
                $model = Cama::find($d->id_cama);
                $model->nombre = $d->nombre;
                $model->area = $d->area != '' ? $d->area : 0;
                $model->cuadros = $d->cuadro > 0 ? $d->cuadro : 1;
                $model->save();
            }

            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> las camas correctamente';
        } catch (\Exception $e) {
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

    public function cambiar_estado_cama(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = Cama::find($request->id);
            $model->estado = !$model->estado;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> la cama correctamente';

            DB::commit();
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
