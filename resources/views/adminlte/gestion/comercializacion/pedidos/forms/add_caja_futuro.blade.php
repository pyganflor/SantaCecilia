<div class="input-group div-compress" style="margin-bottom:5px">
    <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
        <i class="fa fa-fw fa-gift"></i> Nombre Caja
    </div>
    <input type="text" id="nombre_caja" class="form-control text-center input-yura_default">
</div>
<div style="max-height:430px; overflow:auto">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_armar_caja">
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
                <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_armar_caja()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="agregar_caja_futuro()">
        <i class="fa fa-fw fa-gift"></i> Agregar Caja <i class="fa fa-fw fa-arrow-right"></i>
    </button>
</div>

<select id="select_variedades" class="hidden">
    @foreach ($variedades as $var)
        <option value="{{ $var->id_variedad }}">{{ $var->nombre }}</option>
    @endforeach
</select>

<script>
    cant_armar_caja = 0;

    function agregar_caja_futuro() {
        ped = $('#pedido_selected').val();
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
            nombre_caja = $('#nombre_caja').val();
            datos = {
                _token: '{{ csrf_token() }}',
                id_pedido: ped,
                nombre_caja: nombre_caja,
                detalles: JSON.stringify(detalles),
            };
            post_jquery_m('{{ url('pedidos/agregar_caja_futuro') }}', datos, function() {
                cerrar_modals();
                editar_pedido(ped);
                listar_reporte();
            });
        }
    }
</script>
