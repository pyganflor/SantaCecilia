<legend style="margin-bottom: 5px"></legend>
<legend class="text-center" style="font-size: 1.2em">Rentabilidad/m<sup>2</sup> (4 semanas)</legend>
<canvas id="div_chart_rentabilidad_m2_mensual" style="width: 100%; height: 400px"></canvas>

<script>
    var grafica = document.getElementById("div_chart_rentabilidad_m2_mensual").getContext('2d');

    Chart.defaults.global.defaultFontFamily = "Arial";
    Chart.defaults.global.defaultFontStyle = "bold";
    Chart.defaults.global.defaultFontSize = 13;

    array_labels = [];

    array_ventas = [];
    @foreach($indicadores_4_semanas as $pos_item => $item)
    array_ventas.push({{round($item->venta_m2, 2)}});
    array_labels.push('{{$item->semana}}');
            @endforeach
    var ventas = {
            label: 'Ventas/m2',
            data: array_ventas,
            fill: false,
            lineTension: 0.3,
            borderColor: 'blue',
            //borderDash: [15, 3],
            pointBorderColor: 'blue',
            pointBackgroundColor: 'blue',
            pointRadius: 1,
            pointHoverRadius: 10,
            pointStyle: 'triangle',
            backgroundColor: 'blue',
            yAxisID: "y-axis-a"
        };

    array_costos = [];
    @foreach($indicadores_4_semanas as $pos_item => $item)
    array_costos.push({{round($item->costos_m2, 2)}});
            @endforeach
    var costos = {
            label: 'Costos/m2',
            data: array_costos,
            fill: false,
            lineTension: 0.3,
            borderColor: 'red',
            //borderDash: [15, 3],
            pointBorderColor: 'red',
            pointBackgroundColor: 'red',
            pointRadius: 1,
            pointHoverRadius: 10,
            pointStyle: 'rect',
            backgroundColor: 'red',
            yAxisID: "y-axis-a"
        };

    array_rentabilidad = [];
    @foreach($indicadores_4_semanas as $pos_item => $item)
    array_rentabilidad.push({{round($item->ebitda_m2, 2)}});
            @endforeach
    var rentabilidad = {
            label: 'Rentabilidad/m2',
            data: array_rentabilidad,
            fill: false,
            lineTension: 0.3,
            borderColor: 'green',
            //borderDash: [5, 5],
            pointBorderColor: 'black',
            pointBackgroundColor: 'green',
            pointRadius: 1,
            pointHoverRadius: 7,
            pointStyle: 'circle',
            backgroundColor: 'green',
            yAxisID: "y-axis-b",
        };

    var data = {
        labels: array_labels,
        datasets: [rentabilidad, ventas, costos]
    };

    var opciones = {
        scales: {
            xAxes: [{
                gridLines: {
                    display: false,
                    color: "darkgray"
                },
                scaleLabel: {
                    display: true,
                    labelString: "Semanas",
                    fontColor: "gray"
                },
            }],
            yAxes: [{
                id: "y-axis-a",
                gridLines: {
                    color: "black",
                    borderDash: [2, 5],
                },
                scaleLabel: {
                    display: true,
                    labelString: "Ventas y Costos",
                    fontColor: "black"
                },
                ticks: {
                    beginAtZero: true,
                    max: 10,
                },
                position: "left"
            }, {
                id: "y-axis-b",
                gridLines: {
                    display: false,
                    color: "black",
                    borderDash: [2, 5],
                },
                scaleLabel: {
                    display: true,
                    labelString: "Rentabilidad",
                    fontColor: "green"
                },
                ticks: {
                    min: -5,
                    max: 5,
                },
                position: "right"
            }]
        }
    };

    var lineChart = new Chart(grafica, {
        type: 'line',
        data: data,
        options: opciones
    });
</script>