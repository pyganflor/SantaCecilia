@extends('layouts.adminlte.master')

@section('titulo')
    Ciclos de Camas
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Ciclos de Camas
            <small class="text-color_yura">módulo de campo</small>
        </h1>

        <ol class="breadcrumb">
            <li>
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')">
                    <i class="fa fa-home"></i> Inicio
                </a>
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table width="100%">
            <tbody>
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Sector
                            </div>
                            <select id="filtro_sector" class="form-control input-yura_default"
                                onchange="seleccionar_sector()" style="width: 100% !important;">
                                <option value="">Seleccione</option>
                                @foreach ($sectores as $s)
                                    <option value="{{ $s->id_sector }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Bloque
                            </div>
                            <select id="filtro_modulo" class="form-control input-yura_default"
                                style="width: 100% !important;">
                                <option value="">Seleccione</option>
                            </select>
                            <div class="input-group-btn">
                                <button class="btn btn-primary btn-yura_primary" onclick="seleccionar_modulo()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div id="div_listado" style="margin-top: 5px; overflow-y: scroll; max-height: 600px; overflow-x: scroll"></div>
        <div class="text-center hidden" style="margin-top: 5px" id="div_btn_grabar">
            <button type="button" class="btn btn-yura_primary" onclick="store_ciclos()">
                <i class="fa fa-fw fa-save"></i> GRABAR CICLOS
            </button>
        </div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.ciclos_cama.script')
@endsection
