<table width="100%">
    <tr>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    Fecha de Entrega
                </div>
                <input type="date" id="add_fecha" required class="form-control text-center"
                    style="width: 100% !important;" value="{{ hoy() }}">
            </div>
        </td>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark">
                    Cliente
                </div>
                <select id="add_cliente" class="form-control" style="width: 100%" onchange="seleccionar_cliente()">
                    <option value="">Seleccione</option>
                    @foreach ($clientes as $c)
                        <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </td>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark">
                    Carguera
                </div>
                <select id="add_agencia" class="form-control input-yura_default input_seleccionar_cliente"
                    style="width: 100%">
                    <option value="">Seleccione</option>
                </select>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    Consignatario
                </div>
                <select id="add_consignatario" class="form-control input_seleccionar_cliente" style="width: 100%">
                    <option value="">Seleccione</option>
                </select>
            </div>
        </td>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark">
                    Marcacion
                </div>
                <input type="text" id="add_marcacion" required class="form-control text-center"
                    style="width: 100% !important;" maxlength="250">
            </div>
        </td>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark">
                    Tipo de pedido
                </div>
                <select id="add_tipo" required class="form-control" style="width: 100% !important;"
                    onchange="seleccionar_tipo_pedido()">
                    <option value="A">Inventario de Cajas Armadas</option>
                    <option value="F">Armar Manualmente</option>
                </select>
            </div>
        </td>
    </tr>
</table>

<div class="div_tipo_pedido" id="div_tipo_pedido_A">
    <table style="width:100%; margin-top: 5px">
        <tr>
            <td style="vertical-align: top; width: 50%; padding-right: 5px" id="td_inventarios">
                <div class="panel panel-success" style="margin-bottom: 0px" id="panel_inventarios">
                    <div class="panel-heading"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <div id="titulo_inventarios">
                            <b> <i class="fa fa-leaf"></i> INVENTARIO DISPONIBLE </b>
                        </div>
                        <div>
                            <div class="btn-group">
                                <button class="btn btn-xs btn-yura_default" onclick="modificar_div_inv('left')">
                                    <i class="fa fa-arrow-left"></i>
                                </button>
                                <button class="btn btn-xs btn-yura_primary" onclick="modificar_div_inv('center')">
                                    <i class="fa fa-compress"></i>
                                </button>
                                <button class="btn btn-xs btn-yura_default" onclick="modificar_div_inv('right')">
                                    <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body" id="body_inventarios" style="max-height: 500px">
                        <div class="input-group div-compress" style="margin-bottom:10px">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Busqueda
                            </div>
                            <input type="text" id="buscar_inventario"
                                class="form-control text-center input-yura_default" onkeyup="buscar_inventario()">
                        </div>
                        <div id="div_inventario" style="max-height:430px; overflow:auto">
                        </div>
                    </div>
                </div>
            </td>
            <td style="vertical-align: top; padding-left: 5px" id="td_seleccionados">
                <div class="panel panel-success" style="margin-bottom:0px" id="panel_seleccionados">
                    <div class="panel-heading"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <div id="titulo_seleccionados">
                            <b> <i class="fa fa-th"></i> CONTENIDO DEL PEDIDO</b>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                                data-toggle="dropdown" aria-expanded="false">
                                Acciones <span class="fa fa-caret-down"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right sombra_pequeña">
                                <li>
                                    <a href="javascript:void(0)" onclick="unificar_detalles()">
                                        <i class="fa fa-fw fa-gift"></i> Unificar detalles
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" onclick="quitar_detalles()">
                                        <i class="fa fa-fw fa-trash"></i> Quitar detalles
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-body" style="height: max-500px; overflow:auto" id="body_seleccionados">
                        <div id="droppable"
                            style="height: 100%; display:flex; align-items: center; justify-content: center"
                            class="ui-droppable">
                            <div style="color:silver; font-size:16px" id="mensaje-drop">
                                <b>AGREGUE LOS PRODUCTOS AL PEDIDO</b>
                            </div>
                        </div>
                        <div id="div_seleccionados" class="hidden" style="height: 100%; overflow: auto">
                            <table class="table-bordered" style="width: 100%; border:1px solid #9d9d9d"
                                id="table_seleccionados">
                                <tr class="tr_fija_top_0">
                                    <th class="text-center th_yura_green" style="width: 60px">
                                        <div style="width: 60px">
                                            Caja
                                        </div>
                                    </th>
                                    <th class="text-center th_yura_green">
                                        <input type="checkbox" id="check_all_precio" class="pull-left"
                                            style="margin-left: 5px"
                                            onchange="$('.check_all_precio').prop('checked', $(this).prop('checked'))">
                                        <label for="check_all_precio" class="mouse-hand">Variedad</label>
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green">
                                        Long.
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green" colspan="2">
                                        Tallos
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green" colspan="2">
                                        Ramos
                                    </th>
                                    <th class="text-center th_yura_green" style="width: 80px">
                                        Precio
                                        <input type="number" id="all_precio" style="width: 100%;"
                                            class="text-center th_yura_green" onchange="igualar_precio()"
                                            onkeyup="igualar_precio()">
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green" style="width: 80px">
                                        MARCACIONES
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="div_tipo_pedido hidden" id="div_tipo_pedido_F">
    <table style="width:100%; margin-top: 5px">
        <tr>
            <td style="vertical-align: top; width: 50%; padding-right: 5px">
                <div class="panel panel-success" style="margin-bottom: 0px">
                    <div class="panel-heading"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <b> <i class="fa fa-gift"></i> ARMAR CAJA </b>
                    </div>
                    <div class="panel-body" style="max-height: 500px">
                        <div class="input-group div-compress" style="margin-bottom:5px">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                <i class="fa fa-fw fa-gift"></i> Nombre Caja
                            </div>
                            <input type="text" id="nombre_caja"
                                class="form-control text-center input-yura_default">
                        </div>
                        <div style="max-height:430px; overflow:auto">
                            <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
                                id="table_armar_caja">
                                <tr class="tr_fija_top_0">
                                    <th class="text-center th_yura_green">
                                        Variedad
                                    </th>
                                    <th class="text-center th_yura_green" style="width: 60px">
                                        Longitud
                                    </th>
                                    <th class="text-center th_yura_green" style="width: 60px">
                                        TxR
                                    </th>
                                    <th class="text-center th_yura_green" style="width: 60px">
                                        Ramos
                                    </th>
                                    <th class="text-center th_yura_green" style="width: 60px">
                                        Precio
                                    </th>
                                    <th class="text-center th_yura_green" style="width: 30px">
                                        <button type="button" class="btn btn-xs btn-yura_dark"
                                            onclick="add_armar_caja()">
                                            <i class="fa fa-fw fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </table>
                        </div>
                        <div class="text-center" style="margin-top: 5px">
                            <button type="button" class="btn btn-yura_primary" onclick="agregar_caja_manual()">
                                <i class="fa fa-fw fa-gift"></i> Agregar Caja <i class="fa fa-fw fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </td>
            <td style="vertical-align: top; width: 50%; padding-right: 5px">
                <div class="panel panel-success" style="margin-bottom:0px" id="panel_seleccionados">
                    <div class="panel-heading"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <div id="titulo_seleccionados">
                            <b> <i class="fa fa-th"></i> CONTENIDO DEL PEDIDO</b>
                        </div>
                    </div>
                    <div class="panel-body" style="height: max-500px; overflow:auto">
                        <div id="droppable_manual"
                            style="height: 100%; display:flex; align-items: center; justify-content: center"
                            class="ui-droppable">
                            <div style="color:silver; font-size:16px" id="mensaje-drop">
                                <b>AGREGUE LOS PRODUCTOS AL PEDIDO</b>
                            </div>
                        </div>
                        <div id="div_seleccionados_manual" class="hidden" style="height: 100%; overflow: auto">
                            <table class="table-bordered" style="width: 100%; border:1px solid #9d9d9d"
                                id="table_seleccionados_manual">
                                <tr class="tr_fija_top_0">
                                    <th class="text-center th_yura_green" style="width: 60px">
                                        <div style="width: 60px">
                                            Caja
                                        </div>
                                    </th>
                                    <th class="text-center th_yura_green">
                                        Variedad
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green">
                                        Long.
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green">
                                        Tallos
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green">
                                        Ramos
                                    </th>
                                    <th class="text-center th_yura_green" style="width: 80px">
                                        Precio
                                    </th>
                                    <th class="text-center padding_lateral_5 th_yura_green" style="width: 80px">
                                        MARCACIONES
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>


<div class="text-center" style="margin-top: 5px">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_primary" onclick="store_pedido()">
            <i class="fa fa-fw fa-save"></i> Grabar Pedido
        </button>
        <button type="button" class="btn btn-yura_default" onclick="deshacer_pedido(); add_pedido()">
            <i class="fa fa-fw fa-refresh"></i> Reiniciar Formulario
        </button>
        <button type="button" class="btn btn-yura_warning" onclick="deshacer_pedido()">
            <i class="fa fa-fw fa-ban"></i> Cancelar
        </button>
    </div>
</div>

<select id="select_variedades" class="hidden">
    @foreach ($variedades as $var)
        <option value="{{ $var->id_variedad }}">{{ $var->nombre }}</option>
    @endforeach
</select>

<script>
    $('.bootstrap-dialog-footer').addClass('hidden');
    buscar_inventario();
    num_caja = 0;
    cant_armar_caja = 0;
    cant_seleccionados = 0;

    function check_all_selec() {
        $('.check_selec').prop('checked', $('#check_all_selec').prop('checked'));
    }

    function quitar_detalles() {
        for (i = 1; i <= cant_seleccionados; i++) {
            if ($('#check_selec_' + i).prop('checked') == true) {
                dependencias = $('#check_selec_' + i).val().split(',');
                for (x = 0; x < dependencias.length; x++) {
                    $('#tr_seleccionado_' + dependencias[x]).remove();
                }
            }
        }
    }

    function store_pedido() {
        tipo = $('#add_tipo').val();
        if (tipo == 'A') {
            ids_caja_selected = $('.ids_caja_selected');
            data_caja = [];
            for (i = 0; i < ids_caja_selected.length; i++) {
                id = ids_caja_selected[i].value;
                data_caja.push({
                    id: id,
                    marcacion_po: $('#marcacion_po_' + id).val(),
                });
            }
            ids_detalles_selected = $('.ids_detalles_selected');
            data_precio = [];
            for (i = 0; i < ids_detalles_selected.length; i++) {
                id = ids_detalles_selected[i].value;
                precio = $('#precio_' + id).val();
                data_precio.push({
                    id: id,
                    precio: precio,
                });
            }
            if (data_caja.length > 0 && $('#add_cliente').val() != '') {
                datos = {
                    _token: '{{ csrf_token() }}',
                    data_caja: JSON.stringify(data_caja),
                    data_precio: JSON.stringify(data_precio),
                    fecha: $('#add_fecha').val(),
                    cliente: $('#add_cliente').val(),
                    agencia: $('#add_agencia').val(),
                    consignatario: $('#add_consignatario').val(),
                    marcacion: $('#add_marcacion').val(),
                    tipo: tipo
                };
                post_jquery_m('{{ url('pedidos/store_pedido') }}', datos, function() {
                    $(window).off('beforeunload');
                    cerrar_modals();
                    listar_reporte();
                });
            }
        }
        if (tipo == 'F') {
            td_caja_manual = $('.td_caja_manual');
            data = [];
            for (i = 0; i < td_caja_manual.length; i++) {
                num = td_caja_manual[i].getAttribute("data-num");
                nombre_caja = $('#nombre_caja_' + num).val();
                if (nombre_caja != '') {
                    marcacion = $('#marcacion_manual_' + num).val();
                    detalles = [];
                    tr_manual = $('.tr_manual_' + num);
                    for (x = 0; x < tr_manual.length; x++) {
                        id_variedad = $('#id_variedad_manual_' + num + '_' + x).val();
                        longitud = $('#longitud_manual_' + num + '_' + x).val();
                        tallos_x_ramo = $('#tallos_x_ramo_manual_' + num + '_' + x).val();
                        ramos = $('#ramos_manual_' + num + '_' + x).val();
                        precio = $('#precio_manual_' + num + '_' + x).val();
                        detalles.push({
                            id_variedad: id_variedad,
                            longitud: longitud,
                            tallos_x_ramo: tallos_x_ramo,
                            ramos: ramos,
                            precio: precio,
                        });
                    }
                    data.push({
                        nombre_caja: nombre_caja,
                        marcacion: marcacion,
                        detalles: JSON.stringify(detalles)
                    })
                }
            }

            if (data.length > 0) {
                datos = {
                    _token: '{{ csrf_token() }}',
                    data: JSON.stringify(data),
                    fecha: $('#add_fecha').val(),
                    cliente: $('#add_cliente').val(),
                    agencia: $('#add_agencia').val(),
                    consignatario: $('#add_consignatario').val(),
                    marcacion: $('#add_marcacion').val(),
                    tipo: tipo
                };
                post_jquery_m('{{ url('pedidos/store_pedido') }}', datos, function() {
                    $(window).off('beforeunload');
                    cerrar_modals();
                    listar_reporte();
                });
            }
        }
    }

    function deshacer_pedido() {
        ids_caja_selected = $('.ids_caja_selected');
        data = [];
        for (i = 0; i < ids_caja_selected.length; i++) {
            id = ids_caja_selected[i].value;
            data.push(id);
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data)
        }
        post_jquery_m('{{ url('pedidos/deshacer_pedido') }}', datos, function() {
            $(window).off('beforeunload');
            cerrar_modals();
        })
    }

    function igualar_precio() {
        all = $('#all_precio').val();
        check_all_precio = $('.check_all_precio');
        for (i = 0; i < check_all_precio.length; i++) {
            id_det = check_all_precio[i].value;
            if ($('#' + check_all_precio[i].id).prop('checked') == true) {
                $('#precio_' + id_det).val(all);
            }
        }
    }

    function seleccionar_tipo_pedido() {
        tipo = $('#add_tipo').val();
        $('.div_tipo_pedido').addClass('hidden');
        $('#div_tipo_pedido_' + tipo).removeClass('hidden');
    }

    function agregar_caja_manual() {
        tr_armar_manual = $('.tr_armar_manual');
        detalles = [];
        for (i = 0; i < tr_armar_manual.length; i++) {
            id_tr = tr_armar_manual[i].id;
            num = $('#' + id_tr).data('num');
            variedad = $('#armar_variedad_' + num).val();
            nombre_variedad = $('#armar_variedad_' + num + ' option:selected').text();
            longitud = $('#armar_longitud_' + num).val();
            tallos_x_ramo = $('#armar_tallos_x_ramo_' + num).val();
            ramos = $('#armar_ramos_' + num).val();
            precio = $('#armar_precio_' + num).val();
            detalles.push({
                id_variedad: variedad,
                nombre_variedad: nombre_variedad,
                longitud: longitud,
                tallos_x_ramo: tallos_x_ramo,
                ramos: ramos,
                precio: precio,
            });
        }
        if (detalles.length > 0) {
            num_caja++;
            $('#droppable_manual').addClass('hidden');
            $('#div_seleccionados_manual').removeClass('hidden');
            nombre_caja = $('#nombre_caja').val();
            for (i = 0; i < detalles.length; i++) {
                td_caja = '';
                td_marcacion = '';
                if (i == 0) {
                    td_caja = '<td class="text-center td_caja_manual" style="border-color: #9d9d9d" rowspan="' +
                        detalles.length +
                        '" data-num="' + num_caja + '">' +
                        nombre_caja +
                        '<br><button class="btn btn-xs btn-yura_danger" onclick="delete_caja_manual(' + num_caja +
                        ')">' +
                        '<i class="fa fa-fw fa-trash"></i>' +
                        '</button>' +
                        '<input type="hidden" id="nombre_caja_' + num_caja + '" value="' + nombre_caja + '">' +
                        '</td>';
                    td_marcacion = '<td class="text-center" style="border-color: #9d9d9d" rowspan="' +
                        detalles.length +
                        '">' +
                        '<input type="text" class="text-center" style="width: 100%" id="marcacion_manual_' + num_caja +
                        '" placeholder="PO">' +
                        '</td>';
                }
                nombre_variedad = detalles[i]["nombre_variedad"];
                id_variedad = detalles[i]["id_variedad"];
                longitud = detalles[i]["longitud"];
                tallos_x_ramo = detalles[i]["tallos_x_ramo"];
                ramos = detalles[i]["ramos"];
                precio = detalles[i]["precio"];
                $('#table_seleccionados_manual').append(
                    '<tr class="tr_manual_' + num_caja + '">' +
                    td_caja +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    nombre_variedad +
                    '<input type="hidden" id="id_variedad_manual_' + num_caja + '_' + i + '" value="' +
                    id_variedad +
                    '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    longitud + 'cm' +
                    '<input type="hidden" id="longitud_manual_' + num_caja + '_' + i + '" value="' + longitud +
                    '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    tallos_x_ramo +
                    '<input type="hidden" id="tallos_x_ramo_manual_' + num_caja + '_' + i + '" value="' +
                    tallos_x_ramo +
                    '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    ramos +
                    '<input type="hidden" id="ramos_manual_' + num_caja + '_' + i + '" value="' +
                    ramos +
                    '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    precio +
                    '<input type="hidden" id="precio_manual_' + num_caja + '_' + i + '" value="' +
                    precio +
                    '">' +
                    '</td>' +
                    td_marcacion +
                    '</tr>'
                );
            }
        }

        cant_armar_caja = 0;
        $('.tr_armar_manual').remove();
        $('#nombre_caja').val('');
    }

    function delete_caja_manual(num) {
        $('.tr_manual_' + num).remove();
    }

    $(window).on('beforeunload', function() {
        deshacer_pedido();
        return '¿Desea salir?';
    });
</script>
