<legend style="margin-bottom: 5px; font-size: 1.1em" class="text-center">
    "<b>{{ $caja->nombre }}</b>"
</legend>
<div style="max-height:430px; overflow:auto">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_agregar_detalle">
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
                <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_agregar_caja()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_detalle_caja()">
        <i class="fa fa-fw fa-gift"></i> Agregar a la Caja
    </button>
</div>

<select id="select_variedades" class="hidden">
    @foreach ($variedades as $var)
        <option value="{{ $var->id_variedad }}">{{ $var->nombre }}</option>
    @endforeach
</select>

<input type="hidden" id="caja_frio_selected" value="{{ $caja->id_caja_frio }}">

<script>
    cant_agregar_detalle = 0;

    function store_detalle_caja() {
        caja = $('#caja_frio_selected').val();
        tr_agregar_detalle = $('.tr_agregar_detalle');
        detalles = [];
        for (i = 0; i < tr_agregar_detalle.length; i++) {
            id_tr = tr_agregar_detalle[i].id;
            num = $('#' + id_tr).data('num');
            variedad = $('#agregar_variedad_' + num).val();
            nombre_variedad = $('#agregar_variedad_' + num + ' option:selected').text();
            longitud = $('#agregar_longitud_' + num).val();
            tallos_x_ramo = $('#agregar_tallos_x_ramo_' + num).val();
            ramos = $('#agregar_ramos_' + num).val();
            precio = $('#agregar_precio_' + num).val();
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
            datos = {
                _token: '{{ csrf_token() }}',
                id_caja: caja,
                detalles: JSON.stringify(detalles),
            };
            post_jquery_m('{{ url('pedidos/store_detalle_caja') }}', datos, function() {
                cerrar_modals();
                editar_pedido($('#pedido_selected').val());
                listar_reporte();
            });
        }
    }

    function delete_agregar_caja(p) {
        $('#tr_agregar_' + p).remove();
    }

    function add_agregar_caja() {
        cant_agregar_detalle++;
        select_variedades = $('#select_variedades');
        $('#table_agregar_detalle').append('<tr id="tr_agregar_' + cant_agregar_detalle +
            '" class="tr_agregar_detalle" data-num="' +
            cant_agregar_detalle + '">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select style="width: 100%; height: 26px" id="agregar_variedad_' + cant_agregar_detalle + '">' +
            select_variedades.html() +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="agregar_longitud_' +
            cant_agregar_detalle +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="agregar_tallos_x_ramo_' +
            cant_agregar_detalle +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="agregar_ramos_' +
            cant_agregar_detalle +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="agregar_precio_' +
            cant_agregar_detalle +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_agregar_caja(' +
            cant_agregar_detalle + ')">' +
            '<i class="fa fa-fw fa-trash">' +
            '</i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }
</script>
