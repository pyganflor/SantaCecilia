<?php

Route::get('temperaturas', 'Proyecciones\proyTemperaturaController@inicio');
Route::get('kh_temperaturas', 'Proyecciones\proyTemperaturaController@kh_temperaturas');
Route::get('kh_temperaturas/listar_ciclos', 'Proyecciones\proyTemperaturaController@listar_ciclos');
Route::get('temperaturas/add_temperatura', 'Proyecciones\proyTemperaturaController@add_temperatura');
Route::post('temperaturas/store_temperatura', 'Proyecciones\proyTemperaturaController@store_temperatura');
Route::post('temperaturas/buscar_temperatura', 'Proyecciones\proyTemperaturaController@buscar_temperatura');
Route::post('temperaturas/store_all_temperatura', 'Proyecciones\proyTemperaturaController@store_all_temperatura');
Route::get('temperaturas/listar_temperaturas', 'Proyecciones\proyTemperaturaController@listar_temperaturas');
Route::get('temperaturas/exportar_reporte_temperatura', 'Proyecciones\proyTemperaturaController@exportar_reporte_temperatura');