<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green">
                Planta
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Variedad
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Modulo
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Motivo
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Tallos
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
            </th>
        </tr>
        @php
            $total_tallos = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            @php
                $total_tallos += $item->tallos;
                $variedades = getVariedadesByPlanta($item->id_planta, '');
                $modulos = getModulosByVariedad($item->id_variedad);
            @endphp
            <tr>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_planta_{{ $item->id_flor_nacional }}" style="width: 100%; height: 26px;"
                        onchange="select_planta($(this).val(), 'edit_variedad_{{ $item->id_flor_nacional }}', 'edit_variedad_{{ $item->id_flor_nacional }}', '<option>Seleccione</option>')">
                        <option value="">Seleccione</option>
                        @foreach ($plantas as $p)
                            <option value="{{ $p->id_planta }}"
                                {{ $p->id_planta == $item->id_planta ? 'selected' : '' }}>
                                {{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_variedad_{{ $item->id_flor_nacional }}" style="width: 100%; height: 26px;"
                        onchange="buscar_modulos('{{ $item->id_flor_nacional }}')">
                        <option value="">Seleccione</option>
                        @foreach ($variedades as $v)
                            <option value="{{ $v->id_variedad }}"
                                {{ $v->id_variedad == $item->id_variedad ? 'selected' : '' }}>
                                {{ $v->nombre }}
                            </option>
                        @endforeach
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_modulo_{{ $item->id_flor_nacional }}" style="width: 100%; height: 26px;">
                        <option value="">Seleccione</option>
                        @foreach ($modulos as $m)
                            <option value="{{ $m->id_modulo }}"
                                {{ $m->id_modulo == $item->id_modulo ? 'selected' : '' }}>
                                {{ $m->sector_nombre }}: {{ $m->modulo_nombre }}
                            </option>
                        @endforeach
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_motivo_{{ $item->id_flor_nacional }}" style="width: 100%; height: 26px;"
                        onchange="select_planta($(this).val(), 'edit_variedad_{{ $item->id_flor_nacional }}', 'edit_variedad_{{ $item->id_flor_nacional }}', '<option>Seleccione</option>')">
                        <option value="">Seleccione</option>
                        @foreach ($motivos_nacional as $m)
                            <option value="{{ $m->id_motivos_nacional }}"
                                {{ $m->id_motivos_nacional == $item->id_motivos_nacional ? 'selected' : '' }}>
                                {{ $m->nombre }}
                            </option>
                        @endforeach
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_tallos_{{ $item->id_flor_nacional }}" style="width: 100%"
                        value="{{ $item->tallos }}" class="text-center">
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_warning btn-xs" title="Editar"
                            onclick="update_flor_nacional('{{ $item->id_flor_nacional }}')">
                            <i class="fa fa-fw fa-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-yura_danger btn-xs" title="Eliminar"
                            onclick="eliminar_flor_nacional('{{ $item->id_flor_nacional }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green" colspan="4">
                Totales
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_tallos) }}
            </th>
            <th class="text-center th_yura_green">
            </th>
        </tr>
    </table>
</div>

<script>
    function eliminar_flor_nacional(id) {
        texto =
            "<div class='alert alert-danger text-center' style='font-size: 1.5em'>Esta a punto de <b>ELIMINAR</b> la flor nacional</div>";

        modal_quest('modal_eliminar_flor_nacional', texto, 'Eliminar Flor Nacional', true, false, '50%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
            };
            post_jquery_m('flor_nacional/eliminar_flor_nacional', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function update_flor_nacional(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            variedad: $('#edit_variedad_' + id).val(),
            modulo: $('#edit_modulo_' + id).val(),
            motivo: $('#edit_motivo_' + id).val(),
            tallos: $('#edit_tallos_' + id).val(),
        }
        if (datos['variedad'] != '' && datos['modulo'] != '' && datos['motivo'] != '' && datos['tallos'] > 0) {
            post_jquery_m('{{ url('flor_nacional/update_flor_nacional') }}', datos, function() {
                listar_reporte();
            });
        }
    }
</script>