<?php

Route::get('costos_generales', 'Costos\CostosController@costos_generales');
Route::get('costos_generales/listar_reporte', 'Costos\CostosController@listar_reporte_general');
Route::get('costos_generales/exportar_reporte_costos_generales', 'Costos\CostosController@exportar_reporte_costos_generales');