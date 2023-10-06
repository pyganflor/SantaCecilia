<script>
    $('#vista_actual').val('reporte_cuarto_frio');
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            tipo: $('#filtro_tipo').val(),
        };
        get_jquery('{{ url('reporte_cuarto_frio/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('reporte_cuarto_frio/exportar_reporte') }}?planta=' + $('#filtro_planta').val() +
            '&variedad=' + $('#filtro_variedad').val() +
            '&tipo=' + $('#filtro_tipo').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
