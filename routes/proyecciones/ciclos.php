<?php

Route::get('ciclos', 'CiclosCamaController@inicio');
Route::get('ciclos/seleccionar_sector', 'CiclosCamaController@seleccionar_sector');
Route::get('ciclos/seleccionar_modulo', 'CiclosCamaController@seleccionar_modulo');
Route::post('ciclos/store_ciclos', 'CiclosCamaController@store_ciclos');
Route::post('ciclos/update_ciclo', 'CiclosCamaController@update_ciclo');
Route::post('ciclos/terminar_ciclo', 'CiclosCamaController@terminar_ciclo');
Route::post('ciclos/eliminar_ciclo', 'CiclosCamaController@eliminar_ciclo');
