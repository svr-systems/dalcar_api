<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class PurchaseOrderVehicle extends Model
{
  protected function serializeDate(DateTimeInterface $date)
  {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data)
  {
    $rules = [
      'purchase_order_id' => 'required|numeric',
      'purchase_price' => 'required|numeric',
      'commission_amount' => 'required|numeric',
      'vat_type_id' => 'required|numeric',
      'invoice_amount' => 'nullable|numeric',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($req)
  {
    $items = self::query()
      ->where('purchase_order_id', $req->purchase_order_id)
      ->where('is_active', 1)
      ->get([
        'id',
        'is_active',
        'vehicle_id',
        'invoice_amount',
        'commission_amount',
        'purchase_price',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->vehicle = Vehicle::find($item->vehicle_id, [
        'id',
        'vehicle_version_id',
        'vehicle_color_id',
        'vin',
      ]);

      $item->vehicle->vehicle_version = VehicleVersion::find($item->vehicle->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
      $item->vehicle->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
      $item->vehicle->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
      $item->vehicle->vehicle_color = VehicleColor::find($item->vehicle->vehicle_color_id, ['name']);
    }

    return $items;
  }

  static public function getItem($id)
  {
    $item = self::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->vat_type = VatType::find($item->vat_type_id, ['name']);

    $item->vehicle = Vehicle::find($item->vehicle_id);
    $item->vehicle->uiid = Vehicle::getUiid($item->vehicle->id);
    $item->vehicle->branch = Branch::find($item->vehicle->branch_id, ['name']);
    $item->vehicle->vehicle_version = VehicleVersion::find($item->vehicle->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
    $item->vehicle->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
    $item->vehicle->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
    $item->vehicle->vehicle_color = VehicleColor::find($item->vehicle->vehicle_color_id, ['name']);
    $item->vehicle->vehicle_transmission = VehicleTransmission::find($item->vehicle->vehicle_transmission_id, ['name']);
    $item->vehicle->origin_type = OriginType::find($item->vehicle->origin_type_id, ['name']);
    $item->vehicle->custom_office = CustomOffice::find($item->vehicle->custom_office_id, ['name']);

    return $item;
  }
}
