<input type="hidden" id="sector_selected" value="{{ $sector }}">
<div style="overflow-y: scroll; max-height: 550px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                Bloque
            </th>
            <th class="text-center th_yura_green">
                Area
            </th>
            <th class="text-center th_yura_green" style="width: 100px">
            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="new_nombre_modulo" style="width: 100%; background-color: #dddddd"
                    class="text-center" placeholder="Nombre">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="new_area_modulo" style="width: 100%; background-color: #dddddd"
                    class="text-center" placeholder="Area">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_modulo()"
                        title="Crear bloque">
                        <i class="fa fa-fw fa-save"></i> Nuevo
                    </button>
                </div>
            </td>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="edit_nombre_modulo_{{ $item->id_modulo }}" style="width: 100%;"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}" placeholder="Nombre"
                        value="{{ $item->nombre }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_area_modulo_{{ $item->id_modulo }}" style="width: 100%;"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}" placeholder="Area"
                        value="{{ $item->area }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default" title="Editar"
                            onclick="update_modulo('{{ $item->id_modulo }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            title="{{ $item->estado == 1 ? 'DESACTIVAR' : 'ACTIVAR' }}"
                            onclick="cambiar_estado_modulo('{{ $item->id_modulo }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'unlock' : 'lock' }}"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_dark btn_seleccionar_modulo" title="Seleccionar"
                            onclick="listar_camas('{{ $item->id_modulo }}')" id="btn_seleccionar_modulo_{{ $item->id_modulo }}">
                            <i class="fa fa-fw fa-arrow-right"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_modulo() {
        modal_quest('modal_quest-store_modulo',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>CREAR</strong> este bloque?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                sector = $('#sector_selected').val();
                datos = {
                    _token: '{{ csrf_token() }}',
                    sector: sector,
                    nombre: $('#new_nombre_modulo').val().toUpperCase(),
                    area: $('#new_area_modulo').val(),
                };
                if (datos['nombre'] != '') {
                    cerrar_modals();
                    post_jquery_m('{{ url('mapeo_cultivo/store_modulo') }}', datos, function() {
                        listar_modulos(sector);
                    });
                }
            });
    }

    function update_modulo(id) {
        modal_quest('modal_quest-update_modulo',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>MODIFICAR</strong> este bloque?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                sector = $('#sector_selected').val();
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    sector: sector,
                    nombre: $('#edit_nombre_modulo_' + id).val().toUpperCase(),
                    area: $('#edit_area_modulo_' + id).val(),
                };
                if (datos['nombre'] != '') {
                    cerrar_modals();
                    post_jquery_m('{{ url('mapeo_cultivo/update_modulo') }}', datos, function() {
                        listar_modulos(sector);
                    });
                }
            });
    }

    function cambiar_estado_modulo(id, estado) {
        mensaje = estado == 1 ? 'DESACTIVAR' : 'ACTIVAR';
        modal_quest('modal_quest-cambiar_estado_modulo',
            '<div class="alert alert-info text-center" style="font-size: 16px">¿Desea <strong>' + mensaje +
            '</strong> este bloque?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                sector = $('#sector_selected').val();
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                };
                cerrar_modals();
                post_jquery_m('{{ url('mapeo_cultivo/cambiar_estado_modulo') }}', datos, function() {
                    listar_modulos(sector);
                });
            });
    }

    function listar_camas(modulo) {
        datos = {
            modulo: modulo
        };
        get_jquery('{{ url('mapeo_cultivo/listar_camas') }}', datos, function(retorno) {
            $('.btn_seleccionar_modulo').removeClass('btn-yura_primary').addClass('btn-yura_dark')
            $('#btn_seleccionar_modulo_' + modulo).removeClass('btn-yura_dark').addClass('btn-yura_primary')
            $('#div_camas').html(retorno);
        });
    }
</script>
