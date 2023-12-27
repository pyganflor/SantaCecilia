<script>
    $('#vista_actual').val('pedidos');
    listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            cliente: $('#filtro_cliente').val(),
        }
        get_jquery('{{ url('pedidos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_resumen_variedades', );
            $('#table_resumen_variedades_filter').addClass('hidden')
            $('#table_resumen_variedades_filter label input').addClass('input-yura_default')
        });
    }

    function exportar_resumen_pedidos() {
        $.LoadingOverlay('show');
        window.open('{{ url('pedidos/exportar_resumen_pedidos') }}?fecha=' + $('#filtro_fecha').val() +
            '&cliente=' + $('#filtro_cliente').val() +
            '&finca=' + $('#filtro_finca').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function add_pedido() {
        datos = {}
        get_jquery('{{ url('pedidos/add_pedido') }}', datos, function(retorno) {
            modal_view('modal_add_pedido', retorno, '<i class="fa fa-fw fa-plus"></i> Formulario Pedido',
                true, false, '{{ isPC() ? '98%' : '' }}',
                function() {});
        })
    }

    function modal_exportar() {
        datos = {}
        get_jquery('{{ url('pedidos/modal_exportar') }}', datos, function(retorno) {
            modal_view('modal_modal_exportar', retorno, '<i class="fa fa-fw fa-plus"></i> Exportar Pedidos',
                true, false, '{{ isPC() ? '70%' : '' }}',
                function() {});
        })
    }

    function eliminar_pedido(ped) {
        texto =
            "<div class='alert alert-warning text-center' style='font-size: 1.5em'>Esta a punto de <b>ELIMINAR</b> el Pedido</div>" +
            "<div class='alert alert-info text-center' style='font-size: 1.5em'>¿Desea devolver el contenido al inventario de cajas?" +
            "<div class='row'>" +
            "<div class='col-md-6'>" +
            "<label class='mouse-hand' for='radio_devolver0'>No</label>" +
            "<input type='radio' name='radio_devolver' id='radio_devolver0' value='0' style='width: 20px; height: 20px'>" +
            "</div>" +
            "<div class='col-md-6'>" +
            "<input type='radio' name='radio_devolver' id='radio_devolver1' value='1' style='width: 20px; height: 20px' checked>" +
            "<label class='mouse-hand' for='radio_devolver1'>Si</label>" +
            "</div>" +
            "</div>" +
            "</div>";

        modal_quest('modal_eliminar_pedido', texto, 'Eliminar pedido', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id_pedido: ped,
                devolver: $('#radio_devolver1').prop('checked') == true ? 1 : 0,
            };
            post_jquery_m('pedidos/eliminar_pedido', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function eliminar_pedido_futuro(ped) {
        texto =
            "<div class='alert alert-warning text-center' style='font-size: 1.5em'>Esta a punto de <b>ELIMINAR</b> el Pedido</div>";

        modal_quest('modal_eliminar_pedido', texto, 'Eliminar pedido', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id_pedido: ped,
            };
            post_jquery_m('pedidos/eliminar_pedido_futuro', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function buscar_inventario() {
        datos = {
            buscar: $('#buscar_inventario').val(),
        }
        get_jquery('{{ url('pedidos/buscar_inventario') }}', datos, function(retorno) {
            $('#div_inventario').html(retorno);
        }, 'div_inventario');
    }

    function seleccionar_cliente() {
        datos = {
            _token: '{{ csrf_token() }}',
            cliente: $('#add_cliente').val(),
        }
        $('.input_seleccionar_cliente').LoadingOverlay('show');
        $.post('{{ url('pedidos/seleccionar_cliente') }}', datos, function(retorno) {
            $('#add_agencia').html('');
            $('#add_consignatario').html('');

            for (i = 0; i < retorno.agencias.length; i++) {
                $('#add_agencia').append('<option value="' + retorno.agencias[i].id_agencia_carga + '">' +
                    retorno.agencias[i].nombre + '</option>');
            }
            for (i = 0; i < retorno.consignatarios.length; i++) {
                $('#add_consignatario').append('<option value="' + retorno.consignatarios[i].id_consignatario +
                    '">' +
                    retorno.consignatarios[i].nombre + '</option>');
            }
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('.input_seleccionar_cliente').LoadingOverlay('hide');
        });
    }

    function modificar_div_inv(par) {
        if (par == 'left') {
            $('#td_inventarios').css('width', '10%');
            $('#titulo_inventarios').addClass('hidden');
            $('#body_inventarios').addClass('hidden');
            $('#titulo_seleccionados').removeClass('hidden');
            $('#body_seleccionados').removeClass('hidden');
            $('#footer_seleccionados').removeClass('hidden');
        }
        if (par == 'center') {
            $('#td_inventarios').css('width', '50%');
            $('#titulo_inventarios').removeClass('hidden');
            $('#body_inventarios').removeClass('hidden');
            $('#titulo_seleccionados').removeClass('hidden');
            $('#body_seleccionados').removeClass('hidden');
            $('#footer_seleccionados').removeClass('hidden');
        }
        if (par == 'right') {
            $('#td_inventarios').css('width', '90%');
            $('#titulo_inventarios').removeClass('hidden');
            $('#body_inventarios').removeClass('hidden');
            $('#titulo_seleccionados').addClass('hidden');
            $('#body_seleccionados').addClass('hidden');
            $('#footer_seleccionados').addClass('hidden');
        }
    }

    function delete_armar_caja(p) {
        $('#tr_armar_' + p).remove();
    }

    function add_armar_caja() {
        cant_armar_caja++;
        select_variedades = $('#select_variedades');
        $('#table_armar_caja').append('<tr id="tr_armar_' + cant_armar_caja + '" class="tr_armar_manual" data-num="' +
            cant_armar_caja + '">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select style="width: 100%; height: 26px" id="armar_variedad_' + cant_armar_caja + '">' +
            select_variedades.html() +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="armar_longitud_' + cant_armar_caja +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="armar_tallos_x_ramo_' +
            cant_armar_caja +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="armar_ramos_' + cant_armar_caja +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="text-center" style="width: 100%" id="armar_precio_' + cant_armar_caja +
            '" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_armar_caja(' +
            cant_armar_caja + ')">' +
            '<i class="fa fa-fw fa-trash">' +
            '</i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }
</script>
