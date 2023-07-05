<?php

Route::get('plagas', 'Campo\PlagasController@inicio');
Route::get('plagas/listar_reporte', 'Campo\PlagasController@listar_reporte');
Route::post('plagas/update_plaga', 'Campo\PlagasController@update_plaga');
Route::post('plagas/cambiar_estado_plaga', 'Campo\PlagasController@cambiar_estado_plaga');
Route::post('plagas/store_plaga', 'Campo\PlagasController@store_plaga');
Route::get('plagas/rotaciones_plaga', 'Campo\PlagasController@rotaciones_plaga');
Route::get('plagas/listar_incidencias', 'Campo\PlagasController@listar_incidencias');
Route::post('plagas/store_rotacion', 'Campo\PlagasController@store_rotacion');
Route::post('plagas/update_rotacion', 'Campo\PlagasController@update_rotacion');
Route::post('plagas/delete_rotacion', 'Campo\PlagasController@delete_rotacion');
