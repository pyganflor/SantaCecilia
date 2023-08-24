@extends('layouts.adminlte.master')

@section('titulo')
    Monitoreo de Plagas
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Monitoreo de Plagas
            <small class="text-color_yura">módulo de campo</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i>
                    Inicio</a></li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table style="width: 100%">
            <tbody>
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Fecha
                            </div>
                            <input type="date" class="form-control text-center" id="filtro_fecha" value="{{ hoy() }}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Sector
                            </div>
                            <select id="filtro_sector" class="form-control"
                                onchange="seleccionar_sector()" style="width: 100% !important;">
                                @foreach ($sectores as $s)
                                    <option value="{{ $s->id_sector }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Bloque
                            </div>
                            <select id="filtro_modulo" class="form-control"
                                style="width: 100% !important;" onchange="listar_reporte()">
                                <option value="">Seleccione</option>
                            </select>
                            <div class="input-group-btn">
                                <button class="btn btn-primary btn-yura_primary" onclick="listar_reporte()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div id="div_listado" style="margin-top: 5px">
        </div>
    </section>

    <style>
        #tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.campo.monitoreo_plagas.script')
@endsection
