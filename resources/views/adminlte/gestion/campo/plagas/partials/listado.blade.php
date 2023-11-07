<div style="overflow-y: scroll; overflow-x: scroll; height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green">
                NOMBRE
            </th>
            <th class="text-center th_yura_green">
                TIPO
            </th>
            <th class="text-center th_yura_green" style="width: 90px">
                <button type="button" class="btn btn-xs btn-yura_default"
                    onclick="$('#tr_new_plaga').removeClass('hidden'); $('#nombre_new').focus()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        <tr id="tr_new_plaga" class="hidden">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%" class="text-center bg-yura_dark" id="nombre_new"
                    placeholder="NOMBRE" required>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <select style="width: 100%; height: 26px;" class="text-center bg-yura_dark" id="tipo_new" required>
                    <option value="H">Hongos</option>
                    <option value="I">Insectos</option>
                </select>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_plaga()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr id="tr_plaga_{{ $item->id_plaga }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%" class="text-center" id="nombre_{{ $item->id_plaga }}"
                        value="{{ $item->nombre }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select style="width: 100%; height: 26px;" class="text-center"
                        id="tipo_{{ $item->id_plaga }}" required>
                        <option value="H">Hongos</option>
                        <option value="I">Insectos</option>
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark" title="Rotaciones"
                            onclick="rotaciones_plaga('{{ $item->id_plaga }}')">
                            <i class="fa fa-fw fa-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_warning" title="Modificar"
                            onclick="update_plaga('{{ $item->id_plaga }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar o Activar"
                            onclick="cambiar_estado_plaga('{{ $item->id_plaga }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_plaga() {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#nombre_new').val(),
        }
        post_jquery_m('{{ url('plagas/store_plaga') }}', datos, function() {
            listar_reporte();
        }, 'tr_new_plaga');
    }

    function update_plaga(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#nombre_' + id).val(),
            id: id,
        }
        post_jquery_m('{{ url('plagas/update_plaga') }}', datos, function() {
            //listar_reporte();
        }, 'tr_plaga_' + id);
    }

    function cambiar_estado_plaga(p, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar plaga' :
                '<i class="fa fa-fw fa-unlock"></i> Activar plaga',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar esta plaga?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar esta plaga?</div>',
        };
        modal_quest('modal_delete_plaga', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: p,
                };
                post_jquery_m('{{ url('plagas/cambiar_estado_plaga') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }

    function rotaciones_plaga(id) {
        datos = {
            id: id
        };
        get_jquery('{{ url('plagas/rotaciones_plaga') }}', datos, function(retorno) {
            modal_view('modal-view_rotaciones_plaga', retorno,
                '<i class="fa fa-fw fa-list-alt"></i>Rotaciones de la Plaga', true, false, '80%');
        });
    }
</script>
