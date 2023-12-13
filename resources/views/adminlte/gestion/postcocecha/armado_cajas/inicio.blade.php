@extends('layouts.adminlte.master')

@section('titulo')
    Armado de Cajas
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Armado de Cajas
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li>
                <a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura">
                    <i class="fa fa-home text-color_yura"></i>
                    Inicio
                </a>
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh text-color_yura"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div id="div_content_recepciones">
            <table width="100%" style="margin-bottom: 0;" class="hidden">
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                <i class="fa fa-fw fa-barcode"></i> Escanear Codigo
                            </div>
                            <input type="text" id="filtro_codigo_barra" required
                                class="form-control input-yura_default text-center" autofocus
                                style="width: 100% !important;" onchange="escanear_codigo()">
                            <div class="input-group-btn">
                                <button class="btn btn-yura_primary" onclick="escanear_codigo()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div style="overflow-x: scroll; width: 100%">
                <table style="width:100%; margin-top: 5px">
                    <tr>
                        <td style="vertical-align: top; padding-right: 5px" id="td_inventarios">
                            <div class="panel panel-success" style="margin-bottom: 0px; min-width: 550px;"
                                id="panel_inventarios">
                                <div class="panel-heading"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <b> <i class="fa fa-gift"></i> CONTENIDO DE LA CAJA </b>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-yura_primary" onclick="store_caja()">
                                            <i class="fa fa-fw fa-save"></i> GUARDAR CAJA
                                        </button>
                                    </div>
                                </div>
                                <div class="panel-body" id="body_contenido_caja" style="max-height: 700px">
                                    <div class="text-center">
                                        <div class="input-group">
                                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                                <i class="fa fa-fw fa-gift"></i> Nombre Caja
                                            </div>
                                            <input type="text" id="nombre_caja" required class="form-control text-center"
                                                style="width: 100% !important;" placeholder="Nombre de Caja">
                                            <div class="input-group-addon bg-yura_dark">
                                                <i class="fa fa-fw fa-calendar"></i> Fecha
                                            </div>
                                            <input type="date" id="fecha_caja" required
                                                class="form-control input-yura_default text-center"
                                                value="{{ hoy() }}" style="width: 100% !important;"
                                                placeholder="Fecha de Armado">
                                        </div>
                                    </div>
                                    <table class="table-bordered"
                                        style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
                                        <tr>
                                            <th class="text-center th_yura_green">
                                                Variedad
                                            </th>
                                            <th class="text-center th_yura_green">
                                                Longitud
                                            </th>
                                            <th class="text-center th_yura_green">
                                                Tallos
                                            </th>
                                            <th class="text-center th_yura_green">
                                                Edad
                                            </th>
                                            <th class="text-center th_yura_green">
                                                Ramos
                                            </th>
                                            <th class="text-center th_yura_green" style="width: 80px" colspan="2">
                                                Disponibles
                                            </th>
                                        </tr>
                                        <tbody id="table_caja"></tbody>
                                        <tr>
                                            <th class="text-center bg-yura_dark" colspan="2">
                                                Totales
                                            </th>
                                            <th class="text-center bg-yura_dark" id="td_total_tallos_caja">
                                            </th>
                                            <th class="text-center bg-yura_dark">
                                            </th>
                                            <th class="text-center bg-yura_dark" id="td_total_ramos_caja">
                                            </th>
                                            <th class="text-center bg-yura_dark" style="width: 80px">
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                        <td style="vertical-align: top; padding-left: 5px;" id="td_seleccionados" class="hidden">
                            <div class="panel panel-success" style="margin-bottom:0px; min-width: 440px;"
                                id="panel_seleccionados">
                                <div class="panel-heading"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <b> <i class="fa fa-th"></i> DETALLE DE LA LECTURA</b>
                                    <div class="text-center">
                                        <div class="input-group">
                                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                                <i class="fa fa-fw fa-arrow-left"></i> Agregar a la Caja
                                            </div>
                                            <select id="agregar_automaticamente" style="width: 100%"
                                                class="form-control input-yura_default">
                                                <option value="1">Si</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body" style="max-height: 500px; overflow:auto" id="body_escaneado">
                                </div>
                            </div>
                        </td>
                        <td style="vertical-align: top; padding-left: 5px;" id="td_inventario">
                            <div class="panel panel-success" style="margin-bottom:0px; min-width: 440px;"
                                id="panel_inventario">
                                <div class="panel-heading"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <b> <i class="fa fa-th"></i> CUARTO FRIO</b>
                                    <div class="text-center">
                                        <div class="input-group">
                                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                                <i class="fa fa-fw fa-filter"></i> Variedad
                                            </div>
                                            <select id="filtro_inventario_variedad" style="width: 100%"
                                                class="form-control input-yura_default" onchange="buscar_inventario()">
                                                <option value="">Todas</option>
                                                @foreach ($variedades as $var)
                                                    <option value="{{ $var->id_variedad }}">{{ $var->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body" id="body_inventario">
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.armado_cajas.script')
@endsection
