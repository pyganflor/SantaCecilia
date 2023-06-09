<input type="hidden" id="modulo_selected" value="{{ $modulo }}">
<div style="overflow-y: scroll; max-height: 550px">
    <table class="table-bordered" style="width: 100%; border: 1px solid">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                <input type="checkbox" id="check_all_camas" class="mouse-hand"
                    onchange="$('.check_camas').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green">
                Cama
            </th>
            <th class="text-center th_yura_green">
                Area
            </th>
            <th class="text-center th_yura_green">
                Cuadros
            </th>
            <th class="text-center th_yura_green">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_dark"
                        onclick="listar_camas('{{ $modulo }}')" title="Volver a cargar las camas">
                        <i class="fa fa-fw fa-refresh"></i>
                    </button>
                </div>
            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d" colspan="3">
                <input type="number" id="new_num_camas" style="width: 100%; background-color: #dddddd"
                    class="text-center" placeholder="Agregar # Camas">
            </td>
            <td class="text-center" style="border-color: #9d9d9d" colspan="2">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="add_camas()"
                        title="Agregar camas">
                        <i class="fa fa-fw fa-plus"></i> Agregar Filas
                    </button>
                </div>
            </td>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" class="check_camas edit_check_camas mouse-hand"
                        id="edit_check_cama_{{ $pos + 1 }}" value="{{ $pos + 1 }}">
                    <input type="hidden" id="edit_id_cama_{{ $pos + 1 }}" value="{{ $item->id_cama }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="edit_nombre_cama_{{ $pos + 1 }}" style="width: 100%"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}" placeholder="Nombre"
                        value="{{ $item->nombre }}" readonly>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="edit_area_cama_{{ $pos + 1 }}" style="width: 100%"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}" placeholder="Area"
                        value="{{ $item->area }}" onchange="ingresar_area_cama('{{ $pos + 1 }}', 'edit')"
                        onkeyup="ingresar_area_cama('{{ $pos + 1 }}', 'edit')">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="edit_cuadro_cama_{{ $pos + 1 }}" style="width: 100%"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}" placeholder="No. Cuadros"
                        value="{{ $item->cuadros }}" onchange="ingresar_cuadro_cama('{{ $pos + 1 }}', 'edit')"
                        onkeyup="ingresar_cuadro_cama('{{ $pos + 1 }}', 'edit')">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            title="{{ $item->estado == 1 ? 'DESACTIVAR' : 'ACTIVAR' }}"
                            onclick="cambiar_estado_cama('{{ $item->id_cama }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'unlock' : 'lock' }}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        <tbody id="table_camas">

        </tbody>
        <tr id="tr_grabar_camas" class="{{ count($listado) > 0 ? '' : 'hidden' }} tr_fija_bottom_0">
            <td class="text-center th_yura_green" colspan="5">
                <button type="button" class="btn btn-xs btn-block btn-yura_default" onclick="store_camas()"
                    title="Grabar camas">
                    <i class="fa fa-fw fa-save"></i> Grabar Camas
                </button>
            </td>
        </tr>
    </table>
</div>

<script>
    var num_new_camas = {{ count($listado) }};

    function add_camas() {
        num_camas = parseInt($('#new_num_camas').val());
        $('#new_num_camas').val('')
        if (num_camas > 0) {
            campo = "'new'";
            for (i = num_new_camas + 1; i <= num_new_camas + num_camas; i++) {
                $('#table_camas').append('<tr id="new_tr_cama_' + i + '">' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="checkbox" id="new_check_cama_' + i +
                    '" class="mouse-hand check_camas new_check_camas" checked value="' + i + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="text" id="new_nombre_cama_' + i +
                    '" style="width: 100%; background-color: #dddddd" readonly ' +
                    'class="text-center" placeholder="Nombre" value="' + i + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" id="new_area_cama_' + i +
                    '" style="width: 100%; background-color: #dddddd"' +
                    'class="text-center" placeholder="Area" onchange="ingresar_area_cama(' + i +
                    ', ' + campo + ')" onkeyup="ingresar_area_cama(' + i + ', ' + campo + ')">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" id="new_cuadro_cama_' + i +
                    '" style="width: 100%; background-color: #dddddd"' +
                    'class="text-center" placeholder="No. Cuadros" onchange="ingresar_cuadro_cama(' + i +
                    ', ' + campo + ')" onkeyup="ingresar_cuadro_cama(' + i + ', ' + campo + ')">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<button type="button" class="btn btn-xs btn-yura_danger" onclick="quitar_cama(' + i + ')"' +
                    'title="Quitar Cama">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</td>' +
                    '</tr>');
            }
            num_new_camas += num_camas;
            $('#tr_grabar_camas').removeClass('hidden')
        }
    }

    function quitar_cama(num) {
        $('#new_tr_cama_' + num).remove();
    }

    function ingresar_area_cama(num, campo) {
        area = parseInt($('#' + campo + '_area_cama_' + num).val());
        check_camas = $('.check_camas');
        for (x = 0; x < check_camas.length; x++) {
            id = check_camas[x].id;
            num_cama = check_camas[x].value;
            if ($('#' + id).prop('checked')) {
                $('#' + campo + '_area_cama_' + num_cama).val(area);
            }
        }
    }

    function ingresar_cuadro_cama(num, campo) {
        cuadro = parseInt($('#' + campo + '_cuadro_cama_' + num).val());
        check_camas = $('.check_camas');
        for (x = 0; x < check_camas.length; x++) {
            id = check_camas[x].id;
            num_cama = check_camas[x].value;
            if ($('#' + id).prop('checked')) {
                $('#' + campo + '_cuadro_cama_' + num_cama).val(cuadro);
            }
        }
    }

    function store_camas() {
        data_new = [];
        new_check_camas = $('.new_check_camas');
        for (x = 0; x < new_check_camas.length; x++) {
            num_cama = new_check_camas[x].value;
            nombre = $('#new_nombre_cama_' + num_cama).val();
            area = $('#new_area_cama_' + num_cama).val();
            cuadro = $('#new_cuadro_cama_' + num_cama).val();
            if (nombre != '') {
                data_new.push({
                    nombre: nombre,
                    area: area,
                    cuadro: cuadro,
                })
            }
        }

        data_edit = [];
        edit_check_camas = $('.edit_check_camas');
        for (x = 0; x < edit_check_camas.length; x++) {
            num_cama = edit_check_camas[x].value;
            id_cama = $('#edit_id_cama_' + num_cama).val();
            nombre = $('#edit_nombre_cama_' + num_cama).val();
            area = $('#edit_area_cama_' + num_cama).val();
            cuadro = $('#edit_cuadro_cama_' + num_cama).val();
            if (nombre != '') {
                data_edit.push({
                    id_cama: id_cama,
                    nombre: nombre,
                    area: area,
                    cuadro: cuadro,
                })
            }
        }

        if (data_new.length + data_edit.length > 0) {
            modal_quest('modal_quest-store_camas',
                '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>GRABAR</strong> las camas de este bloque?</div>',
                '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
                function() {
                    modulo = $('#modulo_selected').val();
                    datos = {
                        _token: '{{ csrf_token() }}',
                        modulo: modulo,
                        data_new: JSON.stringify(data_new),
                        data_edit: JSON.stringify(data_edit),
                    };
                    if (datos['nombre'] != '') {
                        cerrar_modals();
                        post_jquery_m('{{ url('mapeo_cultivo/store_camas') }}', datos, function() {
                            listar_camas(modulo);
                        });
                        listar_camas(modulo);
                    }
                });
        }
    }

    function cambiar_estado_cama(id, estado) {
        mensaje = estado == 1 ? 'DESACTIVAR' : 'ACTIVAR';
        modal_quest('modal_quest-cambiar_estado_cama',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>' + mensaje +
            '</strong> esta cama?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                modulo = $('#modulo_selected').val();
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                };
                cerrar_modals();
                post_jquery_m('{{ url('mapeo_cultivo/cambiar_estado_cama') }}', datos, function() {
                    listar_camas(modulo);
                });
            });
    }
</script>
