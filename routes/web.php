<?php

use Illuminate\Support\Facades\Route;

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

Route::post('/login', 'App\Http\Controllers\UserController@login');
Route::post('/register', 'App\Http\Controllers\UserController@register');
Route::post('/status', 'App\Http\Controllers\UserController@status');


Route::get('/home', function () {
    dd('Ni hao, 你好');
});
