<script>
    $('#vista_actual').val('flor_nacional');
    listar_reporte();
    
    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
        }
        get_jquery('{{ url('flor_nacional/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        }, 'div_listado');
    }

    function add_flor_nacional() {
        datos = {}
        get_jquery('{{ url('flor_nacional/add_flor_nacional') }}', datos, function(retorno) {
            modal_view('modal_add_flor_nacional', retorno, '<i class="fa fa-fw fa-plus"></i> Formulario Ingreso de Flor Nacional',
                true, false, '{{ isPC() ? '85%' : '' }}',
                function() {});
        })
    }
</script>
