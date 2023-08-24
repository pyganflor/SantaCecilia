<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
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
                    <td class="text-center" style="border: 2px solid black; position: relative; height: 60px;">
                        <span class="span_nombre_cuadro sombra_pequeña mouse-hand">
                            <div class="dropdown">
                                <span class="dropdown-toggle" id="menu_ciclo_{{ $ciclo->id_ciclo_cama }}"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    title="Asignar Incidencia de Plaga">
                                    <sup>c</sup><b>{{ $ciclo->cuadro }}</b>
                                </span>
                                <ul class="dropdown-menu" aria-labelledby="menu_ciclo_{{ $ciclo->id_ciclo_cama }}"
                                    style="overflow-y: scroll; max-height: 350px; width: 200px">
                                    @foreach ($plagas as $p)
                                        <li class="dropdown-header">
                                            <b>{{ $p->nombre }}</b><i class="fa fa-fw fa-caret-down pull-right"></i>
                                        </li>
                                        <li class="text-right bg-yura_danger">
                                            <a href="javascript:void(0)" style="color: white"
                                                onmouseover="$(this).css('color', 'black')"
                                                onmouseleave="$(this).css('color', 'white')"
                                                onclick="store_incidencia('{{ $p->id_plaga }}', 'alta', '{{ $ciclo->id_ciclo_cama }}')">
                                                <i class="fa fa-fw fa-caret-right pull-left"></i>Alta
                                            </a>
                                        </li>
                                        <li class="text-right bg-yura_warning">
                                            <a href="javascript:void(0)" style="color: white"
                                                onmouseover="$(this).css('color', 'black')"
                                                onmouseleave="$(this).css('color', 'white')"
                                                onclick="store_incidencia('{{ $p->id_plaga }}', 'media', '{{ $ciclo->id_ciclo_cama }}')">
                                                <i class="fa fa-fw fa-caret-right pull-left"></i>Media
                                            </a>
                                        </li>
                                        <li class="text-right bg-yura_primary">
                                            <a href="javascript:void(0)" style="color: white"
                                                onmouseover="$(this).css('color', 'black')"
                                                onmouseleave="$(this).css('color', 'white')"
                                                onclick="store_incidencia('{{ $p->id_plaga }}', 'baja', '{{ $ciclo->id_ciclo_cama }}')">
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
</div>

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
        post_jquery_m('{{ url('monitoreo_plagas/store_incidencia') }}', datos, function(retorno) {});
    }
</script>
