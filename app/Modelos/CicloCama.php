<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CicloCama extends Model
{
    protected $table = 'ciclo_cama';
    protected $primaryKey = 'id_ciclo_cama';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cama',
        'fecha_registro',
        'activo',
        'fecha_inicio',
        'fecha_fin',
        'plantas_muertas',
        'esq_x_planta', // conteo de la cantidad de esquejes que se cosecha por semana
        'id_variedad',
        'semana_cosecha',   // semana de inicio de cosecha
        'total_semanas_cosecha',    // numero total de semanas que dura la cosecha
        'id_empresa',
    ];

    public function cama()
    {
        return $this->belongsTo('\yura\Modelos\Cama', 'id_cama');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function contenedores()
    {
        return $this->hasMany('\yura\Modelos\CicloCamaContenedor', 'id_ciclo_cama');
    }

    public function semana_ini()
    {
        return getSemanaByDate($this->fecha_inicio);
    }

    public function semana_vida()
    {
        return round(difFechas($this->fecha_fin, $this->fecha_inicio)->days / 7);
    }

    public function getPlantasProductivas()
    {
        $valor = DB::table('ciclo_cama_contenedor as ccc')
            ->join('contenedor_propag as cp', 'cp.id_contenedor_propag', '=', 'ccc.id_contenedor_propag')
            ->select(DB::raw('sum(ccc.cantidad * cp.cantidad) as cant'))
            ->where('ccc.id_ciclo_cama', $this->id_ciclo_cama)
            ->get()[0]->cant;
        return $valor != '' ? $valor : 0;
    }

    public function getDiasVida()
    {
        return difFechas(date('Y-m-d'), $this->fecha_inicio)->days;
    }

    public function getEsquejesCosechados()
    {
        return DB::table('cosecha_plantas_madres')
            ->select(DB::raw('sum(cantidad) as cant'))
            ->where('id_cama', $this->id_cama)
            ->where('fecha', '>', $this->fecha_inicio)
            ->get()[0]->cant;
    }

    public function getExquejesCosechadosByLastSemana()
    {
        $semana_pasada = getInicioFinLastSemana();

        return DB::table('cosecha_plantas_madres')
            ->select(DB::raw('sum(cantidad) as cant'))
            ->where('id_cama', $this->id_cama)
            ->where('fecha', '>=', $semana_pasada['inicio'])
            ->where('fecha', '<=', $semana_pasada['fin'])
            ->get()[0]->cant;
    }

    public function getCicloContenedorByContenedor($contenedores, $id_cont)
    {
        foreach ($contenedores as $cont) {
            if ($cont->id_contenedor_propag == $id_cont)
                return $cont;
        }
        return '';
    }

    public function getPorcentajeCosechado()
    {
        $total = $this->getPlantasProductivas() * $this->esq_x_planta * ($this->total_semanas_cosecha - $this->semana_cosecha);
        $cosechado = $this->getEsquejesCosechados();
        return round(($cosechado * 100) / $total, 2);
    }

    public function getFechaCosecha()
    {
        $fecha = DB::table('cosecha_plantas_madres')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('fecha', '>', $this->fecha_inicio)
            ->where('fecha', '<=', $this->fecha_fin)
            ->where('id_cama', $this->id_cama)
            ->get()[0]->fecha;
        return $fecha;
    }
}
