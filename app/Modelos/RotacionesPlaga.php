<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class RotacionesPlaga extends Model
{
    protected $table = 'rotaciones_plaga';
    protected $primaryKey = 'id_rotaciones_plaga';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_plaga',
        'fecha_registro',
        'estado',
        'id_producto',
        'dosis',
        'litros_x_cama',
        'incidencia',
        'rotacion',
    ];

    public function plaga()
    {
        return $this->belongsTo('\yura\Modelos\Plaga', 'id_plaga');
    }

    public function producto()
    {
        return $this->belongsTo('\yura\Modelos\Producto', 'id_producto');
    }
}
