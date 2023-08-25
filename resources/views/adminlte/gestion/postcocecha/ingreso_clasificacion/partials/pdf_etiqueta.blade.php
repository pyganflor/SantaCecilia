@for ($i = 1; $i <= $datos['cantidad']; $i++)
    <table style="position: relative; top: -10px; left: 10px; font-family: Arial, Helvetica, sans-serif;">
        <tr style="padding: 0">
            <th style="text-align: center; padding: 0;">
                {!! $barCode->getBarcode($datos['inventario_frio']->id_inventario_frio, $barCode::TYPE_CODE_128, 2) !!}
            </th>
            <th style="text-align: center; padding: 0;">
                {{ $datos['variedad']->nombre }}
            </th>
            <td style="text-align: left;">
                <b>{{ $datos['longitud']->nombre }}<small><sup>cm</sup></small></b>
                <br>
                <b>{{ $datos['tallos_x_ramo'] }}<small><sup>stems</sup></small></b>
            </td>
        </tr>
        <tr style="padding: 0">
            <th style="text-align: left; font-size: 10px; padding: 0">
                {{ getDias(TP_LETRA)[transformDiaPhp(date('w', strtotime($datos['fecha'])))] }}{{ intVal(substr($datos['fecha'], 5, 2)) }}.{{ substr($datos['fecha'], 8, 2) }}
            </th>
            <th style="text-align: center; font-size: 10px; padding: 0" colspan="2">
                PRODUCT of ECUADOR
            </th>
    </table>

    @if ($i < $datos['cantidad'])
        <div style="page-break-after:always;"></div>
    @endif
@endfor

<style>
    div.bar div {
        margin: 0 auto;
    }

    .border {
        border: 1px solid;
    }

    table {
        border-collapse: collapse
    }
</style>
