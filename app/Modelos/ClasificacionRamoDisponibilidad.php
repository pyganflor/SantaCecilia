<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ClasificacionRamoDisponibilidad extends Model
{
    protected $table = 'clasificacion_ramo_disponibilidad';
    protected $primaryKey = 'id_clasificacion_ramo_disponibilidad';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_clasificacion_ramo',
        'ramos_x_caja',
        'id_mezcla',
    ];

    public function clasificacion_ramo()
    {
        return $this->belongsTo('\yura\Modelos\ClasificacionRamo', 'id_clasificacion_ramo');
    }

    public function mezcla()
    {
        return $this->belongsTo('\yura\Modelos\ClasificacionRamo', 'id_mezcla');
    }
}
