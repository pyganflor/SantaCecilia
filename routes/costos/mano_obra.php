<?php

Route::get('gestion_mano_obra', 'Costos\CostosController@gestion_mano_obra');
Route::post('gestion_mano_obra/store_mano_obra', 'Costos\CostosController@store_mano_obra');
Route::post('gestion_mano_obra/update_mano_obra', 'Costos\CostosController@update_mano_obra');
Route::post('gestion_mano_obra/delete_mano_obra', 'Costos\CostosController@delete_mano_obra');
Route::get('gestion_mano_obra/importar_mano_obra', 'Costos\CostosController@importar_mano_obra');
Route::post('gestion_mano_obra/importar_file_mano_obra', 'Costos\CostosController@importar_file_mano_obra');
Route::get('gestion_mano_obra/vincular_actividad_mano_obra', 'Costos\CostosController@vincular_actividad_mano_obra');
Route::post('gestion_mano_obra/store_actividad_mano_obra', 'Costos\CostosController@store_actividad_mano_obra');
Route::post('gestion_mano_obra/importar_file_act_mano_obra', 'Costos\CostosController@importar_file_act_mano_obra');

Route::get('gestion_mano_obra/otros_gastos', 'Costos\CostosController@otros_gastos');
Route::post('gestion_mano_obra/store_otros_gastos', 'Costos\CostosController@store_otros_gastos');
Route::post('gestion_mano_obra/buscar_otros_gastos', 'Costos\CostosController@buscar_otros_gastos');

Route::get('gestion_mano_obra/buscar_moByActividad', 'Costos\CostosController@buscar_moByActividad');
Route::post('gestion_mano_obra/buscar_valorByActividadMOSemana', 'Costos\CostosController@buscar_valorByActividadMOSemana');
Route::post('gestion_mano_obra/save_costo', 'Costos\CostosController@save_costoMO');


/* ---------------------------------- REPORTE ------------------------------------- */
Route::get('reporte_mano_obra', 'Costos\CostosController@reporte_mano_obra');
Route::get('reporte_mano_obra/listar_reporte', 'Costos\CostosController@listar_reporte_mano_obra');
Route::get('reporte_mano_obra/exportar_reporte_mano_obra', 'Costos\CostosController@exportar_reporte_mano_obra');
Route::post('reporte_mano_obra/corregir_costos_mano_obra', 'Costos\CostosController@corregir_costos_mano_obra');