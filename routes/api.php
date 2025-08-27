<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchContrller;
use App\Http\Controllers\CompanyContrller;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserAssistanceController;
use App\Http\Controllers\UserAssistanceFileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('login', [AuthController::class, 'login']);
Route::post('login', [AuthController::class, 'login']);

// Route::get('credential/{user_id}', [ImageController::class, 'UserDNI']);

Route::group(['middleware' => 'auth:api'], function () {

    //Catalogs
    Route::get('roles', [RoleController::class, 'index']);

    //User Assistance File
    Route::post('users/assistance/file/records', [UserAssistanceFileController::class, 'showAssistanceFileRegisters']);
    Route::apiResource('users/assistance/file', UserAssistanceFileController::class);

    //Schedules
    Route::apiResource('schedule', ScheduleController::class);

    //User Assistance
    Route::apiResource('users/assistance', UserAssistanceController::class);

    //Users
    Route::get('users/file/json', [UserController::class, 'getUserFile']);
    Route::post('users/dni', [UserController::class, 'getDni']);
    Route::apiResource('users', UserController::class);

    Route::apiResource('company/branch', BranchContrller::class);
    Route::apiResource('company', CompanyContrller::class);
});