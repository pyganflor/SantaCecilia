<?php

Route::get('flor_nacional', 'Postcosecha\FlorNacionalController@inicio');
Route::get('flor_nacional/listar_reporte', 'Postcosecha\FlorNacionalController@listar_reporte');
Route::get('flor_nacional/add_flor_nacional', 'Postcosecha\FlorNacionalController@add_flor_nacional');
Route::post('flor_nacional/buscar_modulos', 'Postcosecha\FlorNacionalController@buscar_modulos');
Route::post('flor_nacional/store_flor_nacional', 'Postcosecha\FlorNacionalController@store_flor_nacional');
Route::post('flor_nacional/eliminar_flor_nacional', 'Postcosecha\FlorNacionalController@eliminar_flor_nacional');
Route::post('flor_nacional/update_flor_nacional', 'Postcosecha\FlorNacionalController@update_flor_nacional');
