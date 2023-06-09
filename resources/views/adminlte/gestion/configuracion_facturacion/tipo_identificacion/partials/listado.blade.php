<div id="table_tipo_identificacion">
    @if(sizeof($listado)>0)
        <table width="100%" class="table table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
               id="table_content_tipo_identificacion">
            <thead>
            <tr style="background-color: #dd4b39; color: white">
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    NOMBRE
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    CÓDIGO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    OPCIONES
                </th>
            </tr>
            </thead>
           @foreach($listado as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')"
                    class="{{$item->estado == 1 ? '':'error'}}" id="row_marcas_{{$item->id_tipo_identificacion}}">
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->nombre}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->codigo}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <a href="javascript:void(0)" class="btn btn-default btn-xs" title="Editar tipo_identificacion"
                           onclick="add_tipo_identificacion('{{$item->id_tipo_identificacion}}')">
                            <i class="fa fa-fw fa-pencil" style="color: black"></i>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-{{$item->estado == 0 ? 'danger' : 'success' }} btn-xs" title="Eliminar tipo_identificacion"
                           onclick="actualizar_estado_tipo_identificacion('{{$item->id_tipo_identificacion}}','{{$item->estado}}')">
                            <i class="fa fa-fw fa-{{$item->estado == 0 ? 'lock' : 'unlock-alt' }}" style="color: black"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
        <div id="pagination_listado_tipo_identificacion">
        </div>
    @else
        <div class="alert alert-info text-center" style="margin-top: 20px">No se han encontrado coincidencias</div>
    @endif
</div>
