<?php

Route::get('semanas', 'SemanaController@inicio');
Route::get('semanas/get_accion', 'SemanaController@get_accion');
Route::get('semanas/listar_semanas', 'SemanaController@listar_semanas');
Route::post('semanas/procesar', 'SemanaController@procesar');
Route::post('semanas/update_semana', 'SemanaController@update_semana');
Route::get('semanas/igualar_datos', 'SemanaController@igualar_datos');
Route::post('semanas/store_igualar_datos', 'SemanaController@store_igualar_datos');
Route::post('semanas/copiar_semanas', 'SemanaController@copiar_semanas');
Route::post('semanas/actualizar_proyecciones_by_semanas', 'SemanaController@actualizar_proyecciones_by_semanas');
Route::post('semanas/update_semanas', 'SemanaController@update_semanas');
Route::post('semanas/actualizar_siembras_by_semanas', 'SemanaController@actualizar_siembras_by_semanas');
