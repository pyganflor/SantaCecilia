<div class="nav-tabs-custom" style="cursor: move;">
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
        </div>
        <div class="chart tab-pane" id="tallos-chart" style="position: relative">
            <canvas id="chart_tallos" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="ramos-chart" style="position: relative">
            <canvas id="chart_ramos" width="100%" height="40" style="margin-top: 5px"></canvas>
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
                    position: 'bottom',
                    fullWidth: false,
                    onClick: function() {},
                    onHover: function() {},
                    reverse: true,
                },
                showLines: true, // for all datasets
                borderCapStyle: 'round', // "butt" || "round" || "square"
            }
        });
    }
</script>
