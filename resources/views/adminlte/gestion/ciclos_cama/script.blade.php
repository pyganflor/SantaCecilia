<script>
    $('#vista_actual').val('ciclos');

    function seleccionar_modulo() {
        datos = {
            modulo: $('#filtro_modulo').val(),
        };
        if (datos['modulo'] != '')
            get_jquery('{{ url('ciclos/seleccionar_modulo') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
        else
            $('#div_listado').html('');
    }

    function seleccionar_sector() {
        datos = {
            sector: $('#filtro_sector').val(),
        };
        $('#filtro_modulo').LoadingOverlay('show');
        $.get('{{ url('ciclos/seleccionar_sector') }}', datos, function(retorno) {
            $('#filtro_modulo').html(retorno.options);
        }, 'json').always(function() {
            $('#filtro_modulo').LoadingOverlay('hide');
        });
    }
</script>
