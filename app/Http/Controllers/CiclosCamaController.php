<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\Cama;
use yura\Modelos\Modulo;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class CiclosCamaController extends Controller
{
    public function inicio(Request $request)
    {
        $sectores = Sector::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.ciclos_cama.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'sectores' => $sectores
        ]);
    }

    public function seleccionar_sector(Request $request)
    {
        $modulos = Modulo::where('estado', 1)
            ->where('id_sector', $request->sector)
            ->orderBy('nombre')
            ->get();
        $options = '<option value="">Seleccione</option>';
        foreach ($modulos as $m) {
            $options .= '<option value="' . $m->id_modulo . '">' . $m->nombre . '</option>';
        }
        return [
            'options' => $options
        ];
    }

    public function seleccionar_modulo(Request $request)
    {
        $camas = Cama::where('estado', 1)
            ->where('id_modulo', $request->modulo)
            ->orderBy('id_cama')
            ->get();
        $variedades = Variedad::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.ciclos_cama.partials.listado', [
            'camas' => $camas,
            'variedades' => $variedades,
        ]);
    }
}
