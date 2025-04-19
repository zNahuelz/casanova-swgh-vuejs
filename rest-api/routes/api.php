<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
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

Route::group([
    'prefix' => '/supplier',
], function($router){
    Route::post('/', [SupplierController::class, 'createSupplier'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::put('/{id}', [SupplierController::class, 'updateSupplier'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/{id}', [SupplierController::class, 'getSupplier'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::get('/', [SupplierController::class, 'getSuppliers'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
});