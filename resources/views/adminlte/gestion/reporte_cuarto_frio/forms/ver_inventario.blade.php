<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border:1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green">
                Variedad
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Bloque
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                Longitud
            </th>
            @if ($tipo == 'F')
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Tallos x Ramo
                </th>
            @else
                <th class="padding_lateral_5 th_yura_green">
                    Motivo Flor Nacional
                </th>
            @endif
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                Edad
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                Disponibles
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr>
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->variedad->nombre }}
                </td>
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->modulo->sector->nombre }}: {{ $item->modulo->nombre }}
                </td>
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->clasificacion_ramo->nombre }}
                </td>
                @if ($tipo == 'F')
                    <td class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->tallos_x_ramo }}
                    </td>
                @else
                    <td class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->motivo_nacional->nombre }}
                    </td>
                @endif
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ difFechas(hoy(), $item->fecha)->days }} dias
                </td>
                <td style="border-color: #9d9d9d">
                    <input type="number" id="disponibles_{{ $item->id_inventario_frio }}" style="width: 100%"
                        value="{{ $item->disponibles }}" max="{{ $item->disponibles }}" min="0"
                        class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning" title="Dar de Baja"
                            onclick="botar_inventario('{{ $item->id_inventario_frio }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<input type="hidden" id="variedad_selected" value="{{ $variedad }}">
<input type="hidden" id="longitud_selected" value="{{ $longitud }}">
<input type="hidden" id="fecha_selected" value="{{ $fecha }}">
<input type="hidden" id="tipo_selected" value="{{ $tipo }}">

<script>
    function botar_inventario(id) {
        variedad = $('#variedad_selected').val();
        longitud = $('#longitud_selected').val();
        fecha = $('#fecha_selected').val();
        tipo = $('#tipo_selected').val();
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            cantidad: $('#disponibles_' + id).val(),
        }
        post_jquery_m('{{ url('reporte_cuarto_frio/botar_inventario') }}', datos, function() {
            listar_reporte();
            cerrar_modals();
            ver_inventario(variedad, longitud, fecha, tipo);
        });
    }
</script>
