<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BranchContrller;
use App\Http\Controllers\CompanyContrller;
use App\Http\Controllers\CustomOfficeController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\InvestorTypeController;
use App\Http\Controllers\LegacyVehicleController;
use App\Http\Controllers\LegacyVehicleDocumentController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\OriginTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VatTypeController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\VehicleColorController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\VehicleTransmissionController;
use App\Http\Controllers\VehicleVersionController;
use App\Http\Controllers\VendorCotroller;
use App\Http\Controllers\VendorTypeController;
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
    Route::get('states', [StateController::class, 'index']);
    Route::get('municipalities', [MunicipalityController::class, 'index']);
    Route::get('investor_types', [InvestorTypeController::class, 'index']);
    Route::get('vendor_types', [VendorTypeController::class, 'index']);
    Route::get('banks', [BankController::class, 'index']);
    Route::get('vehicle_transmissions', [VehicleTransmissionController::class, 'index']);
    Route::get('vat_types', [VatTypeController::class, 'index']);
    Route::get('origin_types', [OriginTypeController::class, 'index']);
    Route::get('customs_offices', [CustomOfficeController::class, 'index']);

    //Catalogs CRUD
    Route::apiResource('vehicle_brands', VehicleBrandController::class);
    Route::apiResource('vehicle_models', VehicleModelController::class);
    Route::apiResource('vehicle_versions', VehicleVersionController::class);
    Route::apiResource('vehicle_colors', VehicleColorController::class);
    Route::apiResource('expense_types', ExpenseTypeController::class);
    Route::apiResource('document_types', DocumentTypeController::class);

    //Users
    Route::get('users/file/json', [UserController::class, 'getUserFile']);
    Route::post('users/dni', [UserController::class, 'getDni']);
    Route::apiResource('users', UserController::class);

    //Branches
    Route::apiResource('companies/branches', BranchContrller::class);

    //Companies
    Route::apiResource('companies', CompanyContrller::class);

    //Investors
    Route::apiResource('investors', InvestorController::class);

    //Vendors
    Route::apiResource('vendors', VendorCotroller::class);

    //Legacy vehicle documents
    Route::apiResource('legacy_vehicle_documents', LegacyVehicleDocumentController::class);

    //Legacy vehicles
    Route::apiResource('legacy_vehicles', LegacyVehicleController::class);
});