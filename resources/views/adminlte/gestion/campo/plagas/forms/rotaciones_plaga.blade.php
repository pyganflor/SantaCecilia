<input type="hidden" id="plaga_selected" value="{{ $plaga }}">
<div class="nav-tabs-custom" style="cursor: move;">
    <ul class="nav nav-pills nav-justified">
        <li class="active li_incidencia" id="li_alta">
            <a href="#tab_alta" data-toggle="tab" aria-expanded="false">
                Alta
            </a>
        </li>
        <li class="li_incidencia" id="li_media">
            <a href="#tab_media" data-toggle="tab" aria-expanded="true">
                Media
            </a>
        </li>
        <li class="li_incidencia" id="li_baja">
            <a href="#tab_baja" data-toggle="tab" aria-expanded="true">
                Baja
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="tab_alta" style="position: relative">
        </div>
        <div class="chart tab-pane" id="tab_media" style="position: relative">
        </div>
        <div class="chart tab-pane" id="tab_baja" style="position: relative">
        </div>
    </div>
</div>

<script>
    listar_incidencias('alta');
    listar_incidencias('media');
    listar_incidencias('baja');

    function listar_incidencias(incidencia) {
        datos = {
            plaga: $('#plaga_selected').val(),
            incidencia: incidencia,
        };
        get_jquery('{{ url('plagas/listar_incidencias') }}', datos, function(retorno) {
            $('#tab_' + incidencia).html(retorno);
        }, 'tab_' + incidencia);
    }

    function store_rotacion(incidencia) {
        datos = {
            _token: '{{ csrf_token() }}',
            plaga: $('#plaga_selected').val(),
            incidencia: incidencia,
            rotacion: $('#new_rotacion_' + incidencia).val(),
            producto: $('#new_producto_' + incidencia).val(),
            dosis: $('#new_dosis_' + incidencia).val(),
            litros_x_cama: $('#new_litros_x_cama_' + incidencia).val(),
        }
        post_jquery_m('{{ url('plagas/store_rotacion') }}', datos, function() {
            listar_incidencias(incidencia);
        });
    }

    function update_rotacion(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            rotacion: $('#edit_rotacion_' + id).val(),
            producto: $('#edit_producto_' + id).val(),
            dosis: $('#edit_dosis_' + id).val(),
            litros_x_cama: $('#edit_litros_x_cama_' + id).val(),
        }
        post_jquery_m('{{ url('plagas/update_rotacion') }}', datos, function() {});
    }

    function delete_rotacion(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        }
        post_jquery_m('{{ url('plagas/delete_rotacion') }}', datos, function() {
            $('#tr_edit_' + id).remove();
        });
    }
</script>
