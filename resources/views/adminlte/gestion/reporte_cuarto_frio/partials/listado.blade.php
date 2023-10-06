<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px;">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green">
                <div style="width: 130px">
                    Variedades
                </div>
            </th>
            <th class="padding_lateral_5 th_yura_green">
                <div style="width: 60px">
                    Longitud
                </div>
            </th>
            @php
                $totales_dias = [];
            @endphp
            @foreach ($dias as $pos => $l)
                <th class="padding_lateral_5 bg-yura_dark" title="{{ convertDateToText(opDiasFecha('-', $l, hoy())) }}">
                    <div style="width: 60px">
                        {{ $l }}{{ $pos == count($dias) - 1 ? '...' : '' }}
                    </div>
                </th>
                @php
                    $totales_dias[] = 0;
                @endphp
            @endforeach
            <th class="padding_lateral_5 th_yura_green">
                <div style="width: 60px">
                    TOTAL
                </div>
            </th>
        </tr>
        @foreach ($listado as $item)
            @foreach ($item['valores_var'] as $var)
                <tr>
                    <th class="padding_lateral_5 bg-yura_dark">
                        {{ $var['variedad']->nombre }}
                    </th>
                    <th class="padding_lateral_5 bg-yura_dark">
                        {{ $var['variedad']->longitud }}
                    </th>
                    @php
                        $total_var = 0;
                    @endphp
                    @foreach ($var['valores_dias'] as $pos => $val)
                        @php
                            $total_var += $val;
                            $totales_dias[$pos] += $val;
                        @endphp
                        <td class="padding_lateral_5 mouse-hand" style="border-color: #9d9d9d"
                            title="{{ convertDateToText(opDiasFecha('-', $pos, hoy())) }}"
                            onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')"
                            onclick="ver_inventario('{{ $var['variedad']->id_variedad }}', '{{ $var['variedad']->longitud }}', '{{ opDiasFecha('-', $pos, hoy()) }}', '{{ $tipo }}')">
                            {{ number_format($val) }}
                        </td>
                    @endforeach
                    <th class="padding_lateral_5 bg-yura_dark">
                        {{ number_format($total_var) }}
                    </th>
                </tr>
            @endforeach
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green" colspan="2">
                TOTALES
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales_dias as $v)
                <th class="padding_lateral_5 bg-yura_dark">
                    {{ number_format($v) }}
                </th>
                @php
                    $total += $v;
                @endphp
            @endforeach
            <th class="padding_lateral_5 th_yura_green">
                {{ number_format($total) }}
            </th>
        </tr>
    </table>
</div>

<script>
    function ver_inventario(variedad, longitud, fecha, tipo) {
        datos = {
            variedad: variedad,
            longitud: longitud,
            fecha: fecha,
            tipo: tipo,
        }
        get_jquery('{{ url('reporte_cuarto_frio/ver_inventario') }}', datos, function(retorno) {
            modal_view('modal_ver_inventario', retorno, '<i class="fa fa-fw fa-plus"></i> Inventario',
                true, false, '{{ isPC() ? '98%' : '' }}',
                function() {});
        })
    }
</script>
