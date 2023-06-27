@if(count($matriz) > 0)
    <table class="table-bordered table-striped" style="width: 100%; border: 2px solid #9d9d9d; font-size: 0.9em" id="table_costos">
        <thead>
        <tr id="tr_fijo_top_0">
            <th class="text-left" style="border-color: #9d9d9d; background-color: #e9ecef" colspan="{{count($semanas)*5 + 4}}">
                <span style="margin-left: 5px; position: sticky; left: 7px !important;">Costos {{$actividad != '' ? '"'.$actividad->nombre.'"' : ''}}</span>
            </th>
        </tr>
        <tr id="tr_fijo_top_1">
            <th class="text-left th_fijo_left_0" style="border-color: #9d9d9d; background-color: #e9ecef; z-index: 5 !important; width: 125px">
                <span style="margin-left: 5px; margin-right: 5px">SEMANAS</span>
            </th>
            @foreach($semanas as $sem)
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <span style="margin-left: 5px; margin-right: 5px">{{$sem->codigo_semana}}</span>
                </th>
            @endforeach
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <span style="margin-left: 5px; margin-right: 5px">Total</span>
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <span style="margin-left: 5px; margin-right: 5px">%</span>
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            </th>
        </tr>
        <tr id="tr_fijo_top_2" onclick="recalcular_acumulados()">
            <th class="text-left th_fijo_left_0" style="border-color: #9d9d9d; background-color: #e9ecef; z-index: 5 !important;">
                <span style="margin-left: 5px; margin-right: 5px">Totales</span>
            </th>
            @php
                $total = 0;
                $total_cantidad_horas_ordinarias = 0;
                $total_cantidad_horas_50 = 0;
                $total_cantidad_horas_100 = 0;
                $total_cantidad_personal = 0;
            @endphp
            @foreach($totales as $item)
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    @if ($criterio == 'V')
                        <span style="margin-left: 5px; margin-right: 5px">{{number_format($item->cant, 2)}}</span>
                    @endif
                    @if ($criterio == 'C')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($item->total_cantidad_horas_ordinarias, 2)}}</span>
                    @endif
                    @if ($criterio == 'E')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($item->total_cantidad_horas_50, 2)}}</span>
                    @endif
                    @if ($criterio == 'F')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($item->total_cantidad_horas_100, 2)}}</span>
                    @endif
                    @if ($criterio == 'P')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($item->total_cantidad_personal, 2)}}</span>
                    @endif
                    
                </th>
                @php
                    $total_cantidad_horas_ordinarias += round($item->total_cantidad_horas_ordinarias, 2);
                    $total_cantidad_horas_50 += round($item->total_cantidad_horas_50, 2);
                    $total_cantidad_horas_100 += round($item->total_cantidad_horas_100, 2);
                    $total_cantidad_personal += round($item->total_cantidad_personal, 2);
                    $total += round($item->cant, 2);
                @endphp
            @endforeach
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                @if ($criterio == 'V')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($total, 2)}}</span>
                @endif
                @if ($criterio == 'C')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($total_cantidad_horas_ordinarias, 2)}}</span>
                @endif
                @if ($criterio == 'E')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($total_cantidad_horas_50, 2)}}</span>
                @endif
                @if ($criterio == 'F')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($total_cantidad_horas_100, 2)}}</span>
                @endif
                @if ($criterio == 'P')
                    <span style="margin-left: 5px; margin-right: 5px">{{number_format($total_cantidad_personal, 2)}}</span>
                @endif
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef;">
                <span style="margin-left: 5px; margin-right: 5px">100%</span>
            </th>
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <span style="margin-left: 5px; margin-right: 5px">Acum.</span>
            </th>
        </tr>

        </thead>
        <tbody>
        @php
            $acumulado = 0;
        @endphp
        @foreach($matriz as $pos_act => $act)
            <tr onmouseover="$(this).css('background-color', '#77dbf9')" onmouseleave="$(this).css('background-color', '')"
                id="tr_act_mo_{{$pos_act}}">
                @php
                    $total_prod = 0;
                    $total_horas_ordinarias_x_mo = 0;
                    $total_horas_50_x_mo = 0;
                    $total_horas_100_x_mo = 0;
                    $total_personal_x_mo = 0;
                @endphp
                @foreach($act as $pos_item => $item)
                    @if($pos_item == 0)
                        <td class="text-left th_fijo_left_0" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <div style="width: 200px; margin-left: 5px">
                                {{$item->actividad_mano_obra->mano_obra->nombre}}
                                @if(count($act) < count($semanas))
                                    <button type="button" class="btn btn-xs btn-yura_dark pull-right"
                                            onclick="corregir_costos_mano_obra('{{$item->id_actividad_mano_obra}}', '{{$pos_act}}')">
                                        <i class="fa fa-fw fa-refresh"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                    <td class="text-center" style="border-color: #9d9d9d">
                        @if ($criterio == 'V')
                            <span>{{number_format($item->valor, 2)}}</span>
                        @endif
                        @if ($criterio == 'C')
                            <span>{{number_format($item->cantidad_horas, 2)}}</span>
                        @endif
                        @if ($criterio == 'E')
                            <span>{{number_format($item->cantidad_horas_50, 2)}}</span>
                        @endif
                        @if ($criterio == 'F')
                            <span>{{number_format($item->cantidad_horas_100, 2)}}</span>
                        @endif
                        @if ($criterio == 'P')
                            <span>{{number_format($item->cantidad_personal, 2)}}</span>
                        @endif
                    </td>
                    @php
                        $total_prod += round($item->valor, 2);
                        $total_horas_ordinarias_x_mo += round($item->cantidad_horas, 2);
                        $total_horas_50_x_mo += round($item->cantidad_horas_50, 2);
                        $total_horas_100_x_mo += round($item->cantidad_horas_100, 2);
                        $total_personal_x_mo += round($item->cantidad_personal, 2);
                    @endphp
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    @if ($criterio == 'V')
                        <span>{{number_format($total_prod, 2)}}</span>
                    @endif
                    @if ($criterio == 'C')
                        <span>{{number_format($total_horas_ordinarias_x_mo, 2)}}</span>
                    @endif
                    @if ($criterio == 'E')
                        <span>{{number_format($total_horas_50_x_mo, 2)}}</span>
                    @endif
                    @if ($criterio == 'F')
                        <span>{{number_format($total_horas_100_x_mo, 2)}}</span>
                    @endif
                    @if ($criterio == 'P')
                        <span>{{number_format($total_personal_x_mo, 2)}}</span>
                    @endif
                </td>
                <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    @if ($criterio == 'V')
                        <span>
                            {{ $total != 0 ? round(($total_prod / $total) * 100, 2) : 0 }}
                        </span>
                        <input type="hidden" value="{{ $total != 0 ? round(($total_prod / $total) * 100, 2) : 0 }}" class="porcentaje_parcial">
                    @endif
                    @if ($criterio == 'C')
                        <span>
                            {{ $total_cantidad_horas_ordinarias != 0 ? round(($total_horas_ordinarias_x_mo / $total_cantidad_horas_ordinarias) * 100, 2) : 0 }}
                        </span>
                        <input type="hidden" value="{{ $total_cantidad_horas_ordinarias != 0 ? round(($total_horas_ordinarias_x_mo / $total_cantidad_horas_ordinarias) * 100, 2) : 0 }}" class="porcentaje_parcial">
                    @endif
                    @if ($criterio == 'E')
                        <span>
                            {{ $total_cantidad_horas_50 != 0 ? round(($total_horas_50_x_mo / $total_cantidad_horas_50) * 100, 2) : 0 }}
                        </span>
                        <input type="hidden" value="{{ $total_cantidad_horas_50 != 0 ? round(($total_horas_50_x_mo / $total_cantidad_horas_50) * 100, 2) : 0 }}" class="porcentaje_parcial">
                    @endif
                    @if ($criterio == 'F')
                        <span>
                            {{ $total_cantidad_horas_100 != 0 ? round(($total_horas_100_x_mo / $total_cantidad_horas_100) * 100, 2) : 0 }}
                        </span>
                        <input type="hidden" value="{{ $total_cantidad_horas_100 != 0 ? round(($total_horas_100_x_mo / $total_cantidad_horas_100) * 100, 2) : 0 }}" class="porcentaje_parcial">
                    @endif
                    @if ($criterio == 'P')
                        <span>
                            {{ $total_cantidad_personal != 0 ? round(($total_personal_x_mo / $total_cantidad_personal) * 100, 2) : 0 }}
                        </span>
                        <input type="hidden" value="{{ $total_cantidad_personal != 0 ? round(($total_personal_x_mo / $total_cantidad_personal) * 100, 2) : 0 }}" class="porcentaje_parcial">
                    @endif
                </td>
                @php
                if ($criterio == 'V') {
                    if ($total != 0) {
                        $acumulado += ($total_prod / $total) * 100;
                    }
                }
                if ($criterio == 'C') {
                    if ($total_cantidad_horas_ordinarias != 0) {
                        $acumulado += ($total_horas_ordinarias_x_mo / $total_cantidad_horas_ordinarias) * 100;
                    }
                }
                if ($criterio == 'E') {
                    if ($total_cantidad_horas_50 != 0) {
                        $acumulado += ($total_horas_50_x_mo / $total_cantidad_horas_50) * 100;
                    }
                }

                if ($criterio == 'F') {
                    if ($total_cantidad_horas_100 != 0) {
                        $acumulado += ($total_horas_100_x_mo / $total_cantidad_horas_100) * 100;
                    }
                }
                if ($criterio == 'P') {
                    if ($total_cantidad_personal != 0) {
                        $acumulado += ($total_personal_x_mo / $total_cantidad_personal) * 100;
                    }
                }
                @endphp
                <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <span class="acumulado_parcial">{{round($acumulado, 2)}}</span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <script>
        $('#table_costos_wrapper .row:first').hide();

        estructura_tabla('table_costos', false, false);

        function recalcular_acumulados() {
            list_porc_parcial = $('.porcentaje_parcial');
            list_acum_parcial = $('.acumulado_parcial');
            acum = 0;
            for (i = 0; i < list_porc_parcial.length; i++) {
                porc_parcial = list_porc_parcial[i];
                acum += parseFloat(porc_parcial.value);
                list_acum_parcial[i].innerHTML = acum > 100 ? 100 : Math.round(acum * 100) / 100;
            }
        }

        function corregir_costos_mano_obra(act_mo, pos) {
            datos = {
                _token: '{{csrf_token()}}',
                act_mo: act_mo,
                desde: $('#desde').val(),
                hasta: $('#hasta').val(),
            };
            $('#tr_act_mo_' + pos).LoadingOverlay('show');
            $.post('{{url('reporte_mano_obra/corregir_costos_mano_obra')}}', datos, function (retorno) {
                if (retorno.success) {
                } else
                    alerta(retorno.mensaje);
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_act_mo_' + pos).LoadingOverlay('hide');
            });
        }
    </script>

    <style>
        .th_fijo_left_0 {
            position: sticky;
            left: 0;
            z-index: 1;
        }

        #tr_fijo_top_0 .th_fijo_left_0 {
            position: sticky;
            top: 20px;
        }

        #tr_fijo_top_0 th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #tr_fijo_top_1 th {
            position: sticky;
            top: 20px;
        }

        #tr_fijo_top_2 th {
            position: sticky;
            top: 40px;
            z-index: 2;
        }
    </style>
@else
    <div class="well text-center">No hay registros que mostrar</div>
@endif