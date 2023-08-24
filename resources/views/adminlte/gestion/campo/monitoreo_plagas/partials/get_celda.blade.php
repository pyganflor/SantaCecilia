@php
    $mis_plagas = [];
    foreach ($listado as $plaga) {
        $existe = false;
        foreach ($mis_plagas as $p) {
            if ($p->id_plaga == $plaga->id_plaga) {
                $existe = true;
            }
        }
        if (!$existe) {
            $mis_plagas[] = $plaga;
        }
    }
@endphp
@foreach ($mis_plagas as $p)
    @php
        switch ($p->incidencia) {
            case 'alta':
                $bg_plaga = 'bg-yura_danger';
                break;
            case 'media':
                $bg_plaga = 'bg-yura_warning';
                break;
            case 'baja':
                $bg_plaga = 'bg-yura_primary';
                break;
        
            default:
                $bg_plaga = '';
                break;
        }
    @endphp
    <tr style="height: 30px; font-size: 0.8em">
        <th class="text-right {{ $bg_plaga }}" style="padding-right: 2px;">
            {{ str_limit($p->plaga->nombre, 5) }}
        </th>
        <th class="text-center {{ $bg_plaga }}" style="width: 50px; padding-left: 2px;">
            {{ explode(' del ', convertDateToText($p->fecha))[0] }}
        </th>
        <th class="text-right" style="width: 30px; border-color: #9d9d9d">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-yura_default" title="Eliminar Incidencia"
                    onclick="delete_incidencia('{{ $p->id_ciclo_plaga }}', '{{ $ciclo }}')">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </div>
        </th>
    </tr>
@endforeach
