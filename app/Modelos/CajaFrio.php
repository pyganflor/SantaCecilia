<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CajaFrio extends Model
{
    protected $table = 'caja_frio';
    protected $primaryKey = 'id_caja_frio';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'nombre',
        'fecha',
    ];

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleCajaFrio', 'id_caja_frio');
    }

    public function getTotales()
    {
        return DB::table('detalle_caja_frio')
            ->select(
                DB::raw('sum(tallos_x_ramo * ramos) as tallos'),
                DB::raw('sum(ramos) as ramos')
            )
            ->where('id_caja_frio', $this->id_caja_frio)
            ->get()[0];
    }
}
