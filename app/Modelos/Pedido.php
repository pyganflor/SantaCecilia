<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'estado',
        'descripcion',
        'fecha_pedido',
        'empaquetado',
        'confirmado',
        'historico',
        'id_configuracion_empresa',
        'id_exportador'
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetallePedido', 'id_pedido');
    }

    public function agencia_carga()
    {
        return $this->belongsTo('yura\Modelos\AgenciaCarga', 'id_agencia_carga');
    }

    public function consignatario()
    {
        return $this->belongsTo('yura\Modelos\Consignatario', 'id_consignatario');
    }

    public function envios()
    {
        return $this->hasMany('\yura\Modelos\Envio', 'id_pedido');
    }

    public function pedidoMarcacionesOrderAsc()
    {
        return $this->hasMany('\yura\Modelos\DetallePedido', 'id_pedido')
            ->join('marcacion as m', 'detalle_pedido.id_detalle_pedido', '=', 'm.id_detalle_pedido')
            ->join('distribucion as d', 'm.id_marcacion', '=', 'd.id_marcacion')
            ->orderBy('d.pos_pieza', 'asc');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function getTotales()
    {
        $tallos = 0;
        $ramos = 0;
        $monto = 0;
        foreach ($this->detalles as $det) {
            foreach ($det->items as $item) {
                $tallos += $item->ramos_x_caja * $item->tallos_x_ramo * $det->cantidad;
                $ramos += $item->ramos_x_caja * $det->cantidad;
                $monto += $item->ramos_x_caja * $item->tallos_x_ramo * $det->cantidad * $item->precio;
            }
        }
        return [
            'tallos' => $tallos,
            'ramos' => $ramos,
            'monto' => $monto,
        ];
    }

    public function getLastDistribucion()
    {
        $l = DB::table('distribucion as d')
            ->join('marcacion as m', 'm.id_marcacion', '=', 'd.id_marcacion')
            ->join('detalle_pedido as dp', 'dp.id_detalle_pedido', '=', 'm.id_detalle_pedido')
            ->select('d.pos_pieza', 'd.id_distribucion')
            ->where('dp.id_pedido', '=', $this->id_pedido)
            ->orderBy('d.pos_pieza', 'desc')
            ->get();
        $distr = '';
        if (count($l) > 0) {
            $distr = Distribucion::find($l[0]->id_distribucion);
        }
        return $distr;
    }

    public function haveDistribucion()  // 1 -> Es de tipo 'O' y no tiene distribucion; 2 -> es de tipo 'O' y tiene distribucion; 0 -> es 'N'
    {
        if ($this->tipo_especificacion == 'O') {
            $flag = true;
            foreach ($this->detalles as $detalle) {
                foreach ($detalle->marcaciones as $marcacion) {
                    if (count($marcacion->distribuciones) == 0)
                        $flag = false;
                }
            }
            if ($flag)
                return 2;
            else
                return 1;
        } else
            return 0;
    }

    public function getRamosEstandar()
    {
        $r = 0;
        if (!getFacturaAnulada($this->id_pedido)) {
            if (!$this->isTipoSuelto()) {
                foreach ($this->detalles as $det_ped) {
                    foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                        foreach ($esp_emp->detalles as $det_esp) {
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                            $ramos = $det_ped->cantidad * $esp_emp->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);
                            $r += convertToEstandar($ramos, explode('|', getCalibreRamoById($det_esp->id_clasificacion_ramo)->nombre)[0]);
                        }
                    }
                }
            }
        }
        return $r;
    }

    public function getRamosEstandarByVariedad($variedad)
    {
        $r = 0;
        if (!getFacturaAnulada($this->id_pedido)) {
            if (!$this->isTipoSuelto()) {
                foreach ($this->detalles as $det_ped) {
                    foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                        foreach ($esp_emp->detalles as $det_esp) {
                            if ($det_esp->id_variedad == $variedad) {
                                $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                                $ramos = $det_ped->cantidad * $esp_emp->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);
                                $r += convertToEstandar($ramos, explode('|', getCalibreRamoById($det_esp->id_clasificacion_ramo)->nombre)[0]);
                            }
                        }
                    }
                }
            }
        }
        return $r;
    }

    public function getTallos()
    {
        $r = 0;
        if (!getFacturaAnulada($this->id_pedido)) {
            foreach ($this->detalles as $det_ped) {
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                    foreach ($esp_emp->detalles as $det_esp) {
                        $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                        $ramos = $det_ped->cantidad * $esp_emp->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);
                        if ($det_esp->tallos_x_ramos != '') {
                            $r += $ramos * $det_esp->tallos_x_ramos;
                        } else {
                            $r += 0;
                        }
                    }
                }
            }
        }
        return $r;
    }

    public function getTallosByVariedad($variedad)
    {
        $r = 0;
        if (!getFacturaAnulada($this->id_pedido)) {
            foreach ($this->detalles as $det_ped) {
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                    foreach ($esp_emp->detalles as $det_esp) {
                        if ($det_esp->id_variedad == $variedad) {
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                            $ramos = $det_ped->cantidad * $esp_emp->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);
                            if ($det_esp->tallos_x_ramos != '') {
                                $r += $ramos * $det_esp->tallos_x_ramos;
                            } else {
                                $r += 0;
                            }
                        }
                    }
                }
            }
        }
        return $r;
    }

    public function getCajas()
    {   // cajas equivalentes
        if (!getFacturaAnulada($this->id_pedido)) {
            if (!$this->isTipoSuelto()) //Venta en tallos
                return round($this->getRamosEstandar() / getConfiguracionEmpresa()->ramos_x_caja, 2);
            else {
                return $this->getCajasFisicas();
            }
        }
        return 0;
    }

    public function isTipoSuelto()
    {
        foreach ($this->detalles as $det_ped) {
            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                if ($esp_emp->empaque->f_empaque == 'T')
                    return true;
            }
        }
        return false;
    }

    public function getCajasByVariedad($variedad)
    {
        if (!getFacturaAnulada($this->id_pedido)) {
            if (!$this->isTipoSuelto())
                return round($this->getRamosEstandarByVariedad($variedad) / getConfiguracionEmpresa()->ramos_x_caja, 2);
            else
                return $this->getCajasFisicasByVariedad($variedad);
        }
        return 0;
    }

    public function getPrecio()
    {
        $r = 0;
        if (!getFacturaAnulada($this->id_pedido)) {
            foreach ($this->detalles as $det_ped) {
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                    foreach ($esp_emp->detalles as $det_esp) {
                        $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                        $ramos = $det_ped->cantidad * $esp_emp->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);
                        $ramos_col = 0;
                        $precio_col = 0;
                        foreach (Coloracion::All()->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                            ->where('id_especificacion_empaque', $esp_emp->id_especificacion_empaque)
                            ->where('precio', '!=', '') as $col) {
                            $ramos_col += $col->getTotalRamosByDetEsp($det_esp->id_detalle_especificacionempaque);
                            $precio = getPrecioByDetEsp($col->precio, $det_esp->id_detalle_especificacionempaque);
                            $precio_col += ($col->getTotalRamosByDetEsp($det_esp->id_detalle_especificacionempaque) * $precio);
                        }
                        $ramos -= $ramos_col;
                        $precio_final = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                        $precio_final += $precio_col;
                        $r += $precio_final;
                    }
                }
            }
            if (count($this->envios) > 0)
                if ($this->envios[0]->comprobante != '') {  // PEDIDO FACTURADO
                    return $this->envios[0]->comprobante->monto_total;
                } else {
                    if ($this->envios[0]->fatura_cliente_tercero != '') {   // FACTURAR A NOMBRE DE OTRA PERSONA
                        $impuesto = TipoImpuesto::All()
                            ->where('codigo', $this->envios[0]->fatura_cliente_tercero->codigo_impuesto_porcentaje)->first()->porcentaje;
                        if (is_numeric($impuesto)) {
                            $r += $r * ($impuesto / 100);
                        }
                    } else {    // FACTURAR A NOMBRE DEL CLIENTE
                        $impuesto = TipoImpuesto::All()
                            ->where('codigo', $this->cliente->detalle()->codigo_porcentaje_impuesto)->first()->porcentaje;
                        if (is_numeric($impuesto)) {
                            $r += $r * ($impuesto / 100);
                        }
                    }
                }
            else {    // FACTURAR A NOMBRE DEL CLIENTE
                $impuesto = TipoImpuesto::All()
                    ->where('codigo', $this->cliente->detalle()->codigo_porcentaje_impuesto)->first()->porcentaje;
                if (is_numeric($impuesto)) {
                    $r += $r * ($impuesto / 100);
                }
            }
        }
        return $r;
    }

    public function getPrecioByVariedad($variedad)
    {
        $r = 0;
        if (!getFacturaAnulada($this->id_pedido)) {
            foreach ($this->detalles as $det_ped) {
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                    foreach ($esp_emp->detalles as $det_esp) {
                        if ($det_esp->id_variedad == $variedad) {
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                            $ramos = $det_ped->cantidad * $esp_emp->cantidad * (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);
                            $ramos_col = 0;
                            $precio_col = 0;
                            foreach (Coloracion::All()->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                ->where('id_especificacion_empaque', $esp_emp->id_especificacion_empaque)
                                ->where('precio', '!=', '') as $col) {
                                $ramos_col += $col->getTotalRamosByDetEsp($det_esp->id_detalle_especificacionempaque);
                                $precio = getPrecioByDetEsp($col->precio, $det_esp->id_detalle_especificacionempaque);
                                $precio_col += ($col->getTotalRamosByDetEsp($det_esp->id_detalle_especificacionempaque) * $precio);
                            }
                            $ramos -= $ramos_col;
                            $precio_final = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                            $precio_final += $precio_col;
                            $r += $precio_final;
                        }
                    }
                }
            }
            /*if (count($this->envios) > 0)
                if ($this->envios[0]->comprobante != '') {  // PEDIDO FACTURADO
                    return $this->envios[0]->comprobante->monto_total;
                } else {
                    if ($this->envios[0]->fatura_cliente_tercero != '') {   // FACTURAR A NOMBRE DE OTRA PERSONA
                        $impuesto = TipoImpuesto::All()
                            ->where('codigo', $this->envios[0]->fatura_cliente_tercero->codigo_impuesto_porcentaje)->first()->porcentaje;
                        if (is_numeric($impuesto)) {
                            $r += $r * ($impuesto / 100);
                        }
                    } else {    // FACTURAR A NOMBRE DEL CLIENTE
                        $impuesto = TipoImpuesto::All()
                            ->where('codigo', $this->cliente->detalle()->codigo_porcentaje_impuesto)->first()->porcentaje;
                        if (is_numeric($impuesto)) {
                            $r += $r * ($impuesto / 100);
                        }
                    }
                }
            else {    // FACTURAR A NOMBRE DEL CLIENTE
                $impuesto = TipoImpuesto::All()
                    ->where('codigo', $this->cliente->detalle()->codigo_porcentaje_impuesto)->first()->porcentaje;
                if (is_numeric($impuesto)) {
                    $r += $r * ($impuesto / 100);
                }
            }*/
        }
        return $r;
    }

    public function getVariedades() // optimizar consulta
    {
        $r = [];
        foreach ($this->detalles as $det_ped) {
            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                foreach ($esp_emp->detalles as $det_esp) {
                    if (!in_array($det_esp->id_variedad, $r)) {
                        array_push($r, $det_esp->id_variedad);
                    }
                }
            }
        }
        return $r;
    }

    public function getCajasFisicas()
    {
        if (!getFacturaAnulada($this->id_pedido)) {
            $r = DB::table('detalle_pedido as dp')
                ->select(DB::raw('sum(dp.cantidad) as cantidad'))
                ->where('dp.estado', '=', 1)
                ->where('dp.id_pedido', '=', $this->id_pedido)
                ->get()[0]->cantidad;

            return $r;
        }
        return 0;
    }

    public function getCajasFisicasByVariedad($variedad)
    {
        if (!getFacturaAnulada($this->id_pedido)) {
            $r = DB::table('detalle_pedido as dp')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as esp_emp', 'esp_emp.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as det_esp', 'det_esp.id_especificacion_empaque', '=', 'esp_emp.id_especificacion_empaque')
                ->select(DB::raw('sum(dp.cantidad) as cantidad'))
                ->where('dp.estado', '=', 1)
                ->where('dp.id_pedido', '=', $this->id_pedido)
                ->where('det_esp.id_variedad', '=', $variedad)
                ->get()[0]->cantidad;

            return $r;
        }
        return 0;
    }

    public function getPrecioByPedido()
    {
        $r = 0;
        foreach ($this->detalles as $det_ped) {
            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                foreach ($esp_emp->detalles as $det_esp) {
                    if ($esp_emp->empaque->f_empaque == 'T') {  // pedido de tallos sueltos en mallas
                        $r += $det_ped->total_tallos() * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                    } else {
                        if ($this->tipo_especificacion == 'T') {    // flor tinturada
                            foreach ($det_ped->coloracionesByEspEmp($esp_emp->id_especificacion_empaque) as $col) {
                                $marcaciones_coloraciones = MarcacionColoracion::where('estado', 1)
                                    ->where('id_coloracion', $col->id_coloracion)
                                    ->where('id_detalle_especificacionempaque', $det_esp->id_detalle_especificacionempaque)
                                    ->get();
                                foreach ($marcaciones_coloraciones as $marc_col) {
                                    $ramos = $marc_col->cantidad;
                                    if ($marc_col->precio != '') {
                                        $precio = $marc_col->precio;
                                        $r += $ramos * $marc_col->precio;
                                    } else if ($col->precio != '' && havePrecioByDetEsp($col->precio, $det_esp->id_detalle_especificacionempaque)) {
                                        $precio = getPrecioByDetEsp($col->precio, $det_esp->id_detalle_especificacionempaque);
                                        $r += $ramos * $precio;
                                    } else {
                                        $precio = getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                                        $r += $ramos * $precio;
                                    }
                                }
                            }
                        } else {    // pedido normal
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                            $ramos = $det_ped->cantidad * $esp_emp->cantidad * ($esp_emp->especificacion->tipo === "O" ? $det_esp->tallos_x_ramos : (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad));
                            $precio_final = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                            $r += $precio_final;
                        }
                    }
                }
            }
        }

        if (count($this->envios) > 0)
            if ($this->envios[0]->comprobante != '') {  // PEDIDO FACTURADO
                return $this->envios[0]->comprobante->monto_total;
            } else {
                if ($this->envios[0]->fatura_cliente_tercero != '') {   // FACTURAR A NOMBRE DE OTRA PERSONA
                    $impuesto = TipoImpuesto::All()
                        ->where('codigo', $this->envios[0]->fatura_cliente_tercero->codigo_impuesto_porcentaje)->first()->porcentaje;
                    if (is_numeric($impuesto)) {
                        $r += $r * ($impuesto / 100);
                    }
                } else {    // FACTURAR A NOMBRE DEL CLIENTE
                    $impuesto = TipoImpuesto::All()
                        ->where('codigo', $this->cliente->detalle()->codigo_porcentaje_impuesto)->first()->porcentaje;
                    if (is_numeric($impuesto)) {
                        $r += $r * ($impuesto / 100);
                    }
                }
            }
        else {    // FACTURAR A NOMBRE DEL CLIENTE
            $impuesto = TipoImpuesto::All()
                ->where('codigo', $this->cliente->detalle()->codigo_porcentaje_impuesto)->first()->porcentaje;
            if (is_numeric($impuesto)) {
                $r += $r * ($impuesto / 100);
            }
        }

        return $r;
    }

    public function getPrecioByPedidoVariedad($variedad)
    {
        $r = 0;
        foreach ($this->detalles as $det_ped) {
            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                foreach ($esp_emp->detalles->where('id_variedad', $variedad) as $det_esp) {
                    if ($esp_emp->empaque->f_empaque == 'T') {  // pedido de tallos sueltos en mallas
                        $r += $det_esp->total_tallos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                    } else {
                        if ($this->tipo_especificacion == 'T') {    // flor tinturada
                            foreach ($det_ped->coloracionesByEspEmp($esp_emp->id_especificacion_empaque) as $col) {
                                $marcaciones_coloraciones = MarcacionColoracion::where('estado', 1)
                                    ->where('id_coloracion', $col->id_coloracion)
                                    ->where('id_detalle_especificacionempaque', $det_esp->id_detalle_especificacionempaque)
                                    ->get();
                                foreach ($marcaciones_coloraciones as $marc_col) {
                                    $ramos = $marc_col->cantidad;
                                    if ($marc_col->precio != '') {
                                        $r += $ramos * $marc_col->precio;
                                    } else if ($col->precio != '' && havePrecioByDetEsp($col->precio, $det_esp->id_detalle_especificacionempaque)) {
                                        $precio = getPrecioByDetEsp($col->precio, $det_esp->id_detalle_especificacionempaque);
                                        $r += $ramos * $precio;
                                    } else {
                                        $precio = getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                                        $r += $ramos * $precio;
                                    }
                                }
                            }
                        } else {    // pedido normal
                            $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                            $ramos = $det_ped->cantidad * $esp_emp->cantidad * ($esp_emp->especificacion->tipo === "O" ? $det_esp->tallos_x_ramos : (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad));
                            $precio_final = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                            $r += $precio_final;
                        }
                    }
                }
            }
        }
        return $r;
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_configuracion_empresa');
    }

    public function getCajasFull()
    {
        $cajasFull = 0;
        foreach ($this->detalles as $det_ped) {
            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                $cajasFull += ($esp_emp->cantidad * $det_ped->cantidad) * explode('|', $esp_emp->empaque->nombre)[1];
            }
        }
        return $cajasFull;
    }

    public function getCajasFullByVariedad($variedad)
    {

        $cajasFullByVariedad = 0;
        if (!getFacturaAnulada($this->id_pedido)) {
            $ramosStandarCajaTotal = 0;
            $ramosStandarCajaVariedad = 0;
            $factorConversion = 0;
            foreach ($this->detalles as $det_ped) {
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp)
                    foreach ($esp_emp->detalles as $det_esp_emp) {
                        $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                        $ramosStandarCajaTotal += convertToEstandar((isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $det_ped->cantidad, $det_esp_emp->clasificacion_ramo->nombre);
                        $factorConversion += explode('|', $esp_emp->empaque->nombre)[1] * $det_ped->cantidad;
                    }

                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp)
                    foreach ($esp_emp->detalles->where('id_variedad', $variedad) as $det_esp_emp)
                        $ramosStandarCajaVariedad += convertToEstandar((isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad) * $det_ped->cantidad, $det_esp_emp->clasificacion_ramo->nombre);
            }

            $standarTotal = $ramosStandarCajaVariedad > 0 ? $ramosStandarCajaTotal / $ramosStandarCajaVariedad : 0;
            $cajasFullByVariedad = $standarTotal > 0 ? $factorConversion / $standarTotal : 0;

            //dump(round($cajasFullByVariedad,2));

        }
        return round($cajasFullByVariedad, 2);
    }

    public function catntidad_det_esp_emp()
    {
        $cantidad = 0;
        foreach ($this->detalles as $det_ped) {
            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                $cantidad += $esp_emp->detalles->count();
            }
        }
        return $cantidad;
    }

    public function cant_rows_etiqueta($arr_ped)
    {
        $totalCajas = 0;
        foreach ($arr_ped as $ped) {
            foreach ($this->detalles as $det_ped) {
                foreach ($det_ped->marcaciones as $mc) {
                    if (explode("|", $mc->especificacion_empaque->empaque->nombre)[1] === $ped['caja']) {
                        $totalCajas += $mc->distribuciones->count();
                    }
                }
            }
        }
        return $totalCajas;
    }

    public function etiqueta_factura()
    {
        return $this->hasOne('\yura\Modelos\EtiquetaFactura', 'id_pedido');
    }
}
