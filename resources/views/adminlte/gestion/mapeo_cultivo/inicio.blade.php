@extends('layouts.adminlte.master')

@section('titulo')
    Mapeo de Cultivo
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Mapeo de Cultivo
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
        <table style="width: 100%">
            <tr>
                <td style="min-width: 300px; vertical-align: top">
                    <div id="div_sectores"></div>
                </td>
                <td style="min-width: 300px; vertical-align: top">
                    <div id="div_modulos"></div>
                </td>
                <td style="min-width: 300px; vertical-align: top">
                    <div id="div_camas"></div>
                </td>
            </tr>
        </table>
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
    @include('adminlte.gestion.mapeo_cultivo.script')
@endsection
