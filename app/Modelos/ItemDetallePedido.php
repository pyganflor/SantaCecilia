<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ItemDetallePedido extends Model
{
    protected $table = 'item_detalle_pedido';
    protected $primaryKey = 'id_item_detalle_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_pedido',
        'id_finca',
        'id_finca_origen',
        'precio',
        'id_variedad',
        'tallos_x_ramo',
        'longitud',
        'ramos_x_caja',
        'estado',
    ];

    public function detalle_pedido()
    {
        return $this->belongsTo('yura\Modelos\DetallePedido', 'id_detalle_pedido');
    }

    public function finca()
    {
        return $this->belongsTo('yura\Modelos\ConfiguracionEmpresa', 'id_finca');
    }

    public function finca_origen()
    {
        return $this->belongsTo('yura\Modelos\ConfiguracionEmpresa', 'id_finca_origen');
    }

    public function variedad()
    {
        return $this->belongsTo('yura\Modelos\Variedad', 'id_variedad');
    }
}
