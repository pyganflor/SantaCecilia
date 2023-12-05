<script>
    $('#vista_actual').val('flor_nacional');
    listar_reporte();
    
    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
        }
        get_jquery('{{ url('param_disponibilidad/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
