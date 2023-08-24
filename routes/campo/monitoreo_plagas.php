<?php

Route::get('monitoreo_plagas', 'Campo\MonitoreoPlagasController@inicio');
Route::get('monitoreo_plagas/listar_reporte', 'Campo\MonitoreoPlagasController@listar_reporte');
Route::post('monitoreo_plagas/store_incidencia', 'Campo\MonitoreoPlagasController@store_incidencia');
