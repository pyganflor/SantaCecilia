<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr class="tr_fija_top_0">
        <th class="text-center th_yura_green">
            <input type="checkbox" onchange="$('.check_camas, .check_cuadros').prop('checked', $(this).prop('checked'))"
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
                onchange="set_all_campo('plantas_iniciales')" onkeyup="set_all_campo('plantas_iniciales')"
                placeholder="25 000">
        </th>
        <th class="text-center th_yura_green">
            Conteo
            <input type="number" id="all_conteo" style="width: 100%; color: black" class="text-center"
                onchange="set_all_campo('conteo')" onkeyup="set_all_campo('conteo')" placeholder="3.25">
        </th>
        <th class="text-center th_yura_green hidden">
            Sem. Cosecha
            <input type="number" id="all_semana_cosecha" style="width: 100%; color: black" class="text-center"
                onchange="set_all_campo('semana_cosecha')" onkeyup="set_all_campo('semana_cosecha')" placeholder="14">
        </th>
        <th class="text-center th_yura_green" style="width: 100px">
            Opciones
        </th>
    </tr>
    @foreach ($camas as $pos_c => $c)
        @for ($i = 1; $i <= $c->cuadros; $i++)
            @php
                $ciclo = getCicloActivoByCamaCuadro($c->id_cama, $i);
            @endphp
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
                            <option value="{{ $v->id_variedad }}"
                                {{ $ciclo != '' && $ciclo->id_variedad == $v->id_variedad ? 'selected' : '' }}>
                                {{ $v->nombre }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="date" id="fecha_inicio_{{ $i }}_{{ $c->id_cama }}"
                        style="width: 100%; color: black" class="text-center"
                        value="{{ $ciclo != '' ? $ciclo->fecha_inicio : hoy() }}" max="{{ hoy() }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="plantas_iniciales_{{ $i }}_{{ $c->id_cama }}"
                        style="width: 100%; color: black" class="text-center"
                        value="{{ $ciclo != '' ? $ciclo->plantas_iniciales : '' }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="conteo_{{ $i }}_{{ $c->id_cama }}"
                        style="width: 100%; color: black" class="text-center"
                        value="{{ $ciclo != '' ? $ciclo->conteo : '' }}">
                </td>
                <td class="text-center hidden" style="border-color: #9d9d9d">
                    <input type="number" id="semana_cosecha_{{ $i }}_{{ $c->id_cama }}"
                        style="width: 100%; color: black" class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    @if ($ciclo != '')
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary" title="Editar"
                                onclick="update_ciclo('{{ $ciclo->id_ciclo_cama }}', '{{ $c->id_cama }}', '{{ $i }}')">
                                <i class="fa fa-fw fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Terminar"
                                onclick="terminar_ciclo('{{ $ciclo->id_ciclo_cama }}')">
                                <i class="fa fa-fw fa-ban"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar"
                                onclick="eliminar_ciclo('{{ $ciclo->id_ciclo_cama }}')">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    @endif
                </td>
            </tr>
        @endfor
    @endforeach
</table>

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

    function store_ciclos() {
        modal_quest('modal_quest-store_sector',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>CREAR</strong> estos ciclos?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                cerrar_modals();
                data = [];
                check_camas = $('.check_camas');
                for (i = 0; i < check_camas.length; i++) {
                    id_cama = check_camas[i].value;
                    check_cuadros = $('.check_cuadros_' + id_cama);
                    for (y = 0; y < check_cuadros.length; y++) {
                        cuadro = check_cuadros[y].value;
                        id_variedad = $('#id_variedad_' + cuadro + '_' + id_cama).val();
                        fecha_inicio = $('#fecha_inicio_' + cuadro + '_' + id_cama).val();
                        plantas_iniciales = $('#plantas_iniciales_' + cuadro + '_' + id_cama).val();
                        conteo = $('#conteo_' + cuadro + '_' + id_cama).val();
                        semana_cosecha = $('#semana_cosecha_' + cuadro + '_' + id_cama).val();
                        if (id_variedad != '' && fecha_inicio != '' && plantas_iniciales != '' && conteo != '')
                            data.push({
                                id_cama: id_cama,
                                cuadro: cuadro,
                                id_variedad: id_variedad,
                                fecha_inicio: fecha_inicio,
                                plantas_iniciales: plantas_iniciales,
                                conteo: conteo,
                                semana_cosecha: semana_cosecha,
                            })
                    }
                }
                if (data.length > 0) {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        data: JSON.stringify(data),
                    }
                    post_jquery_m('{{ url('ciclos/store_ciclos') }}', datos, function() {
                        seleccionar_modulo();
                    })
                } else {
                    alerta(
                        '<div class="alert alert-warning text-center" style="font-size: 16px">Faltan datos necesarios</div>'
                    )
                }
            });
    }

    function update_ciclo(ciclo, cama, cuadro) {
        modal_quest('modal_quest-store_sector',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>MODIFICAR</strong> este ciclo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                id_variedad = $('#id_variedad_' + cuadro + '_' + cama).val();
                fecha_inicio = $('#fecha_inicio_' + cuadro + '_' + cama).val();
                plantas_iniciales = $('#plantas_iniciales_' + cuadro + '_' + cama).val();
                conteo = $('#conteo_' + cuadro + '_' + cama).val();
                semana_cosecha = $('#semana_cosecha_' + cuadro + '_' + cama).val();
                cerrar_modals();
                if (id_variedad != '' && fecha_inicio != '' && plantas_iniciales != '' && conteo != '') {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        ciclo: ciclo,
                        id_variedad: id_variedad,
                        fecha_inicio: fecha_inicio,
                        plantas_iniciales: plantas_iniciales,
                        conteo: conteo,
                        semana_cosecha: semana_cosecha,
                    }
                    post_jquery_m('{{ url('ciclos/update_ciclo') }}', datos, function() {})
                } else {
                    alerta(
                        '<div class="alert alert-warning text-center" style="font-size: 16px">Faltan datos necesarios</div>'
                    )
                }
            });
    }

    function terminar_ciclo(ciclo) {
        modal_quest('modal_quest-store_sector',
            '<div class="alert alert-info text-center" style="font-size: 16px; margin-bottom: 5px">¿Desea <strong>TERMINAR</strong> este ciclo?</div>' +
            '<div class="input-group">' +
            '<div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">' +
            'Seleccione la fecha final del ciclo' +
            '</div>' +
            '<input type="date" value="{{ hoy() }}" max="{{ hoy() }}" id="fecha_fin" class="form-control input-yura_default" style="width: 100% !important;">' +
            '</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    ciclo: ciclo,
                    fecha_fin: $('#fecha_fin').val(),
                }
                post_jquery_m('{{ url('ciclos/terminar_ciclo') }}', datos, function() {
                    cerrar_modals();
                    seleccionar_modulo();
                })
            });
    }

    function eliminar_ciclo(ciclo) {
        modal_quest('modal_quest-store_sector',
            '<div class="alert alert-warning text-center" style="font-size: 16px; margin-bottom: 5px">¿Desea <strong>ELIMINAR</strong> este ciclo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    ciclo: ciclo,
                }
                post_jquery_m('{{ url('ciclos/eliminar_ciclo') }}', datos, function() {
                    cerrar_modals();
                    seleccionar_modulo();
                })
            });
    }
</script>
