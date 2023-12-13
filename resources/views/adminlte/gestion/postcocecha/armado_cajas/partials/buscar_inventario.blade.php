<div style="max-height: 700px; overflow:auto">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
            </th>
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
                Edad
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Disponibles
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Armar
            </th>
        </tr>
        @foreach ($listado as $item)
            @php
                $variedad = $item->variedad;
                $clasificacion = $item->clasificacion_ramo;
            @endphp
            <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')"
                class="tr_listado_inventario" data-id_inventario="{{ $item->id_inventario_frio }}"
                data-variedad="{{ $variedad->nombre }}" data-longitud="{{ $clasificacion->nombre }}"
                data-edad="{{ difFechas(hoy(), $item->fecha)->days }}" data-disponibles="{{ $item->disponibles }}"
                data-tallos_x_ramo="{{ $item->tallos_x_ramo }}" id="tr_inventario_{{ $item->id_inventario_frio }}">
                <td class="text-center" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_dark"
                        onclick="seleccionar_inventario('{{ $item->id_inventario_frio }}')">
                        <i class="fa fa-fw fa-arrow-left"></i>
                    </button>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $variedad->nombre }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $clasificacion->nombre }} <sup>cm</sup>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $item->tallos_x_ramo }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ difFechas(hoy(), $item->fecha)->days }}<sup>dias</sup>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $item->disponibles }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="cant_inventario_{{ $item->id_inventario_frio }}"
                        value="{{ $item->disponibles }}" min="0" max="{{ $item->disponibles }}"
                        style="width: 100%; color: black" class="text-center">
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function seleccionar_inventario(id_inv) {
        nombre_variedad = $('#tr_inventario_' + id_inv).data('variedad');
        longitud = $('#tr_inventario_' + id_inv).data('longitud');
        edad = $('#tr_inventario_' + id_inv).data('edad');
        tallos_x_ramo = $('#tr_inventario_' + id_inv).data('tallos_x_ramo');
        disponibles = $('#tr_inventario_' + id_inv).data('disponibles');
        ramos = $('#cant_inventario_' + id_inv).val();

        if ($('#new_id_inventario_frio_' + id_inv).length == 0)
            $('#table_caja').append('<tr id="new_tr_' + id_inv + '">' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                nombre_variedad +
                '<input type="hidden" value="' + id_inv + '" class="new_id_inventario_frio">' +
                '<input type="hidden" value="' + id_inv + '" id="new_id_inventario_frio_' + id_inv + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                longitud + ' <sup>cm</sup>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                tallos_x_ramo +
                '<input type="hidden" value="' + tallos_x_ramo + '" id="new_tallos_x_ramo_' + id_inv + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                edad + ' <sup>dias</sup>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="number" min="1" max="' + disponibles +
                '" style="width: 100%" class="text-center" id="new_ramos_' + id_inv +
                '" onchange="calcular_totales_caja()" value="' + ramos + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="number" disabled value="' + disponibles +
                '" style="width: 100%" class="text-center" id="new_disponibles_' + id_inv + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<button type="button" class="btn btn-xs btn-yura_danger" onclick="eliminar_fila_caja(' + id_inv +
                ')">' +
                '<i class="fa fa-fw fa-trash"></i>' +
                '</button>' +
                '</td>' +
                '</tr>');
        else
            alerta(
                '<div class="alert alert-warning text-center">Este <b>INVENTARIO</b> ya esta incluido en la caja</div>'
            );
        calcular_totales_caja();
    }
</script>
