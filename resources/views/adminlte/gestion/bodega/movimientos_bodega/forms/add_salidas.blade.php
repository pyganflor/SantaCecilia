<div style="overflow-x: scroll">
    <table style="width: 100%">
        <tr>
            <td class="text-center">
                <legend style="font-size: 1em; margin-bottom: 2px">
                    Listado de Productos
                </legend>
            </td>
            <td class="text-center">
                <legend style="font-size: 1em; margin-bottom: 2px">
                    Opciones
                </legend>
            </td>
            <td class="text-center">
                <div class="input-group" style="font-size: 1em; margin-bottom: 2px">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        Productos a Sacar
                    </span>
                    <input type="date" id="fecha_salida" style="width: 100%; height: 28px;" class="text-center"
                        value="{{ hoy() }}" max="{{ hoy() }}">
                </div>
            </td>
        </tr>
        <tr>
            <td style="min-width: 350px; width: 550px; vertical-align: top;">
                <div style="overflow-y: scroll; max-height: 600px;">
                    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
                        id="table_listado_add_salidas">
                        <thead>
                            <tr class="tr_fija_top_0">
                                <th class="text-center th_yura_green">
                                    CODIGO
                                </th>
                                <th class="text-center th_yura_green" style="width: 70% !important;">
                                    NOMBRE
                                </th>
                                <th class="text-center th_yura_green">
                                    INVENTARIO
                                </th>
                                <th class="text-center th_yura_green">
                                    UNIDADES
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listado as $item)
                                <tr>
                                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                        {{ $item->codigo }}
                                        <input type="hidden" id="codigo_{{ $item->id_producto }}"
                                            value="{{ $item->codigo }}">
                                        <input type="hidden" class="tr_productos" value="{{ $item->id_producto }}">
                                    </th>
                                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                        {{ $item->nombre }}
                                        <input type="hidden" id="nombre_{{ $item->id_producto }}"
                                            value="{{ $item->nombre }}">
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item->disponibles != floor($item->disponibles) ? number_format($item->disponibles, 2) : number_format($item->disponibles) }}
                                        <input type="hidden" id="disponibles_{{ $item->id_producto }}"
                                            value="{{ $item->disponibles }}">
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        <input type="number" style="width: 100%" class="text-center"
                                            id="unidades_{{ $item->id_producto }}" min="0"
                                            max="{{ $item->disponibles }}"
                                            onchange="validar_salidas('{{ $item->id_producto }}')"
                                            onkeyup="validar_salidas('{{ $item->id_producto }}')">
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </td>
            <td class="text-center padding_lateral_5" style="vertical-align: top">
                <button type="button" class="btn btn-block btn-yura_dark" onclick="agregar_productos()">
                    <i class="fa fa-fw fa-arrow-right"></i> Agregar
                </button>
                <button type="button" class="btn btn-block btn-yura_primary" onclick="store_salidas()">
                    <i class="fa fa-fw fa-save"></i> Grabar
                </button>
            </td>
            <td style="min-width: 350px; width: 550px; vertical-align: top; overflow-y: scroll">
                <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_salidas">
                    <tr class="tr_fija_top_0">
                        <th class="text-center bg-yura_dark">
                            CODIGO
                        </th>
                        <th class="text-center bg-yura_dark">
                            NOMBRE
                        </th>
                        <th class="text-center bg-yura_dark" style="width: 60px">
                            UNIDADES
                        </th>
                        <th class="text-center bg-yura_dark">
                            SECTOR
                        </th>
                        <th class="text-center bg-yura_dark">
                            BLOQUE
                        </th>
                        <th class="text-center bg-yura_dark">
                        </th>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<select class="hidden" id="select_sectores">
    <option value="">Seleccione</option>
    @foreach ($sectores as $item)
        <option value="{{ $item->id_sector }}">
            {{ $item->nombre }}
        </option>
    @endforeach
</select>

<script>
    estructura_tabla('table_listado_add_salidas');
    cant_prod_selected = 0;

    function agregar_productos() {
        select_sectores = $('#select_sectores').html();
        tr_productos = $('.tr_productos');
        for (i = 0; i < tr_productos.length; i++) {
            id_prod = tr_productos[i].value;
            unidades = $('#unidades_' + id_prod).val();
            if (unidades != '' && unidades > 0) {
                cant_prod_selected++;
                codigo = $('#codigo_' + id_prod).val();
                nombre = $('#nombre_' + id_prod).val();
                $('#table_add_salidas').append('<tr id="tr_add_ingreso_' + cant_prod_selected + '">' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    codigo +
                    '<input type="hidden" id="input_producto_selected_' + cant_prod_selected +
                    '" class="id_producto_seleccionado" value="' + id_prod +
                    '" data-pos_selected="' + cant_prod_selected + '">' +
                    '</th>' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    nombre +
                    '</th>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" style="width: 100%" class="text-center"' +
                    'id="unidades_seleccionado_' + cant_prod_selected + '" min="0" value="' + unidades + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<select style="width: 100%; height: 26px" onchange="seleccionar_sector(' + cant_prod_selected +
                    ')"' +
                    'id="sector_seleccionado_' + cant_prod_selected + '" min="0">' +
                    select_sectores +
                    '</select>' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<select style="width: 100%; height: 26px" ' +
                    'id="modulo_seleccionado_' + cant_prod_selected + '" min="0">' +
                    '<option value="">Seleccione</option' +
                    '</select>' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<button type="button" class="btn btn-xs btn-yura_danger" onclick="quitar_fila(' +
                    cant_prod_selected +
                    ')">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</td>' +
                    '</tr>');

            }
        }
    }

    function quitar_fila(id_prod) {
        $('#tr_add_ingreso_' + id_prod).remove();
    }

    function store_salidas() {
        id_producto_seleccionado = $('.id_producto_seleccionado');
        data = [];
        for (i = 0; i < id_producto_seleccionado.length; i++) {
            id_prod = id_producto_seleccionado[i].value;
            pos = $('#' + id_producto_seleccionado[i].id).attr('data-pos_selected');
            unidades = $('#unidades_seleccionado_' + pos).val();
            modulo = $('#modulo_seleccionado_' + pos).val();
            if (unidades > 0) {
                data.push({
                    id_prod: id_prod,
                    unidades: unidades,
                    modulo: modulo,
                })
            }
        }

        if (data.length > 0) {
            mensaje = {
                title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
                mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>GRABAR</b> las salidas?</div>',
            };
            modal_quest('modal_store_salidas', mensaje['mensaje'], mensaje['title'], true, false,
                '{{ isPC() ? '50%' : '' }}',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        fecha: $('#fecha_salida').val(),
                        data: JSON.stringify(data),
                    };
                    post_jquery_m('{{ url('movimientos_bodega/store_salidas') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                    });
                });
        }
    }

    function validar_salidas(id_prod) {
        disponibles = parseFloat($('#disponibles_' + id_prod).val());
        unidades = parseFloat($('#unidades_' + id_prod).val());
        $('#unidades_' + id_prod).removeClass('error');
        if (unidades > disponibles)
            $('#unidades_' + id_prod).addClass('error');
    }

    function seleccionar_sector(id_prod) {
        datos = {
            _token: '{{ csrf_token() }}',
            sector: $('#sector_seleccionado_' + id_prod).val(),
        }
        $('#tr_add_ingreso_' + id_prod).LoadingOverlay('show');
        $.post('{{ url('movimientos_bodega/seleccionar_sector') }}', datos, function(retorno) {
            $('#modulo_seleccionado_' + id_prod).html(retorno.modulos);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#tr_add_ingreso_' + id_prod).LoadingOverlay('hide');
        })
    }
</script>
