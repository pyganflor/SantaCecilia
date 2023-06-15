<div style="overflow-y: scroll; max-height: 600px; overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                <input type="checkbox"
                    onchange="$('.check_camas, .check_cuadros').prop('checked', $(this).prop('checked'))"
                    class="mouse-hand">
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Cama
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Cuadro
            </th>
            <th class="text-center th_yura_green">
                Variedad
                <select id="all_id_variedad" style="width: 100%; color: black; height: 26px;"
                    onchange="set_all_campo('variedad')">
                    <option value="">Seleccione</option>
                    @foreach ($variedades as $v)
                        <option value="{{ $v->id_variedad }}">{{ $v->nombre }}</option>
                    @endforeach
                </select>
            </th>
            <th class="text-center th_yura_green">
                Fecha Inicio
                <input type="date" id="all_fecha_inicio" style="width: 100%; color: black" class="text-center"
                    value="{{ hoy() }}" onchange="set_all_campo('fecha_inicio')" max="{{ hoy() }}" required>
            </th>
            <th class="text-center th_yura_green">
                Ptas Iniciales
                <input type="number" id="all_plantas_iniciales" style="width: 100%; color: black" class="text-center"
                    onchange="set_all_campo('plantas_iniciales')" onkeyup="set_all_campo('plantas_iniciales')" placeholder="25 000">
            </th>
            <th class="text-center th_yura_green">
                Conteo
                <input type="number" id="all_conteo" style="width: 100%; color: black" class="text-center"
                    onchange="set_all_campo('conteo')" onkeyup="set_all_campo('conteo')" placeholder="3.25">
            </th>
            <th class="text-center th_yura_green">
                Sem. Cosecha
                <input type="number" id="all_semana_cosecha" style="width: 100%; color: black" class="text-center"
                    onchange="set_all_campo('semana_cosecha')" onkeyup="set_all_campo('semana_cosecha')" placeholder="14">
            </th>
        </tr>
        @foreach ($camas as $pos_c => $c)
            @for ($i = 1; $i <= $c->cuadros; $i++)
                <tr>
                    @if ($i == 1)
                        <td class="text-center" style="border-color: #9d9d9d" rowspan="{{ $c->cuadros }}">
                            <input type="checkbox" class="check_camas mouse-hand" value="{{ $c->id_cama }}"
                                id="check_cama_{{ $c->id_cama }}"
                                onchange="$('.check_cuadros_{{ $c->id_cama }}').prop('checked', $(this).prop('checked'))">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d" rowspan="{{ $c->cuadros }}">
                            <label for="check_cama_{{ $c->id_cama }}" class="mouse-hand">
                                {{ $c->nombre }}
                            </label>
                        </td>
                    @endif
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="checkbox" class="check_cuadros_{{ $c->id_cama }} check_cuadros mouse-hand"
                            value="{{ $i }}" id="check_cuadro_{{ $i }}_{{ $c->id_cama }}">
                        <label for="check_cuadro_{{ $i }}_{{ $c->id_cama }}" class="mouse-hand">
                            {{ $i }}
                        </label>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select id="id_variedad_{{ $i }}_{{ $c->id_cama }}"
                            style="width: 100%; color: black; height: 26px;">
                            <option value="">Seleccione</option>
                            @foreach ($variedades as $v)
                                <option value="{{ $v->id_variedad }}">{{ $v->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="date" id="fecha_inicio_{{ $i }}_{{ $c->id_cama }}"
                            style="width: 100%; color: black" class="text-center" value="{{ hoy() }}"
                            max="{{ hoy() }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="plantas_iniciales_{{ $i }}_{{ $c->id_cama }}"
                            style="width: 100%; color: black" class="text-center">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="conteo_{{ $i }}_{{ $c->id_cama }}"
                            style="width: 100%; color: black" class="text-center">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="semana_cosecha_{{ $i }}_{{ $c->id_cama }}"
                            style="width: 100%; color: black" class="text-center">
                    </td>
                </tr>
            @endfor
        @endforeach
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary">
        <i class="fa fa-fw fa-save"></i> GRABAR CICLOS
    </button>
</div>

<script>
    function set_all_campo(campo) {
        if (campo == 'variedad') {
            valor = $('#all_id_variedad').val();
            check_camas = $('.check_camas');
            for (i = 0; i < check_camas.length; i++) {
                id_cama = check_camas[i].value;
                check_cuadros = $('.check_cuadros_' + id_cama);
                for (y = 0; y < check_cuadros.length; y++) {
                    cuadro = check_cuadros[y].value;
                    id = check_cuadros[y].id;
                    if ($('#' + id).prop('checked') == true) {
                        $('#id_variedad_' + cuadro + '_' + id_cama).val(valor);
                    }
                }
            }
        }
        if (campo == 'fecha_inicio') {
            valor = $('#all_fecha_inicio').val();
            check_camas = $('.check_camas');
            for (i = 0; i < check_camas.length; i++) {
                id_cama = check_camas[i].value;
                check_cuadros = $('.check_cuadros_' + id_cama);
                for (y = 0; y < check_cuadros.length; y++) {
                    cuadro = check_cuadros[y].value;
                    id = check_cuadros[y].id;
                    if ($('#' + id).prop('checked') == true) {
                        $('#fecha_inicio_' + cuadro + '_' + id_cama).val(valor);
                    }
                }
            }
        }
        if (campo == 'plantas_iniciales') {
            valor = $('#all_plantas_iniciales').val();
            check_camas = $('.check_camas');
            for (i = 0; i < check_camas.length; i++) {
                id_cama = check_camas[i].value;
                check_cuadros = $('.check_cuadros_' + id_cama);
                for (y = 0; y < check_cuadros.length; y++) {
                    cuadro = check_cuadros[y].value;
                    id = check_cuadros[y].id;
                    if ($('#' + id).prop('checked') == true) {
                        $('#plantas_iniciales_' + cuadro + '_' + id_cama).val(valor);
                    }
                }
            }
        }
        if (campo == 'conteo') {
            valor = $('#all_conteo').val();
            check_camas = $('.check_camas');
            for (i = 0; i < check_camas.length; i++) {
                id_cama = check_camas[i].value;
                check_cuadros = $('.check_cuadros_' + id_cama);
                for (y = 0; y < check_cuadros.length; y++) {
                    cuadro = check_cuadros[y].value;
                    id = check_cuadros[y].id;
                    if ($('#' + id).prop('checked') == true) {
                        $('#conteo_' + cuadro + '_' + id_cama).val(valor);
                    }
                }
            }
        }
        if (campo == 'semana_cosecha') {
            valor = $('#all_semana_cosecha').val();
            check_camas = $('.check_camas');
            for (i = 0; i < check_camas.length; i++) {
                id_cama = check_camas[i].value;
                check_cuadros = $('.check_cuadros_' + id_cama);
                for (y = 0; y < check_cuadros.length; y++) {
                    cuadro = check_cuadros[y].value;
                    id = check_cuadros[y].id;
                    if ($('#' + id).prop('checked') == true) {
                        $('#semana_cosecha_' + cuadro + '_' + id_cama).val(valor);
                    }
                }
            }
        }
    }
</script>
