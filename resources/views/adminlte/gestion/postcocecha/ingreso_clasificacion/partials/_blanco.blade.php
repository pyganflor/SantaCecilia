<div style="overflow-y: scroll; max-height: 450px">
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="th_yura_green" style="padding-left: 5px">
                Plantas
            </th>
            <th class="text-center th_yura_green" style="width: 10%">
                Flor Nacional
            </th>
            <th class="text-center th_yura_green" style="width: 10%">
                Cuarto Frío
            </th>
            <th class="text-center th_yura_green" style="width: 10%">
                Opciones
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr>
                <td class="bg-yura_dark" style="padding-left: 5px">
                    {{ $item['planta']->nombre }}
                    <input type="hidden" id="count_form_{{ $item['planta']->id_planta }}" value="0">
                    <select id="select_variedades_{{ $item['planta']->id_planta }}" class="hidden">
                        <option value="">Seleccione</option>
                        @foreach ($item['variedades'] as $v)
                            <option value="{{ $v->id_variedad }}">{{ $v->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center bg-yura_dark" style="width: 10%">
                    <button type="button" class="btn btn-xs btn-yura_default btn-block"
                        id="btn_nacional_planta_{{ $item['planta']->id_planta }}" title="Ver Flor Nacional"
                        onclick="flor_nacional('{{ $item['planta']->id_planta }}')"
                        onmouseover="$('#icon_nacional_planta_{{ $item['planta']->id_planta }}').removeClass('hidden')"
                        onmouseleave="$('#icon_nacional_planta_{{ $item['planta']->id_planta }}').addClass('hidden')">
                        {{ number_format($item['nacional']) }}
                        <i class="fa fa-fw fa-eye hidden"
                            id="icon_nacional_planta_{{ $item['planta']->id_planta }}"></i>
                    </button>
                </td>
                <td class="text-center bg-yura_dark" style="width: 10%">
                    <button type="button" class="btn btn-xs btn-yura_default btn-block"
                        id="btn_inventario_planta_{{ $item['planta']->id_planta }}" title="Ver Inventario"
                        onclick="inventario_frio('{{ $item['planta']->id_planta }}')"
                        onmouseover="$('#icon_inventario_planta_{{ $item['planta']->id_planta }}').removeClass('hidden')"
                        onmouseleave="$('#icon_inventario_planta_{{ $item['planta']->id_planta }}').addClass('hidden')">
                        {{ number_format($item['inventario']) }}
                        <i class="fa fa-fw fa-eye hidden"
                            id="icon_inventario_planta_{{ $item['planta']->id_planta }}"></i>
                    </button>
                </td>
                <td class="text-center bg-yura_dark" style="width: 10%">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="add_form_blanco('{{ $item['planta']->id_planta }}')">
                            <i class="fa fa-fw fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger hidden"
                            id="btn_delete_{{ $item['planta']->id_planta }}"
                            onclick="delete_form_blanco('{{ $item['planta']->id_planta }}')">
                            <i class="fa fa-fw fa-minus"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="border-color: #9d9d9d" colspan="4">
                    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
                        id="table_desglose_planta_{{ $item['planta']->id_planta }}"></table>
                    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
                        id="table_inventario_planta_{{ $item['planta']->id_planta }}"></table>
                </td>
            </tr>
            <tr id="tr_save_all_{{ $item['planta']->id_planta }}" class="hidden">
                <td class="text-center" colspan="3" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_primary"
                        onclick="store_all_blanco('{{ $item['planta']->id_planta }}')">
                        <i class="fa fa-fw fa-save"></i> Grabar Todo
                    </button>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<select id="select_clasificacion_ramos" class="hidden">
    <option value="">Longitud del ramo</option>
    @foreach ($clasificaciones_ramos as $r)
        <option value="{{ $r->id_clasificacion_ramo }}">{{ $r->nombre }}</option>
    @endforeach
</select>

<select id="select_motivos_nacional" class="hidden">
    <option value="">Ningun Motivo</option>
    @foreach ($motivos_nacional as $item)
        <option value="{{ $item->id_motivos_nacional }}">{{ $item->nombre }}</option>
    @endforeach
</select>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>

<script>
    function add_form_blanco(pta) {
        $('#tr_save_all_' + pta).removeClass('hidden');
        count_form = $('#count_form_' + pta).val();
        count_form++;
        select_clasificacion_ramos = $('#select_clasificacion_ramos');
        select_motivos_nacional = $('#select_motivos_nacional');
        select_variedades = $('#select_variedades_' + pta).html();
        $('#table_inventario_planta_' + pta).addClass('hidden');
        $('#table_desglose_planta_' + pta).removeClass('hidden');
        $('#table_desglose_planta_' + pta).append('<tr id="tr_desglose_planta_' + pta + '_' + count_form + '">' +
            '<td style="border-color: #9d9d9d">' +
            '<select id="new_variedad_' + pta + '_' + count_form + '" style="width: 100%" ' +
            'onchange="buscar_inventario(' + pta + '); ' +
            'buscar_modulos(' + pta + ',' + count_form + ')">' +
            select_variedades +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d">' +
            '<select id="new_modulo_' + pta + '_' + count_form + '" style="width: 100%">' +
            '<option value=>Bloque</option>' +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d">' +
            '<select id="new_clasificacion_ramo_' + pta + '_' + count_form + '" style="width: 100%" ' +
            'onchange="buscar_inventario(' + pta + ',' + count_form + ')">' +
            select_clasificacion_ramos.html() +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d">' +
            '<select id="new_motivos_nacional_' + pta + '_' + count_form + '" style="width: 100%">' +
            select_motivos_nacional.html() +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d; width: 60px">' +
            '<input type="number" id="new_tallos_ramo_' + pta + '_' + count_form +
            '" style="width: 100%" placeholder="Tallos x ramo" ' +
            ' class="text-center" min="0" value="25" ' +
            'onkeyup="buscar_inventario(' + pta + ',' + count_form + ')">' +
            '</td>' +
            '<td style="border-color: #9d9d9d; width: 90px">' +
            '<input type="number" id="new_inventario_' + pta + '_' + count_form +
            '" style="width: 100%; background-color: #e9ecef"' +
            ' placeholder="Inventario" class="text-center" readonly>' +
            '</td>' +
            '<td style="border-color: #9d9d9d; width: 90px">' +
            '<input type="number" id="new_cantidad_' + pta + '_' + count_form + '" style="width: 100%"' +
            ' placeholder="Armar" class="text-center" min="1">' +
            '</td>' +
            '<td style="border-color: #9d9d9d" class="text-center">' +
            '<div class="btn-group">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" title="Grabar" ' +
            'onclick="store_blanco(' + pta + ',' + count_form + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '<button type="button" class="btn btn-xs btn-yura_dark" title="Grabar y mostrar PDF de Etiquetas" ' +
            'onclick="store_blanco(' + pta + ',' + count_form + '); ver_pdf_etiquetas(' + pta + ',' + count_form +
            ')">' +
            '<i class="fa fa-fw fa-file-pdf-o"></i>' +
            '</button>' +
            '</div>' +
            '</td>' +
            '</tr>');
        $('#count_form_' + pta).val(count_form);
        $('#btn_delete_' + pta).removeClass('hidden');
    }

    function buscar_modulos(pta, pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            planta: pta,
            variedad: $('#new_variedad_' + pta + '_' + pos).val(),
            finca: $('#new_finca_destino_' + pta + '_' + pos).val(),
        };
        if (datos['variedad'] != '' && datos['finca'] != '') {
            $.post('{{ url('ingreso_clasificacion/buscar_modulos') }}', datos, function(retorno) {
                $('#new_modulo_' + pta + '_' + pos).html(retorno.options);
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            });
        }
    }

    function delete_form_blanco(pta) {
        $('#table_inventario_planta_' + pta).addClass('hidden');
        $('#table_desglose_planta_' + pta).removeClass('hidden');
        count_form = $('#count_form_' + pta).val();
        $('#tr_desglose_planta_' + pta + '_' + count_form).remove();
        count_form--;
        if (count_form == 0) {
            $('#btn_delete_' + pta).addClass('hidden');
            $('#tr_save_all_' + pta).addClass('hidden');
        }
        $('#count_form_' + pta).val(count_form);
    }

    function store_all_blanco(pta) {
        count_form = $('#count_form_' + pta).val();
        data = [];
        for (i = 1; i <= count_form; i++) {
            variedad = $('#new_variedad_' + pta + '_' + i).val();
            modulo = $('#new_modulo_' + pta + '_' + i).val();
            clasificacion_ramo = $('#new_clasificacion_ramo_' + pta + '_' + i).val();
            tallos_x_ramo = $('#new_tallos_x_ramo_' + pta + '_' + i).val();
            cantidad = $('#new_cantidad_' + pta + '_' + i).val();
            if (variedad != '' && modulo != '' && clasificacion_ramo != '' && tallos_x_ramo != '' && cantidad != '') {
                data.push({
                    variedad: variedad,
                    modulo: modulo,
                    clasificacion_ramo: $('#new_clasificacion_ramo_' + pta + '_' + i).val(),
                    motivo: $('#new_motivos_nacional_' + pta + '_' + i).val(),
                    tallos_x_ramo: $('#new_tallos_ramo_' + pta + '_' + i).val(),
                    cantidad: $('#new_cantidad_' + pta + '_' + i).val(),
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#fecha_blanco_filtro').val(),
                data: JSON.stringify(data),
            }
            $.LoadingOverlay('show');
            $.post('{{ url('ingreso_clasificacion/store_all_blanco') }}', datos, function(retorno) {
                window.open('{{ url('ingreso_clasificacion/ver_all_pdf_etiquetas') }}?data=' + retorno.data,
                    '_blank');
                listar_blanco();
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function() {
                $.LoadingOverlay('hide');
            });
        } else {
            alerta('<div class="alert alert-warning text-center">Faltan datos necesarios</div>');
        }
    }

    function store_blanco(pta, count_form) {
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#fecha_blanco_filtro').val(),
            variedad: $('#new_variedad_' + pta + '_' + count_form).val(),
            modulo: $('#new_modulo_' + pta + '_' + count_form).val(),
            clasificacion_ramo: $('#new_clasificacion_ramo_' + pta + '_' + count_form).val(),
            motivo: $('#new_motivos_nacional_' + pta + '_' + count_form).val(),
            tallos_x_ramo: $('#new_tallos_ramo_' + pta + '_' + count_form).val(),
            cantidad: $('#new_cantidad_' + pta + '_' + count_form).val(),
        };
        if (datos['variedad'] != '' && datos['clasificacion_ramo'] != '' && datos['tallos_x_ramo'] > 0 &&
            datos['cantidad'] > 0 && datos['modulo'] != '') {
            post_jquery_m('{{ url('ingreso_clasificacion/store_blanco') }}', datos, function() {
                buscar_inventario(pta, count_form);
                $('#new_cantidad_' + pta + '_' + count_form).val('');
            }, 'tr_desglose_planta_' + pta + '_' + count_form);
        } else {
            alerta('<div class="alert alert-warning text-center">Faltan datos necesarios</div>');
        }
    }

    function buscar_inventario(pta, count_form) {
        datos = {
            _token: '{{ csrf_token() }}',
            planta: pta,
            variedad: $('#new_variedad_' + pta + '_' + count_form).val(),
            modulo: $('#new_modulo_' + pta + '_' + count_form).val(),
            clasificacion_ramo: $('#new_clasificacion_ramo_' + pta + '_' + count_form).val(),
            tallos_x_ramo: $('#new_tallos_ramo_' + pta + '_' + count_form).val(),
            finca_destino: $('#new_finca_destino_' + pta + '_' + count_form).val(),
        };
        if (datos['clasificacion_ramo'] != '' && datos['tallos_x_ramo'] > 0 && datos[
                'finca_destino'] != '') {
            $.post('{{ url('ingreso_clasificacion/buscar_inventario') }}', datos, function(retorno) {
                $('#new_inventario_' + pta + '_' + count_form).val(retorno.variedad);
                $('#btn_inventario_planta_' + pta).html(retorno.planta);
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            });
        }
    }

    function inventario_frio(pta) {
        $('#table_desglose_planta_' + pta).addClass('hidden');
        $('#table_inventario_planta_' + pta).removeClass('hidden');
        datos = {
            planta: pta,
            longitud: $('#longitud_blanco_filtro').val(),
        };
        get_jquery('{{ url('ingreso_clasificacion/inventario_frio') }}', datos, function(retorno) {
            $('#table_inventario_planta_' + pta).html(retorno);
        });
    }

    function flor_nacional(pta) {
        $('#table_desglose_planta_' + pta).addClass('hidden');
        $('#table_inventario_planta_' + pta).removeClass('hidden');
        datos = {
            planta: pta,
        };
        get_jquery('{{ url('ingreso_clasificacion/flor_nacional') }}', datos, function(retorno) {
            $('#table_inventario_planta_' + pta).html(retorno);
        });
    }

    function update_inventario(id, pta, tipo_inv = 'F') {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>MODIFICAR</b> el inventario?</div>";

        modal_quest('modal_update_inventario', texto, 'MODIFICAR INVENTARIO', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                disponibles: $('#edit_disponibles_' + id).val(),
                tallos_x_ramo: $('#edit_tallos_x_ramo_' + id).val(),
            };
            post_jquery_m('{{ url('ingreso_clasificacion/update_inventario') }}', datos, function() {
                if (tipo_inv == 'F')
                    inventario_frio(pta);
                if (tipo_inv == 'N')
                    flor_nacional(pta);
            });
        })
    }

    function botar_inventario(id, pta, tipo_inv = 'F') {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>DAR DE BAJA</b> a la flor?</div>";

        modal_quest('modal_delete_inventario', texto, 'FLOR DE BAJA', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
            };
            post_jquery_m('{{ url('ingreso_clasificacion/botar_inventario') }}', datos, function() {
                if (tipo_inv == 'F')
                    inventario_frio(pta);
                if (tipo_inv == 'N')
                    flor_nacional(pta);
            });
        })
    }

    function delete_inventario(id, pta, tipo_inv = 'F') {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ELIMINAR el REGISTRO</b> del inventario?</div>";

        modal_quest('modal_delete_inventario', texto, 'Eliminar INVENTARIO', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
            }
            post_jquery_m('{{ url('ingreso_clasificacion/delete_inventario') }}', datos, function() {
                cerrar_modals();
                if (tipo_inv == 'F')
                    inventario_frio(pta);
                if (tipo_inv == 'N')
                    flor_nacional(pta);
            })
        })
    }

    function ver_pdf_etiquetas(pta, count_form) {
        $.LoadingOverlay('show');
        fecha = $('#fecha_blanco_filtro').val();
        variedad = $('#new_variedad_' + pta + '_' + count_form).val();
        modulo = $('#new_modulo_' + pta + '_' + count_form).val();
        clasificacion_ramo = $('#new_clasificacion_ramo_' + pta + '_' + count_form).val();
        tallos_x_ramo = $('#new_tallos_ramo_' + pta + '_' + count_form).val();
        finca_destino = $('#new_finca_destino_' + pta + '_' + count_form).val();
        cantidad = $('#new_cantidad_' + pta + '_' + count_form).val();

        if (variedad != '' && tallos_x_ramo > 0 && clasificacion_ramo != '' && cantidad > 0)
            window.open('{{ url('ingreso_clasificacion/ver_pdf_etiquetas') }}?variedad=' + variedad +
                '&modulo=' + modulo +
                '&clasificacion_ramo=' + clasificacion_ramo +
                '&tallos_x_ramo=' + tallos_x_ramo +
                '&finca_destino=' + finca_destino +
                '&fecha=' + fecha +
                '&cantidad=' + cantidad, '_blank');
        $.LoadingOverlay('hide');
    }

    function view_pdf_inventario(inventario) {
        $.LoadingOverlay('show');
        cantidad = $('#edit_disponibles_' + inventario).val();

        if (cantidad > 0)
            window.open('{{ url('ingreso_clasificacion/view_pdf_inventario') }}?inventario=' + inventario +
                '&cantidad=' + cantidad, '_blank');
        $.LoadingOverlay('hide');
    }
</script>
