<tr>
    <th class="text-center th_yura_green" style="border-color: white">
        Fecha
    </th>
    <th class="text-center th_yura_green" style="border-color: white">
        Variedad
    </th>
    <th class="text-center th_yura_green" style="border-color: white">
        Bloque
    </th>
    <th class="text-center th_yura_green" style="border-color: white">
        Longitud
    </th>
    <th class="text-center th_yura_green" style="border-color: white">
        Motivo Flor Nacional
    </th>
    <th class="text-center th_yura_green" style="border-color: white">
        Tallos x ramo
    </th>
    <th class="text-center th_yura_green" style="border-color: white">
        Edad
    </th>
    <th class="text-center th_yura_green" style="border-color: white">
        Disponibles
    </th>
    <th class="text-center th_yura_green" style="border-color: white; width: 110px">
    </th>
</tr>
@foreach ($listado as $item)
    @php
        $clasificacion_ramo = $item->clasificacion_ramo;
        $motivo_nacional = $item->motivo_nacional;
    @endphp
    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" readonly style="width: 100%; background-color: #e9ecef" class="text-center"
                value="{{ $item->fecha }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" readonly style="width: 100%; background-color: #e9ecef" class="text-center"
                value="{{ $item->variedad->nombre }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" readonly style="width: 100%; background-color: #e9ecef" class="text-center"
                value="{{ $item->modulo->sector->nombre }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" readonly style="width: 100%; background-color: #e9ecef" class="text-center"
                value="{{ $clasificacion_ramo->nombre }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" readonly style="width: 100%; background-color: #e9ecef" class="text-center"
                value="{{ isset($motivo_nacional) ? $motivo_nacional->nombre : '' }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" readonly style="width: 100%; background-color: #e9ecef" class="text-center"
                value="{{ $item->tallos_x_ramo }} tallos">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" readonly style="width: 100%; background-color: #e9ecef" class="text-center"
                value="{{ difFechas(hoy(), $item->fecha)->days }} días">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%" class="text-center"
                id="edit_disponibles_{{ $item->id_inventario_frio }}" value="{{ $item->disponibles }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d; width: 60px">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-yura_default" title="Ver Pdf de Etiquetas"
                    onclick="view_pdf_inventario('{{ $item->id_inventario_frio }}')">
                    <i class="fa fa-fw fa-file-pdf-o"></i>
                </button>
                <button type="button" class="btn btn-xs btn-yura_dark" title="Modificar"
                    onclick="update_inventario('{{ $item->id_inventario_frio }}', '{{ $planta }}')">
                    <i class="fa fa-fw fa-pencil"></i>
                </button>
                <button type="button" class="btn btn-xs btn-yura_warning" title="Botar"
                    onclick="botar_inventario('{{ $item->id_inventario_frio }}', '{{ $planta }}')">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
                <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar Registro"
                    onclick="delete_inventario('{{ $item->id_inventario_frio }}', '{{ $planta }}')">
                    <i class="fa fa-fw fa-times"></i>
                </button>
            </div>
        </td>
    </tr>
@endforeach

<script>
    $('#btn_inventario_planta_{{ $planta }}').html(
        '{{ $total_planta }} <i class="fa fa-fw fa-eye hidden" id="icon_inventario_planta_{{ $planta }}"></i>'
    );
</script>
