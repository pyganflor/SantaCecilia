<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ParametrosGenerales extends Model
{
    protected $table = 'parametros_generales';
    protected $primaryKey = 'id_parametros_generales';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'rrhh_minutos_almuerzo',
    ];
}
