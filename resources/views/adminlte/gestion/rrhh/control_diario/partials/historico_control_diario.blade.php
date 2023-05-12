<div class="row">
    @if(!$asignacionMasivaHoras)
        <div class="col-md-12 col-xs-12">
            <h4 title="Puedes prellenar las horas de asistencia en estos campos para ahorrar tiempo en el registro individual">Asistencia rápida <i class="fa fa-info-circle"></i></h4>
        </div>
        <div class="col-md-2 col-xs-6">
            <div class="form-group input-group">
                <span class="input-group-addon bg-yura_dark">Desde</span>
                <input type="time" class="form-control" id="desde_masivo"
                        onkeyup="set_horario_personal('desde',this)"
                        onchange="set_horario_personal('desde',this)">
            </div>
        </div>
        <div class="col-md-2 col-xs-6">
            <div class="form-group input-group">
                <span class="input-group-addon bg-yura_dark">Hasta</span>
                <input type="time" class="form-control" id="hasta_masivo"
                        onkeyup="set_horario_personal('hasta',this)"
                        onchange="set_horario_personal('hasta',this)">
            </div>
        </div>
        <div class="col-md-2 col-xs-6">
            <div class="form-group input-group">
                <span class="input-group-addon bg-yura_dark">Almuerzo</span>
                <div class="btn-group" id="status" data-toggle="buttons">
                    <label class="btn btn-default btn-on active">
                    <input type="radio" value="1" name="time_lunch_masivo" onchange="set_time_lunch_masivo('si',this)" checked="checked">SI</label>
                    <label class="btn btn-default btn-off">
                    <input type="radio" value="0" name="time_lunch_masivo" onchange="set_time_lunch_masivo('no',this)">NO</label>
                </div>
            </div>
        </div>
    @endif
    <div class="col-md-3 col-xs-6">
        <div class="div-parent-buscador">
            Buscador de personal:
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <input type="text" class="form-control" placeholder="Búsqueda de personal" id="busqueda_personal" name="busqueda_personal" onkeyup="buscarPersonal(event)">
    </div>
<div class="col-md-12 col-xs-12">
<span style="font-size: 12px">Personal registrado: <span style="font-weight:bold">{{count($personal)}}</span> - <span>Duración del almuerzo: <span style="font-weight:bold">{{ $ParametrosGenerales->rrhh_minutos_almuerzo >= 60 ? ($ParametrosGenerales->rrhh_minutos_almuerzo/60)." hora".(($ParametrosGenerales->rrhh_minutos_almuerzo > 60) ? "s" : "") : $ParametrosGenerales->rrhh_minutos_almuerzo." minutos" }}</span></span></span>
    <table width="100%" class="table-responsive table-bordered" id="tabla_control_personal" style="font-size: 0.8em; border-color: #9d9d9d">
        <thead>
            <tr id="th_fija_top_0">
                @if(!$asignacionMasivaHoras)
                    <th class="text-center th_yura_green">
                        <input type="checkbox" name="seleccionar_todo_personal" onchange="seleccionar_todo_personal(this)" checked>
                    </th>
                @endif
                <th class="text-center th_yura_green">PERSONAL</th>
                <th class="text-center th_yura_green">IDENTIFICACIÓN</th>
                <th class="text-center th_yura_green">DESDE</th>
                <th class="text-center th_yura_green">HASTA</th>
                <th class="text-center th_yura_green">ALMUERZO</th>
                <th class="text-center th_yura_green">LABOR</th>
                <th class="text-center th_yura_green">OPCIONES</th>
            </tr>
        </thead>
        <tbody>
            @php
            $current_id = "";
            @endphp
            @forelse ($personal as $p)
                <tr id="row_planta_4" style="">
                    <input type="hidden" class="id_personal_detalle" value="{{ $p->id_personal_detalle }}">
                    <input type="hidden" class="input_control_personal" value="{{ $p->id_control_personal }}">
                    @if(!$asignacionMasivaHoras)
                        <td style="border-color: #9d9d9d" class="text-center">
                            <input type="checkbox" class="check_select_personal" {{ isset($p->id_control_personal) ? 'checked' : '' }}>
                        </td>
                    @endif
                    <td style="border-color: #9d9d9d" class="text-center">{{$p->nombre}} {{$p->apellido}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$p->cedula_identidad}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <input type="time" data-identification="{{$p->cedula_identidad}}" class="w-100 input-date-cd"
                                id="cp-{{$p->id_personal_detalle}}" value="{{$p->desde}}">
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <input type="time" class="w-100 input-date-ch"
                                id="cp-{{$p->id_personal_detalle}}" value="{{$p->hasta}}">
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        @php
                        if ($current_id != $p->cedula_identidad) {
                            $current_id = $p->cedula_identidad;
                            $first_time_id = true;
                        } else {
                            $first_time_id = false;
                        }
                        @endphp
                        <input type="checkbox" class="check_active_lunch" {{ !isset($p->id_control_personal) ? 'checked' : ($p->time_lunch > 0 ? ( !$first_time_id ? '' : 'checked') : '') }} {{ !$first_time_id ? 'disabled' : ''}}>
                    </td>
                    <td  style="border-color: #9d9d9d" class="text-center">
                        <select class="w-100 id_mano_obra" style="height: 22px;">
                            <option value="">Seleccione</option>
                            @foreach ($ManoObras as $mo)
                                @php
                                $id_mano_obra_actual = empty($p->id_mano_obra) ? $p->id_mano_obra_first : $p->id_mano_obra;
                                @endphp
                                <option value="{{$mo->id_mano_obra}}"
                                    {{$mo->id_mano_obra === $id_mano_obra_actual ? 'selected' : ''}}>{{$mo->nombre}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td  style="border-color: #9d9d9d" class="text-center">
                        @isset($p->id_control_personal)
                            <button class="btn btn-xs btn-info rounded-4" style="border-radius:18px"
                                    title="Duplicar asistencia" onclick="clone_asistencia('{{$p->cedula_identidad}}',$(this))">
                                <i class="fa fa-clone"></i>
                            </button>
                            <button class="btn btn-xs btn-danger rounded-4" style="border-radius:18px"
                                    title="Eliminar asistencia" onclick="delete_asistencia('{{$p->id_control_personal}}')">
                                <i class="fa fa-trash"></i>
                            </button>
                        @endisset
                    </td>
                </tr>
            @empty
                <div  class="alert alert-info text-center">
                    No se encontraron personal con los filtros seleccionados
                </div>
            @endforelse
        </tbody>
    </table>
</div>
<div class="col-md-3 col-xs-6 pt-20">
    <button class="btn btn-md th_yura_green" onclick="store_control_asistencia()">
        <i class="fa fa-floppy-o"></i>
        Guardar cambios
    </button>
</div>
</div>
<style>

    .btn-camera{
        position: absolute;
        left: 28%;
        bottom: 26px;
        padding: 16px;
        border-radius: 100%;
        background: white;
        width: 55px;
        opacity: .8;
        border: none;
    }

    .btn-camera-flip{
        position: absolute;
        left: 66%;
        bottom: 26px;
        padding: 16px;
        border-radius: 100%;
        background: white;
        width: 55px;
        opacity: .8;
        border: none;
    }
    .pt-20 {
        padding-top: 20px;
    }

    #tabla_control_personal tbody td, #tabla_control_personal tbody th { 
        padding: 3px !important;
    }
    
    .div-parent-buscador {
        display: flex;
        justify-content: right;
        align-items: center;
        height: 34px;
        min-height: 34px;
    }
    .btn-default.btn-on.active {
        background-color: #5bc0de;
        color: white;
        border-radius: 0px;
        outline: none;
        outline-offset: 0;
    }
    .btn-default.btn-off.active{
        background-color: #dd4b39;
        color: white;
        border-radius: 0px;
        outline: none;
        outline-offset: 0;
    }


</style>