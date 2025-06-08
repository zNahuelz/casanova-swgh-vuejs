<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WorkerController;
use App\Http\Middleware\BaseMiddleware;
use App\Http\Middleware\BlobResponseMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/auth',
    'middleware' => BaseMiddleware::class,
], function($router){
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/recover-account', [AuthController::class, 'sendRecoveryMail']);
    Route::post('/verify-token', [AuthController::class, 'verifyRecoveryToken']);
    Route::put('/change-password/token', [AuthController::class, 'changePasswordWithToken']);
    Route::put('/change-password', [AuthController::class, 'changePasswordAndEmail']);
    Route::put('/change-username', [AuthController::class, 'changeUsername'])->middleware('role:ADMINISTRADOR');
    Route::put('/change-personal-info', [AuthController::class, 'changeAddressAndPhone'])->middleware('role:SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/profile', [AuthController::class, 'profile']);
});

Route::group([
    'prefix' => '/user',
    'middleware' => 'role:ADMINISTRADOR'
], function($router){
    Route::get('/', [UserController::class, 'getUsers']);
    Route::post('/', [UserController::class, 'createAdmin']);
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

Route::group([
    'prefix' => '/treatment'
], function($router){
    Route::post('/', [TreatmentController::class, 'createTreatment'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::put('/{id}', [TreatmentController::class, 'updateTreatment'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/', [TreatmentController::class, 'getTreatments'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/{id}', [TreatmentController::class, 'getTreatment'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
});

Route::group([
    'prefix' => '/doctor'
], function($router){
    Route::post('/', [DoctorController::class, 'createDoctor'])->middleware('role:ADMINISTRADOR');
    Route::post('/unavailability', [DoctorController::class, 'createUnavailability'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::put('/{id}', [DoctorController::class, 'updateDoctorInfo'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::put('/availabilities/{id}', [DoctorController::class, 'updateDoctorAvailabilities'])->middleware('role:ADMINISTRADOR,SECRETARIA');
    Route::get('/', [DoctorController::class, 'getDoctors'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/available', [DoctorController::class, 'getAvailableDoctors'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::get('/all', [DoctorController::class, 'getAllDoctors'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/{id}', [DoctorController::class, 'getDoctor'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::delete('/unavailability/{id}', [DoctorController::class, 'removeUnavailability'])->middleware('role:ADMINISTRADOR,SECRETARIA');
});

Route::group([
    'prefix' => '/appointment'
], function($router){
    Route::get('/', [AppointmentController::class, 'getAppointments'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/prepare', [AppointmentController::class, 'prepareAppointment'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::get('/{id}', [AppointmentController::class, 'getAppointmentById'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::post('/', [AppointmentController::class, 'createAppointment'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::put('/', [AppointmentController::class, 'rescheduleAppointment'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::delete('/{id}', [AppointmentController::class, 'cancelAppointment'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
});

Route::group([
    'prefix' => '/patient'
], function($router){
    Route::post('/', [PatientController::class,'createPatient'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::put('/{id}', [PatientController::class, 'updatePatient'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::get('/', [PatientController::class, 'getPatients'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/{id}', [PatientController::class, 'getPatient'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/by-dni/{dni}', [PatientController::class, 'getPatientByDni'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
});

Route::group([
    'prefix' => '/payment'
], function($router){
    Route::get('/', [PaymentController::class, 'getPendingPayments'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/types', [PaymentController::class, 'getPaymentTypes'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/refunds', [PaymentController::class, 'getPendingRefunds'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::delete('/refunds/{id}', [PaymentController::class, 'deleteRefund'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::get('/{id}', [PaymentController::class, 'getInfoByAppointmentId'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::get('/by-dni/{dni}', [PaymentController::class, 'getPendingPaymentsByDni'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA,DOCTOR');
    Route::post('/verify-cart', [PaymentController::class, 'verifyShoppingCart'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
});

Route::group([
    'prefix' => '/voucher'
], function($router){
    Route::post('/', [VoucherController::class, 'createVoucher'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::get('/', [VoucherController::class, 'getVouchers'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    Route::get('/pdf/{id}', [VoucherController::class, 'getVoucherPdfById']);
    Route::get('/{id}', [VoucherController::class, 'getVoucherById'])->middleware('role:ADMINISTRADOR,SECRETARIA,ENFERMERA');
    
});