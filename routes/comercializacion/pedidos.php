<?php

Route::get('pedidos','Comercializacion\PedidoController@inicio');
Route::get('pedidos/listar_reporte','Comercializacion\PedidoController@listar_reporte');
Route::get('pedidos/add_pedido','Comercializacion\PedidoController@add_pedido');
Route::get('pedidos/buscar_inventario','Comercializacion\PedidoController@buscar_inventario');
Route::post('pedidos/store_pedido','Comercializacion\PedidoController@store_pedido');
Route::post('pedidos/eliminar_pedido','Comercializacion\PedidoController@eliminar_pedido');
Route::post('pedidos/seleccionar_cliente','Comercializacion\PedidoController@seleccionar_cliente');
Route::get('pedidos/editar_pedido','Comercializacion\PedidoController@editar_pedido');
