<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Plaga extends Model
{
    protected $table = 'plaga';
    protected $primaryKey = 'id_plaga';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
