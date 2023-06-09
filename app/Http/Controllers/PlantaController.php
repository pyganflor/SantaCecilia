<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Nature\Plant;
use yura\Modelos\ClasificacionUnitaria;
use yura\Modelos\Planta;
use yura\Modelos\Regalias;
use yura\Modelos\Submenu;
use yura\Modelos\Precio;
use DB;
use yura\Modelos\ClasificacionRamo;
use Validator;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Variedad;
use yura\Modelos\VariedadClasificacionUnitaria;
use yura\Modelos\VariedadProveedor;

class PlantaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.plantas_variedades.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::orderBy('nombre')->get(),
            'proveedores' => ConfiguracionEmpresa::where('proveedor', 1)
                ->orderBy('nombre')->get()
        ]);
    }

    public function select_planta(Request $request)
    {
        $variedades = [];
        if ($request->id_planta != '') {
            $planta = Planta::find($request->id_planta);
            $variedades = $planta->variedades;
        }
        $variedades_del_proveedor = [];
        if ($request->id_proveedor != '') {
            $variedades_del_proveedor = DB::table('variedad_proveedor')
                ->where('id_proveedor', $request->id_proveedor)
                ->get()->pluck('id_variedad')->toArray();
        }
        return view('adminlte.gestion.plantas_variedades.partials.listado_variedad', [
            'proveedor' => $request->id_proveedor,
            'variedades' => $variedades,
            'variedades_del_proveedor' => $variedades_del_proveedor,
        ]);
    }

    public function asignar_proveedor(Request $request)
    {
        $model = VariedadProveedor::All()
            ->where('id_variedad', $request->variedad)
            ->where('id_proveedor', $request->id_proveedor)
            ->first();
        if ($model == '') {
            $model = new VariedadProveedor();
            $model->id_variedad = $request->variedad;
            $model->id_proveedor = $request->id_proveedor;
            $model->save();
            return [
                'success' => true,
                'mensaje' => 'Se ha <strong>ASIGNADO</strong> la variedad al proveedor',
            ];
        } else {
            $model->delete();
            return [
                'success' => true,
                'mensaje' => 'Se ha <strong>DESASIGNADO</strong> la variedad del proveedor',
            ];
        }
    }

    /* =====================================================*/

    public function form_compelto(Request $request)
    {
        $plantas = Planta::where('estado', 1)->orderBy('nombre')->get();
        return view('adminlte.gestion.plantas_variedades.forms.form_completo', [
            'plantas' => $plantas,
        ]);
    }

    public function add_planta(Request $request)
    {
        return view('adminlte.gestion.plantas_variedades.forms.add_planta');
    }

    public function store_planta(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:planta',
            'siglas' => 'required',
            'tarifa' => 'required',
            'nandina' => 'required'
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'siglas.required' => 'Las siglas son obligatorias',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = new Planta();
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
            $model->tarifa = $request->tarifa;
            $model->nandina = $request->nandina;
            $model->siglas = $request->siglas;
            $model->fecha_registro = date('Y-m-d H:i:s');

            if ($model->save()) {
                $model = Planta::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado una nueva planta satisfactoriamente</p>'
                    . '</div>';
                bitacora('planta', $model->id_planta, 'I', 'Inserción satisfactoria de una nueva planta');
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

    public function add_variedad(Request $request)
    {
        return view('adminlte.gestion.plantas_variedades.forms.add_variedad', [
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function store_variedad(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'siglas' => 'required|max:25',
            'id_planta' => 'required|',
            //'unidad_medida' => 'required|',
            //'tallos_por_ramo' => 'required|',
            'minimo_apertura' => 'required|',
            'maximo_apertura' => 'required|',
            'estandar' => 'required|',
            'tipo' => 'required|',
            'tallos_x_malla' => 'required|',
            'color' => 'required|max:50'
        ], [
            'tallos_x_malla.required' => 'Los tallos por malla son obligatorios',
            'color.required' => 'El color es obligatorio',
            'nombre.required' => 'El nombre es obligatorio',
            'tipo.required' => 'El tipo es obligatorio',
            'siglas.required' => 'Las siglas son obligatorias',
            'id_planta.required' => 'La planta es obligatoria',
            //'unidad_medida' => 'La unidad de medida es requerida',
            //'tallos_por_ramo.required' => 'El tallo por ramo es requerido',
            'color.max' => 'El color es muy grande',
            'nombre.max' => 'El nombre es muy grande',
            'siglas.max' => 'Las siglas son muy grande',
            'maximo_apertura.required' => 'EL maximo de apertura es obligatorio',
            'minimo_apertura.required' => 'EL minimo de apertura es obligatorio',
            'estandar.required' => 'El campo estandar es obligatorio'
        ]);
        if (!$valida->fails()) {
            if (count(Variedad::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                ->where('id_planta', '=', $request->id_planta)) == 0) {
                $model = new Variedad();
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->siglas = str_limit(mb_strtoupper(espacios($request->siglas)), 25);
                $model->id_planta = $request->id_planta;
                $model->color = $request->color;
                $model->tipo = $request->tipo;
                $model->tallos_x_ramo_estandar = $request->tallos_x_ramo_estandar;
                //  $model->unidad_de_medida = $request->unidad_medida;
                //$model->cantidad = $request->tallos_por_ramo;
                $model->minimo_apertura = $request->minimo_apertura;
                $model->maximo_apertura = $request->maximo_apertura;
                $model->tallos_x_malla = $request->tallos_x_malla;
                $model->fecha_registro = date('Y-m-d H:i:s');
                $model->estandar_apertura = $request->estandar;
                $model->saldo_inicial = $request->saldo_inicial;

                if ($model->save()) {
                    $model = Variedad::All()->last();
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado una nueva variedad satisfactoriamente</p>'
                        . '</div>';
                    bitacora('variedad', $model->id_variedad, 'I', 'Inserción satisfactoria de una nueva variedad');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La variedad "' . espacios($request->nombre) . '" ya se encuentra en esta planta</p>'
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

    /* =====================================================*/

    public function add_proveedor(Request $request)
    {
        return view('adminlte.gestion.plantas_variedades.forms.add_proveedor');
    }

    public function store_proveedor(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:configuracion_empresa',
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = new ConfiguracionEmpresa();
            $model->nombre = str_limit(espacios($request->nombre), 250);
            $model->proveedor = 1;
            $model->fecha_registro = date('Y-m-d H:i:s');

            if ($model->save()) {
                $model = ConfiguracionEmpresa::All()->last();
                $success = true;
                $msg = 'Se ha guardado un nuevo proveedor';
                bitacora('configuracion_empresa', $model->id_configuracion_empresa, 'I', 'Inserción satisfactoria de un nuevo proveedor');
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

    public function edit_proveedor(Request $request)
    {
        if ($request->has('id_proveedor')) {
            $p = ConfiguracionEmpresa::find($request->id_proveedor);
            if ($p != '') {
                return view('adminlte.gestion.plantas_variedades.forms.edit_proveedor', [
                    'proveedor' => $p
                ]);
            } else {
                return '<div class="alert alert-warning text-center">No se ha encontrado el proveedor en el sistema</div>';
            }
        } else {
            return '<div class="alert alert-warning text-center">No se ha seleccionado un proveedor</div>';
        }
    }

    public function update_proveedor(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            if (count(ConfiguracionEmpresa::All()->where('nombre', '=', $request->nombre)
                ->where('id_configuracion_empresa', '!=', $request->id_proveedor)) == 0) {
                $model = ConfiguracionEmpresa::find($request->id_proveedor);
                $model->nombre = str_limit(espacios($request->nombre), 250);

                if ($model->save()) {
                    $success = true;
                    $msg = 'Se ha actualizado el proveedor satisfactoriamente';
                    bitacora('configuracion_empresa', $model->id_configuracion_empresa, 'U', 'Actualización satisfactoria de un proveedor');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La planta "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
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

    /* =====================================================*/

    public function edit_planta(Request $request)
    {
        if ($request->has('id_planta')) {
            $p = Planta::find($request->id_planta);
            if ($p != '') {
                return view('adminlte.gestion.plantas_variedades.forms.edit_planta', [
                    'planta' => $p
                ]);
            } else {
                return '<div class="alert alert-warning text-center">No se ha encontrado la panta en el sistema</div>';
            }
        } else {
            return '<div class="alert alert-warning text-center">No se ha seleccionado una planta</div>';
        }
    }

    public function update_planta(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'id_planta' => 'required|',
            'siglas' => 'required',
            'tarifa' => 'required',
            'nandina' => 'required'
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_planta.required' => 'La platna es obligatoria',
            'siglas.required' => 'Las siglas son obligatorias',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            if (count(Planta::All()->where('nombre', '=', $request->nombre)
                ->where('id_planta', '!=', $request->id_planta)) == 0) {
                $model = Planta::find($request->id_planta);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->tarifa = $request->tarifa;
                $model->nandina = $request->nandina;
                $model->siglas = $request->siglas;
                $model->tipo = $request->tipo;

                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha actualizado la planta satisfactoriamente</p>'
                        . '</div>';
                    bitacora('planta', $model->id_planta, 'U', 'Actualización satisfactoria de una planta');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La planta "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
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

    public function cambiar_estado_planta(Request $request)
    {
        $success = false;
        $msg = '';
        if ($request->has('id_planta')) {
            $p = Planta::find($request->id_planta);
            if ($p != '') {
                $p->estado = $request->estado;
                if ($p->save()) {
                    $texto = $request->estado == 1 ? 'Se ha activado satisfactoriamente' : 'Se ha desactivado satisfactoriamente';
                    $msg = '<div class="alert alert-success text-center">' . $texto . '</div>';
                    $success = true;

                    bitacora('planta', $p->id_planta, 'U', 'Cambio de estado de una planta');
                } else {
                    $msg = '<div class="alert alert-warning text-center">No se ha podido guardar la información en el sistema</div>';
                    $success = false;
                }
            } else {
                $msg = '<div class="alert alert-warning text-center">No se ha encontrado la planta en el sistema</div>';
                $success = false;
            }
        } else {
            $msg = '<div class="alert alert-warning text-center">No se ha seleccionado una planta</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function edit_variedad(Request $request)
    {
        if ($request->has('id_variedad')) {
            $v = Variedad::find($request->id_variedad);
            if ($v != '') {
                return view('adminlte.gestion.plantas_variedades.forms.edit_variedad', [
                    'variedad' => $v,
                    'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
                ]);
            } else {
                return '<div class="alert alert-warning text-center">No se ha encontrado la variedad en el sistema</div>';
            }
        } else {
            return '<div class="alert alert-warning text-center">No se ha seleccionado una variedad</div>';
        }
    }

    public function update_variedad(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'siglas' => 'required|max:25',
            'color' => 'required|max:50',
            'id_planta' => 'required|',
            'tipo' => 'required|',
            //'unidad_medida'  => 'required|',
            //'tallos_por_ramo' => 'required|',
            'minimo_apertura' => 'required|',
            'maximo_apertura' => 'required|',
            'tallos_x_malla' => 'required|',
            'estandar' => 'required|'
        ], [
            'tallos_x_malla.required' => 'Los tallos por malla son obligatorios',
            'color.required' => 'El color es obligatorio',
            'nombre.required' => 'El nombre es obligatorio',
            'tipo.required' => 'El tipo es obligatorio',
            'siglas.required' => 'Las siglas son obligatorias',
            'id_planta.required' => 'La planta es obligatoria',
            //'unidad_medida' => 'La unidad de medida es requerida',
            //'tallos_por_ramo.required' => 'El tallo por ramo es requerido',
            'color.max' => 'El color es muy grande',
            'nombre.max' => 'El nombre es muy grande',
            'siglas.max' => 'Las siglas son muy grande',
            'maximo_apertura.required' => 'EL maximo de apertura es obligatorio',
            'minimo_apertura.required' => 'EL minimo de apertura es obligatorio',
            'estandar.required' => 'El campo estandar es obligatorio'
        ]);
        if (!$valida->fails()) {
            if (count(Variedad::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                ->where('id_planta', '=', $request->id_planta)
                ->where('id_variedad', '!=', $request->id_variedad)) == 0) {
                $model = Variedad::find($request->id_variedad);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->siglas = str_limit(mb_strtoupper(espacios($request->siglas)), 25);
                //$model->unidad_de_medida = $request->unidad_medida;
                //$model->cantidad = $request->tallos_por_ramo;
                $model->tipo = $request->tipo;
                $model->tallos_x_ramo_estandar = $request->tallos_x_ramo_estandar;
                $model->color = $request->color;
                $model->id_planta = $request->id_planta;
                $model->minimo_apertura = $request->minimo_apertura;
                $model->maximo_apertura = $request->maximo_apertura;
                $model->estandar_apertura = $request->estandar;
                $model->tallos_x_malla = $request->tallos_x_malla;
                $model->saldo_inicial = $request->saldo_inicial;
                $model->nombre_unosoft = $request->nombre_unosoft != '' ? $request->nombre_unosoft : $model->nombre;
                $model->compra_flor = $request->compra_flor;

                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha actualizado la variedad satisfactoriamente</p>'
                        . '</div>';
                    bitacora('variedad', $model->id_variedad, 'U', 'Actualización satisfactoria de una variedad');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La variedad "' . espacios($request->nombre) . '" ya se encuentra en esta planta</p>'
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

    public function cambiar_estado_variedad(Request $request)
    {
        $success = false;
        $msg = '';
        if ($request->has('id_variedad')) {
            $v = Variedad::find($request->id_variedad);
            if ($v != '') {
                $v->estado = $request->estado;
                if ($v->save()) {
                    $texto = $request->estado == 1 ? 'Se ha activado satisfactoriamente' : 'Se ha desactivado satisfactoriamente';
                    $msg = '<div class="alert alert-success text-center">' . $texto . '</div>';
                    $success = true;

                    bitacora('variedad', $v->id_variedad, 'U', 'Cambio de estado de una variedad');
                } else {
                    $msg = '<div class="alert alert-warning text-center">No se ha podido guardar la información en el sistema</div>';
                    $success = false;
                }
            } else {
                $msg = '<div class="alert alert-warning text-center">No se ha encontrado la variedad en el sistema</div>';
                $success = false;
            }
        } else {
            $msg = '<div class="alert alert-warning text-center">No se ha seleccionado ninguna variedad</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function form_precio_variedad(Request $request)
    {

        $dataMondea = DB::table('configuracion_empresa')->select('moneda')->first();
        $dataPrecio = Precio::where('precio.id_variedad', $request->id_variedad)
            ->join('variedad as v', 'precio.id_variedad', '=', 'v.id_variedad')->get();
        $dataClasificacionRamos = ClasificacionRamo::join('unidad_medida as um', 'clasificacion_ramo.id_unidad_medida', 'um.id_unidad_medida')->where('clasificacion_ramo.estado', 1)->select('clasificacion_ramo.nombre', 'um.siglas', 'id_clasificacion_ramo')->get();
        return view(
            'adminlte.gestion.plantas_variedades.forms.add_precio',
            [
                'moneda' => $dataMondea,
                'dataPrecio' => $dataPrecio,
                'dataClasificacionRamos' => $dataClasificacionRamos
            ]
        );
    }

    public function store_precio(Request $request)
    {
        //dd($request->all());
        $valida = Validator::make($request->all(), [
            'arrData' => 'required|Array',
        ]);

        if (!$valida->fails()) {

            $msg = '';
            foreach ($request->arrData as $key => $precio) {
                $verifica = false;
                $existPrecio = Precio::where([
                    ['id_variedad', $request->id_variedad],
                    ['id_clasificacion_ramo', $request->arrData[$key][1]]
                ])->get();

                if (count($existPrecio) > 0) {

                    if (!empty($request->arrData[$key][2])) {

                        $objClasificacionRamo = ClasificacionRamo::find($existPrecio[0]->id_clasificacion_ramo);
                        $msg .= '<div class="alert alert-success text-center">Ya existe un precio establecido para la clasificación por ramo de ' . $objClasificacionRamo->nombre . ' , este precio no será guardado nuevamene  </div>';
                        $success = false;
                    } else {

                        $verifica = true;
                        $objPrecio = Precio::find($existPrecio[0]->id_precio);
                        $palabra = 'Actualizado';
                        $accion = 'U';
                    }
                } elseif (count($existPrecio) < 1 && empty($request->arrData[$key][2])) {

                    $verifica = true;
                    $objPrecio = new Precio;
                    $palabra = 'Insertado';
                    $accion = 'I';
                }

                if ($verifica) {

                    $objPrecio->id_clasificacion_ramo = $request->arrData[$key][1];
                    $objPrecio->id_variedad = $request->id_variedad;
                    $objPrecio->cantidad = $request->arrData[$key][0];

                    if ($objPrecio->save()) {

                        $model = Precio::All()->last();
                        $msg .= '<div class="alert alert-success text-center">Se ha ' . $palabra . ' satisfactoriamente el precio ' . $model->cantidad . '</div>';
                        $success = true;
                        bitacora('precio', $model->id_precio, $accion, $palabra . ' de precio');
                    } else {

                        $msg .= '<div class="alert alert-warning text-center">No se ha podido guardar la información en el sistema</div>';
                        $success = false;
                    }
                }
            }
            return [
                'success' => $success,
                'mensaje' => $msg
            ];
        }
    }

    public function add_inptus_precio_variedad(Request $request)
    {
        $dataClasificacionRamos = ClasificacionRamo::join('unidad_medida as um', 'clasificacion_ramo.id_unidad_medida', 'um.id_unidad_medida')->where('clasificacion_ramo.estado', 1)->select('clasificacion_ramo.nombre', 'um.siglas', 'id_clasificacion_ramo')->get();
        return view(
            'adminlte.gestion.plantas_variedades.forms.partials.add_inputs_precio',
            [
                'dataClasificacionRamos' => $dataClasificacionRamos,
                'cntTr' => $request->cant_tr

            ]
        );
    }

    public function update_precio(Request $request)
    {

        if (!empty($request->id_precio)) {

            $model = Precio::find($request->id_precio);

            $request->estado == 1 ? $model->estado = 0 : $model->estado = 1;
            $msg = '';
            if ($model->save()) {
                $success = true;
                $msg .= '<div class="alert alert-success text-center">' .
                    '<p> El precio ha sido modificada exitosamente</p>'
                    . '</div>';
                bitacora('precio', $request->id_precio, 'U', 'Actualización satisfactoria del precio');
            } else {
                $success = false;
                $msg .= '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>';
            }
            return [
                'success' => $success,
                'mensaje' => $msg
            ];
        }
    }

    /* ----------------------------------------------------------------- */
    public function vincular_variedad_unitaria(Request $request)
    {
        $variedad = Variedad::find($request->id_variedad);
        return view('adminlte.gestion.plantas_variedades.forms.vincular_variedad_unitaria', [
            'variedad' => $variedad,
            'clasificaciones' => ClasificacionUnitaria::All()->where('estado', 1),
        ]);
    }

    public function store_vinculo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'variedad' => 'required',
            'unitaria' => 'required',
        ], [
            'variedad.required' => 'La variedad es obligatoria',
            'unitaria.required' => 'La clasificación es obligatoria',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = VariedadClasificacionUnitaria::All()
                ->where('id_variedad', $request->variedad)
                ->where('id_clasificacion_unitaria', $request->unitaria)
                ->first();
            if ($model == '') {
                $model = new VariedadClasificacionUnitaria();
                $model->id_variedad = $request->variedad;
                $model->id_clasificacion_unitaria = $request->unitaria;

                if ($model->save()) {
                    $model = VariedadClasificacionUnitaria::All()->last();
                    $success = true;
                    bitacora('variedad_clasificacion_unitaria', $model->id_variedad_clasificacion_unitaria, 'I', 'Inserción satisfactoria de un nuevo vinculo variedad_clasificacion_unitaria');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $model->delete();
                $success = true;
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

    public function add_regalias(Request $request)
    {
        $variedad = Variedad::find($request->id_variedad);
        $semana_actual = getSemanaByDate(date('Y-m-d'));
        return view('adminlte.gestion.plantas_variedades.forms.add_regalias', [
            'variedad' => $variedad,
            'regalias' => $variedad->regaliasBySemana($semana_actual->codigo),
            'semana_actual' => $semana_actual,
        ]);
    }

    public function buscar_regalias(Request $request)
    {
        $variedad = Variedad::find($request->id_variedad);
        $regalias = $variedad->regaliasBySemana($request->semana);
        return [
            'valor' => $regalias != '' ? $regalias->valor : 0,
        ];
    }

    public function store_regalias(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_variedad' => 'required',
            'semana' => 'required',
            'valor' => 'required',
        ], [
            'semana.required' => 'La semana es obligatoria',
            'id_variedad.required' => 'El área es obligatoria',
            'valor.required' => 'El valor es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = Regalias::All()
                ->where('id_variedad', $request->id_variedad)
                ->where('codigo_semana', $request->semana)
                ->first();
            if ($model == '') {
                $model = new Regalias();
                $model->id_variedad = $request->id_variedad;
                $model->codigo_semana = $request->semana;
            }
            $model->valor = $request->valor;

            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se han guardado la regalía satisfactoriamente</p>'
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

    /* ----------------------------------------------------------------- */

    public function actualizar_planta(Request $request)
    {
        $model = Planta::find($request->id_planta);
        $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
        $model->siglas = $request->siglas;
        $model->tipo = $request->tipo;
        $model->save();
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function actualizar_variedad(Request $request)
    {
        $model = Variedad::find($request->id_variedad);
        $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
        $model->siglas = $request->siglas;
        $model->desecho_enraizamiento = $request->des_enr;
        $model->proyectar_semanal = $request->proy_sem;
        $model->save();
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }
}
