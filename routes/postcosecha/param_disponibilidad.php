<?php

Route::get('param_disponibilidad', 'Postcosecha\ParametrizarDisponibilidadController@inicio');
Route::get('param_disponibilidad/listar_reporte', 'Postcosecha\ParametrizarDisponibilidadController@listar_reporte');
Route::post('param_disponibilidad/store_model', 'Postcosecha\ParametrizarDisponibilidadController@store_model');
Route::post('param_disponibilidad/update_model', 'Postcosecha\ParametrizarDisponibilidadController@update_model');
