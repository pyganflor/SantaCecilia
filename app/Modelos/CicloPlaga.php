<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CicloPlaga extends Model
{
    protected $table = 'ciclo_plaga';
    protected $primaryKey = 'id_ciclo_plaga';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo_cama',
        'id_plaga',
        'activo',
        'incidencia',
        'fecha',
        'estado',
    ];

    public function ciclo_cama()
    {
        return $this->belongsTo('\yura\Modelos\CicloCama', 'id_ciclo_cama');
    }

    public function plaga()
    {
        return $this->belongsTo('\yura\Modelos\Plaga', 'id_plaga');
    }
}
