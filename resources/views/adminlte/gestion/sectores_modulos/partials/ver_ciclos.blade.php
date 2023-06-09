@if (count($modulo->ciclos) > 0)
    <div style="overflow-x: scroll">
        <table class="table-striped table-bordered table-responsive" width="100%"
            style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_ver_ciclos">
            <thead>
                <tr>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d; border-radius: 18px 0 0 0">
                        Inicio
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Variedad
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Área m<sup>2</sup>
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Poda/Siembra
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Dias
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        1ra Flor
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Final
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Plantas Iniciales
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Plantas Muertas
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Conteo
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Ancho Cama
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                        Ancho Camino
                    </th>
                    <th class="text-center th_yura_default" style="border-color: #9d9d9d; border-radius: 0 18px 0 0">
                        Opciones
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach ($ciclos as $pos_ciclo => $ciclo)
                    <input type="hidden" id="activo_ciclo_modal_{{ $ciclo->id_ciclo }}" value="{{ $ciclo->activo }}">
                    <tr class="{{ $ciclo->activo == 1 ? 'background-color_yura' : '' }} {{ $ciclo->estado == 0 ? 'error' : '' }}"
                        title="{{ $ciclo->activo == 1 ? 'Activo' : '' }}">
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">{{ $ciclo->fecha_inicio }}</span>
                            <input type="date" id="fecha_inicio_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->fecha_inicio }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">{{ $ciclo->variedad->siglas }}</span>
                            <select id="variedad_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }}
                        {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }} input-yura_white"
                                style="width: 100%; display: none">
                                @foreach ($variedades as $item)
                                    <option value="{{ $item->id_variedad }}"
                                        {{ $item->id_variedad == $ciclo->id_variedad ? 'selected' : '' }}>
                                        {{ $item->planta->nombre . ': ' . $item->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">{{ $ciclo->area }}m<sup>2</sup></span>
                            <input type="number" id="area_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->area }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span
                                class="elemento_view_{{ $ciclo->id_ciclo }}">{{ $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo) }}</span>
                            <select
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                id="poda_siembra_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                style="width: 100%; display: none">
                                <option value="P" {{ $ciclo->poda_siembra == 'P' ? 'selected' : '' }}>Poda
                                </option>
                                <option value="S" {{ $ciclo->poda_siembra == 'S' ? 'selected' : '' }}>Siembra
                                </option>
                            </select>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            @if ($ciclo->fecha_fin != '')
                                {{ difFechas($ciclo->fecha_fin, $ciclo->fecha_inicio)->days }}
                            @else
                                {{ difFechas(date('Y-m-d'), $ciclo->fecha_inicio)->days }}
                            @endif
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">
                                @if ($ciclo->fecha_cosecha != '')
                                    {{ difFechas($ciclo->fecha_cosecha, $ciclo->fecha_inicio)->days }}
                                @endif
                            </span>
                            <input type="text" id="fecha_cosecha_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->fecha_cosecha != '' ? difFechas($ciclo->fecha_cosecha, $ciclo->fecha_inicio)->days : '' }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required onkeypress="return isNumber(event)"
                                maxlength="3">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">
                                {{ $ciclo->fecha_fin }}
                            </span>
                            <input type="date" id="fecha_fin_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->fecha_fin }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">
                                {{ $ciclo->plantas_iniciales }}
                            </span>
                            <input type="number" id="plantas_iniciales_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->plantas_iniciales }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">
                                {{ $ciclo->plantas_muertas }}
                            </span>
                            <input type="number" id="plantas_muertas_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->plantas_muertas }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">
                                {{ $ciclo->conteo }}
                            </span>
                            <input type="number" id="conteo_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->conteo }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">
                                {{ $ciclo->ancho_cama }}
                            </span>
                            <input type="number" id="ancho_cama_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->ancho_cama }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span class="elemento_view_{{ $ciclo->id_ciclo }}">
                                {{ $ciclo->ancho_camino }}
                            </span>
                            <input type="number" id="ancho_camino_ciclo_modal_{{ $ciclo->id_ciclo }}"
                                value="{{ $ciclo->ancho_camino }}"
                                class="elemento_input_{{ $ciclo->id_ciclo }} text-center input-yura_white {{ $ciclo->activo == 1 ? 'background-color_yura' : '' }}"
                                style="width: 100%; display: none" required>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_default" title="Ver cosechas"
                                    onclick="ver_cosechas('{{ $ciclo->id_ciclo }}')">
                                    <i class="fa fa-fw fa-leaf"></i>
                                </button>
                                @if ($ciclo->activo == 1)
                                    <button type="button" class="btn btn-xs btn-yura_warning" title="Terminar ciclo"
                                        onclick="terminar_ciclo('{{ $ciclo->id_ciclo }}', true, '{{ $modulo->id_modulo }}')">
                                        <i class="fa fa-fw fa-times"></i>
                                    </button>
                                @elseif($cicloActual == '')
                                    <button type="button" class="btn btn-xs btn-yura_warning" title="Abrir ciclo"
                                        onclick="abrir_ciclo('{{ $modulo->id_modulo }}', '{{ $ciclo->id_ciclo }}', true)">
                                        <i class="fa fa-fw fa-check"></i>
                                    </button>
                                @endif
                                <button type="button"
                                    class="btn btn-xs btn-yura_default elemento_view_{{ $ciclo->id_ciclo }}"
                                    title="Editar ciclo" onclick="editar_ciclo('{{ $ciclo->id_ciclo }}')">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-xs btn-yura_default elemento_input_{{ $ciclo->id_ciclo }}"
                                    title="Guardar ciclo"
                                    onclick="guardar_ciclo('{{ $ciclo->id_ciclo }}', '{{ $modulo->id_modulo }}')"
                                    style="display: none">
                                    <i class="fa fa-fw fa-save"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar ciclo"
                                    onclick="eliminar_ciclo('{{ $ciclo->id_ciclo }}', true, '{{ $modulo->id_modulo }}')">
                                    <i class="fa fa-fw fa-trash"></i>
                                </button>
                            </div>
                        </th>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <input type="hidden" id="id_modulo" value="{{ $modulo->id_modulo }}">

    <script>
        estructura_tabla('table_ver_ciclos', false, true);
        $('#table_ver_ciclos_length label').addClass('text-color_yura');
        $('#table_ver_ciclos_length label select').addClass('input-yura_white');
        $('#table_ver_ciclos_filter label').addClass('text-color_yura');
        $('#table_ver_ciclos_filter label input').addClass('input-yura_white');
    </script>
@else
    <div class="alert alert-info text-center">
        No se han encontrado resultados
    </div>
@endif
