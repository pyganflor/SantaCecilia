<table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_formulario">
    <tr>
        <th class="padding_lateral_5 th_yura_green">
            Planta
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Variedad
        </th>
        <th class="padding_lateral_5 th_yura_green" style="width: 200px">
            Modulo
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Motivo
        </th>
        <th class="text-center th_yura_green" style="width: 70px">
            Tallos
        </th>
        <th class="text-center th_yura_green" style="width: 20px">
            <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_row()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    <tr id="tr_form_1">
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="add_planta_1" style="width: 100%; height: 26px;"
                onchange="select_planta($(this).val(), 'add_variedad_1', 'add_variedad_1', '<option>Seleccione</option>')">
                <option value="">Seleccione</option>
                @foreach ($plantas as $p)
                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
            <input type="hidden" class="num_forms" value="1">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="add_variedad_1" style="width: 100%; height: 26px;" onchange="buscar_modulos(1)">
                <option value="">Seleccione</option>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="add_modulo_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="add_motivo_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
                @foreach ($motivos_nacional as $item)
                    <option value="{{ $item->id_motivos_nacional }}">{{ $item->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="add_tallos_1" style="width: 100%" value="0" class="text-center">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
        </td>
    </tr>
</table>
<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_flor_nacional()">
        <i class="fa fa-fw fa-save"></i> GRABAR
    </button>
</div>
<input type="hidden" id="cant_forms" value="1">

<script>
    function add_row() {
        cant_forms = $('#cant_forms').val();
        cant_forms++;
        plantas = $('#add_planta_1');
        motivos_nacional = $('#add_motivo_1');
        parametros_planta = [
            "'add_variedad_" + cant_forms + "'",
            "'new_variedad_" + cant_forms + "'",
            "'<option>Seleccione</option>'",
        ];
        $('#table_add_formulario').append(
            '<tr id="tr_form_' + cant_forms + '">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="add_planta_' + cant_forms + '" style="width: 100%; height: 26px;" ' +
            'onchange="select_planta($(this).val(), ' + parametros_planta[0] + ', ' + parametros_planta[1] + ', ' +
            parametros_planta[2] + ')">' +
            plantas.html() +
            '</select>' +
            '<input type="hidden" class="num_forms" value="' + cant_forms + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="add_variedad_' + cant_forms +
            '" style="width: 100%; height: 26px;" onchange="buscar_modulos(' + cant_forms + ')">' +
            '<option value="">Seleccione</option>' +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="add_modulo_' + cant_forms + '" style="width: 100%; height: 26px;">' +
            '<option value="">Seleccione</option>' +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="add_motivo_' + cant_forms + '" style="width: 100%; height: 26px;">' +
            motivos_nacional.html() +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="add_tallos_' + cant_forms +
            '" style="width: 100%" value="0" class="text-center">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_row(' + cant_forms + ')">' +
            '<i class="fa fa-fw fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '</tr>'
        );
        cant_forms = $('#cant_forms').val(cant_forms);
    }

    function delete_row(p) {
        $('#tr_form_' + p).remove();
    }

    function buscar_modulos(pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: $('#add_variedad_' + pos).val(),
        };
        if (datos['variedad'] != '') {
            $.post('{{ url('flor_nacional/buscar_modulos') }}', datos, function(retorno) {
                $('#add_modulo_' + pos).html(retorno.options);
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            });
        }
    }

    function store_flor_nacional() {
        num_forms = $('.num_forms');
        data = [];
        for (i = 0; i < num_forms.length; i++) {
            pos = num_forms[i].value;
            variedad = $('#add_variedad_' + pos).val();
            modulo = $('#add_modulo_' + pos).val();
            motivo = $('#add_motivo_' + pos).val();
            tallos = $('#add_tallos_' + pos).val();
            if (variedad != '' && modulo != '' && motivo != '' && tallos > 0) {
                data.push({
                    variedad: variedad,
                    modulo: modulo,
                    motivo: motivo,
                    tallos: tallos,
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
                fecha: $('#filtro_fecha').val(),
            }
            post_jquery_m('{{ url('flor_nacional/store_flor_nacional') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        }
    }
</script>
