<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center bg-yura_dark" style="width: 80px">
            Rotacion
        </th>
        <th class="text-center bg-yura_dark">
            Producto
        </th>
        <th class="text-center bg-yura_dark" style="width: 100px">
            Dosis
        </th>
        <th class="text-center bg-yura_dark" style="width: 100px">
            Ltrs x Cama
        </th>
        <th class="text-center bg-yura_dark" style="width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default"
                onclick="$('#tr_new_{{ $incidencia }}').removeClass('hidden')">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    <tr class="hidden" id="tr_new_{{ $incidencia }}">
        <th class="text-center" style="border-color: #9d9d9d;">
            <input type="number" class="text-center" min="1" id="new_rotacion_{{ $incidencia }}"
                style="width: 100%; background-color: #dddddd" placeholder="Rotacion">
        </th>
        <th class="text-center" style="border-color: #9d9d9d;">
            <select id="new_producto_{{ $incidencia }}" style="width: 100%; background-color: #dddddd;">
                @foreach ($productos as $p)
                    <option value="{{ $p->id_producto }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d;">
            <input type="number" class="text-center" min="0" id="new_dosis_{{ $incidencia }}"
                style="width: 100%; background-color: #dddddd" placeholder="Dosis">
        </th>
        <th class="text-center" style="border-color: #9d9d9d;">
            <input type="number" class="text-center" min="0" id="new_litros_x_cama_{{ $incidencia }}"
                style="width: 100%; background-color: #dddddd" placeholder="Ltrs x Cama">
        </th>
        <th class="text-center" style="border-color: #9d9d9d;">
            <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_rotacion('{{ $incidencia }}')">
                <i class="fa fa-fw fa-save"></i> Grabar
            </button>
        </th>
    </tr>
    @foreach ($listado as $item)
        <tr id="tr_edit_{{ $item->id_rotaciones_plaga }}">
            <th class="text-center" style="border-color: #9d9d9d;">
                <input type="number" class="text-center" min="1"
                    id="edit_rotacion_{{ $item->id_rotaciones_plaga }}" style="width: 100%" placeholder="Rotacion"
                    value="{{ $item->rotacion }}">
            </th>
            <th class="text-center" style="border-color: #9d9d9d;">
                <select id="edit_producto_{{ $item->id_rotaciones_plaga }}" style="width: 100%; height: 26px;">
                    @foreach ($productos as $p)
                        <option value="{{ $p->id_producto }}"
                            {{ $p->id_producto == $item->id_producto ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </th>
            <th class="text-center" style="border-color: #9d9d9d;">
                <input type="number" class="text-center" min="0"
                    id="edit_dosis_{{ $item->id_rotaciones_plaga }}" style="width: 100%" placeholder="Dosis"
                    value="{{ $item->dosis }}">
            </th>
            <th class="text-center" style="border-color: #9d9d9d;">
                <input type="number" class="text-center" min="0"
                    id="edit_litros_x_cama_{{ $item->id_rotaciones_plaga }}" style="width: 100%"
                    placeholder="Ltrs x Cama" value="{{ $item->litros_x_cama }}">
            </th>
            <th class="text-center" style="border-color: #9d9d9d;">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_warning"
                        onclick="update_rotacion('{{ $item->id_rotaciones_plaga }}')">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger"
                        onclick="delete_rotacion('{{ $item->id_rotaciones_plaga }}')">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </th>
        </tr>
    @endforeach
</table>

<script>
    setTimeout(() => {
        $("#new_producto_{{ $incidencia }}")
            .select2({
                dropdownParent: $('#div_modal-modal-view_rotaciones_plaga')
            });
    }, 500);
</script>
