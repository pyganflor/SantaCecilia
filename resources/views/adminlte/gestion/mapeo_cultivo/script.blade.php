<script>
    $('#vista_actual').val('mapeo_cultivo');
    listar_sectores();

    function listar_sectores() {
        datos = {};
        get_jquery('{{ url('mapeo_cultivo/listar_sectores') }}', datos, function(retorno) {
            $('#div_sectores').html(retorno);
        });
    }
</script>