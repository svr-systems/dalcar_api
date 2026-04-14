<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomOfficeController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\FinancierController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\InvestorTypeController;
use App\Http\Controllers\InvoiceTypeController;
use App\Http\Controllers\LegacyVehicleController;
use App\Http\Controllers\LegacyVehicleDocumentController;
use App\Http\Controllers\LegacyVehicleExpenseController;
use App\Http\Controllers\LegacyVehicleInvestorController;
use App\Http\Controllers\LegacyVehicleInvoiceController;
use App\Http\Controllers\LegacyVehicleTradeController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\OriginTypeController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderReceiptController;
use App\Http\Controllers\PurchaseOrderVehicleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VatTypeController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\VehicleColorController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleDocumentController;
use App\Http\Controllers\VehicleExpenseController;
use App\Http\Controllers\VehicleInvestorController;
use App\Http\Controllers\VehicleInvoiceController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\VehicleReservationController;
use App\Http\Controllers\VehicleSaleController;
use App\Http\Controllers\VehicleTransmissionController;
use App\Http\Controllers\VehicleVersionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorTypeController;
use Illuminate\Support\Facades\Route;

/**
 * ===========================================
 * Public
 * ===========================================
 */
Route::post('login', [AuthController::class, 'login']);

/**
 * ===========================================
 * Authenticated
 * ===========================================
 */
Route::group(['middleware' => 'auth:api'], function () {
  Route::post('logout', [AuthController::class, 'logout']);
});

/**
 * ===========================================
 * System
 * ===========================================
 */
Route::group(['middleware' => ['auth:api', 'system']], function () {
  /**
   * ===========================================
   * Vehicle Sales
   * ===========================================
   */
  Route::group(['prefix' => 'vehicle_sales'], function () {
    Route::get('reservations', [VehicleSaleController::class, 'getReservationItems']);
    Route::get('reservations/{vehicle_reservation_id}', [VehicleSaleController::class, 'getReservationItem']);
    Route::get('{id}', [VehicleSaleController::class, 'show']);
    Route::get('{id}/receipt', [VehicleSaleController::class, 'getReceipt']);
    Route::post('', [VehicleSaleController::class, 'store']);
  });

  /**
   * ===========================================
   * Vehicle Reservations
   * ===========================================
   */
  Route::group(['prefix' => 'vehicle_reservations'], function () {
    Route::patch('{id}/response', [VehicleReservationController::class, 'response']);
    Route::get('{id}', [VehicleReservationController::class, 'show']);
    Route::get('', [VehicleReservationController::class, 'index']);
  });

  /**
   * ===========================================
   * Vehicles
   * ===========================================
   */
  Route::group(['prefix' => 'vehicles'], function () {
    Route::patch('{vehicle_id}/published_status', [VehicleController::class, 'togglePublishedStatus']);
    Route::patch('{vehicle_id}/sale_price', [VehicleController::class, 'updateSalePrice']);

    Route::delete('vehicle_documents/{id}', [VehicleDocumentController::class, 'destroy']);
    Route::patch('vehicle_documents/{id}', [VehicleDocumentController::class, 'update']);
    Route::get('vehicle_documents/{id}', [VehicleDocumentController::class, 'show']);
    Route::post('vehicle_documents', [VehicleDocumentController::class, 'store']);
    Route::get('{vehicle_id}/vehicle_documents', [VehicleDocumentController::class, 'index']);

    Route::delete('vehicle_invoices/{id}', [VehicleInvoiceController::class, 'destroy']);
    Route::patch('vehicle_invoices/{id}', [VehicleInvoiceController::class, 'update']);
    Route::get('vehicle_invoices/{id}', [VehicleInvoiceController::class, 'show']);
    Route::post('vehicle_invoices', [VehicleInvoiceController::class, 'store']);
    Route::get('{vehicle_id}/vehicle_invoices', [VehicleInvoiceController::class, 'index']);

    Route::delete('vehicle_expenses/{id}', [VehicleExpenseController::class, 'destroy']);
    Route::patch('vehicle_expenses/{id}', [VehicleExpenseController::class, 'update']);
    Route::get('vehicle_expenses/{id}', [VehicleExpenseController::class, 'show']);
    Route::post('vehicle_expenses', [VehicleExpenseController::class, 'store']);
    Route::get('{vehicle_id}/vehicle_expenses', [VehicleExpenseController::class, 'index']);

    Route::delete('vehicle_investors/{id}', [VehicleInvestorController::class, 'destroy']);
    Route::patch('vehicle_investors/{id}', [VehicleInvestorController::class, 'update']);
    Route::get('vehicle_investors/{id}', [VehicleInvestorController::class, 'show']);
    Route::post('vehicle_investors', [VehicleInvestorController::class, 'store']);
    Route::get('{vehicle_id}/vehicle_investors', [VehicleInvestorController::class, 'index']);
  });
  Route::apiResource('vehicles', VehicleController::class);

  /**
   * ===========================================
   * Purchase Orders
   * ===========================================
   */
  Route::group(['prefix' => 'purchase_orders'], function () {
    Route::post('purchase_order_receipts', [PurchaseOrderReceiptController::class, 'storeUpdate']);
    Route::get('{purchase_order_id}/purchase_order_receipts', [PurchaseOrderReceiptController::class, 'index']);

    Route::delete('purchase_order_vehicles/{id}', [PurchaseOrderVehicleController::class, 'destroy']);
    Route::patch('purchase_order_vehicles/{id}', [PurchaseOrderVehicleController::class, 'update']);
    Route::get('purchase_order_vehicles/{id}', [PurchaseOrderVehicleController::class, 'show']);
    Route::post('purchase_order_vehicles', [PurchaseOrderVehicleController::class, 'store']);
    Route::get('{purchase_order_id}/purchase_order_vehicles', [PurchaseOrderVehicleController::class, 'index']);

    Route::get('vendor', [VendorController::class, 'getItemToPurchaseOrder']);
    Route::post('restore', [PurchaseOrderController::class, 'restore']);
  });
  Route::apiResource('purchase_orders', PurchaseOrderController::class);

  /**
   * ===========================================
   * Legacy Vehicles
   * ===========================================
   */
  Route::group(['prefix' => 'legacy_vehicles'], function () {
    Route::apiResource('legacy_vehicle_invoices', LegacyVehicleInvoiceController::class);
    Route::apiResource('legacy_vehicle_documents', LegacyVehicleDocumentController::class);
    Route::apiResource('legacy_vehicle_expenses', LegacyVehicleExpenseController::class);
    Route::apiResource('legacy_vehicle_investors', LegacyVehicleInvestorController::class);
    Route::apiResource('legacy_vehicles_trades', LegacyVehicleTradeController::class);
    Route::post('restore', [LegacyVehicleController::class, 'restore']);
  });
  Route::apiResource('legacy_vehicles', LegacyVehicleController::class);

  /**
   * ===========================================
   * Financiers
   * ===========================================
   */
  Route::group(['prefix' => 'financiers'], function () {
    Route::post('restore', [FinancierController::class, 'restore']);
  });
  Route::apiResource('financiers', FinancierController::class);

  /**
   * ===========================================
   * Vendors
   * ===========================================
   */
  Route::group(['prefix' => 'vendors'], function () {
    Route::post('restore', [VendorController::class, 'restore']);
  });
  Route::apiResource('vendors', VendorController::class);

  /**
   * ===========================================
   * Investors
   * ===========================================
   */
  Route::apiResource('investors', InvestorController::class);

  /**
   * ===========================================
   * Companies
   * ===========================================
   */
  Route::apiResource('companies/branches', BranchController::class);
  Route::apiResource('companies', CompanyController::class);

  /**
   * ===========================================
   * Users
   * ===========================================
   */
  Route::get('users/file/json', [UserController::class, 'getUserFile']);
  Route::post('users/dni', [UserController::class, 'getDni']);
  Route::apiResource('users', UserController::class);

  /**
   * ===========================================
   * Catalogs CRUD
   * ===========================================
   */
  Route::apiResource('invoice_types', InvoiceTypeController::class);
  Route::apiResource('expense_types', ExpenseTypeController::class);
  Route::apiResource('document_types', DocumentTypeController::class);
  Route::apiResource('custom_offices', CustomOfficeController::class);
  Route::apiResource('vehicle_transmissions', VehicleTransmissionController::class);
  Route::apiResource('vehicle_colors', VehicleColorController::class);
  Route::apiResource('vehicle_versions', VehicleVersionController::class);
  Route::apiResource('vehicle_models', VehicleModelController::class);

  Route::get('vehicle_brands/{id}/catalogs', [VehicleBrandController::class, 'getItemCatalogs']);
  Route::apiResource('vehicle_brands', VehicleBrandController::class);

  /**
   * ===========================================
   * Catalogs
   * ===========================================
   */
  Route::get('payment_methods', [PaymentMethodController::class, 'index']);
  Route::get('vat_types', [VatTypeController::class, 'index']);
  Route::get('origin_types', [OriginTypeController::class, 'index']);
  Route::get('banks', [BankController::class, 'index']);
  Route::get('vendor_types', [VendorTypeController::class, 'index']);
  Route::get('investor_types', [InvestorTypeController::class, 'index']);
  Route::get('municipalities', [MunicipalityController::class, 'index']);
  Route::get('states', [StateController::class, 'index']);
  Route::get('roles', [RoleController::class, 'index']);
});

/**
 * ===========================================
 * Seller (role_id == 5)
 * ===========================================
 */
Route::group([
  'prefix' => 'seller',
  'middleware' => ['auth:api', 'seller'],
], function () {
  /**
   * ===========================================
   * Vehicle Reservations
   * ===========================================
   */
  Route::get('vehicles/{vehicle_id}/reservation', [VehicleReservationController::class, 'sellerShow']);
  Route::post('vehicle_reservations', [VehicleReservationController::class, 'sellerStore']);

  /**
   * ===========================================
   * Vehicles
   * ===========================================
   */
  Route::get('vehicles', [VehicleController::class, 'sellerIndex']);
  Route::get('vehicles/{vehicle_id}', [VehicleController::class, 'sellerShow']);

  /**
   * ===========================================
   * Catalogs
   * ===========================================
   */
  Route::get('payment_methods', [PaymentMethodController::class, 'index']);
  Route::get('financiers', [FinancierController::class, 'index']);
});
