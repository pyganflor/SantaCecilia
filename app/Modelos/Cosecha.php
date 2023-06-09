<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cosecha extends Model
{
    protected $table = 'cosecha';
    protected $primaryKey = 'id_cosecha';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cosecha',
        'fecha_ingreso',
        'personal',
        'fecha_registro',
        'estado',
        'hora_inicio',
    ];

    public function recepciones()
    {
        return $this->hasMany('\yura\Modelos\Recepcion', 'id_cosecha');
    }

    public function grupos_cosechaByFinca($finca)
    {
        return CosechaPersonal::where('id_cosecha', $this->id_cosecha)->where('id_empresa', $finca)->get();
    }

    public function getCosechaPersonalByFinca($finca)
    {
        return DB::table('cosecha_personal')
            ->select(DB::raw('sum(personal) as personal'), DB::raw('min(hora_inicio) as hora_inicio'))
            ->where('id_cosecha', $this->id_cosecha)->where('id_empresa', $finca)
            ->get()[0];
    }

    public function recepcionesByFinca($finca)
    {
        return Recepcion::join('desglose_recepcion as dr', 'dr.id_recepcion', 'recepcion.id_recepcion')
            ->select('recepcion.*')->distinct()
            ->where('recepcion.estado', 1)
            ->where('recepcion.id_cosecha', $this->id_cosecha)
            ->where('dr.id_empresa', $finca)
            ->get();
    }

    public function getTotalTallosByModulo($modulo)
    {
        $r = 0;
        foreach ($this->recepciones as $recepcion) {
            foreach ($recepcion->desgloses->where('id_modulo', $modulo) as $desglose) {
                $r += ($desglose->cantidad_mallas * $desglose->tallos_x_malla);
            }
        }
        return $r;
    }

    public function getTotalTallosByModuloVariedad($modulo, $variedad)
    {
        $r = 0;
        foreach ($this->recepciones as $recepcion) {
            foreach ($recepcion->desgloses->where('id_modulo', $modulo)->where('id_variedad', $variedad) as $desglose) {
                $r += ($desglose->cantidad_mallas * $desglose->tallos_x_malla);
            }
        }
        return $r;
    }

    public function getTotalTallos()
    {
        $r = 0;
        foreach ($this->recepciones as $recepcion) {
            $r += $recepcion->cantidad_tallos();
        }
        return $r;
    }

    public function getTotalTallosByFinca($finca)
    {
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.tallos_x_malla * dr.cantidad_mallas) as cantidad'))
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('dr.id_empresa', $finca)
            ->get()[0]->cantidad;
        return $r > 0 ? $r : 0;
    }

    public function getTotalTallosByVariedad($variedad)
    {
        $r = 0;
        $a = [];
        foreach ($this->recepciones as $recepcion) {
            $r += $recepcion->tallos_x_variedad($variedad);
            array_push($a, [
                'recep' => $recepcion,
                'cant' => $recepcion->tallos_x_variedad($variedad)
            ]);
        }
        return $r;
    }

    public function getTotalTallosByVariedadFinca($variedad, $finca)
    {
        $r = 0;
        $a = [];
        foreach ($this->recepciones as $recepcion) {
            $val = $recepcion->tallos_x_variedadByFinca($variedad, $finca);
            $r += $val;
            array_push($a, [
                'recep' => $recepcion,
                'cant' => $val
            ]);
        }
        return $r;
    }

    public function getVariedades()
    {
        $listado = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select('dr.id_variedad')->distinct()
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->get();
        return $listado;
    }

    public function getVariedadesByFinca($finca)
    {
        $listado = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select('dr.id_variedad')->distinct()
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('dr.id_empresa', '=', $finca)
            ->orderBy('v.nombre')
            ->get();
        return $listado;
    }

    public function getTotalTallosByIntervalo($inicio, $fin)
    {
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('r.fecha_ingreso', '>=', $inicio)
            ->where('r.fecha_ingreso', '<', $fin)
            ->get()[0];

        return $r->cantidad;
    }

    public function getTotalTallosByIntervaloFinca($inicio, $fin, $finca)
    {
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('dr.id_empresa', '=', $finca)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('r.fecha_ingreso', '>=', $inicio)
            ->where('r.fecha_ingreso', '<', $fin)
            ->get()[0];

        return $r->cantidad;
    }

    public function getTotalTallosByIntervaloVariedad($inicio, $fin, $variedad)
    {
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('dr.id_variedad', '=', $variedad)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('r.fecha_ingreso', '>=', $inicio)
            ->where('r.fecha_ingreso', '<', $fin)
            ->get()[0];

        return $r->cantidad;
    }

    public function getCantidadHorasTrabajo()
    {
        $last_ingreso = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('max(dr.fecha_registro) as fecha'))
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->get();

        if ($last_ingreso[0]->fecha != '') {
            $horas = difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $this->hora_inicio)->h;
            $horas += round(difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $this->hora_inicio, 2)->i / 60);

            return $horas;
        } else {
            return 0;
        }
    }

    public function getCantidadHorasTrabajoByFinca($finca)
    {
        $last_ingreso = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('max(dr.fecha_registro) as fecha'))
            ->where('dr.estado', '=', 1)
            ->where('dr.id_empresa', '=', $finca)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->get();

        $cosecha_personal = $this->getCosechaPersonalByFinca($finca);
        if ($last_ingreso[0]->fecha != '' && $cosecha_personal != '') {
            $horas = difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $cosecha_personal->hora_inicio)->h;
            $horas += round(difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $cosecha_personal->hora_inicio, 2)->i / 60);

            return $horas;
        } else {
            return 0;
        }
    }

    public function getCantidadHorasTrabajoByVariedad($variedad)
    {
        $last_ingreso = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('max(dr.fecha_registro) as fecha'))
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('dr.id_variedad', '=', $variedad)
            ->get();

        if ($last_ingreso[0]->fecha != '') {
            $horas = difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $this->hora_inicio)->h;
            $horas += round(difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $this->hora_inicio, 2)->i / 60);

            return $horas;
        } else {
            return 0;
        }
    }

    public function getRendimiento()
    {
        if ($this->getCantidadHorasTrabajo() != 0) {
            $r = $this->getTotalTallos() / $this->personal;
            return round($r / $this->getCantidadHorasTrabajo(), 2);
        } else
            return 0;
    }

    public function getRendimientoByFinca($finca)
    {
        $cosecha_personal = $this->getCosechaPersonalByFinca($finca);
        $getCantidadHorasTrabajoByFinca = $this->getCantidadHorasTrabajoByFinca($finca);
        if ($getCantidadHorasTrabajoByFinca > 0) {
            $r = $cosecha_personal->personal > 0 ? $this->getTotalTallosByFinca($finca) / $cosecha_personal->personal : 0;
            return round($r / $getCantidadHorasTrabajoByFinca, 2);
        } else
            return 0;
    }

    public function getRendimientoByVariedad($variedad)
    {
        if ($this->getCantidadHorasTrabajoByVariedad($variedad) != 0) {
            $r = $this->getTotalTallosByVariedad($variedad) / $this->personal;
            return round($r / $this->getCantidadHorasTrabajoByVariedad($variedad), 2);
        } else
            return 0;
    }

    public function getIntervalosHoras()
    {
        $r = [];
        $listado_fechas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select('r.fecha_ingreso as fecha')->distinct()
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->orderBy('r.fecha_ingreso')
            ->get();

        $listado = [];
        foreach ($listado_fechas as $item)
            array_push($listado, $item->fecha);

        foreach ($listado as $item) {
            $intervalo = [
                'fecha_inicio' => substr($item, 0, 10),
                'fecha_fin' => substr(opHorasFecha('+', 1, substr($item, 0, 13) . ':00'), 0, 10),
                'fecha_inicio_full' => substr($item, 0, 13) . ':00',
                'fecha_fin_full' => opHorasFecha('+', 1, substr($item, 0, 13) . ':00'),
                'hora_inicio' => substr($item, 11, 2) . ':00',
                'hora_fin' => substr(opHorasFecha('+', 1, substr($item, 0, 13) . ':00'), 11, 2) . ':00',
            ];
            if (!in_array($intervalo, $r)) {
                array_push($r, $intervalo);
            }
        }

        return $r;
    }

    public function getIntervalosHorasByFinca($finca)
    {
        $r = [];
        $listado_fechas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select('r.fecha_ingreso as fecha')->distinct()
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('dr.id_empresa', '=', $finca)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->orderBy('r.fecha_ingreso')
            ->get();

        $listado = [];
        foreach ($listado_fechas as $item)
            array_push($listado, $item->fecha);

        foreach ($listado as $item) {
            $intervalo = [
                'fecha_inicio' => substr($item, 0, 10),
                'fecha_fin' => substr(opHorasFecha('+', 1, substr($item, 0, 13) . ':00'), 0, 10),
                'fecha_inicio_full' => substr($item, 0, 13) . ':00',
                'fecha_fin_full' => opHorasFecha('+', 1, substr($item, 0, 13) . ':00'),
                'hora_inicio' => substr($item, 11, 2) . ':00',
                'hora_fin' => substr(opHorasFecha('+', 1, substr($item, 0, 13) . ':00'), 11, 2) . ':00',
            ];
            if (!in_array($intervalo, $r)) {
                array_push($r, $intervalo);
            }
        }

        return $r;
    }

    public function getDetallesByIntervalo($inicio, $fin)
    {
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'), 'dr.id_variedad', 'dr.id_modulo')
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('r.fecha_ingreso', '>=', $inicio)
            ->where('r.fecha_ingreso', '<', $fin)
            ->groupBy('dr.id_variedad', 'dr.id_modulo')
            ->get();

        return $r;
    }

    public function getDetallesByIntervaloByFinca($inicio, $fin, $finca)
    {
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'), 'dr.id_variedad', 'dr.id_modulo')
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('dr.id_empresa', '=', $finca)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('r.fecha_ingreso', '>=', $inicio)
            ->where('r.fecha_ingreso', '<', $fin)
            ->groupBy('dr.id_variedad', 'dr.id_modulo')
            ->get();

        return $r;
    }

    public function getClasificacionVerdeByFecha()
    {
        return ClasificacionVerde::All()->where('estado', 1)->where('fecha_ingreso', $this->fecha_ingreso)->first();
    }
}