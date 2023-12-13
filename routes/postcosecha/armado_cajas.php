<?php

Route::get('armado_cajas', 'ArmadoCajasController@inicio');
Route::get('armado_cajas/escanear_codigo', 'ArmadoCajasController@escanear_codigo');
Route::post('armado_cajas/store_caja', 'ArmadoCajasController@store_caja');
Route::get('armado_cajas/buscar_inventario', 'ArmadoCajasController@buscar_inventario');
