<?php

Route::get('mapeo_cultivo', 'MapeoCultivoController@inicio');
Route::get('mapeo_cultivo/listar_sectores', 'MapeoCultivoController@listar_sectores');
Route::post('mapeo_cultivo/store_sector', 'MapeoCultivoController@store_sector');
Route::post('mapeo_cultivo/update_sector', 'MapeoCultivoController@update_sector');
Route::post('mapeo_cultivo/cambiar_estado_sector', 'MapeoCultivoController@cambiar_estado_sector');
Route::get('mapeo_cultivo/listar_modulos', 'MapeoCultivoController@listar_modulos');
Route::post('mapeo_cultivo/store_modulo', 'MapeoCultivoController@store_modulo');
Route::post('mapeo_cultivo/update_modulo', 'MapeoCultivoController@update_modulo');
Route::post('mapeo_cultivo/cambiar_estado_modulo', 'MapeoCultivoController@cambiar_estado_modulo');
Route::get('mapeo_cultivo/listar_camas', 'MapeoCultivoController@listar_camas');
Route::post('mapeo_cultivo/store_camas', 'MapeoCultivoController@store_camas');
Route::post('mapeo_cultivo/cambiar_estado_cama', 'MapeoCultivoController@cambiar_estado_cama');
