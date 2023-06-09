<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenCostosSemanal extends Model
{
    protected $table = 'resumen_costos_semanal';
    protected $primaryKey = 'id_resumen_costos_semanal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codigo_semana',
        'mano_obra',
        'insumos',
        'fijos',
        'regalias',
        'id_empresa',
        'compra_flor',
    ];

    public function semana()
    {
        return Semana::All()->where('estado', 1)->where('codigo', $this->codigo_semana)->first();
    }

    public function empresa()
    {
        return $this->belongsTo('yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}