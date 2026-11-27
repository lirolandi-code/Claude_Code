<?php

/*
|--------------------------------------------------------------------------
| Rutas de la autogestión del empleado
|--------------------------------------------------------------------------
| Copie estas líneas al final de routes/web.php.
| Se usa la forma 'Controlador@metodo', que funciona sin el conversor de sintaxis.
| No llevan el middleware 'auth' porque los empleados tienen su propia sesión
| (ver app/autogestion/Ag.php), separada de los usuarios del sistema central.
*/

Route::get('autogestion', 'AutogestionController@inicio');
Route::get('autogestion/login', 'AutogestionController@login')->name('autogestion.login');
Route::post('autogestion/login', 'AutogestionController@ingresar');
Route::post('autogestion/salir', 'AutogestionController@salir');

Route::get('autogestion/inicio', 'AutogestionController@inicio')->name('autogestion.inicio');
Route::get('autogestion/datos', 'AutogestionController@datos');
Route::get('autogestion/documentacion', 'AutogestionController@documentacion');
Route::post('autogestion/documentacion/{id}', 'AutogestionController@subirDocumento');

Route::get('autogestion/haberes', 'AutogestionController@haberes');
Route::get('autogestion/haberes/recibo/{id}', 'AutogestionController@recibo');
Route::get('autogestion/haberes/formulario1359/{anio}', 'AutogestionController@formulario1359');

Route::get('autogestion/asistencia', 'AutogestionController@asistencia');
Route::get('autogestion/asistencia/bandas', 'AutogestionController@bandas');
Route::get('autogestion/asistencia/licencias', 'AutogestionController@licencias');
Route::get('autogestion/asistencia/accesos', 'AutogestionController@accesos');

Route::get('autogestion/carrera', 'AutogestionController@carrera');
Route::get('autogestion/contrato', 'AutogestionController@contrato');
Route::get('autogestion/mensajes', 'AutogestionController@mensajes');
Route::get('autogestion/mensajes/{id}', 'AutogestionController@mensaje');

Route::get('autogestion/solicitud/{tipo}', 'AutogestionController@solicitud');
Route::post('autogestion/solicitud/{tipo}', 'AutogestionController@enviarSolicitud');
