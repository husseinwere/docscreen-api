<?php

use App\Http\Controllers\Upload\DocUploadController;
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
Route::get('/users/slug/{slug}', [UserController::class, 'getUserBySlug']);

// UPLOADS
Route::post('/uploads', [DocUploadController::class, 'store']);

Route::group(['middleware' => ['auth:sanctum']], function() {

    // AUTH/USERS
    Route::get('/auth/logout', [UserController::class, 'logout']);
    Route::resource('users', UserController::class);
    Route::get('/users/{id}/reset-password', [UserController::class, 'resetPassword']);

    //UPLOADS
    Route::get('/uploads', [DocUploadController::class, 'index']);
    Route::put('/uploads/{id}', [DocUploadController::class, 'update']);
    Route::delete('/uploads/{id}', [DocUploadController::class, 'destroy']);
    Route::post('/uploads/request', [DocUploadController::class, 'requestUpload']);
    Route::post('/uploads/bulk-request', [DocUploadController::class, 'bulkRequestUpload']);

    //EMPLOYEES
    Route::resource('employees', UserController::class);

});
