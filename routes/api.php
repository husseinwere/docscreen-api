<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Employee\DocumentTypeController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Employee\SubmissionRequestController;
use App\Http\Controllers\Employee\UploadController;
use App\Http\Controllers\Employer\EmployerController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// AUTH/USERS
Route::post('/auth/login', [UserController::class, 'login']);

// EMPLOYERS
Route::post('/employers', [EmployerController::class, 'store']);

// UPLOADS
Route::get('/uploads', [UploadController::class, 'index']);
Route::post('/uploads', [UploadController::class, 'store']);

// SUBMISSION REQUESTS
Route::get('/submission-requests', [SubmissionRequestController::class, 'index']);

// EMPLOYEES
Route::get('/employees/{id}', [EmployeeController::class, 'show']);

Route::group(['middleware' => ['auth:sanctum']], function() {

    // AUTH/USERS
    Route::get('/auth/logout', [UserController::class, 'logout']);
    Route::get('/auth/{id}/reset-password', [UserController::class, 'resetPassword']);

    // ADMINS
    Route::resource('admins', AdminController::class);

    // EMPLOYERS
    Route::get('/employers', [EmployerController::class, 'index']);
    Route::get('/employers/{id}', [EmployerController::class, 'show']);
    Route::put('/employers/{id}', [EmployerController::class, 'update']);
    Route::delete('/employers/{id}', [EmployerController::class, 'destroy']);

    // DOCUMENT TYPES
    Route::resource('document-types', DocumentTypeController::class);

    // UPLOADS
    Route::put('/uploads/{id}', [UploadController::class, 'update']);
    Route::delete('/uploads/{id}', [UploadController::class, 'destroy']);

    // SUBMISSION REQUESTS
    Route::post('/submission-requests', [SubmissionRequestController::class, 'store']);
    Route::post('/submission-requests/bulk/store', [SubmissionRequestController::class, 'storeBulk']);
    Route::put('/submission-requests/{id}', [SubmissionRequestController::class, 'update']);
    Route::delete('/submission-requests/{id}', [SubmissionRequestController::class, 'destroy']);

    //EMPLOYEES
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    Route::post('/employees/bulk-create', [EmployeeController::class, 'storeBulk']);

});
