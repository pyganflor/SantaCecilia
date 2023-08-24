<table class="table table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr class="tr_fija_top_0">
        <th class="text-center th_yura_green" style="width: 80px">
            CAMA
        </th>
        <th class="text-center th_yura_green" colspan="{{ $max_ciclos }}">
            PLAGAS / CUADRO
        </th>
    </tr>
    @foreach ($listado as $item)
        <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $item['cama']->nombre }}
            </th>
            @foreach ($item['ciclos'] as $ciclo)
                @php
                    $mis_plagas = [];
                    foreach ($ciclo['plagas'] as $plaga) {
                        $existe = false;
                        foreach ($mis_plagas as $p) {
                            if ($p->id_plaga == $plaga->id_plaga) {
                                $existe = true;
                            }
                        }
                        if (!$existe) {
                            $mis_plagas[] = $plaga;
                        }
                    }
                @endphp
                <td class="text-right"
                    style="border: 2px solid black; position: relative; width: 100px; vertical-align: top; padding: 0">
                    <table
                        style="width: 100%; min-width: 160px; border: 1px solid #9d9d9d; border-collapse: collapse; border-spacing: 0;"
                        class="table-bordered">
                        @foreach ($mis_plagas as $p)
                            @php
                                switch ($p->incidencia) {
                                    case 'alta':
                                        $bg_plaga = 'bg-yura_danger';
                                        break;
                                    case 'media':
                                        $bg_plaga = 'bg-yura_warning';
                                        break;
                                    case 'baja':
                                        $bg_plaga = 'bg-yura_primary';
                                        break;
                                
                                    default:
                                        $bg_plaga = '';
                                        break;
                                }
                            @endphp
                            <tr style="height: 30px; font-size: 0.8em">
                                <th class="text-right {{ $bg_plaga }}" style="padding-right: 2px">
                                    {{ str_limit($p->plaga->nombre, 5) }}
                                </th>
                                <th class="text-center {{ $bg_plaga }}" style="width: 50px; padding-left: 2px">
                                    {{ explode(' del ', convertDateToText($p->fecha))[0] }}
                                </th>
                                <th class="text-right" style="width: 30px; border-color: #9d9d9d">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-yura_default"
                                            title="Eliminar Incidencia"
                                            onclick="delete_incidencia('{{ $p->id_ciclo_plaga }}')">
                                            <i class="fa fa-fw fa-trash"></i>
                                        </button>
                                    </div>
                                </th>
                            </tr>
                        @endforeach
                    </table>

                    <span class="span_nombre_cuadro sombra_pequeña mouse-hand">
                        <div class="dropdown">
                            <span class="dropdown-toggle" id="menu_ciclo_{{ $ciclo['ciclo']->id_ciclo_cama }}"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                title="Asignar Incidencia de Plaga">
                                <sup>c</sup><b>{{ $ciclo['ciclo']->cuadro }}</b>
                            </span>
                            <ul class="dropdown-menu" aria-labelledby="menu_ciclo_{{ $ciclo['ciclo']->id_ciclo_cama }}"
                                style="overflow-y: scroll; max-height: 350px; width: 200px">
                                @foreach ($plagas as $p)
                                    <li class="dropdown-header">
                                        <b>{{ $p->nombre }}</b><i class="fa fa-fw fa-caret-down pull-right"></i>
                                    </li>
                                    <li class="text-right bg-yura_danger">
                                        <a href="javascript:void(0)" style="color: white"
                                            onmouseover="$(this).css('color', 'black')"
                                            onmouseleave="$(this).css('color', 'white')"
                                            onclick="store_incidencia('{{ $p->id_plaga }}', 'alta', '{{ $ciclo['ciclo']->id_ciclo_cama }}')">
                                            <i class="fa fa-fw fa-caret-right pull-left"></i>Alta
                                        </a>
                                    </li>
                                    <li class="text-right bg-yura_warning">
                                        <a href="javascript:void(0)" style="color: white"
                                            onmouseover="$(this).css('color', 'black')"
                                            onmouseleave="$(this).css('color', 'white')"
                                            onclick="store_incidencia('{{ $p->id_plaga }}', 'media', '{{ $ciclo['ciclo']->id_ciclo_cama }}')">
                                            <i class="fa fa-fw fa-caret-right pull-left"></i>Media
                                        </a>
                                    </li>
                                    <li class="text-right bg-yura_primary">
                                        <a href="javascript:void(0)" style="color: white"
                                            onmouseover="$(this).css('color', 'black')"
                                            onmouseleave="$(this).css('color', 'white')"
                                            onclick="store_incidencia('{{ $p->id_plaga }}', 'baja', '{{ $ciclo['ciclo']->id_ciclo_cama }}')">
                                            <i class="fa fa-fw fa-caret-right pull-left"></i>Baja
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </span>
                </td>
            @endforeach
        </tr>
    @endforeach
</table>

<style>
    .span_nombre_cuadro {
        position: absolute;
        padding: 2px 4px 4px 2px;
        left: 0;
        top: 0;
        border-radius: 0 0 16px 0;
        background-image: linear-gradient(to bottom, #6ce0e4, #7ef6ff8a);
        color: black !important;
    }
</style>

<script>
    function store_incidencia(plaga, incidencia, ciclo) {
        datos = {
            _token: '{{ csrf_token() }}',
            plaga: plaga,
            incidencia: incidencia,
            ciclo: ciclo,
            fecha: $('#filtro_fecha').val(),
        };
        post_jquery_m('{{ url('monitoreo_plagas/store_incidencia') }}', datos, function(retorno) {
            listar_reporte();
        });
    }

    function delete_incidencia(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        };
        post_jquery_m('{{ url('monitoreo_plagas/delete_incidencia') }}', datos, function(retorno) {
            listar_reporte();
        });
    }
</script>
