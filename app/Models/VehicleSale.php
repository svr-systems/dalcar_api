<?php

namespace App\Models;

use App\Http\Controllers\GenController;
use Illuminate\Database\Eloquent\Model;
use Validator;

class VehicleSale extends Model
{
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
    'is_active' => 'boolean',
    'is_finance' => 'boolean',
  ];

  public static function validStore($data)
  {
    $data['is_finance'] = filter_var($data['is_finance'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    $rules = [
      'vehicle_id' => 'required|numeric',
      'customer_id' => 'required|numeric',
      'vehicle_reservation_id' => 'nullable|numeric',
      'seller_user_id' => 'required|numeric',
      'is_finance' => 'required|boolean',
      'financier_id' => 'nullable|numeric',
      'notes' => 'nullable',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  public static function getItem($id)
  {
    $item = self::query()->find($id);

    if (!$item) {
      return null;
    }

    $item->created_by = User::query()->find($item->created_by_id, ['id', 'email']);
    $item->updated_by = User::query()->find($item->updated_by_id, ['id', 'email']);

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

    $item->customer = Customer::query()->find($item->customer_id, [
      'id',
      'user_id',
      'name',
      'paternal_surname',
      'maternal_surname',
      'email',
      'phone',
      'ine_path',
      'rfc',
      'notes',
    ]);

    if ($item->customer) {
      $item->customer->full_name = GenController::getFullName($item->customer);
    }

    $item->vehicle_reservation = VehicleReservation::query()->find($item->vehicle_reservation_id, [
      'id',
      'reservation_amount',
      'payment_method_id',
      'response_at',
      'expires_at',
      'paid_at',
      'paid_by_id',
    ]);

    if ($item->vehicle_reservation) {
      $item->vehicle_reservation->payment_method = PaymentMethod::query()->find(
        $item->vehicle_reservation->payment_method_id,
        ['id', 'name']
      );

      $item->vehicle_reservation->paid_by = User::query()->find(
        $item->vehicle_reservation->paid_by_id,
        ['id', 'email']
      );
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

    $item->financier = Financier::query()->find($item->financier_id, ['id', 'name']);

    $item->payments = VehicleSalePayment::getItemsByVehicleSaleId($item->id);
    $item->reservation_payment = collect($item->payments)->firstWhere('sale_payment_type_id', 1);

    return $item;
  }
}