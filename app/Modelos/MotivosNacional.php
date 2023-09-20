<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class MotivosNacional extends Model
{
    protected $table = 'motivos_nacional';
    protected $primaryKey = 'id_motivos_nacional';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
