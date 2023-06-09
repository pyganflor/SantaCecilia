<script type="text/javascript" src="{{url('js/gridviewscroll/gridviewscroll.js')}}"></script>

<div id="div_content_fixed">
    <table class="table-striped table-bordered table-hover" style="font-size: 0.8em" width="100%" id="tabla_proyecciones">
        <thead>
        <tr class="fila_fija_1">
            <th class="text-center columna_fija_1" style="width: 250px" id="celda_semanas">
                Semanas
                <input type="hidden" id="semana_desde_listado" value="{{$semanas[0]->codigo}}">
            </th>
            @php
                $tallos_proyectados = [];
                $tallos_cosechados = [];
                $ptas_iniciales = [];
                $total_area = [];
            @endphp
            @foreach($semanas as $pos_sem => $sem)
                <th class="text-center celda_semana_{{$sem->id_semana}}" style="width: 250px">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                            {{$sem->codigo}}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a href="javascript:void(0)" onclick="new_cultivo('{{$sem->id_semana}}')">
                                    Nueva poda
                                </a>
                            </li>
                        </ul>
                    </div>

                    <input type="hidden" id="semana_{{$pos_sem}}" value="{{$sem->codigo}}">
                </th>
                @php
                    /* =============== INICIALIZAR TOTALES ===================== */
                    $tallos_proyectados[$pos_sem] = 0;
                    $tallos_cosechados[$pos_sem] = 0;
                    $ptas_iniciales[$pos_sem] = 0;
                    $total_area[$pos_sem] = 0;
                @endphp
            @endforeach
            <th class="text-center" style="width: 250px">
                Semanas
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($modulos as $mod)
            <tr id="tr_modulo_{{$mod['modulo']->id_modulo}}">
                <th class="text-center columna_fija_1" style="border-color: #9d9d9d; background-color: #e9ecef"
                    id="celda_modulo_{{$mod['modulo']->id_modulo}}">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                            @if($variedad_model->proyectar_semanal == 1)
                                {{explode('-', $mod['modulo']->nombre)[1]}}
                            @else
                                {{$mod['modulo']->nombre}}
                            @endif
                        </button>
                        <ul class="dropdown-menu" style="z-index: 12; margin-left: 100px">
                            <li>
                                <a href="javascript:void(0)" onclick="actualizar_manual('{{$mod['modulo']->id_modulo}}')">
                                    Actualizar manualmente
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="javascript:void(0)" onclick="get_row_byModulo('{{$mod['modulo']->id_modulo}}')">
                                    Refrescar fila
                                </a>
                            </li>
                        </ul>
                    </div>
                </th>
                @foreach($semanas as $pos_sem => $sem)
                    @php
                        $existe = false;
                    @endphp
                    @foreach($mod['valores'] as $pos_val => $val)
                        @if($sem->codigo == $val->semana)
                            @php
                                $existe = true;
                                $fondo = '';
                                $texto = 'black';
                                $title = '<em>Mod: '.$mod['modulo']->nombre.'</em><br>'.
                                            '<em>Sem: '.$val->semana.'</em><br>';
                                if($val->tipo == 'P'){
                                    if(substr($val->info, 2) == 2)
                                        $fondo = '#ffb100'; // poda de 2
                                    else if(substr($val->info, 2) > 2) {
                                        $fondo = '#f70b00'; // poda de 3 o más
                                        $texto = 'white';
                                    } else
                                        $fondo = '#efff00'; // poda de 1
                                        $title .= '<em>Ptas.Ini: '.number_format($val->plantas_iniciales).'</em><br>';
                                        $title .= '<em>Ptas.Act: '.number_format($val->plantas_actuales).'</em><br>';
                                        $title .= '<em>T/Ptas: '.($val->tallos_planta).'</em><br>';
                                        $title .= '<em>Sem.Cos: '.($val->semana_poda_siembra).'</em><br>';
                                        $title .= '<em>Curva: '.($val->curva).'</em><br>';
                                        $title .= '<em>Desecho: '.($val->desecho).'%</em><br>';
                                } else if($val->tipo == 'S'){
                                    $fondo = '#08ffe8'; // siembra
                                    $title .= '<em>Ptas.Ini: '.number_format($val->plantas_iniciales).'</em><br>';
                                    $title .= '<em>Ptas.Act: '.number_format($val->plantas_actuales).'</em><br>';
                                    $title .= '<em>T/Ptas: '.($val->tallos_planta).'</em><br>';
                                    $title .= '<em>Sem.Cos: '.($val->semana_poda_siembra).'</em><br>';
                                    $title .= '<em>Curva: '.($val->curva).'</em><br>';
                                    $title .= '<em>Desecho: '.($val->desecho).'%</em><br>';
                                } else if($val->tipo == 'Y'){
                                    if($val->poda_siembra == 0)
                                        $fondo = '#08ffe8'; // siembra
                                    else if($val->poda_siembra == 1)
                                        $fondo = '#efff00'; // poda de 1
                                    else if($val->poda_siembra == 2)
                                        $fondo = '#ffb100'; // poda de 2
                                    else if($val->poda_siembra > 2) {
                                        $fondo = '#f70b00'; // poda de 3 o más
                                        $texto = 'white';
                                    }
                                    if($val->info != 'C'){  // no está cerrada la proyeccion
                                        $title .= '<em>Ptas.Ini: '.number_format($val->plantas_iniciales).'</em><br>';
                                        $title .= '<em>T/Ptas: '.($val->tallos_planta).'</em><br>';
                                        $title .= '<em>Sem.Cos: '.($val->semana_poda_siembra).'</em><br>';
                                        $title .= '<em>Curva: '.($val->curva).'</em><br>';
                                        $title .= '<em>Desecho: '.($val->desecho).'%</em><br>';
                                    } else {
                                        $title .= '<em>Cierre de módulo</em>';
                                    }
                                } else if($val->tipo == 'T'){
                                    $fondo = '#03de00';
                                    if($val->tabla == 'C'){   // ciclo
                                        $title .= '<em>Ptas.Ini: '.number_format($val->plantas_iniciales).'</em><br>';
                                        $title .= '<em>Ptas.Act: '.number_format($val->plantas_actuales).'</em><br>';
                                        $title .= '<em>T/Ptas: '.($val->tallos_planta).'</em><br>';
                                        $title .= '<em>Sem.Cos: '.($val->semana_poda_siembra).'</em><br>';
                                        $title .= '<em>Curva: '.($val->curva).'</em><br>';
                                        $title .= '<em>Desecho: '.($val->desecho).'%</em><br>';
                                    } else {    // proyeccion_modulo
                                        $title .= '<em>Ptas.Ini: '.number_format($val->plantas_iniciales).'</em><br>';
                                        $title .= '<em>T/Ptas: '.($val->tallos_planta).'</em><br>';
                                        $title .= '<em>Sem.Cos: '.($val->semana_poda_siembra).'</em><br>';
                                        $title .= '<em>Curva: '.($val->curva).'</em><br>';
                                        $title .= '<em>Desecho: '.($val->desecho).'%</em><br>';
                                    }
                                }
                                $title .= '<em>Área: '.($val->area).'</em><br>';
                            @endphp
                            <td class="text-center celda_hovered celda_semana_{{$sem->id_semana}} celda_modulo_{{$mod['modulo']->id_modulo}} {{in_array($val->tipo, ['F', 'P', 'S', 'T', 'Y']) ? 'mouse-hand' : ''}}"
                                style="border-color: #9d9d9d; background-color: {{$fondo}}; color: {{$texto}}"
                                id="celda_{{$mod['modulo']->id_modulo}}_{{$pos_sem}}"
                                onclick="select_celda('{{$val->tipo}}', '{{$mod['modulo']->id_modulo}}', '{{$val->semana}}', '{{$val->id_variedad}}', '{{$val->tabla}}', '{{$val->modelo}}')"
                                onmouseover="mouse_over_celda('celda_{{$mod['modulo']->id_modulo}}_{{$pos_sem}}', 1)"
                                onmouseleave="mouse_over_celda('celda_{{$mod['modulo']->id_modulo}}_{{$pos_sem}}', 0)">
                                <span data-toggle="tooltip" data-placement="top" data-html="true"
                                      title="{{in_array($val->tipo, ['S', 'P', 'T', 'Y']) ? $title : ''}}">
                                    @if($val->tipo == 'T')
                                        <strong style="font-size: 0.8em; margin-bottom: 0">
                                            {{$val->proyectados != '' ? number_format($val->proyectados, 2) : 0}}
                                        </strong>
                                    @elseif($val->tipo == 'Y')
                                        <p style="margin-top: 0; margin-bottom: 0">
                                            {{$val->info}}-{{$val->poda_siembra}}
                                        </p>
                                    @else
                                        <p style="margin-top: 0; margin-bottom: 0">
                                            {{$val->info}}
                                        </p>
                                    @endif
                                    @if($val->cosechados > 0)
                                        <p style="margin-top: 0; margin-bottom: 0">
                                            <strong style="font-size: 0.8em">{{number_format($val->cosechados)}}</strong>
                                        </p>
                                    @endif
                                </span>
                            </td>
                        @endif
                    @endforeach
                    @if(!$existe)
                        <td class="text-center celda_hovered celda_semana_{{$sem->id_semana}} celda_modulo_{{$mod['modulo']->id_modulo}} mouse-hand"
                            style="border-color: #9d9d9d;" id="celda_{{$mod['modulo']->id_modulo}}_{{$pos_sem}}"
                            onmouseover="mouse_over_celda('celda_{{$mod['modulo']->id_modulo}}_{{$pos_sem}}', 1)"
                            onmouseleave="mouse_over_celda('celda_{{$mod['modulo']->id_modulo}}_{{$pos_sem}}', 0)">
                        </td>
                    @endif
                @endforeach

                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                            @if($variedad_model->proyectar_semanal == 1)
                                {{explode('-', $mod['modulo']->nombre)[1]}}
                            @else
                                {{$mod['modulo']->nombre}}
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a href="javascript:void(0)" onclick="actualizar_manual('{{$mod['modulo']->id_modulo}}')">
                                    Actualizar manualmente
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="javascript:void(0)" onclick="get_row_byModulo('{{$mod['modulo']->id_modulo}}')">
                                    Refrescar fila
                                </a>
                            </li>
                        </ul>
                    </div>
                </th>
            </tr>
        @endforeach
        </tbody>

        {{-- CALCULAR TOTALES --}}
        @foreach($modulos as $mod)
            @foreach($semanas as $pos_sem => $sem)
                @foreach($mod['valores'] as $pos_val => $val)
                    @php
                        if($sem->codigo == $val->semana){
                            $tallos_proyectados[$pos_sem] += $val->proyectados;
                            $tallos_cosechados[$pos_sem] += $val->cosechados;
                            if (in_array($val->tipo, ['S', 'P', 'Y'])){
                                $ptas_iniciales[$pos_sem] += $val->plantas_iniciales;
                                $total_area[$pos_sem] += $val->area;
                            }
                        }
                    @endphp
                @endforeach
            @endforeach
        @endforeach

        {{-- TOTALES --}}
        <tfooter>
            <tr style="background-color: #fdff8b">
                <th class="text-center columna_fija_1" style="border-color: #9d9d9d; background-color: #fdff8b !important;">
                    Proyectados
                </th>
                @foreach($tallos_proyectados as $pos_val => $val)
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if($val > 0)
                            @php
                                if($semanas[$pos_val]->fecha_inicial >= $semana_actual->fecha_inicial && $semanas[$pos_val]->fecha_inicial <= opDiasFecha('+', 34, $semana_actual->fecha_inicial)){
                                    $calibre = $calibre_actual;
                                } else {
                                    $calibre = getCalibreByRangoVariedad($semanas[$pos_val]->fecha_inicial, $semanas[$pos_val]->fecha_final, $variedad);
                                }
                                if($calibre <= 0){
                                    if($semanas[$pos_val]->tallos_ramo_poda > 0){
                                        $calibre = $semanas[$pos_val]->tallos_ramo_poda;
                                    }
                                }
                            @endphp
                            <span data-toggle="tooltip" data-placement="top" data-html="true"
                                  title="{{$semanas[$pos_val]->codigo}} <br>
                            <small>Calib:<em>{{$calibre}}</em></small>">
                            {{number_format($val, 2)}}
                                {{--<br>
                            <strong>
                                @if($calibre > 0)
                                    {{number_format(round(($val / $calibre) / $ramos_x_caja, 2), 2)}}
                                @endif
                            </strong>--}}
                            </span>
                        @endif
                    </th>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d">
                    Proyectados
                </th>
            </tr>
            <tr style="background-color: #c4c4ff">
                <th class="text-center columna_fija_1" style="border-color: #9d9d9d; background-color: #c4c4ff !important;">
                    Cosechados
                </th>
                {{--@foreach($tallos_cosechados as $pos_val => $val)
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if($val > 0)
                            <span data-toggle="tooltip" data-placement="top" data-html="true"
                                  title="{{$semanas[$pos_val]->codigo}}">
                                {{number_format($val, 2)}}
                            </span>
                        @endif
                    </th>
                @endforeach--}}
                @foreach($semanas as $sem)
                    @php
                        $valor = 0;
                        foreach($total_cosechados as $t)
                            if ($sem->codigo == $t->semana)
                                $valor = $t->tallos_cosechados;
                    @endphp
                    <th class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        <span data-toggle="tooltip" data-placement="top" data-html="true"
                          title="{{$sem->codigo}}">
                            {{number_format($valor, 2)}}
                        </span>
                    </th>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d">
                    Cosechados
                </th>
            </tr>
            <tr style="background-color: #0c7605; color: white">
                <th class="text-center columna_fija_1" style="border-color: #9d9d9d; background-color: #0c7605 !important;">
                    Ptas. Iniciales
                </th>
                @foreach($ptas_iniciales as $pos_val => $val)
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if($val > 0)
                            <span data-toggle="tooltip" data-placement="top" data-html="true"
                                  title="{{$semanas[$pos_val]->codigo}}">
                            {{number_format($val, 2)}}
                        </span>
                        @endif
                    </th>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d">
                    Ptas. Iniciales
                </th>
            </tr>
            <tr style="background-color: #3b3b78; color: white">
                <th class="text-center columna_fija_1" style="border-color: #9d9d9d; background-color: #3b3b78 !important;">
                    Área
                </th>
                @foreach($total_area as $pos_val => $val)
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if($val > 0)
                            <span data-toggle="tooltip" data-placement="top" data-html="true"
                                  title="{{$semanas[$pos_val]->codigo}}">
                            {{number_format($val, 2)}}
                        </span>
                        @endif
                    </th>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d">
                    Área
                </th>
            </tr>

            <tr>
                <th class="text-center columna_fija_1" style="border-color: #9d9d9d; background-color: #e9ecef; width: 250px">
                    Módulos
                </th>
                @foreach($semanas as $sem)
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; width: 250px">
                    <span data-toggle="tooltip" data-placement="top" data-html="true"
                          title="<em>T.Ramo: {{$sem->tallos_ramo_poda}}</em><br>
                          <em>T.Pta: {{$sem->tallos_planta_poda}}</em><br>
                          <em>%Desecho: {{$sem->desecho}}</em>">
                        {{$sem->codigo}}
                    </span>
                    </th>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; width: 250px">
                    Módulos
                </th>
            </tr>
        </tfooter>
    </table>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="text-left" style="margin-top: 10px">
            <legend style="font-size: 1em; margin-bottom: 0">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseConfig">
                    <strong style="color: black">Configuración <i class="fa fa-fw fa-caret-down"></i></strong>
                </a>
                <small style="color: #f7f8f8" class="pull-right">{{$variedad}}</small>

            </legend>
            <div class="panel-collapse collapse" id="collapseConfig">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                <i class="fa fa-fw fa-calendar"></i> Mín. ini. cosecha %
                            </span>
                            <input type="number" class="form-control input-yura_default" id="proy_minimo_cosecha" required="" min="1" max="50"
                                   value="{{$configuracion->proy_minimo_cosecha}}" onchange="update_config('proy_minimo_cosecha')">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input-group">
                            <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                <i class="fa fa-fw fa-calendar"></i> Máx. fin cosecha %
                            </span>
                            <input type="number" class="form-control input-yura_default" id="proy_maximo_cosecha_fin" required="" min="10"
                                   max="50" value="{{$configuracion->proy_maximo_cosecha_fin}}"
                                   onchange="update_config('proy_maximo_cosecha_fin')">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="text-right" style="margin-top: 10px">
            <legend style="font-size: 1em; margin-bottom: 0">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseLeyenda">
                    <strong style="color: black">Leyenda <i class="fa fa-fw fa-caret-down"></i></strong>
                </a>
            </legend>
            <div class="panel-collapse collapse" id="collapseLeyenda">
                <ul style="margin-top: 5px" class="list-unstyled">
                    <li>Tercera poda o posterior <i class=" fa fa-fw fa-circle" style="color: #f70b00"></i></li>
                    <li>Segunda poda <i class="fa fa-fw fa-circle" style="color: #ffb100"></i></li>
                    <li>Primera poda <i class="fa fa-fw fa-circle" style="color: #efff00"></i></li>
                    <li>Siembra <i class="fa fa-fw fa-circle" style="color: #08ffe8"></i></li>
                    {{--<li>Proyección <i class="fa fa-fw fa-circle" style="color: #9100ff7d"></i></li>--}}
                    <li>Semana de cosecha <i class="fa fa-fw fa-circle" style="color: #03de00"></i></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    $('#fincas_propias').val('{{$finca_actual}}');

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    function get_row_byModulo(mod) {
        datos = {
            modulo: mod,
            variedad: $('#filtro_predeterminado_variedad').val(),
            desde: $('#semana_0').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('proy_cosecha/get_row_byModulo')}}', datos, function (retorno) {
            $('#tr_modulo_' + mod).html(retorno);
        }, 'tr_modulo_' + mod);
    }

    function restaurar_proyeccion(mod) {
        if (mod != null) {
            datos = {
                _token: '{{csrf_token()}}',
                modulo: mod
            };
            $('#tr_modulo_' + mod).LoadingOverlay('show');
            $.post('{{url('proy_cosecha/restaurar_proyeccion')}}', datos, function (retorno) {
                setTimeout(function () {
                    get_row_byModulo(mod);
                }, 500);
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_modulo_' + mod).LoadingOverlay('hide');
            });
        } else {
            var all = $('.checkbox_modulo');
            var selected = [];
            for (i = 0; i < all.length; i++) {
                if ($('#' + all[i].id).prop('checked') == true) {
                    selected.push(all[i].id.substr(16));
                }
            }

            factor = (Math.round((100 / selected.length) * 100) / 100);
            total_progress = 0;
            $('#div_barra_progreso').removeClass('hide');

            for (i = 0; i < selected.length; i++) {
                datos = {
                    _token: '{{csrf_token()}}',
                    modulo: selected[i]
                };
                mod = datos['modulo'];

                $('#tr_modulo_' + mod).LoadingOverlay('show');

                $.post('{{url('proy_cosecha/restaurar_proyeccion')}}', datos, function (retorno) {
                    mod = retorno.modulo;
                    total_progress += factor;
                    $('#barra_progreso').css('width', total_progress + '%');
                    $('#celda_modulo_' + mod).LoadingOverlay('hide');

                    if (mod == selected[selected.length - 1]) {
                        setTimeout(function () {
                            $('#div_barra_progreso').hide();
                        }, 500);
                    }
                    get_row_byModulo(mod);
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $('#tr_modulo_' + mod).LoadingOverlay('hide');
                });
            }
        }
    }

    function actualizar_proyecciones(mod) {
        if (mod != null) {
            datos = {
                _token: '{{csrf_token()}}',
                modulo: mod,
                variedad: $('#filtro_predeterminado_variedad').val(),
                desde: $('#filtro_predeterminado_desde').val(),
                hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_modulo_' + mod).LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_proyecciones')}}', datos, function (retorno) {
                setTimeout(function () {
                    get_row_byModulo(mod);
                }, 500);
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_modulo_' + mod).LoadingOverlay('hide');
            });
        } else {
            var all = $('.checkbox_modulo');
            var selected = [];
            for (i = 0; i < all.length; i++) {
                if ($('#' + all[i].id).prop('checked') == true) {
                    selected.push(all[i].id.substr(16));
                }
            }

            factor = (Math.round((100 / selected.length) * 100) / 100);
            total_progress = 0;
            $('#div_barra_progreso').removeClass('hide');

            for (i = 0; i < selected.length; i++) {
                datos = {
                    _token: '{{csrf_token()}}',
                    modulo: selected[i],
                    variedad: $('#filtro_predeterminado_variedad').val(),
                    desde: $('#filtro_predeterminado_desde').val(),
                    hasta: $('#filtro_predeterminado_hasta').val(),
                };
                mod = datos['modulo'];

                $('#tr_modulo_' + mod).LoadingOverlay('show');

                $.post('{{url('proy_cosecha/actualizar_proyecciones')}}', datos, function (retorno) {
                    mod = retorno.modulo;
                    total_progress += factor;
                    $('#barra_progreso').css('width', total_progress + '%');
                    $('#celda_modulo_' + mod).LoadingOverlay('hide');

                    if (mod == selected[selected.length - 1]) {
                        setTimeout(function () {
                            $('#div_barra_progreso').hide();
                            //listar_proyecciones_cosecha();
                        }, 500);
                    }
                    get_row_byModulo(mod);
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $('#tr_modulo_' + mod).LoadingOverlay('hide');
                });
            }
        }
    }

    function actualizar_semana(sem) {
        if (sem != null) {
            datos = {
                _token: '{{csrf_token()}}',
                semana: sem,
                modulos: [0],
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            $('.celda_semana_' + sem).LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_semana')}}', datos, function (retorno) {
                setTimeout(function () {
                    listar_proyecciones_cosecha();
                }, 500);
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('.celda_semana_' + sem).LoadingOverlay('hide');
            });
        } else {
            var all = $('.check_semana');
            var selected = [];
            for (i = 0; i < all.length; i++) {
                if ($('#' + all[i].id).prop('checked') == true) {
                    selected.push(all[i].id.substr(13));
                }
            }

            all = $('.checkbox_modulo');
            var modulos = [];
            for (i = 0; i < all.length; i++) {
                if ($('#' + all[i].id).prop('checked') == true) {
                    modulos.push(all[i].id.substr(16));
                }
            }

            if (modulos.length == 0)
                for (i = 0; i < all.length; i++) {
                    modulos.push(all[i].id.substr(16));
                }

            factor = (Math.round((100 / selected.length) * 100) / 100);
            total_progress = 0;
            $('#div_barra_progreso').removeClass('hide');

            for (i = 0; i < selected.length; i++) {
                datos = {
                    _token: '{{csrf_token()}}',
                    semana: selected[i],
                    modulos: modulos,
                    variedad: $('#filtro_predeterminado_variedad').val(),
                };
                sem = datos['semana'];

                $('.celda_semana_' + sem).LoadingOverlay('show');
                $.post('{{url('proy_cosecha/actualizar_semana')}}', datos, function (retorno) {
                    sem = retorno.semana;
                    total_progress += factor;
                    $('#barra_progreso').css('width', total_progress + '%');
                    $('.celda_semana_' + sem).LoadingOverlay('hide');

                    if (sem == selected[selected.length - 1]) {
                        setTimeout(function () {
                            $('#div_barra_progreso').hide();
                            listar_proyecciones_cosecha();
                        }, 500);
                    }
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $('.celda_semana_' + sem).LoadingOverlay('hide');
                });
            }
        }
    }

    function select_all_modulos(input) {
        if (input.prop('checked') == true) {  // select all
            $('.checkbox_modulo').prop('checked', true);
        } else {    // deseleccionar todos
            $('.checkbox_modulo').prop('checked', false);
        }
    }

    function select_all_semanas() {
        if ($('#check_semana_all').prop('checked') == true) {  // deseleccionar all
            $('#check_semana_all').prop('checked', false);
            $('.check_semana').prop('checked', false);
        } else {    // select todos
            $('#check_semana_all').prop('checked', true);
            $('.check_semana').prop('checked', true);
        }
    }

    function actualizar_manual(mod) {
        if (mod != null) {
            $('.celda_modulo_' + mod).each(function (pos) {
                id = $('.celda_modulo_' + mod)[pos].id;
                $('#' + id).removeAttr('onclick').click(function () {
                    id = $(this).prop('id');
                    pos_sem = id.split('_')[2];
                    datos = {
                        _token: '{{csrf_token()}}',
                        modulo: mod,
                        variedad: $('#filtro_predeterminado_variedad').val(),
                        desde: $('#semana_' + pos_sem).val(),
                        hasta: $('#semana_' + pos_sem).val(),
                        id_html: id,
                        get_obj: true,
                    };

                    $('#' + id).html('');
                    $('#' + id).LoadingOverlay('show');
                    $.post('{{url('proy_cosecha/actualizar_proyecciones')}}', datos, function (retorno) {
                        id = retorno.id_html;
                        var text = '';
                        if (retorno.model['tipo'] == 'T') {
                            proyectados = retorno.model['proyectados'] != "" ? retorno.model['proyectados'] : 0;
                            text += '<strong style="font-size: 0.8em; margin-bottom: 0">' + proyectados + '</strong>';
                        } else {
                            info = retorno.model['info'];
                            text += '<p style="margin-top: 0; margin-bottom: 0">' + info + '</p>';
                        }
                        cosechados = retorno.model['cosechados'];
                        if (cosechados > 0) {
                            text += '<p style="margin-top: 0; margin-bottom: 0">' +
                                '<strong style="font-size: 0.8em">' + cosechados + ' </strong>' +
                                '</p>';
                        }
                        $('#' + id).html(text);
                    }, 'json').fail(function (retorno) {
                        console.log(retorno);
                        alerta_errores(retorno.responseText);
                    }).always(function () {
                        $('#' + id).LoadingOverlay('hide');
                    });
                }).addClass('mouse-hand').css('background-color', 'rgba(55, 222, 0, 0.5)');
            })
        } else {
            var all = $('.checkbox_modulo');
            var selected = [];
            for (i = 0; i < all.length; i++) {
                if ($('#' + all[i].id).prop('checked') == true) {
                    selected.push(all[i].id.substr(16));
                }
            }

            for (i = 0; i < selected.length; i++) {
                mod = selected[i];
                $('.celda_modulo_' + mod).each(function (pos) {
                    id = $('.celda_modulo_' + mod)[pos].id;
                    $('#' + id).removeAttr('onclick').click(function () {
                        id = $(this).prop('id');
                        pos_sem = id.split('_')[2];
                        datos = {
                            _token: '{{csrf_token()}}',
                            modulo: mod,
                            variedad: $('#filtro_predeterminado_variedad').val(),
                            desde: $('#semana_' + pos_sem).val(),
                            hasta: $('#semana_' + pos_sem).val(),
                            id_html: id,
                            get_obj: true,
                        };

                        $('#' + id).html('');
                        $('#' + id).LoadingOverlay('show');
                        $.post('{{url('proy_cosecha/actualizar_proyecciones')}}', datos, function (retorno) {
                            id = retorno.id_html;
                            var text = '';
                            if (retorno.model['tipo'] == 'T') {
                                proyectados = retorno.model['proyectados'] != "" ? retorno.model['proyectados'] : 0;
                                text += '<strong style="font-size: 0.8em; margin-bottom: 0">' + proyectados + '</strong>';
                            } else {
                                info = retorno.model['info'];
                                text += '<p style="margin-top: 0; margin-bottom: 0">' + info + '</p>';
                            }
                            cosechados = retorno.model['cosechados'];
                            if (cosechados > 0) {
                                text += '<p style="margin-top: 0; margin-bottom: 0">' +
                                    '<strong style="font-size: 0.8em">' + cosechados + ' </strong>' +
                                    '</p>';
                            }
                            $('#' + id).html(text);
                        }, 'json').fail(function (retorno) {
                            console.log(retorno);
                            alerta_errores(retorno.responseText);
                        }).always(function () {
                            $('#' + id).LoadingOverlay('hide');
                        });
                    }).addClass('mouse-hand').css('background-color', 'rgba(55, 222, 0, 0.5)');
                })
            }
        }
    }

    function actualizar_datos() {
        var all = $('.check_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(13));
            }
        }

        all = $('.checkbox_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(16));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                semanas: semanas,
                modulos: modulos,
            };
            get_jquery('{{url('proy_cosecha/actualizar_datos')}}', datos, function (retorno) {
                modal_view('modal-view_actualizar_datos', retorno, '<i class="fa fa-fw fa-edit"></i> Actualizar datos', true, false, '{{isPC() ? '50%' : ''}}');
            });
        }
    }

    function mover_fechas() {
        var all = $('.check_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(13));
            }
        }

        all = $('.checkbox_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(16));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                semanas: semanas,
                modulos: modulos,
            };
            get_jquery('{{url('proy_cosecha/mover_fechas')}}', datos, function (retorno) {
                modal_view('modal-view_mover_cosecha', retorno, '<i class="fa fa-fw fa-edit"></i> Mover cosecha', true, false, '{{isPC() ? '50%' : ''}}');
            });
        }
    }

    function mover_cosecha() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                mover: $('#cosecha').val(),
                check_save_proy: $('#check_save_proyeccion').prop('checked'),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
                semana_hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_mover_cosecha').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/mover_cosecha')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_mover_cosecha').LoadingOverlay('hide');
            });
        }
    }

    function mover_inicio_proy() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                mover: $('#ini_proy').val(),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
                semana_hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_mover_inicio_proy').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/mover_inicio_proy')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_mover_inicio_proy').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_tipo() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                tipo: $('#tipo').val(),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            $('#tr_actualizar_tipo').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_tipo')}}', datos, function (retorno) {
                listar_proyecciones_cosecha('celda_button_tipo');
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_tipo').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_plantas_iniciales() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                plantas_iniciales: $('#plantas_iniciales').val(),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            $('#tr_actualizar_plantas_iniciales').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_plantas_iniciales')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_plantas_iniciales').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_desecho() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                desecho: $('#desecho').val(),
                check_save_semana: $('#check_save_semana').prop('checked'),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            $('#tr_actualizar_desecho').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_desecho')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_desecho').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_tallos_planta() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                tallos_planta: $('#tallos_x_planta').val(),
                check_save_semana: $('#check_save_semana').prop('checked'),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            $('#tr_actualizar_tallos_planta').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_tallos_planta')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_tallos_planta').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_tallos_ramo() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                tallos_ramo: $('#tallos_x_ramo').val(),
                check_save_semana: $('#check_save_semana').prop('checked'),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            $('#tr_actualizar_tallos_ramo').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_tallos_ramo')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_tallos_ramo').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_curva() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                curva: $('#curva').val(),
                check_save_semana: $('#check_save_semana').prop('checked'),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
                semana_hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_actualizar_curva').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_curva')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_curva').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_semana_cosecha() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        all = $('.check_id_modulo');
        var modulos = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                modulos.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0 && modulos.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                semana_cosecha: $('#semana_cosecha').val(),
                check_save_semana: $('#check_save_semana').prop('checked'),
                semanas: semanas,
                modulos: modulos,
                variedad: $('#filtro_predeterminado_variedad').val(),
                semana_hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_actualizar_semana_cosecha').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_semana_cosecha')}}', datos, function (retorno) {
                for (i = 0; i < modulos.length; i++) {
                    get_row_byModulo(modulos[i]);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_semana_cosecha').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_semana_cosecha_siembra() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                semana_cosecha_siembra: $('#semana_cosecha_siembra').val(),
                semanas: semanas,
                variedad: $('#filtro_predeterminado_variedad').val(),
                semana_hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_actualizar_semana_cosecha_siembra').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_semana_cosecha_siembra')}}', datos, function (retorno) {
                listar_proyecciones_cosecha('celda_button_semana_cosecha_siembra');
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_semana_cosecha_siembra').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_tallos_planta_siembra() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                tallos_planta_siembra: $('#tallos_planta_siembra').val(),
                semanas: semanas,
                variedad: $('#filtro_predeterminado_variedad').val(),
                semana_hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_actualizar_tallos_planta_siembra').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_tallos_planta_siembra')}}', datos, function (retorno) {
                listar_proyecciones_cosecha('celda_button_tallos_planta_siembra');
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_tallos_planta_siembra').LoadingOverlay('hide');
            });
        }
    }

    function actualizar_tallos_ramo_siembra() {
        var all = $('.check_id_semana');
        var semanas = [];
        for (i = 0; i < all.length; i++) {
            if ($('#' + all[i].id).prop('checked') == true) {
                semanas.push(all[i].id.substr(10));
            }
        }

        if (semanas.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                tallos_ramo_siembra: $('#tallos_ramo_siembra').val(),
                semanas: semanas,
                variedad: $('#filtro_predeterminado_variedad').val(),
                semana_hasta: $('#filtro_predeterminado_hasta').val(),
            };
            $('#tr_actualizar_tallos_ramo_siembra').LoadingOverlay('show');
            $.post('{{url('proy_cosecha/actualizar_tallos_ramo_siembra')}}', datos, function (retorno) {
                listar_proyecciones_cosecha('celda_button_tallos_ramo_siembra');
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_actualizar_tallos_ramo_siembra').LoadingOverlay('hide');
            });
        }
    }

    function new_proyeccion(id_semana) {
        datos = {
            id_semana: id_semana,
        }
        get_jquery('{{url('proy_cosecha/new_proyeccion')}}', datos, function (retorno) {
            modal_view('modal_view-new_proyeccion', retorno, '<i class="fa fa-fw fa-plus"></i> Nueva Proyección', true, false,
                '{{isPC() ? '50%' : ''}}');
        });
    }

    function new_cultivo(id_semana) {
        datos = {
            id_semana: id_semana,
            id_variedad: '{{$variedad_model->id_variedad}}',
        };
        get_jquery('{{url('proy_cosecha/new_cultivo')}}', datos, function (retorno) {
            modal_view('modal_view-new_cultivo', retorno, '<i class="fa fa-fw fa-plus"></i> Nuevo cultivo', true, false,
                '{{isPC() ? '50%' : ''}}');
        });
    }

    function update_config(id) {
        datos = {
            _token: '{{csrf_token()}}',
            campo: id,
            valor: $('#' + id).val()
        };
        post_jquery('{{url('proy_cosecha/update_config')}}', datos, function () {
        }, 'collapseConfig');
    }

    function actualizar_proyecciones_job(mod) {
        datos = {
            _token: '{{csrf_token()}}',
            modulo: mod,
            variedad: $('#filtro_predeterminado_variedad').val(),
            semana_desde: $('#semana_desde_listado').val()
        };
        $('#tr_modulo_' + mod).LoadingOverlay('show');
        $.post('{{url('proy_cosecha/actualizar_proyecciones_job')}}', datos, function () {

        }).always(function () {
            $('#tr_modulo_' + mod).LoadingOverlay('hide');
        });
    }
</script>

<style>
    #div_content_fixed {
        overflow-x: scroll;
        overflow-y: scroll;
        width: 100%;
        max-height: 450px;
        border: 2px solid #9d9d9d;
    }

    #tabla_proyecciones {
        border-spacing: 0 !important;
        border: 1px solid #9d9d9d !important;
    }

    #tabla_proyecciones th, #tabla_proyecciones td {
        border-spacing: 0;
    }

    #tabla_proyecciones thead .fila_fija_1 th {
        background-color: #e9ecef !important;
        border: 1px solid #9d9d9d !important;
        z-index: 9;
        position: sticky;
        top: 0;
    }

    #tabla_proyecciones thead .fila_fija_2 th {
        background-color: #e9ecef !important;
        border: 1px solid #9d9d9d !important;
        z-index: 9;
        position: sticky;
        top: 22px;
    }

    #tabla_proyecciones tr .columna_fija_1 {
        background-color: #e9ecef !important;
        border: 1px solid #9d9d9d !important;
        z-index: 8;
        position: sticky;
        left: 0 !important;
    }

    #tabla_proyecciones tr .columna_fija_2 {
        background-color: #e9ecef !important;
        border: 1px solid #9d9d9d !important;
        z-index: 8;
        position: sticky;
        left: 15px;
    }

    #celda_opciones_semanas {
        left: 0 !important;
        top: 0 !important;
        z-index: 10 !important;
    }

    #celda_semanas {
        left: 15px !important;
        top: 0 !important;
        z-index: 10 !important;
    }

    #celda_opciones_modulos {
        left: 0 !important;
        top: 22px !important;
        z-index: 10 !important;
    }

    #celda_modulos {
        left: 15px !important;
        top: 22px !important;
        z-index: 10 !important;
    }
</style>