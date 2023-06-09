<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Validator;
use yura\Http\Controllers\Controller;
use yura\Jobs\CicloUpdateCampo;
use yura\Jobs\jobActualizarFenogramaEjecucion;
use yura\Jobs\jobActualizarProyeccion;
use yura\Jobs\jobUpdateProyeccionUpdateSemana;
use yura\Jobs\jobUpdateResumenTotalSemanalExportcalas;
use yura\Jobs\ProyeccionUpdateCampo;
use yura\Jobs\ProyeccionUpdateCiclo;
use yura\Jobs\ProyeccionUpdateProy;
use yura\Jobs\ProyeccionUpdateSemanal;
use yura\Jobs\ResumenSemanaCosecha;
use yura\Modelos\Ciclo;
use yura\Modelos\Modulo;
use yura\Modelos\Planta;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\Sector;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class proyCosechaController extends Controller
{
    public function inicio(Request $request)
    {
        $hasta = getSemanaByDate(opDiasFecha('+', 70, date('Y-m-d')));
        return view('adminlte.gestion.proyecciones.cosecha.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_hasta' => $hasta,
            'plantas' => Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
                ->select('planta.*')->distinct()
                ->where('v.estado', 1)
                ->where('planta.estado', 1)
                ->where('planta.tiene_ciclos', 1)
                ->where('v.compra_flor', 0)
                ->where('planta.tipo', 'N')
                ->orderBy('planta.nombre')
                ->get(),
        ]);
    }

    public function listar_proyecciones(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        set_time_limit(120);
        $finca_actual = getFincaActiva();

        $semana_desde_par = Semana::All()->where('codigo', $request->desde)->first();
        $semana_hasta = Semana::All()->where('codigo', $request->hasta)->first();

        $variedad = Variedad::find($request->variedad);

        if ($semana_desde_par != '' && $semana_hasta != '') {
            $fecha_ini = DB::table('ciclo as c')
                ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                ->select(DB::raw('min(c.fecha_inicio) as fecha_inicio'))
                ->where('c.estado', '=', 1)
                ->where('c.activo', '=', 1)
                ->where('c.id_variedad', '=', $request->variedad)
                ->where('c.fecha_fin', '>=', $semana_desde_par->fecha_inicial)
                ->where('m.proyectar_semanal', $variedad->proyectar_semanal)
                ->where('m.estado', '=', 1);
            if ($finca_actual != 'T')
                $fecha_ini = $fecha_ini->where('c.id_empresa', '=', $finca_actual);
            $fecha_ini = $fecha_ini->get()[0]->fecha_inicio;

            if ($fecha_ini != '') {
                $semana_desde = getSemanaByDate($fecha_ini);

                $semanas = Semana::where('estado', 1)
                    ->where('id_variedad', '=', $request->variedad)
                    ->where('codigo', '>=', $semana_desde->codigo)
                    ->where('codigo', '<=', $request->hasta)
                    ->orderBy('codigo')->get();

                $query_modulos = DB::table('ciclo as c')
                    ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                    ->select('c.id_modulo')->distinct()
                    ->where('c.estado', '=', 1)
                    ->where('m.estado', '=', 1)
                    ->where('m.proyectar_semanal', $variedad->proyectar_semanal)
                    ->where('c.id_variedad', '=', $request->variedad)
                    ->where('c.fecha_fin', '>=', $semana_desde_par->fecha_inicial);
                if ($finca_actual != 'T')
                    $query_modulos = $query_modulos->where('c.id_empresa', '=', $finca_actual);
                $query_modulos = $query_modulos->orderBy('c.activo', 'desc')
                    ->orderBy('c.fecha_inicio', 'asc')
                    ->get();

                $ids_modulos = [];
                foreach ($query_modulos as $item) {
                    array_push($ids_modulos, $item->id_modulo);
                }

                $modulos_inactivos = DB::table('proyeccion_modulo as p')
                    ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                    ->select('p.id_modulo')->distinct()
                    ->where('p.estado', '=', 1)
                    ->where('m.estado', '=', 1)
                    ->where('m.proyectar_semanal', $variedad->proyectar_semanal)
                    ->where('p.id_variedad', '=', $request->variedad)
                    ->where('p.fecha_inicio', '>=', $semana_desde_par->fecha_inicial)
                    ->whereNotIn('p.id_modulo', $ids_modulos);
                if ($finca_actual != 'T')
                    $modulos_inactivos = $modulos_inactivos->where('p.id_empresa', $finca_actual);
                $modulos_inactivos = $modulos_inactivos->orderBy('p.fecha_inicio', 'asc')->get();

                $query_modulos = $query_modulos->merge($modulos_inactivos);

                $array_modulos = [];
                foreach ($query_modulos as $mod) {
                    $mod = getModuloById($mod->id_modulo);

                    $valores = $mod->getProyeccionesByRango($semana_desde->codigo, $request->hasta, $request->variedad);

                    array_push($array_modulos, [
                        'modulo' => $mod,
                        'valores' => $valores,
                    ]);
                }

                $semana_actual = getSemanaByDate(date('Y-m-d'));
                $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, $semana_actual->fecha_inicial));
                $calibre_actual = getCalibreByRangoVariedad($semana_pasada->fecha_inicial, $semana_pasada->fecha_final, $request->variedad);

                $configuracion = getConfiguracionEmpresa();

                /* total_cosechados */
                $total_cosechados = DB::table('resumen_total_semanal_exportcalas')
                    ->select('semana',
                        DB::raw('sum(tallos_cosechados) as tallos_cosechados'))
                    ->where('id_variedad', $request->variedad)
                    ->where('id_empresa', $finca_actual)
                    ->where('semana', '>=', $semana_desde->codigo)
                    ->where('semana', '<=', $request->hasta)
                    ->groupBy('semana')
                    ->orderBy('semana')
                    ->get();

                return view('adminlte.gestion.proyecciones.cosecha.partials.listado', [
                    'variedad_model' => $variedad,
                    'semanas' => $semanas,
                    'modulos' => $array_modulos,
                    'variedad' => $request->variedad,
                    'semana_desde' => $semana_desde,
                    'opcion' => $request->opcion,
                    'detalle' => $request->detalle,
                    'ramos_x_caja' => $configuracion->ramos_x_caja,
                    'semana_actual' => $semana_actual,
                    'semana_pasada' => $semana_pasada,
                    'calibre_actual' => $calibre_actual,
                    'configuracion' => $configuracion,
                    'finca_actual' => $finca_actual,
                    'total_cosechados' => $total_cosechados,
                ]);
            } else
                return '<div class="alert alert-info text-center">No se han encontrado resultados en el rango establecido</div>';
        } else
            return '<div class="alert alert-info text-center">Semanas incorrectas</div>';
    }

    public function select_celda(Request $request)
    {
        if ($request->tipo == 'F') {    // crear una proyecccion
            return view('adminlte.gestion.proyecciones.cosecha.forms.new_proy', [
                'modulo' => getModuloById($request->modulo),
                'semana' => Semana::All()->where('codigo', $request->semana)->where('id_variedad', $request->variedad)->first(),
                'variedad' => getVariedad($request->variedad),
                'last_ciclo' => Ciclo::All()
                    ->where('estado', 1)
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $request->modulo)->last(),
            ]);
        }
        if ($request->tipo == 'Y') {    // crear una proyecccion
            $semana = Semana::All()->where('codigo', $request->semana)->where('id_variedad', $request->variedad)->first();
            $variedad = getVariedad($request->variedad);
            $proyeccion = ProyeccionModulo::find($request->modelo);
            return view('adminlte.gestion.proyecciones.cosecha.forms.edit_proy', [
                'modulo' => getModuloById($request->modulo),
                'semana' => $semana,
                'variedad' => $variedad,
                'proyeccion' => $proyeccion,
                'last_ciclo' => $proyeccion->last_ciclo(),
            ]);
        }
        if (in_array($request->tipo, ['P', 'S'])) {    // editar ciclo poda
            $semana = Semana::All()->where('codigo', $request->semana)->where('id_variedad', $request->variedad)->first();
            $variedad = getVariedad($request->variedad);
            return view('adminlte.gestion.proyecciones.cosecha.forms.edit_ciclo', [
                'modulo' => getModuloById($request->modulo),
                'semana' => $semana,
                'variedad' => $variedad,
                'ciclo' => Ciclo::find($request->modelo),
            ]);
        }
        if ($request->tipo == 'T') {    // crear una proyecccion
            if ($request->tabla == 'P') {
                $semana = Semana::All()->where('codigo', $request->semana)->where('id_variedad', $request->variedad)->first();
                $variedad = getVariedad($request->variedad);
                $proyeccion = ProyeccionModulo::find($request->modelo);
                return view('adminlte.gestion.proyecciones.cosecha.forms.edit_proy', [
                    'modulo' => getModuloById($request->modulo),
                    'semana' => $semana,
                    'variedad' => $variedad,
                    'proyeccion' => $proyeccion,
                    'last_ciclo' => $proyeccion->last_ciclo(),
                ]);
            } else {
                $semana = Semana::All()->where('codigo', $request->semana)->where('id_variedad', $request->variedad)->first();
                $variedad = getVariedad($request->variedad);
                return view('adminlte.gestion.proyecciones.cosecha.forms.edit_ciclo', [
                    'modulo' => getModuloById($request->modulo),
                    'semana' => $semana,
                    'variedad' => $variedad,
                    'ciclo' => Ciclo::find($request->modelo),
                ]);
            }
        }
    }

    public function store_proyeccion(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_modulo' => 'required',
            'id_variedad' => 'required',
            'id_semana' => 'required',
            'tipo' => 'required',
            'curva' => 'required',
            'semana_poda_siembra' => 'required',
            'plantas_iniciales' => 'required',
            'desecho' => 'required',
            'tallos_planta' => 'required',
            'tallos_ramo' => 'required',
        ], [
            'id_modulo.required' => 'El modulo es obligatorio',
            'tipo.required' => 'El tipo es obligatorio',
            'desecho.required' => 'El desecho es obligatorio',
            'plantas_iniciales.required' => 'Las plantas iniciales son obligatorias',
            'tallos_planta.required' => 'Los tallos por planta son obligatorios',
            'tallos_ramo.required' => 'Los tallos por ramo son obligatorios',
            'semana_poda_siembra.required' => 'Las semanas son obligatorias',
            'curva.required' => 'La curva es obligatoria',
            'id_variedad.required' => 'La variedad es obligatoria',
            'id_semana.required' => 'La semana es obligatoria',
        ]);
        if (!$valida->fails()) {
            $modulo = getModuloById($request->id_modulo);
            $poda_siembra = 0;
            $semana_ini = Semana::find($request->id_semana);
            $model = new ProyeccionModulo();
            $model->id_modulo = $request->id_modulo;
            $model->id_variedad = $request->id_variedad;
            $model->id_semana = $request->id_semana;
            $model->fecha_inicio = $semana_ini->fecha_final;
            $model->tipo = $request->tipo;
            $model->curva = $request->curva;
            $model->semana_poda_siembra = $request->semana_poda_siembra;
            $model->plantas_iniciales = $request->plantas_iniciales;
            $model->desecho = $request->desecho;
            $model->tallos_planta = $request->tallos_planta;
            $model->tallos_ramo = $request->tallos_ramo;
            $model->poda_siembra = $poda_siembra;
            $model->id_empresa = $modulo->id_empresa;

            if ($request->tipo == 'P') {
                $last_ciclo = Ciclo::All()
                    ->where('estado', 1)
                    ->where('id_variedad', $request->id_variedad)
                    ->where('id_modulo', $request->id_modulo)
                    ->sortBy('fecha_inicio')
                    ->last();
                if ($last_ciclo != '') {
                    $last_proy = ProyeccionModulo::All()
                        ->where('estado', 1)
                        ->where('id_modulo', $request->id_modulo)
                        ->where('id_variedad', $request->id_variedad)
                        ->where('fecha_inicio', '<', $semana_ini->fecha_final)
                        ->sortBy('fecha_inicio')
                        ->last();
                    if ($last_proy != '') {
                        $poda_siembra = $last_proy->poda_siembra + 1;
                    } else {
                        $poda_siembra = intval($last_ciclo->modulo->getPodaSiembraByCiclo($last_ciclo->id_ciclo) + 1);
                    }
                    $model->poda_siembra = $poda_siembra;
                }
            }

            if ($model->save()) {
                $model = ProyeccionModulo::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado una nueva proyección satisfactoriamente</p>'
                    . '</div>';
                bitacora('proyeccion_modulo', $model->id_proyeccion_modulo, 'I', 'Inserción satisfactoria de una nueva proyección');

                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                $cant_semanas_new = $request->semana_poda_siembra + count(explode('-', $request->curva));   // cantidad de semanas que durará la proy new

                $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                    ->where('id_modulo', $request->id_modulo)
                    ->where('id_variedad', $request->id_variedad)
                    ->where('semana', '>=', $model->semana->codigo)
                    ->orderBy('semana')
                    ->get();

                $last_semana_new = '';
                $pos_cosecha = 0;
                foreach ($proyecciones as $pos_proy => $proy) {
                    if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // // dentro de las semanas de la proy
                        $proy->tabla = 'P';
                        $proy->modelo = $model->id_proyeccion_modulo;

                        $proy->plantas_iniciales = $request->plantas_iniciales;
                        $proy->tallos_planta = $request->tallos_planta;
                        $proy->tallos_ramo = $request->tallos_planta;
                        $proy->curva = $request->curva;
                        $proy->poda_siembra = $poda_siembra;
                        $proy->semana_poda_siembra = $request->semana_poda_siembra;
                        $proy->desecho = $request->desecho;
                        $proy->area = $model->modulo->area;
                        $proy->tipo = 'I';
                        $proy->info = ($pos_proy + 1) . 'º';
                        $proy->proyectados = 0;

                        if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                            $proy->tipo = 'Y';
                            $proy->info = $request->tipo;
                        }
                        if ($pos_proy + 1 >= $request->semana_poda_siembra) {  // semana de cosecha **
                            $proy->tipo = 'T';
                            $total = $request->plantas_iniciales * $request->tallos_planta;
                            $total = $total * ((100 - $request->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $request->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                    } else {    // fuera de las semanas de la proy
                        if ($last_semana_new == '') {
                            $last_semana_new = $proy->semana;
                        }
                        $proy->tipo = 'F';
                        $proy->proyectados = 0;
                        $proy->info = '-';
                        $proy->activo = 0;
                        $proy->plantas_iniciales = null;
                        $proy->plantas_actuales = null;
                        $proy->desecho = null;
                        $proy->curva = null;
                        $proy->semana_poda_siembra = null;
                        $proy->tallos_planta = null;
                        $proy->poda_siembra = null;
                        $proy->tabla = null;
                        $proy->modelo = null;
                    }
                    $proy->save();
                }

                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                $semana_desde = $last_semana_new;
                $semana_fin = getLastSemanaByVariedad($request->id_variedad);

                /*if ($semana_desde != '')
                    ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $request->id_variedad, $request->id_modulo, 0)
                        ->onQueue('proy_cosecha/store_proyeccion');*/

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                /*jobUpdateResumenTotalSemanalExportcalas::dispatch($semana_desde, $semana_fin->codigo, $request->id_variedad)
                    ->onQueue('proy_cosecha/store_proyeccion');*/

                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado la proyección satisfactoriamente</p>'
                    . '</div>';
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_proyeccion(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_proyeccion_modulo' => 'required',
            'semana' => 'required',
            'tipo' => 'required',
            'curva' => 'required',
            'area' => 'required',
            'semana_poda_siembra' => 'required',
            'plantas_iniciales' => 'required',
            'desecho' => 'required',
            'tallos_planta' => 'required',
            'tallos_ramo' => 'required',
        ], [
            'id_proyeccion_modulo.required' => 'La proyección es obligatoria',
            'tipo.required' => 'El tipo es obligatorio',
            'desecho.required' => 'El desecho es obligatorio',
            'plantas_iniciales.required' => 'Las plantas iniciales son obligatorias',
            'tallos_planta.required' => 'Los tallos por planta son obligatorios',
            'tallos_ramo.required' => 'Los tallos por ramo son obligatorios',
            'semana_poda_siembra.required' => 'Las semanas son obligatorias',
            'curva.required' => 'La curva es obligatoria',
            'area.required' => 'El área es obligatoria',
            'semana.required' => 'La semana es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = ProyeccionModulo::find($request->id_proyeccion_modulo);
            $obj = [
                'id_modulo' => $model->id_modulo,
                'id_variedad' => $model->id_variedad,
            ];
            $semana_ini_proy = Semana::All()->where('estado', 1)->where('id_variedad', $model->id_variedad)
                ->where('codigo', $request->semana)->first();

            $poda_siembra = 0;
            if ($semana_ini_proy != '') {
                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                if ($model->id_semana != $semana_ini_proy->id_semana || $model->tipo != $request->tipo || $model->curva != $request->curva || 1 ||
                    $model->semana_poda_siembra != $request->semana_poda_siembra || $model->plantas_iniciales != $request->plantas_iniciales ||
                    $model->desecho != $request->desecho || $model->tallos_planta != $request->tallos_planta ||
                    $model->tallos_ramo != $request->tallos_ramo) { // hubo algun cambio

                    $semana_ini = min($model->semana->codigo, $semana_ini_proy->codigo);

                    $next_proy = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'P')
                        ->where('tipo', 'Y')
                        ->where('semana', '>', $semana_ini)
                        ->where('id_modulo', $model->id_modulo)
                        ->where('id_variedad', $model->id_variedad)
                        ->where('modelo', '!=', $model->id_proyeccion_modulo)
                        ->orderBy('semana')
                        ->get()->take(1);
                    $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                    $poda_siembra = 0;
                    if ($request->tipo == 'P') {
                        $last_ciclo = Ciclo::All()
                            ->where('estado', 1)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('id_modulo', $model->id_modulo)
                            ->sortBy('fecha_inicio')
                            ->last();
                        if ($last_ciclo != '') {
                            $last_proy = ProyeccionModulo::All()
                                ->where('estado', 1)
                                ->where('id_modulo', $model->id_modulo)
                                ->where('id_variedad', $model->id_variedad)
                                ->where('fecha_inicio', '<', $model->fecha_inicio)
                                ->sortBy('fecha_inicio')
                                ->last();
                            if ($last_proy != '') {
                                $poda_siembra = $last_proy->poda_siembra + 1;
                            } else {
                                $poda_siembra = intval($last_ciclo->modulo->getPodaSiembraByCiclo($last_ciclo->id_ciclo) + 1);
                            }
                        }
                    }

                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('semana', '>=', $semana_ini)
                        ->where('id_modulo', $model->id_modulo)
                        ->where('id_variedad', $model->id_variedad)
                        ->orderBy('semana')
                        ->get();

                    $cant_semanas_new = $request->semana_poda_siembra + count(explode('-', $request->curva));   // cantidad de semanas que durará la proy new

                    $last_semana = '';
                    $last_semana_new = '';
                    $pos_cosecha = 0;
                    $pos_proy = 0;
                    $pos_proy_new = '';
                    foreach ($proyecciones as $proy) {
                        if ($proy->tabla != 'C') {   // validar que el rango de semanas consultadas estan fuera de los ciclos reales
                            if ($request->tipo == 'C') {
                                $proy->tipo = 'F';
                                $proy->proyectados = 0;
                                $proy->info = '-';
                                $proy->activo = 0;
                                $proy->plantas_iniciales = null;
                                $proy->plantas_actuales = null;
                                $proy->desecho = null;
                                $proy->curva = null;
                                $proy->area = null;
                                $proy->semana_poda_siembra = null;
                                $proy->tallos_planta = null;
                                $proy->poda_siembra = null;
                                $proy->tabla = null;
                                $proy->modelo = null;
                            } else {
                                if ($proy->semana < $semana_ini_proy->codigo) { // se movio para adelante la proy, y se trata de una semana anterior
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->area = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;

                                } else if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // // dentro de las semanas de la proy
                                    $proy->tabla = 'P';
                                    $proy->modelo = $model->id_proyeccion_modulo;

                                    $proy->plantas_iniciales = $request->plantas_iniciales;
                                    $proy->tallos_planta = $request->tallos_planta;
                                    $proy->tallos_ramo = $request->tallos_ramo;
                                    $proy->curva = $request->curva;
                                    $proy->poda_siembra = $poda_siembra;
                                    $proy->semana_poda_siembra = $request->semana_poda_siembra;
                                    $proy->desecho = $request->desecho;
                                    $proy->area = $request->area;
                                    $proy->tipo = 'I';
                                    $proy->info = ($pos_proy + 1) . 'º';
                                    $proy->proyectados = 0;

                                    if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                        $proy->tipo = 'Y';
                                        $proy->info = $request->tipo;
                                    }
                                    if ($pos_proy + 1 >= $request->semana_poda_siembra) {  // semana de cosecha **
                                        $proy->tipo = 'T';
                                        $total = $request->plantas_iniciales * $request->tallos_planta;
                                        $total = $total * ((100 - $request->desecho) / 100);
                                        $proy->proyectados = round($total * (explode('-', $request->curva)[$pos_cosecha] / 100), 2);
                                        $pos_cosecha++;
                                    }
                                    $pos_proy++;
                                } else if ($next_proy != '') {    // semanas despues de la proyeccion, pero en caso de que exista una siguiente proy
                                    if ($last_semana == '')
                                        $last_semana = $proy->semana;
                                    if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                        if ($pos_proy_new == '') {
                                            $pos_proy_new = 0;
                                            $pos_cosecha = 0;
                                        }
                                        if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                            $proy->tabla = 'P';
                                            $proy->modelo = $next_proy->modelo;

                                            $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                            $proy->tallos_planta = $next_proy->tallos_planta;
                                            $proy->tallos_ramo = $next_proy->tallos_ramo;
                                            $proy->curva = $next_proy->curva;
                                            $proy->poda_siembra = $next_proy->info == 'S' ? 0 : $poda_siembra + 1;
                                            $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                            $proy->desecho = $next_proy->desecho;
                                            $proy->area = $next_proy->area;
                                            $proy->tipo = 'I';
                                            $proy->info = ($pos_proy_new + 1) . 'º';
                                            $proy->proyectados = 0;

                                            if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                                $proy->tipo = $next_proy->tipo;
                                                $proy->info = $next_proy->info;
                                            }
                                            if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                                $proy->tipo = 'T';
                                                $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                                $total = $total * ((100 - $next_proy->desecho) / 100);
                                                $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                                $pos_cosecha++;
                                            }
                                        } else {    // semanas despues de la proyeccion
                                            if ($last_semana_new == '') {
                                                $last_semana_new = $proy->semana;
                                            }
                                            $proy->tipo = 'F';
                                            $proy->proyectados = 0;
                                            $proy->info = '-';
                                            $proy->activo = 0;
                                            $proy->plantas_iniciales = null;
                                            $proy->plantas_actuales = null;
                                            $proy->desecho = null;
                                            $proy->curva = null;
                                            $proy->area = null;
                                            $proy->semana_poda_siembra = null;
                                            $proy->tallos_planta = null;
                                            $proy->poda_siembra = null;
                                            $proy->tabla = null;
                                            $proy->modelo = null;
                                        }
                                        $pos_proy_new++;
                                    } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                        //dd($proy->semana . '... es una semana que queda vacia antes de la siguiente proy');
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->area = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    } else {    // no hay que mover pero es una semana a partir de la siguiente proyeccion
                                        if ($pos_proy_new == '') {
                                            $pos_proy_new = 0;
                                        }
                                        if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {    // es una semana de la siguiente proyeccion
                                            $proy->poda_siembra = $next_proy->info == 'S' ? 0 : $poda_siembra + 1;
                                        }
                                        $pos_proy_new++;
                                    }
                                    $pos_proy++;
                                } else {    // fuera de las semanas de la proy
                                    if ($last_semana_new == '') {
                                        $last_semana_new = $proy->semana;
                                    }
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->area = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;

                                    $pos_proy++;
                                }
                            }
                        } else {
                            break;
                        }
                        $proy->save();
                    }

                    if ($last_semana_new == '')
                        $last_semana_new = $last_semana;

                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado la proyección satisfactoriamente</p>'
                        . '</div>';

                    /* ======================== ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ====================== */
                    ProyeccionUpdateProy::dispatch($request->id_proyeccion_modulo, $request->semana, $request->tipo, $request->curva, $request->semana_poda_siembra, $request->plantas_iniciales, $request->desecho, $request->tallos_planta, $request->tallos_ramo)
                        ->onQueue('proy_cosecha/update_proyeccion')->onConnection('sync');

                    /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                    $semana_desde = $last_semana_new;
                    $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);

                    /*if ($semana_desde != '')
                        ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                            ->onQueue('proy_cosecha/update_proyeccion');*/

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    /*jobUpdateResumenTotalSemanalExportcalas::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'])
                        ->onQueue('proy_cosecha/update_proyeccion');*/
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-info text-center">' .
                        '<p>No se han encontrado cambios</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La semana de inicio no se encuentra en el sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_ciclo' => 'required',
            'poda_siembra' => 'required',
            'curva' => 'required',
            'semana_poda_siembra' => 'required',
            'plantas_iniciales' => 'required',
            'plantas_muertas' => 'required',
            'desecho' => 'required',
            'conteo' => 'required',
            'area' => 'required',
        ], [
            'id_ciclo.required' => 'El ciclo es obligatorio',
            'area.required' => 'El área es obligatorio',
            'poda_siembra.required' => 'La poda siembra es obligatoria',
            'desecho.required' => 'El desecho es obligatorio',
            'plantas_iniciales.required' => 'Las plantas iniciales son obligatorias',
            'plantas_muertas.required' => 'Las plantas muertas son obligatorias',
            'conteo.required' => 'Los tallos por planta son obligatorios',
            'semana_poda_siembra.required' => 'Las semanas son obligatorias',
            'curva.required' => 'La curva es obligatoria',
        ]);
        $success = false;
        if (!$valida->fails()) {
            $ciclo = Ciclo::find($request->id_ciclo);
            /* ======================== ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ====================== */
            ProyeccionUpdateCiclo::dispatch($request->id_ciclo, $request->semana_poda_siembra, $request->curva, $request->poda_siembra, $request->plantas_iniciales, $request->plantas_muertas, $request->desecho, $request->conteo, $request->area, $request->no_recalcular_curva == 'true' ? 1 : 0)
                ->onQueue('update_ciclo')->onConnection('sync');

            jobUpdateProyeccionUpdateSemana::dispatch($ciclo->id_modulo)->onQueue('proy_cosecha')->onConnection('sync');

            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p>Se ha actualizado el ciclo satisfactoriamente</p>'
                . '</div>';

            /* ---------------- ACTUALIZAR PROYECCIONES ------------------- */
            $finca = getFincaActiva();
            $semana_desde = $ciclo->semana();
            $semanas = Semana::where('codigo', '>=', $semana_desde->codigo)
                ->where('id_variedad', $ciclo->id_variedad)
                ->get();
            foreach ($semanas as $sem) {
                jobActualizarProyeccion::dispatch($ciclo->id_variedad, '', $sem, $finca)
                    ->onQueue('actualizar_resumen_job');
            }

            /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
            jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                ->onQueue('actualizar_resumen_job');
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function restaurar_proyeccion(Request $request)
    {
        Log::info('INICIO DE RESTAURACION de PROYECCION, MODULO: ' . $request->modulo);
        Artisan::call('proyeccion:auto_create', [
            'modulo' => $request->modulo
        ]);
        return [
            'success' => true,
            'modulo' => $request->modulo
        ];
    }

    public function actualizar_proyecciones(Request $request)
    {
        Artisan::call('proyeccion:update_semanal', [
            'semana_desde' => $request->desde,
            'semana_hasta' => $request->hasta,
            'variedad' => $request->variedad,
            'modulo' => $request->modulo,
            'restriccion' => 0,
        ]);
        if (!$request->get_obj)
            return [
                'success' => true,
                'modulo' => $request->modulo
            ];
        else {
            $semana = Semana::All()
                ->where('estado', 1)
                ->where('codigo', $request->desde)
                ->where('id_variedad', $request->variedad)
                ->first();
            return [
                'success' => true,
                'modulo' => $request->modulo,
                'model' => ProyeccionModuloSemana::All()
                    ->where('estado', 1)
                    ->where('id_modulo', $request->modulo)
                    ->where('id_variedad', $request->variedad)
                    ->where('semana', $semana->codigo)
                    ->first(),
                'id_html' => $request->id_html,
            ];
        }
    }

    public function actualizar_semana(Request $request)
    {
        $semana = Semana::find($request->semana);
        foreach ($request->modulos as $mod)
            ProyeccionUpdateSemanal::dispatch($semana->codigo, $semana->codigo, $request->variedad, $mod, 0)
                ->onQueue('resumen_cosecha_semanal');
        return [
            'success' => true,
            'semana' => $request->semana
        ];
    }

    public function actualizar_datos(Request $request)
    {
        $modulos = [];
        foreach ($request->modulos as $mod)
            array_push($modulos, getModuloById($mod));

        $semanas = [];
        foreach ($request->semanas as $sem)
            array_push($semanas, Semana::find($sem));

        return view('adminlte.gestion.proyecciones.cosecha.forms.actualizar_datos', [
            'semanas' => $semanas,
            'modulos' => $modulos,
        ]);
    }

    /* ------------------------------------------------------------------- */
    public function actualizar_tipo(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ===================== CICLO ===================== */
                $ciclo = Ciclo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                    ->where('fecha_inicio', '<=', $sem->fecha_final)
                    ->where('estado', 1)
                    ->first();

                /* ====================== PROYECCION ===================== */
                $proy = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                /* ========================= ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ======================== */
                if ($ciclo != '') {
                    CicloUpdateCampo::dispatch($ciclo->id_ciclo, 'Tipo', $request->tipo)
                        ->onQueue('proy_cosecha/actualizar_tipo')->onConnection('sync');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                    jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                        ->onQueue('proy_cosecha/actualizar_tipo');
                }
                if ($proy != '')
                    ProyeccionUpdateCampo::dispatch($proy->id_proyeccion_modulo, 'Tipo', $request->tipo)
                        ->onQueue('proy_cosecha/actualizar_tipo')->onConnection('sync');

                /* ========================= ACTUALIZAR TABLA PROYECCION_MODULO_SEMANA ======================== */
                if ($ciclo != '' || $proy != '') {
                    $model = ProyeccionModuloSemana::All()
                        ->where('estado', 1)
                        ->where('id_modulo', $mod)
                        ->where('semana', $sem->codigo)
                        ->where('id_variedad', $request->variedad)
                        ->first();

                    $model->tipo = $model->tipo != 'Y' ? $request->tipo : $model->tipo;
                    if (in_array($model->tipo, ['S', 'P'])) {   // se trata de un ciclo
                        /* ===================== RECALCULAR el # de PODA_SIEMBRA ===================== */
                        $proyecciones = ProyeccionModuloSemana::whereIn('tipo', ['S', 'P', 'Y'])
                            ->where('id_modulo', $mod)
                            ->where('id_variedad', $request->variedad)
                            ->where('semana', '>=', $sem->codigo)
                            ->orderBy('semana')
                            ->get();

                        $poda_siembra = $model->modulo->getPodaSiembraByCiclo($model->id_ciclo);
                        foreach ($proyecciones as $proy) {
                            if ($proy->tipo == 'Y') {
                                if ($proy->info == 'P') {
                                    $last_proy = ProyeccionModulo::All()
                                        ->where('estado', 1)
                                        ->where('id_modulo', $proy->id_modulo)
                                        ->where('id_variedad', $proy->id_variedad)
                                        ->where('fecha_inicio', '<', $proy->fecha_inicio)
                                        ->sortBy('fecha_inicio')
                                        ->last();
                                    if ($last_proy != '') {
                                        $poda_siembra = $last_proy->poda_siembra + 1;
                                    } else {
                                        $poda_siembra = intval($poda_siembra + 1);
                                    }
                                }
                                $proy->poda_siembra = $poda_siembra;
                            } else {
                                $proy->tipo = $request->poda_siembra;
                                $proy->info = $request->poda_siembra == 'S' ? 'S-0' : $request->poda_siembra . '-' . $poda_siembra;
                            }
                            $proy->save();
                        }
                    } else {    // se trata de una proy
                        $poda_siembra = 0;
                        if ($request->tipo == 'P') {
                            $last_ciclo = Ciclo::All()
                                ->where('estado', 1)
                                ->where('id_variedad', $model->id_variedad)
                                ->where('id_modulo', $model->id_modulo)
                                ->sortBy('fecha_inicio')
                                ->last();
                            if ($last_ciclo != '') {
                                /* ===================== RECALCULAR el # de PODA_SIEMBRA ===================== */
                                $proyecciones = ProyeccionModuloSemana::whereIn('tipo', ['Y'])
                                    ->where('id_modulo', $mod)
                                    ->where('id_variedad', $request->variedad)
                                    ->where('semana', '>=', $sem->codigo)
                                    ->orderBy('semana')
                                    ->get();

                                $poda_siembra = $last_ciclo->modulo->getPodaSiembraByCiclo($last_ciclo->id_ciclo);
                                foreach ($proyecciones as $proy) {
                                    if ($proy->tipo == 'Y') {
                                        if ($proy->info == 'P') {
                                            $last_proy = ProyeccionModulo::All()
                                                ->where('estado', 1)
                                                ->where('id_modulo', $proy->id_modulo)
                                                ->where('id_variedad', $proy->id_variedad)
                                                ->where('fecha_inicio', '<', $proy->fecha_inicio)
                                                ->sortBy('fecha_inicio')
                                                ->last();
                                            if ($last_proy != '') {
                                                $poda_siembra = $last_proy->poda_siembra + 1;
                                            } else {
                                                $poda_siembra = intval($poda_siembra + 1);
                                            }
                                        }
                                        $proy->poda_siembra = $poda_siembra;
                                    }
                                    $proy->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_curva(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ================ CICLOS ===================== */
                $ciclo = Ciclo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                    ->where('fecha_inicio', '<=', $sem->fecha_final)
                    ->where('estado', 1)
                    ->first();

                /* ================ PROYECCIONES ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                /* ========================= ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ======================== */
                if ($ciclo != '' && $ciclo->activo == 1) {
                    if ($ciclo->curva != $request->curva) {
                        $model = $ciclo;
                        $obj = [
                            'id_modulo' => $model->id_modulo,
                            'id_variedad' => $model->id_variedad,
                        ];
                        $semana_ini_proy = $model->semana();

                        $semana_ini = min($model->semana()->codigo, $semana_ini_proy->codigo);    // en este caso son la misma semana

                        $next_proy = ProyeccionModuloSemana::where('estado', 1)
                            ->where('tabla', 'P')
                            ->where('tipo', 'Y')
                            ->where('semana', '>', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('modelo', '!=', $model->id_ciclo)
                            ->orderBy('semana')
                            ->get()->take(1);
                        $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                        $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                            ->where('semana', '>=', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->orderBy('semana')
                            ->get();

                        $cant_semanas_new = $model->semana_poda_siembra + count(explode('-', $request->curva));   // cantidad de semanas que durará la proy new

                        $last_semana = '';
                        $last_semana_new = '';
                        $pos_cosecha = 0;
                        $pos_proy = 0;
                        $pos_proy_new = '';
                        foreach ($proyecciones as $proy) {
                            if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // dentro de las semanas de la proy // semana de cosecha **
                                $proy->tabla = 'C';
                                $proy->modelo = $model->id_ciclo;

                                $proy->plantas_iniciales = $model->plantas_actuales();
                                $proy->tallos_planta = $model->conteo;
                                $proy->tallos_ramo = 0;
                                $proy->curva = $request->curva;
                                $proy->poda_siembra = $model->poda_siembra;
                                $proy->semana_poda_siembra = $model->semana_poda_siembra;
                                $proy->desecho = $model->desecho;
                                $proy->area = $model->area;
                                $proy->tipo = 'I';
                                $proy->info = ($pos_proy + 1) . 'º';

                                if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                    $proy->tipo = $model->poda_siembra;
                                    $proy->info = $model->poda_siembra . '-' . $model->modulo->getPodaSiembraByCiclo($model->id_ciclo);
                                }
                                if ($pos_proy + 1 >= $model->semana_poda_siembra) {
                                    $proy->tipo = 'T';
                                    $total = $model->plantas_actuales() * $model->conteo;
                                    $total = $total * ((100 - $model->desecho) / 100);
                                    $proy->proyectados = round($total * (explode('-', $request->curva)[$pos_cosecha] / 100), 2);
                                    $pos_cosecha++;
                                }
                                $pos_proy++;
                            } else if ($next_proy != '') {    // semanas despues de la proyeccion, pero en caso de que exista una siguiente proy
                                if ($last_semana == '')
                                    $last_semana = $proy->semana;
                                if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                    //dd($proy->semana . '... hay que mover la siguiente proyeccion');
                                    if ($pos_proy_new == '') {
                                        $pos_proy_new = 0;
                                        $pos_cosecha = 0;
                                    }
                                    if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                        $proy->tabla = 'P';
                                        $proy->modelo = $next_proy->modelo;

                                        $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                        $proy->tallos_planta = $next_proy->tallos_planta;
                                        $proy->tallos_ramo = $next_proy->tallos_ramo;
                                        $proy->curva = $next_proy->curva;
                                        $proy->poda_siembra = $next_proy->poda_siembra;
                                        $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                        $proy->desecho = $next_proy->desecho;
                                        $proy->area = $next_proy->area;
                                        $proy->tipo = 'I';
                                        $proy->info = ($pos_proy_new + 1) . 'º';
                                        $proy->proyectados = 0;

                                        if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                            $proy->tipo = $next_proy->tipo;
                                            $proy->info = $next_proy->info;
                                        }
                                        if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                            $proy->tipo = 'T';
                                            $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                            $total = $total * ((100 - $next_proy->desecho) / 100);
                                            $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                            $pos_cosecha++;
                                        }
                                    } else {    // semanas despues de la proyeccion
                                        if ($last_semana_new == '') {
                                            $last_semana_new = $proy->semana;
                                        }
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    }
                                    $pos_proy_new++;
                                } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                    //dd($proy->semana . '... es una semana que queda vacia antes de la siguiente proy');
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;
                                }
                                $pos_proy++;
                            } else {    // fuera de las semanas de la proy
                                if ($last_semana_new == '') {
                                    $last_semana_new = $proy->semana;
                                }
                                $proy->tipo = 'F';
                                $proy->proyectados = 0;
                                $proy->info = '-';
                                $proy->activo = 0;
                                $proy->plantas_iniciales = null;
                                $proy->plantas_actuales = null;
                                $proy->desecho = null;
                                $proy->curva = null;
                                $proy->semana_poda_siembra = null;
                                $proy->tallos_planta = null;
                                $proy->poda_siembra = null;
                                $proy->tabla = null;
                                $proy->modelo = null;

                                $pos_proy++;
                            }
                            $proy->save();
                        }

                        if ($last_semana_new == '')
                            $last_semana_new = $last_semana;

                        /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                        $semana_desde = $last_semana_new;
                        $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);


                        CicloUpdateCampo::dispatch($ciclo->id_ciclo, 'Curva', $request->curva)
                            ->onQueue('proy_cosecha/actualizar_curva');

                        /*if ($semana_desde != '')
                            ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                                ->onQueue('proy_cosecha');*/

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                        /*jobUpdateResumenTotalSemanalExportcalas::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'])
                            ->onQueue('proy_cosecha/actualizar_curva');*/
                    }
                }
                if ($proyeccion != '') {
                    if ($proyeccion->curva != $request->curva) {
                        $model = $proyeccion;
                        $obj = [
                            'id_modulo' => $model->id_modulo,
                            'id_variedad' => $model->id_variedad,
                        ];
                        $semana_ini_proy = $model->semana;

                        $semana_ini = min($model->semana->codigo, $semana_ini_proy->codigo);    // en este caso son la misma semana

                        $next_proy = ProyeccionModuloSemana::where('estado', 1)
                            ->where('tabla', 'P')
                            ->where('tipo', 'Y')
                            ->where('semana', '>', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('modelo', '!=', $model->id_proyeccion_modulo)
                            ->orderBy('semana')
                            ->get()->take(1);
                        $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                        $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                            ->where('semana', '>=', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->orderBy('semana')
                            ->get();

                        $cant_semanas_new = $model->semana_poda_siembra + count(explode('-', $request->curva));   // cantidad de semanas que durará la proy new

                        $last_semana = '';
                        $last_semana_new = '';
                        $pos_cosecha = 0;
                        $pos_proy = 0;
                        $pos_proy_new = '';
                        foreach ($proyecciones as $proy) {
                            if ($proy->tabla != 'C') {   // validar que el rango de semanas consultadas estan fuera de los ciclos reales
                                if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // dentro de las semanas de la proy // semana de cosecha **
                                    $proy->tabla = 'P';
                                    $proy->modelo = $model->id_proyeccion_modulo;

                                    $proy->plantas_iniciales = $model->plantas_iniciales;
                                    $proy->tallos_planta = $model->tallos_planta;
                                    $proy->tallos_ramo = $model->tallos_ramo;
                                    $proy->curva = $request->curva;
                                    $proy->poda_siembra = $model->poda_siembra;
                                    $proy->semana_poda_siembra = $model->semana_poda_siembra;
                                    $proy->desecho = $model->desecho;
                                    $proy->area = $model->modulo->area;
                                    $proy->tipo = 'I';
                                    $proy->info = ($pos_proy + 1) . 'º';

                                    if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                        $proy->tipo = 'Y';
                                        $proy->info = $model->tipo;
                                    }
                                    if ($pos_proy + 1 >= $model->semana_poda_siembra) {
                                        $proy->tipo = 'T';
                                        $total = $model->plantas_iniciales * $model->tallos_planta;
                                        $total = $total * ((100 - $model->desecho) / 100);
                                        $proy->proyectados = round($total * (explode('-', $request->curva)[$pos_cosecha] / 100), 2);
                                        $pos_cosecha++;
                                    }
                                    $pos_proy++;
                                } else if ($next_proy != '') {    // semanas despues de la proyeccion, pero en caso de que exista una siguiente proy
                                    if ($last_semana == '')
                                        $last_semana = $proy->semana;
                                    if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                        //dd($proy->semana . '... hay que mover la siguiente proyeccion');
                                        if ($pos_proy_new == '') {
                                            $pos_proy_new = 0;
                                            $pos_cosecha = 0;
                                        }
                                        if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                            $proy->tabla = 'P';
                                            $proy->modelo = $next_proy->modelo;

                                            $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                            $proy->tallos_planta = $next_proy->tallos_planta;
                                            $proy->tallos_ramo = $next_proy->tallos_ramo;
                                            $proy->curva = $next_proy->curva;
                                            $proy->poda_siembra = $next_proy->poda_siembra;
                                            $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                            $proy->desecho = $next_proy->desecho;
                                            $proy->area = $next_proy->area;
                                            $proy->tipo = 'I';
                                            $proy->info = ($pos_proy_new + 1) . 'º';
                                            $proy->proyectados = 0;

                                            if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                                $proy->tipo = $next_proy->tipo;
                                                $proy->info = $next_proy->info;
                                            }
                                            if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                                $proy->tipo = 'T';
                                                $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                                $total = $total * ((100 - $next_proy->desecho) / 100);
                                                $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                                $pos_cosecha++;
                                            }
                                        } else {    // semanas despues de la proyeccion
                                            if ($last_semana_new == '') {
                                                $last_semana_new = $proy->semana;
                                            }
                                            $proy->tipo = 'F';
                                            $proy->proyectados = 0;
                                            $proy->info = '-';
                                            $proy->activo = 0;
                                            $proy->plantas_iniciales = null;
                                            $proy->plantas_actuales = null;
                                            $proy->desecho = null;
                                            $proy->curva = null;
                                            $proy->semana_poda_siembra = null;
                                            $proy->tallos_planta = null;
                                            $proy->poda_siembra = null;
                                            $proy->tabla = null;
                                            $proy->modelo = null;
                                        }
                                        $pos_proy_new++;
                                    } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                        //dd($proy->semana . '... es una semana que queda vacia antes de la siguiente proy');
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    }
                                    $pos_proy++;
                                } else {    // fuera de las semanas de la proy
                                    if ($last_semana_new == '') {
                                        $last_semana_new = $proy->semana;
                                    }
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;

                                    $pos_proy++;
                                }
                            } else {
                                break;
                            }
                            $proy->save();
                        }

                        if ($last_semana_new == '')
                            $last_semana_new = $last_semana;

                        /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                        $semana_desde = $last_semana_new;
                        $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);

                        ProyeccionUpdateCampo::dispatch($model->id_proyeccion_modulo, 'Curva', $request->curva)
                            ->onQueue('proy_cosecha/actualizar_curva');

                        /*if ($semana_desde != '')
                            ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                                ->onQueue('proy_cosecha');*/

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                        /*jobUpdateResumenTotalSemanalExportcalas::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'])
                            ->onQueue('proy_cosecha');*/
                    }
                }
            }

            /* ================ SEMANAS ===================== */
            if ($request->check_save_semana == 'true') {
                $sem->curva = $request->curva;
                $sem->save();
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_semana_cosecha(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ================ CICLOS ===================== */
                $ciclo = Ciclo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                    ->where('fecha_inicio', '<=', $sem->fecha_final)
                    ->where('estado', 1)
                    ->first();

                /* ================ PROYECCIONES ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                /* ========================= ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ======================== */
                if ($ciclo != '' && $ciclo->activo == 1) {
                    if ($ciclo->semana_poda_cosecha != $request->semana_cosecha) {
                        $model = $ciclo;
                        $obj = [
                            'id_modulo' => $model->id_modulo,
                            'id_variedad' => $model->id_variedad,
                        ];
                        $semana_ini_proy = $model->semana();

                        $semana_ini = min($model->semana()->codigo, $semana_ini_proy->codigo);    // en este caso son la misma semana

                        $next_proy = ProyeccionModuloSemana::where('estado', 1)
                            ->where('tabla', 'P')
                            ->where('tipo', 'Y')
                            ->where('semana', '>', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('modelo', '!=', $model->id_ciclo)
                            ->orderBy('semana')
                            ->get()->take(1);
                        $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                        $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                            ->where('semana', '>=', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->orderBy('semana')
                            ->get();

                        $cant_semanas_new = $request->semana_cosecha + count(explode('-', $model->curva));   // cantidad de semanas que durará la proy new

                        $last_semana = '';
                        $last_semana_new = '';
                        $pos_cosecha = 0;
                        $pos_proy = 0;
                        $pos_proy_new = '';
                        foreach ($proyecciones as $proy) {
                            if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // dentro de las semanas del ciclo // semana de cosecha **
                                $proy->tabla = 'C';
                                $proy->modelo = $model->id_ciclo;

                                $proy->plantas_iniciales = $model->plantas_actuales();
                                $proy->tallos_planta = $model->conteo;
                                $proy->tallos_ramo = 0;
                                $proy->curva = $model->curva;
                                $proy->poda_siembra = $model->poda_siembra;
                                $proy->semana_poda_siembra = $request->semana_cosecha;
                                $proy->desecho = $model->desecho;
                                $proy->area = $model->area;
                                $proy->tipo = 'I';
                                $proy->info = ($pos_proy + 1) . 'º';

                                if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                    $proy->tipo = $model->poda_siembra;
                                    $proy->info = $model->poda_siembra . '-' . $model->modulo->getPodaSiembraByCiclo($model->id_ciclo);
                                }
                                if ($pos_proy + 1 >= $request->semana_cosecha) {
                                    $proy->tipo = 'T';
                                    $total = $model->plantas_actuales() * $model->conteo;
                                    $total = $total * ((100 - $model->desecho) / 100);
                                    $proy->proyectados = round($total * (explode('-', $model->curva)[$pos_cosecha] / 100), 2);
                                    $pos_cosecha++;
                                }
                                $pos_proy++;
                            } else if ($next_proy != '') {    // semanas despues del ciclo, pero en caso de que exista una siguiente proy
                                if ($last_semana == '')
                                    $last_semana = $proy->semana;
                                if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                    if ($pos_proy_new == '') {
                                        $pos_proy_new = 0;
                                        $pos_cosecha = 0;
                                    }
                                    if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                        $proy->tabla = 'P';
                                        $proy->modelo = $next_proy->modelo;

                                        $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                        $proy->tallos_planta = $next_proy->tallos_planta;
                                        $proy->tallos_ramo = $next_proy->tallos_ramo;
                                        $proy->curva = $next_proy->curva;
                                        $proy->poda_siembra = $next_proy->poda_siembra;
                                        $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                        $proy->desecho = $next_proy->desecho;
                                        $proy->area = $next_proy->area;
                                        $proy->tipo = 'I';
                                        $proy->info = ($pos_proy_new + 1) . 'º';
                                        $proy->proyectados = 0;

                                        if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                            $proy->tipo = $next_proy->tipo;
                                            $proy->info = $next_proy->info;
                                        }
                                        if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                            $proy->tipo = 'T';
                                            $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                            $total = $total * ((100 - $next_proy->desecho) / 100);
                                            $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                            $pos_cosecha++;
                                        }
                                    } else {    // semanas despues de la proyeccion
                                        if ($last_semana_new == '') {
                                            $last_semana_new = $proy->semana;
                                        }
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    }
                                    $pos_proy_new++;
                                } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;
                                }
                                $pos_proy++;
                            } else {    // fuera de las semanas del ciclo
                                if ($last_semana_new == '') {
                                    $last_semana_new = $proy->semana;
                                }
                                $proy->tipo = 'F';
                                $proy->proyectados = 0;
                                $proy->info = '-';
                                $proy->activo = 0;
                                $proy->plantas_iniciales = null;
                                $proy->plantas_actuales = null;
                                $proy->desecho = null;
                                $proy->curva = null;
                                $proy->semana_poda_siembra = null;
                                $proy->tallos_planta = null;
                                $proy->poda_siembra = null;
                                $proy->tabla = null;
                                $proy->modelo = null;

                                $pos_proy++;
                            }
                            $proy->save();
                        }

                        if ($last_semana_new == '')
                            $last_semana_new = $last_semana;

                        /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                        $semana_desde = $last_semana_new;
                        $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);

                        CicloUpdateCampo::dispatch($ciclo->id_ciclo, 'SemanaCosecha', $request->semana_cosecha)
                            ->onQueue('proy_cosecha/actualizar_semana_cosecha');

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                        jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                            ->onQueue('proy_cosecha/actualizar_semana_cosecha');

                        /*if ($semana_desde != '')
                            ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                                ->onQueue('proy_cosecha');*/

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                        /*jobUpdateResumenTotalSemanalExportcalas::dispatch($model->semana()->codigo, $semana_fin->codigo, $obj['id_variedad'])
                            ->onQueue('proy_cosecha');*/
                    }
                }
                if ($proyeccion != '') {
                    if ($proyeccion->semana_poda_siembra != $request->semana_cosecha) {
                        $model = $proyeccion;
                        $obj = [
                            'id_modulo' => $model->id_modulo,
                            'id_variedad' => $model->id_variedad,
                        ];
                        $semana_ini_proy = $model->semana;

                        $semana_ini = min($model->semana->codigo, $semana_ini_proy->codigo);    // en este caso son la misma semana

                        $next_proy = ProyeccionModuloSemana::where('estado', 1)
                            ->where('tabla', 'P')
                            ->where('tipo', 'Y')
                            ->where('semana', '>', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('modelo', '!=', $model->id_proyeccion_modulo)
                            ->orderBy('semana')
                            ->get()->take(1);
                        $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                        $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                            ->where('semana', '>=', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->orderBy('semana')
                            ->get();

                        $cant_semanas_new = $request->semana_cosecha + count(explode('-', $model->curva));   // cantidad de semanas que durará la proy new

                        $last_semana = '';
                        $last_semana_new = '';
                        $pos_cosecha = 0;
                        $pos_proy = 0;
                        $pos_proy_new = '';
                        foreach ($proyecciones as $proy) {
                            if ($proy->tabla != 'C') {   // validar que el rango de semanas consultadas estan fuera de los ciclos reales
                                if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // dentro de las semanas de la proy // semana de cosecha **
                                    $proy->tabla = 'P';
                                    $proy->modelo = $model->id_proyeccion_modulo;

                                    $proy->plantas_iniciales = $model->plantas_iniciales;
                                    $proy->tallos_planta = $model->tallos_planta;
                                    $proy->tallos_ramo = $model->tallos_ramo;
                                    $proy->curva = $model->curva;
                                    $proy->poda_siembra = $model->poda_siembra;
                                    $proy->semana_poda_siembra = $request->semana_cosecha;
                                    $proy->desecho = $model->desecho;
                                    $proy->area = $model->modulo->area;
                                    $proy->tipo = 'I';
                                    $proy->info = ($pos_proy + 1) . 'º';

                                    if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                        $proy->tipo = 'Y';
                                        $proy->info = $model->tipo;
                                    }
                                    if ($pos_proy + 1 >= $request->semana_cosecha) {
                                        $proy->tipo = 'T';
                                        $total = $model->plantas_iniciales * $model->tallos_planta;
                                        $total = $total * ((100 - $model->desecho) / 100);
                                        $proy->proyectados = round($total * (explode('-', $model->curva)[$pos_cosecha] / 100), 2);
                                        $pos_cosecha++;
                                    }
                                    $pos_proy++;
                                } else if ($next_proy != '') {    // semanas despues de la proyeccion, pero en caso de que exista una siguiente proy
                                    if ($last_semana == '')
                                        $last_semana = $proy->semana;
                                    if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                        //dd($proy->semana . '... hay que mover la siguiente proyeccion');
                                        if ($pos_proy_new == '') {
                                            $pos_proy_new = 0;
                                            $pos_cosecha = 0;
                                        }
                                        if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                            $proy->tabla = 'P';
                                            $proy->modelo = $next_proy->modelo;

                                            $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                            $proy->tallos_planta = $next_proy->tallos_planta;
                                            $proy->tallos_ramo = $next_proy->tallos_ramo;
                                            $proy->curva = $next_proy->curva;
                                            $proy->poda_siembra = $next_proy->poda_siembra;
                                            $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                            $proy->desecho = $next_proy->desecho;
                                            $proy->area = $next_proy->area;
                                            $proy->tipo = 'I';
                                            $proy->info = ($pos_proy_new + 1) . 'º';
                                            $proy->proyectados = 0;

                                            if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                                $proy->tipo = $next_proy->tipo;
                                                $proy->info = $next_proy->info;
                                            }
                                            if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                                $proy->tipo = 'T';
                                                $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                                $total = $total * ((100 - $next_proy->desecho) / 100);
                                                $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                                $pos_cosecha++;
                                            }
                                        } else {    // semanas despues de la proyeccion
                                            if ($last_semana_new == '') {
                                                $last_semana_new = $proy->semana;
                                            }
                                            $proy->tipo = 'F';
                                            $proy->proyectados = 0;
                                            $proy->info = '-';
                                            $proy->activo = 0;
                                            $proy->plantas_iniciales = null;
                                            $proy->plantas_actuales = null;
                                            $proy->desecho = null;
                                            $proy->curva = null;
                                            $proy->semana_poda_siembra = null;
                                            $proy->tallos_planta = null;
                                            $proy->poda_siembra = null;
                                            $proy->tabla = null;
                                            $proy->modelo = null;
                                        }
                                        $pos_proy_new++;
                                    } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                        //dd($proy->semana . '... es una semana que queda vacia antes de la siguiente proy');
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    }
                                    $pos_proy++;
                                } else {    // fuera de las semanas de la proy
                                    if ($last_semana_new == '') {
                                        $last_semana_new = $proy->semana;
                                    }
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;

                                    $pos_proy++;
                                }
                            } else {
                                break;
                            }
                            $proy->save();
                        }

                        if ($last_semana_new == '')
                            $last_semana_new = $last_semana;

                        /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                        $semana_desde = $last_semana_new;
                        $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);

                        ProyeccionUpdateCampo::dispatch($model->id_proyeccion_modulo, 'SemanaCosecha', $request->semana_cosecha)
                            ->onQueue('proy_cosecha/actualizar_semana_cosecha');

                        /*if ($semana_desde != '')
                            ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                                ->onQueue('proy_cosecha');*/

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                        /*jobUpdateResumenTotalSemanalExportcalas::dispatch($proyeccion->semana->codigo, $semana_fin->codigo, $proyeccion->id_variedad)
                            ->onQueue('proy_cosecha');*/
                    }
                }
            }

            /* ================ SEMANAS ===================== */
            if ($request->check_save_semana == 'true') {
                $sem->semana_poda = $request->semana_cosecha;
                $sem->save();
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_plantas_iniciales(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ===================== CICLO ===================== */
                $ciclo = Ciclo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                    ->where('fecha_inicio', '<=', $sem->fecha_final)
                    ->where('estado', 1)
                    ->first();

                /* ====================== PROYECCION ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                if ($ciclo != '') {
                    /* ========================= ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ======================== */
                    CicloUpdateCampo::dispatch($ciclo->id_ciclo, 'PlantasIniciales', $request->plantas_iniciales)
                        ->onQueue('proy_cosecha/actualizar_plantas_iniciales')->onConnection('sync');

                    $ciclo = Ciclo::find($ciclo->id_ciclo);

                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'C')
                        ->where('modelo', $ciclo->id_ciclo)
                        ->orderBy('semana')
                        ->get();

                    $pos_cosecha = 0;
                    foreach ($proyecciones as $pos_proy => $proy) {
                        $proy->plantas_iniciales = $ciclo->plantas_actuales();
                        if ($proy->tipo == 'T') {
                            $total = $ciclo->plantas_actuales() * $proy->tallos_planta;
                            $total = $total * ((100 - $proy->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                        $proy->save();
                    }


                    $semana_fin = getLastSemanaByVariedad($ciclo->id_variedad);
                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    jobUpdateResumenTotalSemanalExportcalas::dispatch($ciclo->semana()->codigo, $semana_fin->codigo, $ciclo->id_variedad)
                        ->onQueue('proy_cosecha');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                    jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                        ->onQueue('proy_cosecha/actualizar_plantas_iniciales');
                }
                if ($proyeccion != '') {
                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'P')
                        ->where('modelo', $proyeccion->id_proyeccion_modulo)
                        ->orderBy('semana')
                        ->get();

                    $pos_cosecha = 0;
                    foreach ($proyecciones as $pos_proy => $proy) {
                        $proy->plantas_iniciales = $request->plantas_iniciales;
                        if ($proy->tipo == 'T') {
                            $total = $request->plantas_iniciales * $proy->tallos_planta;
                            $total = $total * ((100 - $proy->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                        $proy->save();
                    }

                    ProyeccionUpdateCampo::dispatch($proyeccion->id_proyeccion_modulo, 'PlantasIniciales', $request->plantas_iniciales)
                        ->onQueue('proy_cosecha/actualizar_plantas_iniciales');

                    $semana_fin = getLastSemanaByVariedad($proyeccion->id_variedad);
                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    jobUpdateResumenTotalSemanalExportcalas::dispatch($proyeccion->semana->codigo, $semana_fin->codigo, $proyeccion->id_variedad)
                        ->onQueue('proy_cosecha');
                }
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_desecho(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ===================== CICLO ===================== */
                $ciclo = Ciclo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                    ->where('fecha_inicio', '<=', $sem->fecha_final)
                    ->where('estado', 1)
                    ->first();

                /* ====================== PROYECCION ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                if ($ciclo != '') {
                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'C')
                        ->where('modelo', $ciclo->id_ciclo)
                        ->orderBy('semana')
                        ->get();

                    $pos_cosecha = 0;
                    foreach ($proyecciones as $pos_proy => $proy) {
                        $proy->desecho = $request->desecho;
                        if ($proy->tipo == 'T') {
                            $total = $proy->plantas_iniciales * $proy->tallos_planta;
                            $total = $total * ((100 - $request->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                        $proy->save();
                    }

                    /* ========================= ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ======================== */
                    CicloUpdateCampo::dispatch($ciclo->id_ciclo, 'Desecho', $request->desecho)
                        ->onQueue('proy_cosecha/actualizar_desecho');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    $semana_fin = getLastSemanaByVariedad($ciclo->id_variedad);
                    jobUpdateResumenTotalSemanalExportcalas::dispatch($ciclo->semana()->codigo, $semana_fin->codigo, $ciclo->id_variedad)
                        ->onQueue('proy_cosecha/actualizar_desecho');

                }
                if ($proyeccion != '') {
                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'P')
                        ->where('modelo', $proyeccion->id_proyeccion_modulo)
                        ->orderBy('semana')
                        ->get();

                    $pos_cosecha = 0;
                    foreach ($proyecciones as $pos_proy => $proy) {
                        $proy->desecho = $request->desecho;
                        if ($proy->tipo == 'T') {
                            $total = $proy->plantas_iniciales * $proy->tallos_planta;
                            $total = $total * ((100 - $request->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                        $proy->save();
                    }

                    ProyeccionUpdateCampo::dispatch($proyeccion->id_proyeccion_modulo, 'Desecho', $request->desecho)
                        ->onQueue('proy_cosecha/actualizar_desecho');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    $semana_fin = getLastSemanaByVariedad($proyeccion->id_variedad);
                    jobUpdateResumenTotalSemanalExportcalas::dispatch($proyeccion->semana->codigo, $semana_fin->codigo, $proyeccion->id_variedad)
                        ->onQueue('proy_cosecha/actualizar_desecho');
                }
            }

            /* ================ SEMANAS ===================== */
            if ($request->check_save_semana == 'true') {
                $sem->desecho = $request->desecho;
                $sem->save();
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_tallos_planta(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ===================== CICLO ===================== */
                $ciclo = Ciclo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                    ->where('fecha_inicio', '<=', $sem->fecha_final)
                    ->where('estado', 1)
                    ->first();

                /* ====================== PROYECCION ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                if ($ciclo != '') {
                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'C')
                        ->where('modelo', $ciclo->id_ciclo)
                        ->orderBy('semana')
                        ->get();

                    $pos_cosecha = 0;
                    foreach ($proyecciones as $pos_proy => $proy) {
                        $proy->tallos_planta = $request->tallos_planta;
                        if ($proy->tipo == 'T') {
                            $total = $proy->plantas_iniciales * $proy->tallos_planta;
                            $total = $total * ((100 - $proy->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                        $proy->save();
                    }

                    /* ========================= ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ======================== */
                    CicloUpdateCampo::dispatch($ciclo->id_ciclo, 'TallosPlanta', $request->tallos_planta)
                        ->onQueue('proy_cosecha/actualizar_tallos_planta');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    $semana_fin = getLastSemanaByVariedad($ciclo->id_variedad);
                    jobUpdateResumenTotalSemanalExportcalas::dispatch($ciclo->semana()->codigo, $semana_fin->codigo, $ciclo->id_variedad)
                        ->onQueue('proy_cosecha/actualizar_tallos_planta');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                    jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                        ->onQueue('proy_cosecha/actualizar_tallos_planta');
                }
                if ($proyeccion != '') {
                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'P')
                        ->where('modelo', $proyeccion->id_proyeccion_modulo)
                        ->orderBy('semana')
                        ->get();

                    $pos_cosecha = 0;
                    foreach ($proyecciones as $pos_proy => $proy) {
                        $proy->tallos_planta = $request->tallos_planta;
                        if ($proy->tipo == 'T') {
                            $total = $proy->plantas_iniciales * $proy->tallos_planta;
                            $total = $total * ((100 - $proy->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                        $proy->save();
                    }

                    ProyeccionUpdateCampo::dispatch($proyeccion->id_proyeccion_modulo, 'TallosPlanta', $request->tallos_planta)
                        ->onQueue('proy_cosecha/actualizar_tallos_planta');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    $semana_fin = getLastSemanaByVariedad($proyeccion->id_variedad);
                    jobUpdateResumenTotalSemanalExportcalas::dispatch($proyeccion->semana->codigo, $semana_fin->codigo, $proyeccion->id_variedad)
                        ->onQueue('proy_cosecha/actualizar_tallos_planta');
                }
            }

            /* ================ SEMANAS ===================== */
            if ($request->check_save_semana == 'true') {
                $sem->tallos_planta_poda = $request->tallos_planta;
                $sem->save();
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_tallos_ramo(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ====================== PROYECCION ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                if ($proyeccion != '') {
                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'P')
                        ->where('modelo', $proyeccion->id_proyeccion_modulo)
                        ->orderBy('semana')
                        ->get();

                    foreach ($proyecciones as $pos_proy => $proy) {
                        $proy->tallos_ramo = $request->tallos_ramo;
                        $proy->save();
                    }

                    ProyeccionUpdateCampo::dispatch($proyeccion->id_proyeccion_modulo, 'TallosRamo', $request->tallos_ramo)
                        ->onQueue('proy_cosecha/actualizar_tallos_ramo');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    $semana_fin = getLastSemanaByVariedad($proyeccion->id_variedad);
                    jobUpdateResumenTotalSemanalExportcalas::dispatch($proyeccion->semana->codigo, $semana_fin->codigo, $proyeccion->id_variedad)
                        ->onQueue('proy_cosecha/actualizar_tallos_ramo');
                }
            }

            /* ================ SEMANAS ===================== */
            if ($request->check_save_semana == 'true') {
                $sem->tallos_ramo_poda = $request->tallos_ramo;
                $sem->save();
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_semana_cosecha_siembra(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            $sem->semana_siembra = $request->semana_cosecha_siembra;
            $sem->save();
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_tallos_planta_siembra(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            $sem->tallos_planta_siembra = $request->tallos_planta_siembra;
            $sem->save();
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function actualizar_tallos_ramo_siembra(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            $sem->tallos_ramo_siembra = $request->tallos_ramo_siembra;
            $sem->save();
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    /* ------------------------------------------------------------------- */
    public function mover_fechas(Request $request)
    {
        $modulos = [];
        foreach ($request->modulos as $mod)
            array_push($modulos, getModuloById($mod));

        $semanas = [];
        foreach ($request->semanas as $sem)
            array_push($semanas, Semana::find($sem));

        return view('adminlte.gestion.proyecciones.cosecha.forms.mover_fechas', [
            'semanas' => $semanas,
            'modulos' => $modulos,
        ]);
    }

    public function mover_cosecha(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ===================== CICLO ===================== */
                $ciclo = Ciclo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('fecha_inicio', '>=', $sem->fecha_inicial)
                    ->where('fecha_inicio', '<=', $sem->fecha_final)
                    ->where('estado', 1)
                    ->first();

                /* ====================== PROYECCION ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                /* ========================= ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ======================== */
                if ($ciclo != '') {
                    $semana_cosecha = $ciclo->semana_poda_siembra + $request->mover;
                    if ($semana_cosecha != $ciclo->semana_poda_siembra && $semana_cosecha >= 5 && $ciclo->activo == 1) {
                        $model = $ciclo;
                        $obj = [
                            'id_modulo' => $model->id_modulo,
                            'id_variedad' => $model->id_variedad,
                        ];
                        $semana_ini_proy = $model->semana();

                        $semana_ini = min($model->semana()->codigo, $semana_ini_proy->codigo);    // en este caso son la misma semana

                        $next_proy = ProyeccionModuloSemana::where('estado', 1)
                            ->where('tabla', 'P')
                            ->where('tipo', 'Y')
                            ->where('semana', '>', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('modelo', '!=', $model->id_ciclo)
                            ->orderBy('semana')
                            ->get()->take(1);
                        $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                        $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                            ->where('semana', '>=', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->orderBy('semana')
                            ->get();

                        $cant_semanas_new = $semana_cosecha + count(explode('-', $model->curva));   // cantidad de semanas que durará la proy new

                        $last_semana = '';
                        $last_semana_new = '';
                        $pos_cosecha = 0;
                        $pos_proy = 0;
                        $pos_proy_new = '';
                        foreach ($proyecciones as $proy) {
                            if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // dentro de las semanas de la proy // semana de cosecha **
                                $proy->tabla = 'C';
                                $proy->modelo = $model->id_ciclo;

                                $proy->plantas_iniciales = $model->plantas_actuales();
                                $proy->tallos_planta = $model->conteo;
                                $proy->tallos_ramo = 0;
                                $proy->curva = $model->curva;
                                $proy->poda_siembra = $model->poda_siembra;
                                $proy->semana_poda_siembra = $semana_cosecha;
                                $proy->desecho = $model->desecho;
                                $proy->area = $model->area;
                                $proy->tipo = 'I';
                                $proy->info = ($pos_proy + 1) . 'º';

                                if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                    $proy->tipo = $model->poda_siembra;
                                    $proy->info = $model->poda_siembra . '-' . $model->modulo->getPodaSiembraByCiclo($model->id_ciclo);
                                }
                                if ($pos_proy + 1 >= $semana_cosecha) {
                                    $proy->tipo = 'T';
                                    $total = $model->plantas_actuales() * $model->conteo;
                                    $total = $total * ((100 - $model->desecho) / 100);
                                    $proy->proyectados = round($total * (explode('-', $model->curva)[$pos_cosecha] / 100), 2);
                                    $pos_cosecha++;
                                }
                                $pos_proy++;
                            } else if ($next_proy != '') {    // semanas despues de la proyeccion, pero en caso de que exista una siguiente proy
                                if ($last_semana == '')
                                    $last_semana = $proy->semana;
                                if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                    //dd($proy->semana . '... hay que mover la siguiente proyeccion');
                                    if ($pos_proy_new == '') {
                                        $pos_proy_new = 0;
                                        $pos_cosecha = 0;
                                    }
                                    if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                        $proy->tabla = 'P';
                                        $proy->modelo = $next_proy->modelo;

                                        $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                        $proy->tallos_planta = $next_proy->tallos_planta;
                                        $proy->tallos_ramo = $next_proy->tallos_ramo;
                                        $proy->curva = $next_proy->curva;
                                        $proy->poda_siembra = $next_proy->poda_siembra;
                                        $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                        $proy->desecho = $next_proy->desecho;
                                        $proy->area = $next_proy->area;
                                        $proy->tipo = 'I';
                                        $proy->info = ($pos_proy_new + 1) . 'º';
                                        $proy->proyectados = 0;

                                        if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                            $proy->tipo = $next_proy->tipo;
                                            $proy->info = $next_proy->info;
                                        }
                                        if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                            $proy->tipo = 'T';
                                            $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                            $total = $total * ((100 - $next_proy->desecho) / 100);
                                            $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                            $pos_cosecha++;
                                        }
                                    } else {    // semanas despues de la proyeccion
                                        if ($last_semana_new == '') {
                                            $last_semana_new = $proy->semana;
                                        }
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    }
                                    $pos_proy_new++;
                                } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                    //dd($proy->semana . '... es una semana que queda vacia antes de la siguiente proy');
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;
                                }
                                $pos_proy++;
                            } else {    // fuera de las semanas de la proy
                                if ($last_semana_new == '') {
                                    $last_semana_new = $proy->semana;
                                }
                                $proy->tipo = 'F';
                                $proy->proyectados = 0;
                                $proy->info = '-';
                                $proy->activo = 0;
                                $proy->plantas_iniciales = null;
                                $proy->plantas_actuales = null;
                                $proy->desecho = null;
                                $proy->curva = null;
                                $proy->semana_poda_siembra = null;
                                $proy->tallos_planta = null;
                                $proy->poda_siembra = null;
                                $proy->tabla = null;
                                $proy->modelo = null;

                                $pos_proy++;
                            }
                            $proy->save();
                        }

                        if ($last_semana_new == '')
                            $last_semana_new = $last_semana;

                        /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                        $semana_desde = $last_semana_new;
                        $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);

                        CicloUpdateCampo::dispatch($ciclo->id_ciclo, 'SemanaCosecha', $semana_cosecha)
                            ->onQueue('proy_cosecha/actualizar_semana_cosecha');

                        /* if ($semana_desde != '')
                             ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                                 ->onQueue('proy_cosecha');*/

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                        /*jobUpdateResumenTotalSemanalExportcalas::dispatch($semana_ini, $semana_fin->codigo, $model->id_variedad)
                            ->onQueue('proy_cosecha');*/
                    }
                }
                if ($proyeccion != '' && $request->check_save_proy == 'true') {
                    $semana_cosecha = $proyeccion->semana_poda_siembra + $request->mover;
                    if ($semana_cosecha != $proyeccion->semana_poda_siembra && $semana_cosecha >= 5) {
                        $model = $proyeccion;
                        $obj = [
                            'id_modulo' => $model->id_modulo,
                            'id_variedad' => $model->id_variedad,
                        ];
                        $semana_ini_proy = $model->semana;

                        $semana_ini = min($model->semana->codigo, $semana_ini_proy->codigo);    // en este caso son la misma semana

                        $next_proy = ProyeccionModuloSemana::where('estado', 1)
                            ->where('tabla', 'P')
                            ->where('tipo', 'Y')
                            ->where('semana', '>', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('modelo', '!=', $model->id_proyeccion_modulo)
                            ->orderBy('semana')
                            ->get()->take(1);
                        $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                        $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                            ->where('semana', '>=', $semana_ini)
                            ->where('id_modulo', $model->id_modulo)
                            ->where('id_variedad', $model->id_variedad)
                            ->orderBy('semana')
                            ->get();

                        $cant_semanas_new = $semana_cosecha + count(explode('-', $model->curva));   // cantidad de semanas que durará la proy new

                        $last_semana = '';
                        $last_semana_new = '';
                        $pos_cosecha = 0;
                        $pos_proy = 0;
                        $pos_proy_new = '';
                        foreach ($proyecciones as $proy) {
                            if ($proy->tabla != 'C') {   // validar que el rango de semanas consultadas estan fuera de los ciclos reales
                                if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // dentro de las semanas de la proy // semana de cosecha **
                                    $proy->tabla = 'P';
                                    $proy->modelo = $model->id_proyeccion_modulo;

                                    $proy->plantas_iniciales = $model->plantas_iniciales;
                                    $proy->tallos_planta = $model->tallos_planta;
                                    $proy->tallos_ramo = $model->tallos_ramo;
                                    $proy->curva = $model->curva;
                                    $proy->poda_siembra = $model->poda_siembra;
                                    $proy->semana_poda_siembra = $semana_cosecha;
                                    $proy->desecho = $model->desecho;
                                    $proy->area = $model->modulo->area;
                                    $proy->tipo = 'I';
                                    $proy->info = ($pos_proy + 1) . 'º';

                                    if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                        $proy->tipo = 'Y';
                                        $proy->info = $model->tipo;
                                    }
                                    if ($pos_proy + 1 >= $semana_cosecha) {
                                        $proy->tipo = 'T';
                                        $total = $model->plantas_iniciales * $model->tallos_planta;
                                        $total = $total * ((100 - $model->desecho) / 100);
                                        $proy->proyectados = round($total * (explode('-', $model->curva)[$pos_cosecha] / 100), 2);
                                        $pos_cosecha++;
                                    }
                                    $pos_proy++;
                                } else if ($next_proy != '') {    // semanas despues de la proyeccion, pero en caso de que exista una siguiente proy
                                    if ($last_semana == '')
                                        $last_semana = $proy->semana;
                                    if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                        //dd($proy->semana . '... hay que mover la siguiente proyeccion');
                                        if ($pos_proy_new == '') {
                                            $pos_proy_new = 0;
                                            $pos_cosecha = 0;
                                        }
                                        if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                            $proy->tabla = 'P';
                                            $proy->modelo = $next_proy->modelo;

                                            $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                            $proy->tallos_planta = $next_proy->tallos_planta;
                                            $proy->tallos_ramo = $next_proy->tallos_ramo;
                                            $proy->curva = $next_proy->curva;
                                            $proy->poda_siembra = $next_proy->poda_siembra;
                                            $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                            $proy->desecho = $next_proy->desecho;
                                            $proy->area = $next_proy->area;
                                            $proy->tipo = 'I';
                                            $proy->info = ($pos_proy_new + 1) . 'º';
                                            $proy->proyectados = 0;

                                            if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                                $proy->tipo = $next_proy->tipo;
                                                $proy->info = $next_proy->info;
                                            }
                                            if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                                $proy->tipo = 'T';
                                                $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                                $total = $total * ((100 - $next_proy->desecho) / 100);
                                                $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                                $pos_cosecha++;
                                            }
                                        } else {    // semanas despues de la proyeccion
                                            if ($last_semana_new == '') {
                                                $last_semana_new = $proy->semana;
                                            }
                                            $proy->tipo = 'F';
                                            $proy->proyectados = 0;
                                            $proy->info = '-';
                                            $proy->activo = 0;
                                            $proy->plantas_iniciales = null;
                                            $proy->plantas_actuales = null;
                                            $proy->desecho = null;
                                            $proy->curva = null;
                                            $proy->semana_poda_siembra = null;
                                            $proy->tallos_planta = null;
                                            $proy->poda_siembra = null;
                                            $proy->tabla = null;
                                            $proy->modelo = null;
                                        }
                                        $pos_proy_new++;
                                    } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                        //dd($proy->semana . '... es una semana que queda vacia antes de la siguiente proy');
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    }
                                    $pos_proy++;
                                } else {    // fuera de las semanas de la proy
                                    if ($last_semana_new == '') {
                                        $last_semana_new = $proy->semana;
                                    }
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;

                                    $pos_proy++;
                                }
                            } else {
                                break;
                            }
                            $proy->save();
                        }

                        if ($last_semana_new == '')
                            $last_semana_new = $last_semana;

                        /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                        $semana_desde = $last_semana_new;
                        $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);

                        ProyeccionUpdateCampo::dispatch($model->id_proyeccion_modulo, 'SemanaCosecha', $semana_cosecha)
                            ->onQueue('proy_cosecha/actualizar_semana_cosecha');

                        /*if ($semana_desde != '')
                            ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                                ->onQueue('proy_cosecha');*/

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                        /*jobUpdateResumenTotalSemanalExportcalas::dispatch($semana_ini, $semana_fin->codigo, $model->id_variedad)
                            ->onQueue('proy_cosecha');*/
                    }
                }
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function mover_inicio_proy(Request $request)
    {
        foreach ($request->semanas as $sem) {
            $sem = Semana::find($sem);
            foreach ($request->modulos as $mod) {
                /* ====================== PROYECCION ===================== */
                $proyeccion = ProyeccionModulo::All()
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $mod)
                    ->where('id_semana', $sem->id_semana)
                    ->where('estado', 1)
                    ->first();

                /* ========================= ACTUALIZAR LAS TABLAS PROYECCION_MODULO ======================== */
                if ($proyeccion != '' && $request->mover != 0) {
                    $semana_proy = $proyeccion->semana;

                    /* -------------------- obtener la nueva semana de inicio --------------------- */
                    if ($request->mover > 0) {
                        $semana_ini_new = Semana::where('estado', 1)
                            ->where('id_variedad', $proyeccion->id_variedad)
                            ->where('codigo', '>', $semana_proy->codigo)
                            ->orderBy('codigo', 'asc')
                            ->get()
                            ->take($request->mover)
                            ->last();
                    } else if ($request->mover < 0) {
                        $semana_ini_new = Semana::where('estado', 1)
                            ->where('id_variedad', $proyeccion->id_variedad)
                            ->where('codigo', '<', $semana_proy->codigo)
                            ->orderBy('codigo', 'desc')
                            ->get()
                            ->take($request->mover * -1)
                            ->last();
                    }

                    $model = $proyeccion;
                    $obj = [
                        'id_modulo' => $model->id_modulo,
                        'id_variedad' => $model->id_variedad,
                    ];

                    $semana_ini_proy = $semana_ini_new;

                    $semana_ini = min($semana_proy->codigo, $semana_ini_proy->codigo);

                    $poda_siembra = 0;
                    $next_proy = ProyeccionModuloSemana::where('estado', 1)
                        ->where('tabla', 'P')
                        ->where('tipo', 'Y')
                        ->where('semana', '>', $semana_ini)
                        ->where('id_modulo', $model->id_modulo)
                        ->where('id_variedad', $model->id_variedad)
                        ->where('modelo', '!=', $model->id_proyeccion_modulo)
                        ->orderBy('semana')
                        ->get()->take(1);
                    $next_proy = count($next_proy) > 0 ? $next_proy[0] : '';

                    $poda_siembra = 0;
                    if ($model->tipo == 'P') {
                        $last_ciclo = Ciclo::All()
                            ->where('estado', 1)
                            ->where('id_variedad', $model->id_variedad)
                            ->where('id_modulo', $model->id_modulo)
                            ->sortBy('fecha_inicio')
                            ->last();
                        if ($last_ciclo != '') {
                            $last_proy = ProyeccionModulo::All()
                                ->where('estado', 1)
                                ->where('id_modulo', $model->id_modulo)
                                ->where('id_variedad', $model->id_variedad)
                                ->where('fecha_inicio', '<', $model->fecha_inicio)
                                ->sortBy('fecha_inicio')
                                ->last();
                            if ($last_proy != '') {
                                $poda_siembra = $last_proy->poda_siembra + 1;
                            } else {
                                $poda_siembra = intval($last_ciclo->modulo->getPodaSiembraByCiclo($last_ciclo->id_ciclo) + 1);
                            }
                        }
                    }

                    $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                        ->where('semana', '>=', $semana_ini)
                        ->where('id_modulo', $model->id_modulo)
                        ->where('id_variedad', $model->id_variedad)
                        ->orderBy('semana')
                        ->get();

                    $cant_semanas_new = $model->semana_poda_siembra + count(explode('-', $model->curva));   // cantidad de semanas que durará la proy new

                    $last_semana = '';
                    $last_semana_new = '';
                    $pos_cosecha = 0;
                    $pos_proy = 0;
                    $pos_proy_new = '';
                    foreach ($proyecciones as $proy) {
                        if ($proy->tabla != 'C') {   // validar que el rango de semanas consultadas estan fuera de los ciclos reales
                            if ($model->tipo == 'C') {
                                $proy->tipo = 'F';
                                $proy->proyectados = 0;
                                $proy->info = '-';
                                $proy->activo = 0;
                                $proy->plantas_iniciales = null;
                                $proy->plantas_actuales = null;
                                $proy->desecho = null;
                                $proy->curva = null;
                                $proy->semana_poda_siembra = null;
                                $proy->tallos_planta = null;
                                $proy->poda_siembra = null;
                                $proy->tabla = null;
                                $proy->modelo = null;
                            } else {
                                if ($proy->semana < $semana_ini_proy->codigo) { // se movio para adelante la proy, y se trata de una semana anterior
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;

                                } else if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // // dentro de las semanas de la proy
                                    $proy->tabla = 'P';
                                    $proy->modelo = $model->id_proyeccion_modulo;

                                    $proy->plantas_iniciales = $model->plantas_iniciales;
                                    $proy->tallos_planta = $model->tallos_planta;
                                    $proy->tallos_ramo = $model->tallos_ramo;
                                    $proy->curva = $model->curva;
                                    $proy->poda_siembra = $poda_siembra;
                                    $proy->semana_poda_siembra = $model->semana_poda_siembra;
                                    $proy->desecho = $model->desecho;
                                    $proy->area = $model->modulo->area;
                                    $proy->tipo = 'I';
                                    $proy->info = ($pos_proy + 1) . 'º';
                                    $proy->proyectados = 0;

                                    if ($pos_proy + 1 == 1) {   // primera semana de proyeccion
                                        $proy->tipo = 'Y';
                                        $proy->info = $model->tipo;
                                    }
                                    if ($pos_proy + 1 >= $model->semana_poda_siembra) {  // semana de cosecha **
                                        $proy->tipo = 'T';
                                        $total = $model->plantas_iniciales * $model->tallos_planta;
                                        $total = $total * ((100 - $model->desecho) / 100);
                                        $proy->proyectados = round($total * (explode('-', $model->curva)[$pos_cosecha] / 100), 2);
                                        $pos_cosecha++;
                                    }
                                    $pos_proy++;
                                } else if ($next_proy != '') {    // semanas despues de la proyeccion, pero en caso de que exista una siguiente proy
                                    if ($last_semana == '')
                                        $last_semana = $proy->semana;
                                    if ($last_semana > $next_proy->semana) {    // hay que mover la siguiente proyeccion
                                        if ($pos_proy_new == '') {
                                            $pos_proy_new = 0;
                                            $pos_cosecha = 0;
                                        }
                                        if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {   // esta dentro de las semanas de la proyeccion
                                            $proy->tabla = 'P';
                                            $proy->modelo = $next_proy->modelo;

                                            $proy->plantas_iniciales = $next_proy->plantas_iniciales;
                                            $proy->tallos_planta = $next_proy->tallos_planta;
                                            $proy->tallos_ramo = $next_proy->tallos_ramo;
                                            $proy->curva = $next_proy->curva;
                                            $proy->poda_siembra = $next_proy->info == 'S' ? 0 : $poda_siembra + 1;
                                            $proy->semana_poda_siembra = $next_proy->semana_poda_siembra;
                                            $proy->desecho = $next_proy->desecho;
                                            $proy->area = $next_proy->area;
                                            $proy->tipo = 'I';
                                            $proy->info = ($pos_proy_new + 1) . 'º';
                                            $proy->proyectados = 0;

                                            if ($pos_proy_new + 1 == 1) {   // primera semana de proyeccion
                                                $proy->tipo = $next_proy->tipo;
                                                $proy->info = $next_proy->info;
                                            }
                                            if ($pos_proy_new + 1 >= $next_proy->semana_poda_siembra) {  // semana de cosecha
                                                $proy->tipo = 'T';
                                                $total = $next_proy->plantas_iniciales * $next_proy->tallos_planta;
                                                $total = $total * ((100 - $next_proy->desecho) / 100);
                                                $proy->proyectados = round($total * (explode('-', $next_proy->curva)[$pos_cosecha] / 100), 2);
                                                $pos_cosecha++;
                                            }
                                        } else {    // semanas despues de la proyeccion
                                            if ($last_semana_new == '') {
                                                $last_semana_new = $proy->semana;
                                            }
                                            $proy->tipo = 'F';
                                            $proy->proyectados = 0;
                                            $proy->info = '-';
                                            $proy->activo = 0;
                                            $proy->plantas_iniciales = null;
                                            $proy->plantas_actuales = null;
                                            $proy->desecho = null;
                                            $proy->curva = null;
                                            $proy->semana_poda_siembra = null;
                                            $proy->tallos_planta = null;
                                            $proy->poda_siembra = null;
                                            $proy->tabla = null;
                                            $proy->modelo = null;
                                        }
                                        $pos_proy_new++;
                                    } else if ($proy->semana < $next_proy->semana) {    // es una semana que queda vacia antes de la siguiente proy
                                        $proy->tipo = 'F';
                                        $proy->proyectados = 0;
                                        $proy->info = '-';
                                        $proy->activo = 0;
                                        $proy->plantas_iniciales = null;
                                        $proy->plantas_actuales = null;
                                        $proy->desecho = null;
                                        $proy->curva = null;
                                        $proy->semana_poda_siembra = null;
                                        $proy->tallos_planta = null;
                                        $proy->poda_siembra = null;
                                        $proy->tabla = null;
                                        $proy->modelo = null;
                                    } else {    // no hay que mover pero es una semana a partir de la siguiente proyeccion
                                        if ($pos_proy_new == '') {
                                            $pos_proy_new = 0;
                                        }
                                        if ($pos_proy_new + 1 <= $next_proy->semana_poda_siembra + count(explode('-', $next_proy->curva)) - 1) {    // es una semana de la siguiente proyeccion
                                            $proy->poda_siembra = $next_proy->info == 'S' ? 0 : $poda_siembra + 1;
                                        }
                                        $pos_proy_new++;
                                    }
                                    $pos_proy++;
                                } else {    // fuera de las semanas de la proy
                                    if ($last_semana_new == '') {
                                        $last_semana_new = $proy->semana;
                                    }
                                    $proy->tipo = 'F';
                                    $proy->proyectados = 0;
                                    $proy->info = '-';
                                    $proy->activo = 0;
                                    $proy->plantas_iniciales = null;
                                    $proy->plantas_actuales = null;
                                    $proy->desecho = null;
                                    $proy->curva = null;
                                    $proy->semana_poda_siembra = null;
                                    $proy->tallos_planta = null;
                                    $proy->poda_siembra = null;
                                    $proy->tabla = null;
                                    $proy->modelo = null;

                                    $pos_proy++;
                                }
                            }
                        } else {
                            break;
                        }
                        $proy->save();
                    }

                    if ($last_semana_new == '')
                        $last_semana_new = $last_semana;

                    /* ======================== ACTUALIZAR LAS TABLAS CICLO y PROYECCION_MODULO ====================== */
                    ProyeccionUpdateProy::dispatch($model->id_proyeccion_modulo, $semana_ini_new->codigo, $model->tipo, $model->curva, $model->semana_poda_siembra, $model->plantas_iniciales, $model->desecho, $model->tallos_planta, $model->tallos_ramo)
                        ->onQueue('proy_cosecha/update_proyeccion');

                    /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA FINAL ====================== */
                    $semana_desde = $last_semana_new;
                    $semana_fin = getLastSemanaByVariedad($obj['id_variedad']);

                    /*if ($semana_desde != '')
                        ProyeccionUpdateSemanal::dispatch($semana_desde, $semana_fin->codigo, $obj['id_variedad'], $obj['id_modulo'], 0)
                            ->onQueue('proy_cosecha/update_proyeccion');*/

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_COSECHA_SEMANA FINAL ====================== */
                    /*jobUpdateResumenTotalSemanalExportcalas::dispatch($semana_ini, $semana_fin->codigo, $model->id_variedad)
                        ->onQueue('proy_cosecha/update_proyeccion');*/
                }
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    /* ------------------------------------------------------------------- */
    public function get_row_byModulo(Request $request)
    {
        $list = ProyeccionModuloSemana::where('estado', '=', 1)
            ->where('id_modulo', '=', $request->modulo)
            ->where('id_variedad', '=', $request->variedad)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->orderBy('semana')
            ->get();

        $semanas = Semana::where('estado', 1)
            ->where('id_variedad', '=', $request->variedad)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')->get();

        return view('adminlte.gestion.proyecciones.cosecha.partials._row', [
            'modulo' => getModuloById($request->modulo),
            'proyecciones' => $list,
            'semanas' => $semanas,
            'variedad_model' => Variedad::find($request->variedad),
        ]);
    }

    public function new_proyeccion(Request $request)
    {
        $semana = Semana::find($request->id_semana);
        $variedad = $semana->variedad;
        $modulos = [];
        foreach (Modulo::All()->where('estado', 1)->where('area', '>', 0)->sortBy('nombre') as $m) {   // módulos inactivos
            if ($m->cicloActual() == '') {
                array_push($modulos, $m);
            }
        }
        return view('adminlte.gestion.proyecciones.cosecha.forms.nueva_proyeccion', [
            'semana' => $semana,
            'variedad' => $variedad,
            'modulos' => $modulos,
        ]);
    }

    public function new_cultivo(Request $request)
    {
        $finca = getFincaActiva();
        $semana = Semana::find($request->id_semana);
        $variedad = Variedad::find($request->id_variedad);
        $sectores = Sector::where('estado', 1)
            ->where('id_empresa', $finca)
            ->get();
        return view('adminlte.gestion.proyecciones.cosecha.forms.new_cultivo', [
            'semana' => $semana,
            'variedad' => $variedad,
            'sectores' => $sectores,
        ]);
    }

    public function store_new_cultivo(Request $request)
    {
        if ($request->fecha_inicio <= hoy()) {
            $finca = getFincaActiva();
            $model = Modulo::All()
                ->where('nombre', $request->siglas_variedad . '-' . $request->semana)
                ->where('estado', 1)
                ->where('proyectar_semanal', 1)
                ->where('id_empresa', $finca)
                ->first();

            if ($model == '') {
                /* ------------------- crear modulo ------------------ */
                $modulo = new Modulo();
                $modulo->nombre = 'P:' . $request->siglas_variedad . '-' . $request->semana;
                $modulo->area = $request->area;
                $modulo->id_sector = $request->sector;
                $modulo->proyectar_semanal = 1;
                $modulo->id_empresa = $finca;
                $modulo->save();
                $modulo = Modulo::All()->last();

                /* ------------------- crear ciclo ------------------ */
                $ciclo = new Ciclo();
                $ciclo->id_modulo = $modulo->id_modulo;
                $ciclo->id_variedad = $request->variedad;
                $ciclo->area = $request->area;
                $ciclo->fecha_inicio = $request->fecha_inicio;
                $ciclo->fecha_fin = hoy();
                $ciclo->poda_siembra = 'P';
                $ciclo->plantas_iniciales = $request->plantas_iniciales;
                $ciclo->plantas_muertas = 0;
                $ciclo->conteo = $request->conteo;
                $ciclo->curva = $request->curva;
                $ciclo->semana_poda_siembra = $request->semana_cosecha;
                $ciclo->desecho = $request->desecho;
                $ciclo->id_empresa = $finca;
                $ciclo->save();
                $ciclo = Ciclo::All()->last();

                $num_sem = $request->semana_cosecha + count(explode('-', $request->curva)) - 2;
                $fecha_hasta = opDiasFecha('+', $num_sem * 7, $request->fecha_inicio);
                $sem_desde = getSemanaByDate($request->fecha_inicio);
                $sem_hasta = getSemanaByDate($fecha_hasta);
                $semanas = DB::table('semana')
                    ->select('*')
                    ->where('codigo', '>=', $sem_desde->codigo)
                    ->where('codigo', '<=', $sem_hasta->codigo)
                    ->where('id_variedad', $request->variedad)
                    ->get();
                $pos_cosecha = 0;
                foreach ($semanas as $pos_s => $sem) {
                    $model = new ProyeccionModuloSemana();
                    $model->id_modulo = $modulo->id_modulo;
                    $model->id_variedad = $request->variedad;
                    $model->semana = $sem->codigo;

                    $model->tabla = 'C';
                    $model->modelo = $ciclo->id_ciclo;

                    $model->plantas_iniciales = $request->plantas_iniciales;
                    $model->tallos_planta = $request->conteo;
                    $model->tallos_ramo = 0;
                    $model->curva = $request->curva;
                    $model->poda_siembra = 'P';
                    $model->semana_poda_siembra = $request->semana_cosecha;
                    $model->desecho = $request->desecho;
                    $model->area = $request->area;
                    $model->tipo = 'I';
                    $model->info = ($pos_s + 1) . 'º';
                    $model->proyectados = 0;

                    if ($pos_s + 1 == 1) {   // primera semana de ciclo
                        $model->tipo = 'P';
                        $model->info = 'P...';
                    }
                    if ($pos_s + 1 >= $request->semana_cosecha) {  // semana de cosecha **
                        $model->tipo = 'T';
                        $total = $request->plantas_iniciales * $request->conteo;
                        $total = $total * ((100 - $request->desecho) / 100);
                        $model->proyectados = round($total * (explode('-', $request->curva)[$pos_cosecha] / 100), 2);
                        $pos_cosecha++;
                    }
                    $model->save();
                }

                return [
                    'success' => true,
                    'mensaje' => '<div class="alert alert-success text-center">' .
                        'Se ha creado el cultivo en la semana <strong>' . $request->semana . '</strong> satisfactoriamente</div>',
                ];
            } else {
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-danger text-center">' .
                        'Ya existe un cultivo en la semana <strong>' . $request->semana . '</strong> de la variedad indicada</div>',
                ];
            }
        } else
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-danger text-center">' .
                    'La fecha de inicio debe ser menor o igual al día de hoy.</div>',
            ];
    }

    public function store_nuevo_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_modulo' => 'required',
            'id_proyeccion_modulo' => 'required',
            'area' => 'required',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'poda_siembra' => 'required',
            'plantas_iniciales' => 'required',
            'curva' => 'required',
            'conteo' => 'required',
            'desecho' => 'required',
            'semana_poda_siembra' => 'required',
        ], [
            'id_modulo.required' => 'El módulo es obligatorio',
            'conteo.required' => 'El conteo es obligatorio',
            'area.required' => 'El área es obligatoria',
            'desecho.required' => 'El desecho es obligatorio',
            'id_proyeccion_modulo.required' => 'La proyección es obligatoria',
            'curva.required' => 'La curva es obligatoria',
            'semana_poda_siembra.required' => 'La semana de inicio de cosecha es obligatoria',
            'plantas_iniciales.required' => 'Las plantas iniciales son obligatorias',
            'fecha_inicio.required' => 'La fecha de inicio de cilo es obligatoria',
            'fecha_fin.required' => 'La fecha fin del cilo anterior es obligatoria',
            'poda_siembra.required' => 'El campo poda/siembra es obligatorio',
        ]);
        if (!$valida->fails()) {
            /* ------------------------ Cerrar ciclo anterior ------------------- */
            $last_ciclo = Ciclo::All()
                ->where('estado', 1)
                ->where('activo', 1)
                ->where('id_modulo', $request->id_modulo)
                ->first();
            if ($last_ciclo != '') {
                $last_ciclo->activo = 0;
                $last_ciclo->fecha_fin = $request->fecha_fin;
                $last_ciclo->save();
            }

            $proyeccion = ProyeccionModulo::find($request->id_proyeccion_modulo);
            /* ------------------------ Nuevo ciclo ------------------- */
            $ciclo = new Ciclo();
            $ciclo->id_modulo = $request->id_modulo;
            $ciclo->id_variedad = $proyeccion->id_variedad;
            $ciclo->area = $request->area;
            $ciclo->fecha_inicio = $request->fecha_inicio;
            $ciclo->fecha_fin = date('Y-m-d');
            $ciclo->poda_siembra = $request->poda_siembra;
            $ciclo->desecho = $request->desecho;
            $ciclo->curva = $request->curva;
            $ciclo->semana_poda_siembra = $request->semana_poda_siembra;
            $ciclo->conteo = $request->conteo;
            $ciclo->plantas_iniciales = $request->plantas_iniciales;
            $ciclo->id_empresa = getModuloById($request->id_modulo)->id_empresa;

            $semana = $proyeccion->semana;

            if ($ciclo->save()) {
                $ciclo = Ciclo::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado un nuevo ciclo satisfactoriamente</p>'
                    . '</div>';
                bitacora('ciclo', $ciclo->id_ciclo, 'I', 'Inserción satisfactoria de un nuevo ciclo');

                /* ===================== QUITAR PROYECCION =================== */
                $proyeccion->estado = 0;
                $proyeccion->save();
                bitacora('proyeccion_modulo', $proyeccion->id_proyeccion_modulo, 'U', 'Actualizacion satisfactoria del estado');

                /* ===================== CREAR SIGUIENTE PROYECCION ==================== */
                $sum_semana = intval($ciclo->semana_poda_siembra) + intval(count(explode('-', $ciclo->curva)));
                $codigo = $semana->codigo;
                $i = 1;
                $next = 1;
                while ($i < $sum_semana) {
                    $new_codigo = $codigo + $next;
                    $query = Semana::All()
                        ->where('estado', '=', 1)
                        ->where('codigo', '=', $new_codigo)
                        ->where('id_variedad', '=', $ciclo->id_variedad)
                        ->first();

                    if ($query != '') {
                        $i++;
                    }
                    $next++;
                }

                $proy = new ProyeccionModulo();
                $proy->id_modulo = $ciclo->id_modulo;
                $proy->id_semana = $query->id_semana;
                $proy->id_variedad = $ciclo->id_variedad;
                $proy->tipo = 'P';
                $proy->curva = $ciclo->curva;
                $proy->semana_poda_siembra = $ciclo->semana_poda_siembra;
                $proy->poda_siembra = $ciclo->modulo->getPodaSiembraByCiclo($ciclo->id_ciclo) + 1;
                $proy->plantas_iniciales = $ciclo->plantas_iniciales != '' ? $ciclo->plantas_iniciales : 0;
                $proy->desecho = $ciclo->desecho;
                $proy->tallos_planta = $ciclo->conteo != '' ? $ciclo->conteo : 0;
                $proy->tallos_ramo = $query->tallos_ramo_poda != '' ? $query->tallos_ramo_poda : 0;
                $proy->fecha_inicio = $query->fecha_final;
                $proy->id_empresa = $ciclo->id_empresa;

                $proy->save();

                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                $cant_semanas_new = $request->semana_poda_siembra + count(explode('-', $ciclo->curva));   // cantidad de semanas que durará el ciclo new

                $proyecciones = ProyeccionModuloSemana::where('estado', 1)
                    ->where('id_modulo', $ciclo->id_modulo)
                    ->where('id_variedad', $ciclo->id_variedad)
                    ->where('semana', '>=', $ciclo->semana()->codigo)
                    ->orderBy('semana')
                    ->get();

                $last_semana_new = '';
                $pos_cosecha = 0;
                foreach ($proyecciones as $pos_proy => $proy) {
                    if ($pos_proy + 1 <= $cant_semanas_new - 1) {   // // dentro de las semanas del ciclo
                        $proy->tabla = 'C';
                        $proy->modelo = $ciclo->id_ciclo;

                        $proy->plantas_iniciales = $request->plantas_iniciales;
                        $proy->tallos_planta = $request->conteo;
                        $proy->tallos_ramo = 0;
                        $proy->curva = $request->curva;
                        $proy->poda_siembra = $request->poda_siembra;
                        $proy->semana_poda_siembra = $request->semana_poda_siembra;
                        $proy->desecho = $request->desecho;
                        $proy->area = $request->area;
                        $proy->tipo = 'I';
                        $proy->info = ($pos_proy + 1) . 'º';
                        $proy->proyectados = 0;

                        if ($pos_proy + 1 == 1) {   // primera semana de ciclo
                            $proy->tipo = $request->poda_siembra;
                            $proy->info = $request->poda_siembra . '-' . $proyeccion->poda_siembra;
                        }
                        if ($pos_proy + 1 >= $request->semana_poda_siembra) {  // semana de cosecha **
                            $proy->tipo = 'T';
                            $total = $ciclo->plantas_actuales() * $request->conteo;
                            $total = $total * ((100 - $request->desecho) / 100);
                            $proy->proyectados = round($total * (explode('-', $request->curva)[$pos_cosecha] / 100), 2);
                            $pos_cosecha++;
                        }
                    } else {    // fuera de las semanas del ciclo
                        if ($last_semana_new == '') {
                            $last_semana_new = $proy->semana;
                        }
                        $proy->tipo = 'F';
                        $proy->proyectados = 0;
                        $proy->info = '-';
                        $proy->activo = 0;
                        $proy->plantas_iniciales = null;
                        $proy->plantas_actuales = null;
                        $proy->desecho = null;
                        $proy->curva = null;
                        $proy->semana_poda_siembra = null;
                        $proy->tallos_planta = null;
                        $proy->poda_siembra = null;
                        $proy->tabla = null;
                        $proy->modelo = null;
                    }
                    $proy->save();
                }

                /*$semana_fin = getLastSemanaByVariedad($ciclo->id_variedad);
                ProyeccionUpdateSemanal::dispatch($ciclo->semana()->codigo, $semana_fin->codigo, $ciclo->id_variedad, $ciclo->id_modulo, 0)
                    ->onQueue('proy_cosecha');
                jobUpdateResumenTotalSemanalExportcalas::dispatch($ciclo->semana()->codigo, $semana_fin->codigo, $ciclo->id_variedad)
                    ->onQueue('proy_cosecha');*/
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_config(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'campo' => 'required',
            'valor' => 'required',
        ], [
            'campo.required' => 'El campo es obligatorio',
            'valor.required' => 'El valor es obligatorio',
        ]);
        if (!$valida->fails()) {
            $model = getConfiguracionEmpresa();
            $model[$request->campo] = $request->valor;

            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado la configuración satisfactoriamente</p>'
                    . '</div>';
                bitacora('configuracion_empresa', $model->id_configuracion_empresa, 'U', 'Modificación satisfactoria de configuracion empresa');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    /* -------------------------------------------------------------------- */
    public function actualizar_proyecciones_job(Request $request)
    {
        $semana_fin = getLastSemanaByVariedad($request->variedad);
        ProyeccionUpdateSemanal::dispatch($request->semana_desde, $semana_fin->codigo, $request->variedad, $request->modulo, 0)
            ->onQueue('actualizar_proyecciones_job');
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha añadido el proceso para ejecutarse en segundo plano</div>',
        ];
    }
}