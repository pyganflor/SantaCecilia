<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DetallePedido extends Model
{
    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_agencia_carga',
        'cantidad',
        'estado',
        'fecha_registro',
        'precio',
        'orden',
        'id_variedad',
        'id_clasificacion_ramo',
        'tallos_x_ramo',
        'longitud',
        'id_empaque',
        'ramos_x_caja',
        'id_caja',
        'id_inventario_frio',
    ];

    public function cliente_especificacion()
    {
        return $this->belongsTo('yura\Modelos\ClientePedidoEspecificacion', 'id_cliente_especificacion');
    }

    public function agencia_carga()
    {
        return $this->belongsTo('yura\Modelos\AgenciaCarga', 'id_agencia_carga');
    }

    public function pedido()
    {
        return $this->belongsTo('yura\Modelos\Pedido', 'id_pedido');
    }

    public function items()
    {
        return $this->hasMany('\yura\Modelos\ItemDetallePedido', 'id_detalle_pedido');
    }

    public function marcaciones()
    {
        return $this->hasMany('\yura\Modelos\Marcacion', 'id_detalle_pedido');
    }

    public function getTotales()
    {
        $tallos = 0;
        $ramos = 0;
        $monto = 0;
        foreach ($this->items as $item) {
            $tallos += $item->ramos_x_caja * $item->tallos_x_ramo * $this->cantidad;
            $ramos += $item->ramos_x_caja * $this->cantidad;
            $monto += $item->ramos_x_caja * $item->tallos_x_ramo * $this->cantidad * $item->precio;
        }
        return [
            'tallos' => $tallos,
            'ramos' => $ramos,
            'monto' => $monto,
        ];
    }

    public function marcacionesByEspEmp($esp_emp)
    {
        return Marcacion::All()->where('id_detalle_pedido', $this->id_detalle_pedido)
            ->where('id_especificacion_empaque', $esp_emp);
    }

    public function marcacionesDistribucionByEspEmp($esp_emp)
    {
        return Marcacion::where('id_detalle_pedido', $this->id_detalle_pedido)
            ->where('id_especificacion_empaque', $esp_emp)
            ->join('distribucion', 'distribucion.id_marcacion', 'marcacion.id_marcacion');
    }

    public function coloraciones()
    {
        return $this->hasMany('\yura\Modelos\Coloracion', 'id_detalle_pedido');
    }

    public function coloracionesByEspEmp($esp_emp)
    {
        return Coloracion::All()->where('id_detalle_pedido', $this->id_detalle_pedido)
            ->where('id_especificacion_empaque', $esp_emp);
    }

    public function getColoracionesMarcacionesByEspEmp($esp_emp)
    {
        return [
            'coloraciones' => Coloracion::where('id_detalle_pedido', $this->id_detalle_pedido)
                ->where('id_especificacion_empaque', $esp_emp)->get(),
            'marcaciones' => Marcacion::where('id_detalle_pedido', $this->id_detalle_pedido)
                ->where('id_especificacion_empaque', $esp_emp)->get(),
        ];
    }

    public function haveDistribucion()
    {
        if (count($this->marcaciones) > 0) {
            foreach ($this->marcaciones as $m) {
                if (count($m->distribuciones) == 0)
                    return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function haveDistribucionByEspEmp($esp_emp)
    {
        if (count($this->marcaciones) > 0) {
            foreach ($this->marcacionesByEspEmp($esp_emp) as $m) {
                if (count($m->distribuciones) == 0)
                    return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function data_tallos()
    {
        return $this->hasOne('yura\Modelos\DataTallos', 'id_detalle_pedido');
    }

    public function total_tallos()
    {
        return $this->data_tallos->mallas * $this->data_tallos->tallos_x_malla;
    }

    public function detalle_pedido_dato_exportacion()
    {
        return $this->hasMany('yura\Modelos\DetallePedidoDatoExportacion', 'id_detalle_pedido');
    }

    public function getPrecioTinturadoByEspEmp($esp_emp)
    {
        $esp_emp = EspecificacionEmpaque::find($esp_emp);
        $r = 0;
        foreach ($esp_emp->detalles as $det_esp) {
            foreach ($this->coloracionesByEspEmp($esp_emp->id_especificacion_empaque) as $col) {
                $marcaciones_coloraciones = MarcacionColoracion::where('estado', 1)
                    ->where('id_coloracion', $col->id_coloracion)
                    ->where('id_detalle_especificacionempaque', $det_esp->id_detalle_especificacionempaque)
                    ->get();
                foreach ($marcaciones_coloraciones as $marc_col) {
                    $ramos = $marc_col->cantidad;
                    if ($marc_col->precio != '') {
                        $r += $ramos * $marc_col->precio;
                    } else if ($col->precio != '') {
                        $precio = getPrecioByDetEsp($col->precio, $det_esp->id_detalle_especificacionempaque);
                        $r += $ramos * $precio;
                    } else {
                        $precio = getPrecioByDetEsp($this->precio, $det_esp->id_detalle_especificacionempaque);
                        $r += $ramos * $precio;
                    }
                }
            }
        }
        return $r;
    }
}
