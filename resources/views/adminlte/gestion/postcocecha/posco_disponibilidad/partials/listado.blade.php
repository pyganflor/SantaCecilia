<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Longitud
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Inventario
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Ramos x Caja
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Cajas
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Ramos Sobrantes
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Cajas Mixtas
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_cajas = 0;
                $total_cajas_mixtas = 0;
            @endphp
            @foreach ($listado as $pos_v => $item)
                @php
                    $cajas_mixtas = 0;
                    foreach ($item['valores_var'] as $x => $long) {
                        $ramos_sobrantes = $long['sobra'];
                        for ($i = $x + 1; $i < count($item['valores_var']); $i++) {
                            if ($long['param']->id_mezcla == $item['valores_var'][$i]['longitud']->id_clasificacion_ramo) {
                                $ramos_sobrantes += $item['valores_var'][$i]['sobra'];
                            } else {
                            }
                        }
                        $cajas_mixtas += intval($ramos_sobrantes / $long['param']->ramos_x_caja);
                    }
                    $total_cajas_mixtas += $cajas_mixtas;
                @endphp
                @foreach ($item['valores_var'] as $pos_l => $long)
                    @php
                        $total_cajas += $long['cajas'];
                    @endphp
                    <tr class="tr_var_{{ $item['variedad']->id_variedad }}"
                        onmouseover="$('.tr_var_{{ $item['variedad']->id_variedad }}').addClass('bg-yura_dark')"
                        onmouseleave="$('.tr_var_{{ $item['variedad']->id_variedad }}').removeClass('bg-yura_dark')">
                        @if ($pos_l == 0)
                            <th class="padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item['valores_var']) }}">
                                {{ $item['variedad']->nombre }}
                            </th>
                        @endif
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $long['longitud']->longitud }}cm
                            <sup>{{ $long['longitud']->id_clasificacion_ramo }}</sup>
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $long['longitud']->cantidad }}
                        </th>
                        <td class="padding_lateral_5" style="border-color: #9d9d9d">
                            <small>x</small>{{ $long['param']->ramos_x_caja }}
                        </td>
                        <th class="padding_lateral_5 bg-yura_dark">
                            {{ $long['cajas'] }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $long['sobra'] }}
                        </th>
                        @if ($pos_l == 0)
                            <th class="padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item['valores_var']) }}">
                                {{ $cajas_mixtas }}
                            </th>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tr>
            <th class="padding_lateral_5 th_yura_green" colspan="4">
                TOTALES
            </th>
            <th class="padding_lateral_5 th_yura_green">
                {{ number_format($total_cajas) }}
            </th>
            <th class="padding_lateral_5 th_yura_green">
            </th>
            <th class="padding_lateral_5 th_yura_green">
                {{ number_format($total_cajas_mixtas) }}
            </th>
        </tr>
    </table>
</div>
