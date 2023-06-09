<div style="overflow-y: scroll; max-height: 550px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                Sector
            </th>
            <th class="text-center th_yura_green">
                Area
            </th>
            <th class="text-center th_yura_green" style="width: 100px">
            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="new_nombre_sector" style="width: 100%; background-color: #dddddd"
                    class="text-center" placeholder="Nombre">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="new_area_sector" style="width: 100%; background-color: #dddddd"
                    class="text-center" placeholder="Area">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-block btn-yura_primary" onclick="store_sector()"
                        title="Crear Sector">
                        <i class="fa fa-fw fa-save"></i> Nuevo
                    </button>
                </div>
            </td>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="edit_nombre_sector_{{ $item->id_sector }}" style="width: 100%;"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}" placeholder="Nombre"
                        value="{{ $item->nombre }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_area_sector_{{ $item->id_sector }}" style="width: 100%;"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}" placeholder="Area"
                        value="{{ $item->area }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default" title="Editar"
                            onclick="update_sector('{{ $item->id_sector }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            title="{{ $item->estado == 1 ? 'DESACTIVAR' : 'ACTIVAR' }}"
                            onclick="cambiar_estado_sector('{{ $item->id_sector }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'unlock' : 'lock' }}"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_dark btn_seleccionar_sector"
                            title="Seleccionar" id="btn_seleccionar_sector_{{ $item->id_sector }}"
                            onclick="listar_modulos('{{ $item->id_sector }}')">
                            <i class="fa fa-fw fa-arrow-right"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_sector() {
        modal_quest('modal_quest-store_sector',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>CREAR</strong> este sector?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    nombre: $('#new_nombre_sector').val().toUpperCase(),
                    area: $('#new_area_sector').val(),
                };
                if (datos['nombre'] != '') {
                    cerrar_modals();
                    post_jquery_m('{{ url('mapeo_cultivo/store_sector') }}', datos, function() {
                        listar_sectores();
                    });
                }
            });
    }

    function update_sector(id) {
        modal_quest('modal_quest-update_sector',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>MODIFICAR</strong> este sector?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    nombre: $('#edit_nombre_sector_' + id).val().toUpperCase(),
                    area: $('#edit_area_sector_' + id).val(),
                };
                if (datos['nombre'] != '') {
                    cerrar_modals();
                    post_jquery_m('{{ url('mapeo_cultivo/update_sector') }}', datos, function() {
                        listar_sectores();
                    });
                }
            });
    }

    function cambiar_estado_sector(id, estado) {
        mensaje = estado == 1 ? 'DESACTIVAR' : 'ACTIVAR';
        modal_quest('modal_quest-cambiar_estado_sector',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>' + mensaje +
            '</strong> este sector?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                };
                cerrar_modals();
                post_jquery_m('{{ url('mapeo_cultivo/cambiar_estado_sector') }}', datos, function() {
                    listar_sectores();
                });
            });
    }

    function listar_modulos(sector) {
        datos = {
            sector: sector
        };
        get_jquery('{{ url('mapeo_cultivo/listar_modulos') }}', datos, function(retorno) {
            $('.btn_seleccionar_sector').removeClass('btn-yura_primary').addClass('btn-yura_dark')
            $('#btn_seleccionar_sector_' + sector).removeClass('btn-yura_dark').addClass('btn-yura_primary')
            $('#div_modulos').html(retorno);
        });
    }
</script>
