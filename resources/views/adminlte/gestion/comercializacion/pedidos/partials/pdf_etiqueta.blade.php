@foreach ($datos['pedido']->detalles as $pos_det => $det)
    @php
        $caja_frio = $det->caja_frio;
    @endphp
    <div style="position: relative; top: -40px; left: -30px; width: 260px">
        <table class="text-center" style="font-size: 0.9em; width: 100%">
            <tr>
                <td style="text-align: center;">
                    <img src="{{ public_path('/images/logo_senae.png') }}" width="50px" alt="Logo">
                </td>
                <td style="text-align: left;">
                    {!! $barCode->getBarcode('05520234001224073', $barCode::TYPE_CODE_128, 1) !!}
                    05520234001224073
                </td>
            </tr>
            <tr>
                <td style="text-align: center; font-size: 0.8em; border-bottom: 1px solid black" colspan="2">
                    PAIS DESTINO: {{ $datos['pedido']->consignatario->pais()->nombre }}
                </td>
            </tr>
        </table>

        <table style="width: 100%">
            <tr>
                <th class="text-center" colspan="2">
                    ECSTACECILIA-ROSES
                </th>
            </tr>
            <tr>
                <td class="text-center" style="font-size: 0.8em" colspan="2">
                    {{ $datos['pedido']->cliente->detalle()->nombre }}
                </td>
            </tr>
            <tr>
                <td style="font-size: 0.6em">
                    <em>AWB:</em> {{ $datos['pedido']->guia_madre }}
                </td>
                <td style="font-size: 0.6em; text-align: right">
                    <em>PO/Label:</em> {{ $datos['pedido']->marcacion }}
                </td>
            </tr>
            <tr>
                <td style="font-size: 0.6em">
                    <em>HAWB:</em> {{ $datos['pedido']->guia_hija }}
                </td>
                <td style="font-size: 0.6em; text-align: right">
                    <em>INVOICE: {{ str_pad($datos['pedido']->factura, 8, '0', STR_PAD_LEFT) }}</em>
                </td>
            </tr>
            <tr>
                <th style="font-size: 0.6em; text-align: right" colspan="2">
                    BOX: <b>{{ $pos_det + 1 }}/{{ count($datos['pedido']->detalles) }}</b> {{ $caja_frio->tipo }}
                </th>
            </tr>
        </table>

        <table class="border-1px" style="font-size: 10px">
            <tr>
                <td class="border-1px text-center" style="width: 110px">
                    VARIETIES
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
            </tr>
            @php
                $total_tallos = 0;
                $total_ramos = 0;
            @endphp
            @foreach ($caja_frio->detalles as $pos_item => $item)
                @php
                    $variedad = $item->variedad;
                    $total_tallos += $item->ramos * $item->tallos_x_ramo;
                    $total_ramos += $item->ramos;
                @endphp
                <tr>
                    <td class="text-center border-1px">
                        {{ $variedad->nombre }}
                    </td>
                    <td class="text-center border-1px">
                        {{ $item->longitud }}
                    </td>
                    <td class="text-center border-1px">
                        {{ $item->ramos }}
                    </td>
                    <td class="text-center border-1px">
                        {{ $item->ramos * $item->tallos_x_ramo }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: right">
                    TOTAL
                </td>
                <th class="border-1px text-center">
                    {{ $total_ramos }}
                </th>
                <th class="border-1px text-center">
                    {{ $total_tallos }}
                </th>
            </tr>
            <tr>
                <th style="text-align: left" colspan="4">
                    AGENCY: {{ $datos['pedido']->agencia_carga->nombre }}
                </th>
            </tr>
        </table>
        <div class="text-center" style="font-size: 0.7em; width: 100%">
            <b>PRODUCT GROWN IN ECUADOR</b>
        </div>
    </div>

    @if ($pos_det < count($datos['pedido']->detalles) - 1)
        <div style="page-break-after:always;"></div>
    @endif
@endforeach

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
