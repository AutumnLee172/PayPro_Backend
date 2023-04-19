<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/user')->group(function () {
    Route::post('/register', 'App\Http\Controllers\UserController@register');
    Route::post('/login', 'App\Http\Controllers\UserController@login');
});

Route::prefix('/wallet')->group(function () {
    Route::post('/new', 'App\Http\Controllers\WalletController@new');
    Route::post('/get', 'App\Http\Controllers\WalletController@get');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
