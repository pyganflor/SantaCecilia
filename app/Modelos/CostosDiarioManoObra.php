<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CostosDiarioManoObra extends Model
{
    protected $table = 'costos_diario_mano_obra';
    protected $primaryKey = 'id_costos_diario_mano_obra';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_actividad_mano_obra',
        'id_control_personal',
        'fecha',
        'codigo_semana',
        'valor',
        'cantidad',
        'fecha_registro',
        'id_personal',
        'id_empresa',
        'valor_50',
        'valor_100'
    ];

    public function actividad_mano_obra()
    {
        return $this->belongsTo('\yura\Modelos\ActividadManoObra', 'id_actividad_mano_obra');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}