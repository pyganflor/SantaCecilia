<div class="row">
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Ventas <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_monto = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_monto += $item['monto'];
                    @endphp
                    <tr>
                        <th style="color: white">
                            Semana: {{ $item['semana']->codigo }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item['monto']) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_monto) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Tallos <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_tallos = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_tallos += $item['tallos'];
                    @endphp
                    <tr>
                        <th style="color: white">
                            Semana: {{ $item['semana']->codigo }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item['tallos']) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_tallos) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Ramos <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_ramos = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_ramos += $item['ramos'];
                    @endphp
                    <tr>
                        <th style="color: white">
                            Semana: {{ $item['semana']->codigo }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item['ramos']) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_ramos) }}</strong>
                </a>
            </p>
        </div>
    </div>
</div>
