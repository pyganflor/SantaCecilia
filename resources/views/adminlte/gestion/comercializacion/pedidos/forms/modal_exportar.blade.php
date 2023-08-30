<div style="overflow-x: scroll">
    <div class="input-group input-group">
        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Desde
        </div>
        <input type="date" id="filtro_desde" name="filtro_desde" required
            class="form-control input-yura_default text-center" style="width: 100% !important;"
            value="{{ $desde }}">
        <div class="input-group-addon bg-yura_dark">
            Hasta
        </div>
        <input type="date" id="filtro_hasta" name="filtro_hasta" required
            class="form-control input-yura_default text-center" style="width: 100% !important;"
            value="{{ $hasta }}">
        <div class="input-group-addon bg-yura_dark">
            Cliente
        </div>
        <select id="filtro_cliente_exportar" style="width: 100%" class="form-control">
            <option value="T">Todos los clientes</option>
            @foreach ($clientes as $c)
                <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
            @endforeach
        </select>
        <div class="input-group-btn">
            <button class="btn btn-primary btn-yura_primary" onclick="exportar_pedidos()">
                <i class="fa fa-fw fa-download"></i> Resumen
            </button>
            <button class="btn btn-primary btn-yura_dark" onclick="exportar_estado_cliente()">
                <i class="fa fa-fw fa-download"></i> Estado de Cliente
            </button>
        </div>
    </div>
</div>

<script>
    function exportar_pedidos() {
        $.LoadingOverlay('show');
        window.open('{{ url('pedidos/exportar_pedidos') }}?desde=' + $('#filtro_desde').val() +
            '&hasta=' + $('#filtro_hasta').val() +
            '&cliente=' + $('#filtro_cliente_exportar').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function exportar_estado_cliente() {
        cliente = $('#filtro_cliente_exportar').val();
        if (cliente != 'T') {
            $.LoadingOverlay('show');
            window.open('{{ url('pedidos/exportar_estado_cliente') }}?desde=' + $('#filtro_desde').val() +
                '&hasta=' + $('#filtro_hasta').val() +
                '&cliente=' + $('#filtro_cliente_exportar').val(), '_blank');
            $.LoadingOverlay('hide');
        }
    }
</script>
