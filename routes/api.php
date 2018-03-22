<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['api','cors']], function () {
    Route::post('new', 'ApiController@register');
    Route::post('login', 'ApiController@login');
    Route::group(['middleware' => 'jwt.auth'], function () {
        Route::get('me', 'ApiController@getUser');
        Route::put('me', 'ApiController@updateUser');
        Route::delete('me', 'ApiController@deleteUser');
        Route::post('logout', 'ApiController@logout');
    });
});