<div style="position: relative; top: -35px; left: -30px; width: 505px">
    <table class="border-1px" style="font-size: 0.9em">
        <tr>
            <th style="width: 40%; font-size: 0.8em; text-align: left">
                {!! $barCode->getBarcode(str_pad($datos['pedido']->factura, 8, '0', STR_PAD_LEFT), $barCode::TYPE_CODE_128, 1) !!}
                INVOICE: {{ str_pad($datos['pedido']->factura, 8, '0', STR_PAD_LEFT) }}
            </th>
            <th style="text-align: left">
                ECSTACECILIA-ROSES
            </th>
        </tr>
    </table>
    <table>
        <tr>
            <td style="vertical-align: top;">
                <table>
                    <tr>
                        <td style="font-size: 0.6em; width: 70px">
                            CUSTOMER:
                        </td>
                        <td style="font-size: 0.6em">
                            <b>{{ $datos['pedido']->cliente->detalle()->nombre }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 0.6em">
                            COUNTRY:
                        </td>
                        <td style="font-size: 0.6em">
                            <b>{{ $datos['pedido']->consignatario->pais()->nombre }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 0.6em">
                            DAE:
                        </td>
                        <td style="font-size: 0.6em">
                            <b>{{ $datos['pedido']->codigo_dae }}</b>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td style="font-size: 0.6em; text-align: right; margin-right: 5px !important">
                            M.A.W.B:
                        </td>
                        <td style="font-size: 0.6em; text-align: left">
                            <b>{{ $datos['pedido']->guia_madre }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 0.6em; text-align: right; margin-right: 5px !important">
                            H.A.W.B:
                        </td>
                        <td style="font-size: 0.6em; text-align: left">
                            <b>{{ $datos['pedido']->guia_hija }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 0.6em; text-align: right; margin-right: 5px !important">
                            CARRIER:
                        </td>
                        <td style="font-size: 0.6em; text-align: left">
                            <b>{{ $datos['pedido']->agencia_carga->nombre }}</b>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 22%; vertical-align: bottom">
                <table>
                    <tr>
                        <th style="font-size: 0.6em; text-align: right; margin-right: 5px !important">
                            DATE
                        </th>
                        <th style="font-size: 0.6em; text-align: left">
                            {{ $datos['pedido']->fecha_pedido }}
                        </th>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="border-1px" style="font-size: 10px">
        <tr>
            <td class="border-1px text-center">
                #BOX
            </td>
            <td class="border-1px text-center">
                BOX.T
            </td>
            <td class="border-1px text-center">
                FLOWER
            </td>
            <td class="border-1px text-center">
                VARIETIES
            </td>
            <td class="border-1px text-center">
                NANDINA
            </td>
            <td class="border-1px text-center">
                LENGHT
            </td>
            <td class="border-1px text-center">
                BUNCH
            </td>
            <td class="border-1px text-center">
                STEMS
            </td>
            <td class="border-1px text-center">
                UNIT. $
            </td>
            <td class="border-1px text-center">
                TOTAL
            </td>
        </tr>
        @php
            $total_tallos = 0;
            $total_ramos = 0;
            $total_monto = 0;
        @endphp
        @foreach ($datos['pedido']->detalles as $pos_det => $det)
            @php
                $caja_frio = $det->caja_frio;
            @endphp
            @foreach ($caja_frio->detalles as $pos_item => $item)
                @php
                    $variedad = getVariedad($item->id_variedad);
                    $planta = $variedad->planta;
                    $total_tallos += $item->ramos * $item->tallos_x_ramo;
                    $total_ramos += $item->ramos;
                    $total_monto += $item->precio * $item->ramos * $item->tallos_x_ramo;
                @endphp
                <tr>
                    <td class="text-center border-1px">
                        {{ $pos_det + 1 }}
                    </td>
                    <td class="text-center border-1px">
                        {{ $caja_frio->tipo }}
                    </td>
                    <td class="text-center border-1px">
                        {{ $planta->nombre }}
                    </td>
                    <td class="text-center border-1px">
                        {{ $variedad->nombre }}
                    </td>
                    <td class="text-center border-1px">
                        {{ $planta->nandina }}
                    </td>
                    <th class="text-center border-1px">
                        {{ $item->longitud }}
                    </th>
                    <th class="text-center border-1px">
                        {{ $item->ramos }}
                    </th>
                    <th class="text-center border-1px">
                        {{ $item->ramos * $item->tallos_x_ramo }}
                    </th>
                    <td class="border-1px text-center">
                        {{ $item->precio }}
                    </td>
                    <td class="border-1px text-center">
                        {{ number_format($item->precio * $item->ramos * $item->tallos_x_ramo, 2) }}
                    </td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td class="text-center" colspan="6">
                TOTALS
            </td>
            <th class="border-1px text-center">
                {{ number_format($total_ramos) }}
            </th>
            <th class="border-1px text-center">
                {{ number_format($total_tallos) }}
            </th>
            <th class="border-1px text-center">
            </th>
            <th class="border-1px text-center">
                {{ number_format($total_monto, 2) }}
            </th>
        </tr>
    </table>
</div>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
    }

    .sin_margen {
        margin: 0;
        padding: 0;
    }

    .border-1px {
        border: 1px solid black;
    }

    .text-center {
        text-align: center;
    }

    table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
    }

    td,
    th {
        padding: 0;
        margin: 0;
    }
</style>
