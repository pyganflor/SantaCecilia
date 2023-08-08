<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DesgloseRecepcion extends Model
{
    protected $table = 'desglose_recepcion';
    protected $primaryKey = 'id_desglose_recepcion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_desglose_recepcion',
        'id_recepcion',
        'id_variedad',
        'cantidad_mallas',
        'tallos_x_malla',
        'fecha_registro',
        'estado',
        'id_modulo',
        'id_empresa',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function recepcion()
    {
        return $this->belongsTo('\yura\Modelos\Recepcion', 'id_recepcion');
    }

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }

    public function cosechador()
    {
        return $this->belongsTo('\yura\Modelos\Cosechador', 'id_cosechador');
    }
}
