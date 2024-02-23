@extends('layouts.adminlte.master')

@section('titulo')
    Flor Nacional
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Reporte
            <small class="text-color_yura">Flor Nacional</small>
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
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Motivo
                        </span>
                        <select name="filtro_motivo" id="filtro_motivo" class="form-control">
                            <option value="">Todos los motivos</option>
                            @foreach ($motivos as $m)
                                <option value="{{ $m->id_motivos_nacional }}">{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i>
                        </span>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control">
                            <option value="">Todas las variedades</option>
                            @foreach ($variedades as $item)
                                <option value="{{ $item->id_variedad }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Desde
                        </span>
                        <input type="date" class="form-control" id="filtro_desde" name="filtro_desde" required
                            value="{{ $desde }}" max="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Hasta
                        </span>
                        <input type="date" class="form-control" id="filtro_hasta" name="filtro_hasta" required
                            value="{{ $hasta }}" max="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-filter"></i>
                        </span>
                        <select name="filtro_tipo" id="filtro_tipo" class="form-control">
                            <option value="V">Variedades</option>
                            <option value="M">Motivos</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            {{-- <button type="button" class="btn btn-yura_default" onclick="exportar_reporte()"
                                title="Exportar Excel">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button> --}}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_listado" style="margin-top: 5px"></div>
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.crm.reporte_flor_nacional.script')
@endsection
