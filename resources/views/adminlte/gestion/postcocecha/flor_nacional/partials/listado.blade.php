<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
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
                Total Cosechado
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px" rowspan="2">
                % Nacional
            </th>
        </tr>
        <tr>
            @foreach ($motivos_nacional as $item)
                <th class="padding_lateral_5 bg-yura_dark" style="width: 160px">
                    <div style="width: 80px">
                        {{ $item->nombre }}
                    </div>
                </th>
            @endforeach
        </tr>
        @php
            $total_nacional = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            <tr>
                <th class="padding_lateral_5" style="border-color: #9d9d9d; background-color: #eeeeee">
                    {{ $item['variedad']->nombre }}
                </th>
                @foreach ($item['valores'] as $pos_val => $val)
                    @php
                        $total_nacional += $val;
                    @endphp
                    <td style="border-color: #9d9d9d">
                        <input type="number" class="text-center" value="{{ $val }}" style="width: 100%"
                            id="tallos_{{ $item['variedad']->id_variedad }}_{{ $motivos_nacional[$pos_val]->id_motivos_nacional }}"
                            onchange="store_flor_nacional('{{ $item['variedad']->id_variedad }}', '{{ $motivos_nacional[$pos_val]->id_motivos_nacional }}')">
                    </td>
                @endforeach
                <th style="border-color: #9d9d9d">
                    <input type="text" class="text-center" readonly value="{{ number_format($total_nacional) }}"
                        style="width: 100%; background-color: #eeeeee">
                </th>
                <th style="border-color: #9d9d9d">
                    <input type="text" class="text-center" readonly value="{{ number_format($item['cosechados']) }}"
                        style="width: 100%; background-color: #eeeeee">
                </th>
                <th style="border-color: #9d9d9d">
                    <input type="text" class="text-center" readonly
                        value="{{ porcentaje($total_nacional, $total_nacional + $item['cosechados'], 1) }}"
                        style="width: 100%; background-color: #eeeeee">
                </th>
            </tr>
        @endforeach
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

    }
</script>
