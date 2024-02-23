<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#monto-chart" data-toggle="tab" aria-expanded="true">
                Ventas
            </a>
        </li>
        <li class="">
            <a href="#tallos-chart" data-toggle="tab" aria-expanded="false">
                Tallos
            </a>
        </li>
        <li class="">
            <a href="#ramos-chart" data-toggle="tab" aria-expanded="true">
                Ramos
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="monto-chart" style="position: relative">
            <canvas id="chart_monto" width="100%" height="40" style="margin-top: 5px"></canvas>

            @php
                $total_monto = 0;
                foreach ($data as $d) {
                    foreach ($d['valores'] as $item) {
                        $total_monto += $item->monto;
                    }
                }
            @endphp
            <div class="row">
                @foreach ($data as $pos_d => $d)
                    @php
                        $total_longitud = 0;
                        foreach ($d['valores'] as $item) {
                            $total_longitud += $item->monto;
                        }
                        $porcentaje = porcentaje($total_longitud, $total_monto, 1);
                    @endphp
                    <div class="col-sm-2 col-xs-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-green">
                                <b>{{ $porcentaje }}%</b>
                            </span>
                            <h5 class="description-header">
                                <b>${{ number_format($total_longitud, 2) }}</b>
                            </h5>
                            <span class="description-text">
                                <b>{{ $d['longitud'] }}cm</b>
                            </span>
                        </div>
                    </div>
                @endforeach
                <div class="col-sm-2 col-xs-6">
                    <div class="description-block border-right">
                        <span class="description-percentage text-green">
                            <b>100%</b>
                        </span>
                        <h5 class="description-header">
                            <b>${{ number_format($total_monto, 2) }}</b>
                        </h5>
                        <span class="description-text">
                            <b>TOTAL</b>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="chart tab-pane" id="tallos-chart" style="position: relative">
            <canvas id="chart_tallos" width="100%" height="40" style="margin-top: 5px"></canvas>

            @php
                $total_tallos = 0;
                foreach ($data as $d) {
                    foreach ($d['valores'] as $item) {
                        $total_tallos += $item->tallos;
                    }
                }
            @endphp
            <div class="row">
                @foreach ($data as $pos_d => $d)
                    @php
                        $total_longitud = 0;
                        foreach ($d['valores'] as $item) {
                            $total_longitud += $item->tallos;
                        }
                        $porcentaje = porcentaje($total_longitud, $total_tallos, 1);
                    @endphp
                    <div class="col-sm-2 col-xs-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-green">
                                <b>{{ $porcentaje }}%</b>
                            </span>
                            <h5 class="description-header">
                                <b>{{ number_format($total_longitud) }}</b>
                            </h5>
                            <span class="description-text">
                                <b>{{ $d['longitud'] }}cm</b>
                            </span>
                        </div>
                    </div>
                @endforeach
                <div class="col-sm-2 col-xs-6">
                    <div class="description-block border-right">
                        <span class="description-percentage text-green">
                            <b>100%</b>
                        </span>
                        <h5 class="description-header">
                            <b>{{ number_format($total_tallos) }}</b>
                        </h5>
                        <span class="description-text">
                            <b>TOTAL</b>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="chart tab-pane" id="ramos-chart" style="position: relative">
            <canvas id="chart_ramos" width="100%" height="40" style="margin-top: 5px"></canvas>

            @php
                $total_ramos = 0;
                foreach ($data as $d) {
                    foreach ($d['valores'] as $item) {
                        $total_ramos += $item->ramos;
                    }
                }
            @endphp
            <div class="row">
                @foreach ($data as $pos_d => $d)
                    @php
                        $total_longitud = 0;
                        foreach ($d['valores'] as $item) {
                            $total_longitud += $item->ramos;
                        }
                        $porcentaje = porcentaje($total_longitud, $total_ramos, 1);
                    @endphp
                    <div class="col-sm-2 col-xs-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-green">
                                <b>{{ $porcentaje }}%</b>
                            </span>
                            <h5 class="description-header">
                                <b>{{ number_format($total_longitud) }}</b>
                            </h5>
                            <span class="description-text">
                                <b>{{ $d['longitud'] }}cm</b>
                            </span>
                        </div>
                    </div>
                @endforeach
                <div class="col-sm-2 col-xs-6">
                    <div class="description-block border-right">
                        <span class="description-percentage text-green">
                            <b>100%</b>
                        </span>
                        <h5 class="description-header">
                            <b>{{ number_format($total_ramos) }}</b>
                        </h5>
                        <span class="description-text">
                            <b>TOTAL</b>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    construir_char_multiple('Tallos', 'chart_tallos');
    construir_char_multiple('Ramos', 'chart_ramos');
    construir_char_multiple('Monto', 'chart_monto');

    function construir_char_multiple(label, id) {
        labels = [];
        datasets = [];
        @foreach ($labels as $dia)
            labels.push("{{ $dia }}");
        @endforeach

        {{-- Data_list --}}
        @foreach ($data as $pos_d => $d)
            data_list = [];
            if (label == 'Tallos') {
                @foreach ($d['valores'] as $item)
                    data_list.push("{{ $item->tallos }}");
                @endforeach
            } else if (label == 'Ramos') {
                @foreach ($d['valores'] as $item)
                    data_list.push("{{ $item->ramos }}");
                @endforeach
            } else if (label == 'Monto') {
                @foreach ($d['valores'] as $item)
                    data_list.push("{{ round($item->monto, 2) }}");
                @endforeach
            }

            datasets.push({
                label: '{{ $d['longitud'] }}cm' + ' ',
                data: data_list,
                backgroundColor: '{{ getListColores()[$pos_d] }}',
                borderColor: '{{ getListColores()[$pos_d] }}',
                borderWidth: 2,
                fill: {{ $fill_grafica }},
            });
        @endforeach

        ctx = document.getElementById(id).getContext('2d');
        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: false
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0.2, // disables bezier curves
                    }
                },
                tooltips: {
                    mode: 'point' // nearest, point, index, dataset, x, y
                },
                legend: {
                    display: true,
                    position: 'top',
                    fullWidth: false,
                    /*onClick: function() {},
                    onHover: function() {},*/
                    reverse: true,
                },
                showLines: true, // for all datasets
                borderCapStyle: 'round', // "butt" || "round" || "square"
            }
        });
    }
</script>
