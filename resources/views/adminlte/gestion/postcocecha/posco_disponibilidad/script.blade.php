<script>
    $('#vista_actual').val('flor_nacional');
    listar_reporte();
    
    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
        }
        get_jquery('{{ url('posco_disponibilidad/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
