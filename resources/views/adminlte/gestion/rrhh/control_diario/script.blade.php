<script>

$(function(){
    $("#vista_actual").val(location.pathname)
    $("#fecha_search_control_diario").attr("max", new Date().toISOString().split("T")[0])
    obtener_control_diario(true)
})

function obtener_control_diario(buscar_actividad=false){

    let data= {
        id_area: $("#id_search_area").val(),
        id_actividad: $("#id_search_actividad").val(),
        id_mano_obra: $("#id_search_mano_obra").val(),
        fecha: $("#fecha_search_control_diario").val()
    }

    $.LoadingOverlay('show');

    $.get('{{url('control_diario/buscar_control_diario')}}', data, function (retorno) {

        $('#div_control_diario').html(retorno)
        $.LoadingOverlay('hide');
        buscar_actividad && obtener_atividad_mano_obra('actividad')

    }).fail(function (retorno) {
        console.log(retorno);
        alerta_errores(retorno.responseText);
    }).always(function () {
        $.LoadingOverlay("hide");
    })

}

function obtener_atividad_mano_obra(tipo) {

    let data = {
        tipo,
        id_search_area: $('#id_search_area').val(),
        id_search_actividad: $('#id_search_actividad').val(),
    }

    get_jquery('{{ url('control_diario/obtener_actividad_mano_obra') }}', data, retorno => {

        let html = `<option value="">Todas</option>`

        if(tipo == 'actividad'){

            retorno.forEach(e => {
                html += `<option value="${e.id_actividad}">${e.nombre}</option>`
            })
            $('#id_search_actividad').html(html)

        }else if(tipo == 'mano_obra'){

            retorno.forEach(e => {
                html += `<option value="${e.id_mano_obra}">${e.mano_obra.nombre}</option>`
            })
            $('#id_search_mano_obra').html(html)

        }
    },  tipo == 'actividad' ? 'id_search_actividad' : 'id_search_mano_obra' )

}

function add_control_personal(identificacion =null){

    let data= {
        fecha: $("#fecha_search_control_diario").val(),
        identificacion,
        hora_desde: $("#desde_masivo").val(),
        hora_hasta: $("#hasta_masivo").val(),
    }

    get_jquery('{{ url('control_diario/add_control_personal') }}', data, retorno => {
        console.log('retorno', retorno)

        if($("#tabla_control_personal tbody tr").length){
            $("#tabla_control_personal tbody tr:first").before(retorno)
        }else{
            $("#tabla_control_personal tbody").append(retorno)
        }
        
    }, 'div_control_diario' )

}

function seleccionar_personal(select){

    let identificacion = $(select).children('option:selected').data('identificacion')
    let id_personal_detalle = $(select).children('option:selected').data('id-personal-detalle')

    $(select).parent().next().attr('id', 'persona_detalle_' + identificacion)
    $(select).parent().parent().find('td#persona_detalle_' + identificacion).text(identificacion)
    $(select).parent().parent().find('input.id_personal_detalle').val(id_personal_detalle)

}

function seleccionar_todo_personal(check){

    let check_personal = $("input.check_select_personal")
    $(check).is(':checked') ? check_personal.prop('checked', true) : check_personal.prop('checked', false)

}

function set_horario_personal(tipo, input){

    let check_personal = $("input.check_select_personal")

    if (tipo == 'desde') {
        $.each(check_personal,(i,j) => {
            $(j).is(':checked') && $(j).parent().next().next().next().find('input.input-date-cd').val($(input).val())
        })
    } else {
        $.each(check_personal,(i,j) => {
            $(j).is(':checked') && $(j).parent().next().next().next().next().find('input.input-date-ch').val($(input).val())
        })
    }
}

function hasAttendanceOverlap(asistencias) {
  // Objeto auxiliar para almacenar las fechas de las asistencias por personalId
  const fechasPorPersonal = {};

  // Agrupa las fechas de las asistencias por personalId
  for (let i = 0; i < asistencias.length; i++) {
    const asistencia = asistencias[i];
    if (asistencia.desde && asistencia.hasta) {
      const personalId = asistencia.personalId;
      if (!fechasPorPersonal[personalId]) {
        fechasPorPersonal[personalId] = [];
      }
      const desde = Date.parse(`01/01/2000 ${asistencia.desde}`);
      const hasta = Date.parse(`01/01/2000 ${asistencia.hasta}`);
      fechasPorPersonal[personalId].push({desde, hasta});
    }
  }
  // Compara las fechas almacenadas en el objeto `fechasPorPersonal`
  for (const personalId in fechasPorPersonal) {
    const fechas = fechasPorPersonal[personalId];
    for (let i = 0; i < fechas.length; i++) {
      console.log(`comparando verticalmente ${personalId} `+i);
      const {desde: desde1, hasta: hasta1} = fechas[i];
      for (let j = i + 1; j < fechas.length; j++) {
        console.log(`comparando horizontalmente ${personalId} `+j);
        const {desde: desde2, hasta: hasta2} = fechas[j];
        if (desde1 < hasta2 && hasta1 > desde2) {
          return personalId;
        }
      }
    }
  }

  return false;
}

function set_time_lunch_masivo(tipo, lunch_masivo) {
    // alert(lunch_masivo.value);
    const check_personal = $("input.check_select_personal");
    const desde_masivo = $('#desde_masivo').val();
    const hasta_masivo = $('#hasta_masivo').val();

    $.each(check_personal,(i,j)=>{
        let desde_input = $(j).parent().next().next().next().find('input.input-date-cd').val();
        if (desde_input !== "") {
            desde_input = new Date("2000-01-01T" + desde_input); // crea un objeto Date con la hora y fecha
            desde_input = desde_input.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: false}); // formatea la hora sin los segundos
        }
        let hasta_input = $(j).parent().next().next().next().next().find('input.input-date-ch').val();
        if (hasta_input !== "") {
            hasta_input = new Date("2000-01-01T" + hasta_input); // crea un objeto Date con la hora y fecha
            hasta_input = hasta_input.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: false}); // formatea la hora sin los segundos
        }
        let check_lunch = $(j).parent().next().next().next().next().next().find('input.check_active_lunch');
        if ($(j).is(':checked') && !$(check_lunch).is(':disabled') && desde_input === desde_masivo && hasta_input === hasta_masivo ) {
            if (lunch_masivo.value === "1") {
                check_lunch.prop('checked', true);
            } else {
                check_lunch.prop('checked', false);
            }
        }
    });
}

function store_control_asistencia(){
    let validate= true;
    let datos=[]
    let attendances = [];
    $.each($("input.input-date-cd"), function(i, j) {
        if ($(j).parent().parent().find('input.check_select_personal').is(':checked')) {
            $(j).attr("style","");
            attendances.push({
                personalId: $(j).attr("data-identification"),
                desde: $(j).val(), 
                hasta: $(j).parent().next().find('input.input-date-ch').val(),
            });
            if (!$(j).val()) {
                $(j).attr("style","border: 2px solid #D6006E;");
                validate= false;
            }
            datos.push({
                id_control_personal: $(j).parent().parent().find('input.input_control_personal').val(),
                id_personal_detalle: $(j).parent().parent().find('input.id_personal_detalle').val(),
                desde: $(j).val(),
                hasta: $(j).parent().next().find('input.input-date-ch').val(),
                check_active_lunch: $(j).parent().next().next().find('input.check_active_lunch').prop('checked'),
                id_mano_obra: $(j).parent().parent().find('select.id_mano_obra').val()
            });
        }
    });
    let overlapError = hasAttendanceOverlap(attendances);
    // attendances.some((attendance, i) => {
    //     console.log("escaneando");
        
    //     return overlapError;
    // });
    if (validate) {
        if (!overlapError) {
            let data= {
            _token: '{{csrf_token()}}',
            fecha: $("#fecha_search_control_diario").val(),
            datos
            }
            post_jquery_m('{{url('control_diario/store_control_personal')}}', data, () => {
                obtener_control_diario()
            });
        } else {
            alerta_errores(`Ha ocurrido un error, por favor verifique que las horas no se solapen en el personal con cédula "${overlapError}".`);
        }
    } else {
        alerta_errores("Ha ocurrido un error, por favor verifique que no existan campos vacíos");
    }
}

function clone_asistencia(identificacion =null, obj = null){
const id_mano_obra= $(obj).parent().prev().find("select.id_mano_obra").val();
let data= {
    fecha: $("#fecha_search_control_diario").val(),
    identificacion,
    hora_desde: $("#desde_masivo").val(),
    hora_hasta: $("#hasta_masivo").val(),
    id_mano_obra,
}
get_jquery('{{ url('control_diario/add_control_personal') }}', data, retorno => {
    // console.log('retorno', retorno)

    if($("#tabla_control_personal tbody tr").length){
        $("#tabla_control_personal tbody tr:first").before(retorno)
    }else{
        $("#tabla_control_personal tbody").append(retorno)
    }
    
}, 'div_control_diario' )

}

function delete_asistencia(id_control_personal){

    let data= {
        _token: '{{csrf_token()}}',
        id_control_personal
    }
    post_jquery_m('{{url('control_diario/delete_control_personal')}}', data, function () {
       obtener_control_diario()
    })

}

function buscarPersonal(event) {
  const value = event.target.value.toLowerCase();
  $("table#tabla_control_personal tbody tr").filter(function() {
    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
  });
}

function modal_camara(){

    $.LoadingOverlay('show');

    $.get('control_diario/modal_foto', {}, function (retorno) {
        modal_view('modal_view_foto_asistencia', retorno, '<i class="fa fa-fw fa-picture-o"></i> Foto de asistencia', true, false, '{{isPC() ? '75%' : ''}}');
    });
    $.LoadingOverlay('hide');
}
</script>
