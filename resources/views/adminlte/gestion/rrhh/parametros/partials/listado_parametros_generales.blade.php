<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_parametros_generaless">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Minutos de Almuerzo</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            Editar
        </th>
    </tr>
    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_parametros_generales}}">{{$item->rrhh_minutos_almuerzo}}</span>
                <input type="number" max="600" id="input_nombre_{{$item->id_parametros_generales}}" value="{{$item->rrhh_minutos_almuerzo}}" class="text-center hidden" oninput="this.value = this.value.trim().replace(/^0+/, '').replace(/[^0-9]/g, '');" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_parametros_generales('{{$item->id_parametros_generales}}')" 
                        id="btn_edit_parametros_generales_{{$item->id_parametros_generales}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_parametros_generales_{{$item->id_parametros_generales}}"
                     onclick="update_parametros_generales('{{$item->id_parametros_generales}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    var cant_nuevos = 0;

    function editar_parametros_generales(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_parametros_generales_'+id).addClass('hidden');
        $('#btn_update_parametros_generales_'+id).removeClass('hidden');
    }

    function add_parametros_generales() {
        cant_nuevos++;
        $('#tabla_parametros_generaless').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="rrhh_minutos_almuerzo_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_parametros_generales(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_parametros_generales(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            rrhh_minutos_almuerzo: $('#rrhh_minutos_almuerzo_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_parametros_generales')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_parametros_generales(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_parametros_generales: id,
            rrhh_minutos_almuerzo: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_parametros_generales')}}', datos, function () {
            listar_parametro();
        });
    }
</script>