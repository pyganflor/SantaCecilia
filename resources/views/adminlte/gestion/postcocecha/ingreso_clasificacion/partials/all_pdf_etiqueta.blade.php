@foreach ($datos as $pos => $d)
    @for ($i = 1; $i <= $d['cantidad']; $i++)
        <table style="position: relative; font-family: Arial, Helvetica, sans-serif;">
            <tr style="padding: 0">
                <th style="text-align: center; padding: 0;">
                    {!! $barCode->getBarcode($d['inventario_frio']->id_inventario_frio, $barCode::TYPE_CODE_128, 2) !!}
                </th>
                <th style="text-align: center; padding: 0;">
                    {{ $d['variedad']->nombre }}
                </th>
                <td style="text-align: left;">
                    <b>{{ $d['longitud']->nombre }}</b><small><sup>cm</sup></small>
                    <br>
                    {{ $d['tallos_x_ramo'] }}<small><sup>stems</sup></small>
                </td>
            </tr>
            <tr style="padding: 0">
                <td style="text-align: left; font-size: 10px; padding: 0">
                    {{ getDias(TP_LETRA)[transformDiaPhp(date('w', strtotime($d['fecha'])))] }}{{ intVal(substr($d['fecha'], 5, 2)) }}.{{ substr($d['fecha'], 8, 2) }}
                </td>
                <td style="text-align: center; font-size: 10px; padding: 0" colspan="2">
                    PRODUCT of ECUADOR
                </td>
        </table>

        <div style="page-break-after:always;"></div>
    @endfor
@endforeach

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
