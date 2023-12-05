<div style="overflow-y: scroll; overflow-x: scroll; height: 700px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green">
                LONGITUD
            </th>
            <th class="text-center th_yura_green">
                RAMOS x CAJA
            </th>
            <th class="text-center th_yura_green">
                MEZCLAR
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                <button type="button" class="btn btn-xs btn-yura_default"
                    onclick="$('#tr_new_model').removeClass('hidden'); $('#codigo_new').focus()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        <tr id="tr_new_model" class="hidden">
            <th class="text-center" style="border-color: #9d9d9d">
                <select style="width: 100%; height: 26px;" class="text-center bg-yura_dark" id="clasificacion_new"
                    required>
                    @foreach ($clasificaciones as $cl)
                        <option value="{{ $cl->id_clasificacion_ramo }}">
                            {{ $cl->nombre }}
                        </option>
                    @endforeach
                </select>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%" class="text-center bg-yura_dark" id="ramos_x_caja_new"
                    placeholder="Ramos x Caja" required>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <select style="width: 100%; height: 26px;" class="text-center bg-yura_dark" id="mezcla_new"
                    required>
                    <option value="">Ninguna</option>
                    @foreach ($clasificaciones as $cl)
                        <option value="{{ $cl->id_clasificacion_ramo }}">
                            {{ $cl->nombre }}
                        </option>
                    @endforeach
                </select>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_model()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr id="tr_model_{{ $item->id_clasificacion_ramo_disponibilidad }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <select style="width: 100%; height: 26px;" class="text-center"
                        id="clasificacion_{{ $item->id_clasificacion_ramo_disponibilidad }}" required>
                        @foreach ($clasificaciones as $cl)
                            <option value="{{ $cl->id_clasificacion_ramo }}"
                                {{ $cl->id_clasificacion_ramo == $item->id_clasificacion_ramo ? 'selected' : '' }}>
                                {{ $cl->nombre }}
                            </option>
                        @endforeach
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%" class="text-center"
                        id="ramos_x_caja_{{ $item->id_clasificacion_ramo_disponibilidad }}"
                        value="{{ $item->ramos_x_caja }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select style="width: 100%; height: 26px;" class="text-center"
                        id="mezcla_{{ $item->id_clasificacion_ramo_disponibilidad }}" required>
                        <option value="">Ninguna</option>
                        @foreach ($clasificaciones as $cl)
                            <option value="{{ $cl->id_clasificacion_ramo }}"
                                {{ $cl->id_clasificacion_ramo == $item->id_mezcla ? 'selected' : '' }}>
                                {{ $cl->nombre }}
                            </option>
                        @endforeach
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_model('{{ $item->id_clasificacion_ramo_disponibilidad }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="delete_model('{{ $item->id_clasificacion_ramo_disponibilidad }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_model() {
        datos = {
            _token: '{{ csrf_token() }}',
            clasificacion: $('#clasificacion_new').val(),
            ramos_x_caja: $('#ramos_x_caja_new').val(),
            mezcla: $('#mezcla_new').val(),
        }
        post_jquery_m('{{ url('param_disponibilidad/store_model') }}', datos, function() {
            listar_reporte();
        }, 'tr_new_model');
    }

    function update_model(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            clasificacion: $('#clasificacion_' + id).val(),
            ramos_x_caja: $('#ramos_x_caja_' + id).val(),
            mezcla: $('#mezcla_' + id).val(),
            id: id,
        }
        post_jquery_m('{{ url('param_disponibilidad/update_model') }}', datos, function() {
            //listar_reporte();
        }, 'tr_model' + id);
    }

    function cambiar_estado_producto(p, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar producto' :
                '<i class="fa fa-fw fa-unlock"></i> Activar producto',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este producto?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este producto?</div>',
        };
        modal_quest('modal_delete_producto', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: p,
                };
                post_jquery_m('{{ url('param_disponibilidad/cambiar_estado_producto') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
