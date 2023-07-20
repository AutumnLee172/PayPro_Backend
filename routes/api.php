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
    Route::post('/merchant/register', 'App\Http\Controllers\UserController@register_merchant');
    Route::post('/login', 'App\Http\Controllers\UserController@login');    
    Route::post('/login_merchant', 'App\Http\Controllers\UserController@login_merchant');
    Route::post('/update_username', 'App\Http\Controllers\UserController@update_username');
    Route::post('/update_password', 'App\Http\Controllers\UserController@update_password');
    Route::post('/getUsername', 'App\Http\Controllers\UserController@getUsername');
});

Route::prefix('/trx')->group(function () {
    Route::post('/internal/new', 'App\Http\Controllers\TransactionController@newInternal');
    Route::post('/external/new', 'App\Http\Controllers\TransactionController@newExternal');
    Route::post('/get', 'App\Http\Controllers\TransactionController@getTransactions');
    Route::post('/pay', 'App\Http\Controllers\TransactionController@biometricPayment');
    Route::post('/get_merchant', 'App\Http\Controllers\TransactionController@getTransactions_merchant');
});

Route::prefix('/wallet')->group(function () {
    Route::post('/new', 'App\Http\Controllers\WalletController@new');
    Route::post('/get', 'App\Http\Controllers\WalletController@get');
});

Route::prefix('/notification')->group(function () {
    Route::post('/get', 'App\Http\Controllers\NotificationController@get');
});

Route::prefix('/home')->group(function () {
    Route::post('/getWalletsValue', 'App\Http\Controllers\HomeController@getWalletsValue');
    Route::post('/getLatestActivities', 'App\Http\Controllers\HomeController@getLatestActivities');
    Route::post('/getChartData', 'App\Http\Controllers\HomeController@getChartData');
});

Route::prefix('/biometric')->group(function () {
    Route::post('/register', 'App\Http\Controllers\BiometricController@registerBiometric');
    Route::post('/check', 'App\Http\Controllers\BiometricController@checkifPending');
    Route::post('/approve', 'App\Http\Controllers\BiometricController@approve');
    Route::post('/decline', 'App\Http\Controllers\BiometricController@decline');
    Route::post('/set', 'App\Http\Controllers\BiometricController@setPreferredWallet');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
