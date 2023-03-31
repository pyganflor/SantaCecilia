<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_tipo_contratos">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Nombre</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_tipo_contrato()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_tipo_contrato}}">{{$item->nombre}}</span>
                <input type="text" id="input_nombre_{{$item->id_tipo_contrato}}" value="{{$item->nombre}}" class="text-center hidden" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_tipo_contrato('{{$item->id_tipo_contrato}}')" 
                        id="btn_edit_tipo_contrato_{{$item->id_tipo_contrato}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_tipo_contrato_{{$item->id_tipo_contrato}}"
                     onclick="update_tipo_contrato('{{$item->id_tipo_contrato}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar"
                                    onclick="actualizar_tipo_contrato('{{$item->id_tipo_contrato}}','{{$item->estado}}')"
                                    id="boton_cargo_{{$item->id_cargo}}">
                                <i class="fa fa-fw {{$item->estado == 1 ? 'fa-trash' : 'fa-unlock'}}" style="color: white"
                                   id="icon_tipo_contrato_{{$item->id_tipo_contrato}}"></i>
                            </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    var cant_nuevos = 0;

    function editar_tipo_contrato(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_tipo_contrato_'+id).addClass('hidden');
        $('#btn_update_tipo_contrato_'+id).removeClass('hidden');
    }

    function add_tipo_contrato() {
        cant_nuevos++;
        $('#tabla_tipo_contratos').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_tipo_contrato_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_tipo_contrato(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_tipo_contrato(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_tipo_contrato_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_tipo_contrato')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_tipo_contrato(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_tipo_contrato: id,
            nombre: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_tipo_contrato')}}', datos, function () {
            listar_parametro();
        });
    }
</script>
