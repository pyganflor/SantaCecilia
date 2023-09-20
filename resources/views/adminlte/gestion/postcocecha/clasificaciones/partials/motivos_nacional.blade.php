<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Nombre
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
        </th>
    </tr>

    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center form-control" style="width: 100%" id="new_nombre_motivo">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button class="btn btn-yura_primary" onclick="store_motivo_nacional()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </td>
    </tr>

    @foreach ($listado as $item)
        <tr class="{{ $item->estado == 1 ? '' : 'error' }}">
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" style="width: 100%; background-color: #e9ecef"
                    id="edit_nombre_motivo_{{ $item->id_motivos_nacional }}" value="{{ $item->nombre }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div class="btn-group">
                    <button class="btn btn-yura_warning btn-xs"
                        onclick="update_motivo_nacional('{{ $item->id_motivos_nacional }}')">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button class="btn btn-yura_danger btn-xs"
                        onclick="cambiar_estado_motivo_nacional('{{ $item->id_motivos_nacional }}')">
                        <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'unlock' : 'lock' }}"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>
