<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="padding_lateral_5 th_yura_green col_fija_left_0" rowspan="2">
                <div style="width: 140px">
                    Variedad
                </div>
            </th>
            <th class="text-center th_yura_green" colspan="{{ count($motivos_nacional) }}">
                Motivos
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px" rowspan="2">
                Total Nacional
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px" rowspan="2">
                Total Exportable
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px" rowspan="2">
                Total Procesado
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px" rowspan="2">
                % Nacional
            </th>
        </tr>
        <tr>
            @php
                $totales_motivos = [];
            @endphp
            @foreach ($motivos_nacional as $item)
                <th class="padding_lateral_5 bg-yura_dark td_motivos" style="width: 160px"
                    data-id_motivo="{{ $item->id_motivos_nacional }}">
                    <div style="width: 80px">
                        {{ $item->nombre }}
                    </div>
                </th>
                @php
                    $totales_motivos[] = 0;
                @endphp
            @endforeach
        </tr>
        @php
            $total_nacional = 0;
            $total_exportable = 0;
            $total_procesado = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            <tr>
                <th class="padding_lateral_5 td_variedad col_fija_left_0"
                    style="border-color: #9d9d9d; background-color: #eeeeee"
                    data-id_variedad="{{ $item['variedad']->id_variedad }}">
                    {{ $item['variedad']->nombre }}
                </th>
                @php
                    $total_nacional_var = 0;
                @endphp
                @foreach ($item['valores'] as $pos_val => $val)
                    @php
                        $total_nacional_var += $val;
                        $totales_motivos[$pos_val] += $val;
                    @endphp
                    <td style="border-color: #9d9d9d">
                        <input type="number"
                            class="text-center tallos_motivo_{{ $motivos_nacional[$pos_val]->id_motivos_nacional }}"
                            value="{{ $val }}" style="width: 100%"
                            id="tallos_{{ $item['variedad']->id_variedad }}_{{ $motivos_nacional[$pos_val]->id_motivos_nacional }}"
                            onchange="store_flor_nacional('{{ $item['variedad']->id_variedad }}', '{{ $motivos_nacional[$pos_val]->id_motivos_nacional }}')">
                    </td>
                @endforeach
                <th style="border-color: #9d9d9d">
                    <input type="text" class="text-center" readonly value="{{ number_format($total_nacional_var) }}"
                        style="width: 100%; background-color: #eeeeee"
                        id="total_nacional_{{ $item['variedad']->id_variedad }}">
                </th>
                <th style="border-color: #9d9d9d">
                    <input type="text" class="text-center" readonly
                        value="{{ number_format($item['cosechados'] - $total_nacional_var) }}"
                        style="width: 100%; background-color: #eeeeee"
                        id="total_exportable_{{ $item['variedad']->id_variedad }}">
                </th>
                <th style="border-color: #9d9d9d">
                    <input type="text" class="text-center" readonly value="{{ number_format($item['cosechados']) }}"
                        style="width: 100%; background-color: #eeeeee"
                        id="total_procesado_{{ $item['variedad']->id_variedad }}">
                </th>
                <th style="border-color: #9d9d9d">
                    <input type="text" class="text-center" readonly
                        value="{{ porcentaje($total_nacional_var, $item['cosechados'], 1) }}"
                        style="width: 100%; background-color: #eeeeee"
                        id="porcentaje_nacional_{{ $item['variedad']->id_variedad }}">
                </th>
                @php
                    $total_nacional += $total_nacional_var;
                    $total_exportable += $item['cosechados'] - $total_nacional_var;
                    $total_procesado += $item['cosechados'];
                @endphp
            </tr>
        @endforeach
        @php
            $porcentaje_nacional = porcentaje($total_nacional, $total_procesado, 1);
        @endphp
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green col_fija_left_0">
                TOTALES
            </th>
            @foreach ($totales_motivos as $pos => $val)
                <th class="text-center bg-yura_dark">
                    <input type="text" class="text-center bg-yura_dark" readonly value="{{ $val }}"
                        style="width: 100%" id="total_motivo_{{ $motivos_nacional[$pos]->id_motivos_nacional }}">
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                <input type="text" class="text-center th_yura_green" readonly value="{{ $total_nacional }}"
                    style="width: 100%" id="total_nacional">
            </th>
            <th class="text-center th_yura_green">
                <input type="text" class="text-center th_yura_green" readonly value="{{ $total_exportable }}"
                    style="width: 100%" id="total_exportable">
            </th>
            <th class="text-center th_yura_green">
                <input type="text" class="text-center th_yura_green" readonly value="{{ $total_procesado }}"
                    style="width: 100%" id="total_procesado">
            </th>
            <th class="text-center th_yura_green">
                <input type="text" class="text-center th_yura_green" readonly value="{{ $porcentaje_nacional }}"
                    style="width: 100%" id="total_porcentaje">
            </th>
        </tr>
        <tr>
            <th class="text-center th_yura_green col_fija_left_0">
                %
            </th>
            @foreach ($totales_motivos as $pos => $val)
                @php
                    $valor = $total_nacional > 0 ? round(($val * $porcentaje_nacional) / $total_nacional, 2) : 0;
                @endphp
                <th class="text-center bg-yura_dark">
                    <input type="text" class="text-center bg-yura_dark" readonly value="{{ $valor }}"
                        style="width: 100%" id="total_motivo_{{ $motivos_nacional[$pos]->id_motivos_nacional }}">
                </th>
            @endforeach
        </tr>
    </table>
</div>

<style>
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"] {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

<script>
    function store_flor_nacional(variedad, motivo) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            motivo: motivo,
            tallos: $('#tallos_' + variedad + '_' + motivo).val(),
            fecha: $('#filtro_fecha').val(),
        }
        post_jquery_m('{{ url('flor_nacional/store_flor_nacional') }}', datos, function() {
            calcular_totales();
        }, 'tallos_' + variedad + '_' + motivo);
    }

    function calcular_totales() {
        td_variedad = $('.td_variedad');
        td_motivos = $('.td_motivos');
        for (i = 0; i < td_variedad.length; i++) {
            id_var = td_variedad[i].getAttribute('data-id_variedad');
            total_nacional_var = 0;
            for (x = 0; x < td_motivos.length; x++) {
                id_motivo = td_motivos[x].getAttribute('data-id_motivo');
                tallos = parseInt($('#tallos_' + id_var + '_' + id_motivo).val());
                if (tallos > 0)
                    total_nacional_var += tallos;
            }
            $('#total_nacional_' + id_var).val(total_nacional_var);
            total_procesado_var = parseInt($('#total_procesado_' + id_var).val());
            total_exportable = total_procesado_var - total_nacional_var;
            if (total_exportable > 0)
                $('#total_exportable_' + id_var).val(total_exportable);
            porcentaje_var = Math.round(((total_nacional_var / total_procesado_var) * 100) * 100) / 100;
            if (porcentaje_var > 0)
                $('#porcentaje_nacional_' + id_var).val(porcentaje_var);
        }

        total_nacional = 0;
        for (x = 0; x < td_motivos.length; x++) {
            id_motivo = td_motivos[x].getAttribute('data-id_motivo');
            tallos_motivo = $('.tallos_motivo_' + id_motivo);
            total_motivo = 0;
            for (y = 0; y < tallos_motivo.length; y++) {
                tallos = parseInt(tallos_motivo[y].value);
                if (tallos > 0) {
                    total_motivo += tallos;
                    total_nacional += tallos;
                }
            }
            $('#total_motivo_' + id_motivo).val(total_motivo);
        }
        $('#total_nacional').val(total_nacional);
        total_procesado = parseInt($('#total_procesado').val());
        total_exportable = total_procesado - total_nacional;
        if (total_exportable > 0)
            $('#total_exportable').val(total_exportable);
        porcentaje = Math.round(((total_nacional / total_procesado) * 100) * 100) / 100;
        $('#total_porcentaje').val(porcentaje);
    }
</script>
