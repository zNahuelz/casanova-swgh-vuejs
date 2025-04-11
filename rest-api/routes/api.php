<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\BaseMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function(Request $request){
    return 'Hello World!!';
});

Route::group([
    'prefix' => '/auth',
    'middleware' => BaseMiddleware::class,
], function($router){
    Route::post('/login', [AuthController::class, 'login']);
});