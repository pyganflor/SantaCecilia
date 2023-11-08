<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class FlorNacional extends Model
{
    protected $table = 'flor_nacional';
    protected $primaryKey = 'id_flor_nacional';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'fecha_registro',
        'id_modulo',
        'id_motivos_nacional',
        'tallos',
        'fecha',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }

    public function motivos_nacional()
    {
        return $this->belongsTo('\yura\Modelos\MotivosNacional', 'id_motivos_nacional');
    }
}
