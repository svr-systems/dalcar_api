<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use App\Http\Controllers\GenController;
use Illuminate\Database\Eloquent\Model;
use Validator;

class VehicleReservation extends Model
{
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
    'is_active' => 'boolean',
    'is_approved' => 'boolean',
    'response_at' => 'datetime:Y-m-d H:i:s',
    'expires_at' => 'date:Y-m-d',
    'is_finance' => 'boolean',
    'is_preapproved' => 'boolean',
    'has_trade_in' => 'boolean',
    'trade_in_is_refactored' => 'boolean',
  ];

  public static function validStore($data)
  {
    $data['is_finance'] = filter_var($data['is_finance'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $data['is_preapproved'] = filter_var($data['is_preapproved'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $data['has_trade_in'] = filter_var($data['has_trade_in'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $data['trade_in_is_refactored'] = filter_var($data['trade_in_is_refactored'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    $rules = [
      'vehicle_id' => 'required|numeric',
      'customer_name' => 'required|min:2|max:191',
      'customer_paternal_surname' => 'required|min:2|max:25',
      'customer_maternal_surname' => 'nullable|min:2|max:25',
      'customer_email' => 'required|email|max:191',
      'customer_phone' => 'nullable|min:10|max:15',
      'is_finance' => 'required|boolean',
      'financier_id' => 'nullable|numeric',
      'is_preapproved' => 'nullable|boolean',
      'reservation_days' => 'required|numeric|min:1',
      'reservation_amount' => 'required|numeric|min:0',
      'payment_method_id' => 'required|numeric',
      'has_trade_in' => 'required|boolean',
      'trade_in_brand' => 'nullable|max:60',
      'trade_in_model' => 'nullable|max:60',
      'trade_in_version' => 'nullable|max:60',
      'trade_in_model_year' => 'nullable|numeric',
      'trade_in_color' => 'nullable|max:40',
      'trade_in_km' => 'nullable|numeric|min:0',
      'trade_in_invoice_type' => 'nullable|max:40',
      'trade_in_is_refactored' => 'nullable|boolean',
      'notes' => 'nullable',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  public static function hasBlockingReservation($vehicle_id, $seller_user_id = null): bool
  {
    $query = self::query()
      ->where('vehicle_id', $vehicle_id)
      ->where('is_active', 1)
      ->where(function ($query) {
        $query
          ->whereNull('is_approved')
          ->orWhere('is_approved', 1);
      });

    if (!is_null($seller_user_id)) {
      $query->where('seller_user_id', '!=', $seller_user_id);
    }

    return $query->exists();
  }

  public static function getItemSeller($vehicle_id, $seller_user_id)
  {
    return self::query()
      ->where('vehicle_id', $vehicle_id)
      ->where('seller_user_id', $seller_user_id)
      ->where('is_active', 1)
      ->where(function ($query) {
        $query
          ->whereNull('is_approved')
          ->orWhere('is_approved', 1);
      })
      ->orderByDesc('id')
      ->first();
  }

  public static function getItems($request)
  {
    $filter = in_array((int) $request->filter, [1, 2, 3]) ? (int) $request->filter : 1;

    $items = self::query()
      ->join('vehicles', 'vehicles.id', '=', 'vehicle_reservations.vehicle_id')
      ->join('vehicle_versions', 'vehicle_versions.id', '=', 'vehicles.vehicle_version_id')
      ->join('vehicle_models', 'vehicle_models.id', '=', 'vehicle_versions.vehicle_model_id')
      ->join('vehicle_brands', 'vehicle_brands.id', '=', 'vehicle_models.vehicle_brand_id')
      ->join('vehicle_colors', 'vehicle_colors.id', '=', 'vehicles.vehicle_color_id')
      ->join('users AS seller_users', 'seller_users.id', '=', 'vehicle_reservations.seller_user_id')
      ->leftJoin('payment_methods', 'payment_methods.id', '=', 'vehicle_reservations.payment_method_id')
      ->leftJoin('financiers', 'financiers.id', '=', 'vehicle_reservations.financier_id');

    if ($filter === 1) {
      $items
        ->where('vehicle_reservations.is_active', 1)
        ->whereNull('vehicle_reservations.is_approved');
    }

    if ($filter === 2) {
      $items
        ->where('vehicle_reservations.is_active', 1)
        ->where('vehicle_reservations.is_approved', 1);
    }

    if ($filter === 3) {
      $items->where('vehicle_reservations.is_approved', 0);
    }

    $items = $items
      ->orderByDesc('vehicle_reservations.id')
      ->get([
        'vehicle_reservations.id',
        'vehicle_reservations.is_approved',
        'vehicle_brands.name AS vehicle_brand_name',
        'vehicle_models.name AS vehicle_model_name',
        'seller_users.name',
        'seller_users.paternal_surname',
        'seller_users.maternal_surname',
        'vehicle_reservations.customer_name',
        'vehicle_reservations.customer_paternal_surname',
        'vehicle_reservations.customer_maternal_surname',
        'vehicles.id AS vehicle_id',
        'vehicle_versions.model_year AS vehicle_version_model_year',
        'vehicle_colors.name AS vehicle_color_name',
        'payment_methods.name AS payment_method_name',
        'financiers.name AS financier_name',
        'vehicle_reservations.reservation_amount',
        'vehicle_reservations.created_at',
        'vehicle_reservations.expires_at',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->vehicle_uiid = Vehicle::getUiid($item->vehicle_id);
      $item->seller_user_full_name = GenController::getFullName($item);
      $item->customer_full_name = trim($item->customer_name . ' ' . $item->customer_paternal_surname . ' ' . $item->customer_maternal_surname);
    }

    return $items;
  }

  public static function getItem($id)
  {
    $item = self::query()->find($id);

    if (!$item) {
      return null;
    }

    $item->created_by = User::query()->find($item->created_by_id, ['id', 'email']);
    $item->updated_by = User::query()->find($item->updated_by_id, ['id', 'email']);
    $item->seller_user = User::query()->find($item->seller_user_id, [
      'id',
      'email',
      'name',
      'paternal_surname',
      'maternal_surname',
    ]);

    if ($item->seller_user) {
      $item->seller_user->full_name = GenController::getFullName($item->seller_user);
    }

    $item->response_by = User::query()->find($item->response_by_id, ['id', 'email']);

    $item->vehicle = Vehicle::query()->find($item->vehicle_id, [
      'id',
      'vehicle_version_id',
      'vehicle_color_id',
      'vehicle_transmission_id',
      'vin',
      'engine_number',
      'repuve',
      'vehicle_key',
      'passenger_capacity',
      'notes',
      'sale_price',
    ]);

    if ($item->vehicle) {
      $item->vehicle->uiid = Vehicle::getUiid($item->vehicle->id);
      $item->vehicle->vehicle_version = VehicleVersion::query()->find(
        $item->vehicle->vehicle_version_id,
        ['name', 'vehicle_model_id', 'model_year']
      );

      if ($item->vehicle->vehicle_version) {
        $item->vehicle->vehicle_version->vehicle_model = VehicleModel::query()->find(
          $item->vehicle->vehicle_version->vehicle_model_id,
          ['name', 'vehicle_brand_id']
        );

        if ($item->vehicle->vehicle_version->vehicle_model) {
          $item->vehicle->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::query()->find(
            $item->vehicle->vehicle_version->vehicle_model->vehicle_brand_id,
            ['name']
          );
        }
      }

      $item->vehicle->vehicle_color = VehicleColor::query()->find($item->vehicle->vehicle_color_id, ['name']);
      $item->vehicle->vehicle_transmission = VehicleTransmission::query()->find(
        $item->vehicle->vehicle_transmission_id,
        ['name']
      );
    }

    $item->financier = Financier::query()->find($item->financier_id, ['id', 'name']);
    $item->payment_method = PaymentMethod::query()->find($item->payment_method_id, ['id', 'name']);

    $item->customer_ine_b64 = DocMgrController::getB64($item->customer_ine_path, 'VehicleReservation');
    $item->preapproval_b64 = DocMgrController::getB64($item->preapproval_path, 'VehicleReservation');

    $item->suggested_expires_at = is_null($item->is_approved) && $item->created_at
      ? $item->created_at->copy()->addDays((int) $item->reservation_days)->format('Y-m-d')
      : null;

    return $item;
  }

  public static function getItemsToSale($request)
  {
    $items = self::query()
      ->join('vehicles', 'vehicles.id', '=', 'vehicle_reservations.vehicle_id')
      ->join('vehicle_versions', 'vehicle_versions.id', '=', 'vehicles.vehicle_version_id')
      ->join('vehicle_models', 'vehicle_models.id', '=', 'vehicle_versions.vehicle_model_id')
      ->join('vehicle_brands', 'vehicle_brands.id', '=', 'vehicle_models.vehicle_brand_id')
      ->join('users AS seller_users', 'seller_users.id', '=', 'vehicle_reservations.seller_user_id')
      ->leftJoin('payment_methods', 'payment_methods.id', '=', 'vehicle_reservations.payment_method_id')
      ->where('vehicle_reservations.is_active', 1)
      ->where('vehicle_reservations.is_approved', 1)
      ->whereNull('vehicle_reservations.paid_at')
      ->orderByDesc('vehicle_reservations.id')
      ->get([
        'vehicle_reservations.id',
        'vehicle_reservations.customer_name',
        'vehicle_reservations.customer_paternal_surname',
        'vehicle_reservations.customer_maternal_surname',
        'vehicle_reservations.reservation_amount',
        'vehicle_reservations.expires_at',
        'vehicles.id AS vehicle_id',
        'vehicle_brands.name AS vehicle_brand_name',
        'vehicle_models.name AS vehicle_model_name',
        'vehicle_versions.model_year AS vehicle_version_model_year',
        'seller_users.name',
        'seller_users.paternal_surname',
        'seller_users.maternal_surname',
        'payment_methods.name AS payment_method_name',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->vehicle_uiid = Vehicle::getUiid($item->vehicle_id);

      $item->customer_full_name = GenController::getFullName((object) [
        'name' => $item->customer_name,
        'paternal_surname' => $item->customer_paternal_surname,
        'maternal_surname' => $item->customer_maternal_surname,
      ]);

      $item->seller_user_full_name = GenController::getFullName($item);
    }

    return $items;
  }

  public static function getItemToSale($id)
  {
    $item = self::query()->find($id);

    if (!$item) {
      return null;
    }

    if (!boolval($item->is_active)) {
      return null;
    }

    if (!boolval($item->is_approved)) {
      return null;
    }

    if (!is_null($item->paid_at)) {
      return null;
    }

    $item->seller_user = User::query()->find($item->seller_user_id, [
      'id',
      'email',
      'name',
      'paternal_surname',
      'maternal_surname',
    ]);

    if ($item->seller_user) {
      $item->seller_user->full_name = GenController::getFullName($item->seller_user);
    }

    $item->paid_by = User::query()->find($item->paid_by_id, ['id', 'email']);
    $item->response_by = User::query()->find($item->response_by_id, ['id', 'email']);

    $item->vehicle = Vehicle::query()->find($item->vehicle_id, [
      'id',
      'vehicle_version_id',
      'vehicle_color_id',
      'vehicle_transmission_id',
      'vin',
      'engine_number',
      'repuve',
      'vehicle_key',
      'passenger_capacity',
      'notes',
      'sale_price',
      'final_sale_price',
      'sale_commission_amount',
    ]);

    if ($item->vehicle) {
      $item->vehicle->uiid = Vehicle::getUiid($item->vehicle->id);

      $item->vehicle->vehicle_version = VehicleVersion::query()->find(
        $item->vehicle->vehicle_version_id,
        ['name', 'vehicle_model_id', 'model_year']
      );

      if ($item->vehicle->vehicle_version) {
        $item->vehicle->vehicle_version->vehicle_model = VehicleModel::query()->find(
          $item->vehicle->vehicle_version->vehicle_model_id,
          ['name', 'vehicle_brand_id']
        );

        if ($item->vehicle->vehicle_version->vehicle_model) {
          $item->vehicle->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::query()->find(
            $item->vehicle->vehicle_version->vehicle_model->vehicle_brand_id,
            ['name']
          );
        }
      }

      $item->vehicle->vehicle_color = VehicleColor::query()->find(
        $item->vehicle->vehicle_color_id,
        ['name']
      );

      $item->vehicle->vehicle_transmission = VehicleTransmission::query()->find(
        $item->vehicle->vehicle_transmission_id,
        ['name']
      );
    }

    $item->payment_method = PaymentMethod::query()->find($item->payment_method_id, ['id', 'name']);
    $item->financier = Financier::query()->find($item->financier_id, ['id', 'name']);

    $item->customer_ine_b64 = DocMgrController::getB64($item->customer_ine_path, 'VehicleReservation');
    $item->customer_ine_doc = null;

    $item->preapproval_b64 = DocMgrController::getB64($item->preapproval_path, 'VehicleReservation');

    $item->customer_full_name = GenController::getFullName((object) [
      'name' => $item->customer_name,
      'paternal_surname' => $item->customer_paternal_surname,
      'maternal_surname' => $item->customer_maternal_surname,
    ]);

    return $item;
  }
}