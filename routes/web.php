<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', 'UserController@testOrm');

//RUTAS USUARIO
Route::post('/registro', 'UserController@registro');

//RUTAS BILLETERA
Route::post('/consultar', 'BilleteraController@consultar');
Route::post('/recargar', 'BilleteraController@recargar');
Route::post('/pagar', 'BilleteraController@pagar');
Route::post('/confirmar', 'BilleteraController@confirmar');
