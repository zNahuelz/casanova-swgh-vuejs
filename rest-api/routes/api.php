<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkerController;
use App\Http\Middleware\BaseMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/auth',
    'middleware' => BaseMiddleware::class,
], function($router){
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/recover_account', [AuthController::class, 'sendRecoveryMail']);
    Route::post('/verify_token', [AuthController::class, 'verifyRecoveryToken']);
    Route::put('/change_password/token', [AuthController::class, 'changePasswordWithToken']);
    Route::put('/change_password', [AuthController::class, 'changePasswordAndEmail']);
    Route::put('/change_username', [AuthController::class, 'changeUsername'])->middleware('role:ADMINISTRADOR');
    Route::put('/change_personal_info', [AuthController::class, 'changeAddressAndPhone'])->middleware('role:SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/profile', [AuthController::class, 'profile']);
});

Route::group([
    'prefix' => '/user',
    'middleware' => 'role:ADMINISTRADOR'
], function($router){
    Route::get('/', [UserController::class, 'getUsers']);
    Route::post('/reset', [UserController::class, 'resetPassword']);
    Route::delete('/disable/{id}', [UserController::class, 'deleteUser']);
    Route::put('/enable/{id}', [UserController::class, 'restoreUser']);
});

Route::group([
    'prefix' => '/supplier',
], function($router){
    Route::post('/', [SupplierController::class, 'createSupplier'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::put('/{id}', [SupplierController::class, 'updateSupplier'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/{id}', [SupplierController::class, 'getSupplier'])->where('id','[0-9]+')->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/', [SupplierController::class, 'getSuppliers'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/all', [SupplierController::class, 'getAllSuppliers'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::delete('/{id}',[SupplierController::class, 'deleteSupplier'])->middleware('role:ADMINISTRADOR');
});

Route::group([
    'prefix' => '/presentation',
], function($router){
    Route::post('/', [PresentationController::class, 'createPresentation'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::put('/{id}', [PresentationController::class, 'updatePresentation'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/{id}', [PresentationController::class, 'getPresentation'])->where('id','[0-9]+')->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/',[PresentationController::class, 'getPresentations'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/all', [PresentationController::class, 'getAllPresentations'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
});

Route::group([
    'prefix' => '/setting'
], function($router){
    Route::post('/', [SettingController::class, 'createSetting'])->middleware('role:ADMINISTRADOR');
    Route::put('/{id}', [SettingController::class, 'updateSetting'])->middleware('role:ADMINISTRADOR');
    Route::get('/{key}', [SettingController::class, 'getSettingByKey'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/', [SettingController::class, 'getSettings'])->middleware('role:ADMINISTRADOR');
});

Route::group([
    'prefix' => '/medicine'
], function($router){
    Route::post('/', [MedicineController::class, 'createMedicine'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::put('/{id}', [MedicineController::class, 'updateMedicine'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/generate-barcode', [MedicineController::class, 'generateRandomBarcode'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/id/{id}', [MedicineController::class, 'getMedicineById'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/barcode/{barcode}', [MedicineController::class, 'getMedicineByBarcode'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/', [MedicineController::class, 'getMedicines'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
});

Route::group([
    'prefix' => '/worker'
], function($router){
    Route::post('/', [WorkerController::class, 'createWorker'])->middleware('role:ADMINISTRADOR');
    Route::put('/{id}', [WorkerController::class, 'updateWorker'])->middleware('role:ADMINISTRADOR');
    Route::get('/', [WorkerController::class, 'getWorkers'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/{id}', [WorkerController::class, 'getWorker'])->middleware('role:ADMINISTRADOR,SECRETARIA');

});