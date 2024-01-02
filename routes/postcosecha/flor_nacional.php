<?php

Route::get('flor_nacional', 'Postcosecha\FlorNacionalController@inicio');
Route::get('flor_nacional/listar_reporte', 'Postcosecha\FlorNacionalController@listar_reporte');
Route::post('flor_nacional/store_flor_nacional', 'Postcosecha\FlorNacionalController@store_flor_nacional');
