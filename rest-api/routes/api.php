<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\BaseMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/auth',
    'middleware' => BaseMiddleware::class,
], function($router){
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/profile', [AuthController::class, 'profile']);
});