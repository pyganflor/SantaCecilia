<?php

Route::get('posco_disponibilidad', 'Postcosecha\ReporteDisponibilidadController@inicio');
Route::get('posco_disponibilidad/listar_reporte', 'Postcosecha\ReporteDisponibilidadController@listar_reporte');
