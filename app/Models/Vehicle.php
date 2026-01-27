<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Vehicle extends Model
{
  protected function serializeDate(DateTimeInterface $date)
  {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data, $id)
  {
    $rules = [
      'branch_id' => 'required|numeric',
      'vehicle_version_id' => 'required|numeric',
      'vehicle_transmission_id' => 'required|numeric',
      'vehicle_color_id' => 'required|numeric',
      'vin' => 'nullable|min:2|max:17|unique:vehicles,vin,' . $id,
      'engine_number' => 'nullable|min:2|max:30',
      'repuve' => 'nullable|min:2|max:25',
      'vehicle_key' => 'nullable|min:2|max:20',
      'passenger_capacity' => 'nullable|min:1|max:70',
      'notes' => 'nullable',
      'origin_type_id' => 'nullable|numeric',
      'pediment_number' => 'nullable|min:2|max:30',
      'pediment_date' => 'nullable|date',
      'custom_office_id' => 'nullable|numeric',
      'pediment_notes' => 'nullable',
    ];

    $msgs = ['vin.unique' => 'El VIN ya ha sido registrado'];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'A-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($request)
  {
    $items = self::query()
      ->join('purchase_order_vehicles', 'purchase_order_vehicles.vehicle_id', 'vehicles.id')
      ->join('purchase_orders', 'purchase_orders.id', 'purchase_order_vehicles.purchase_order_id')
      ->where('vehicles.is_active', 1)
      ->where('purchase_order_vehicles.is_active', 1)
      ->where('purchase_orders.is_active', 1)
      ->whereNotnull('purchase_orders.paid_at')
      ->orderByDesc('vehicles.id')
      ->get([
        'vehicles.id',
        'vehicles.vehicle_version_id',
        'vehicles.vehicle_color_id',
        'vehicles.vin',
        'purchase_order_vehicles.purchase_price',
        'purchase_orders.order_date',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = self::getUiid($item->id);
      $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
      $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
      $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
      $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
    }

    return $items;
  }

  static public function getItem($id)
  {
    $item = self::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);

    $item->uiid = Vehicle::getUiid($item->id);
    $item->branch = Branch::find($item->branch_id, ['name']);
    $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
    $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
    $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
    $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
    $item->vehicle_transmission = VehicleTransmission::find($item->vehicle_transmission_id, ['name']);
    $item->origin_type = OriginType::find($item->origin_type_id, ['name']);
    $item->custom_office = CustomOffice::find($item->custom_office_id, ['name']);

    $item->purchase_order_vehicle = PurchaseOrderVehicle::query()
      ->where('vehicle_id', $item->id)
      ->first([
        'id',
        'invoice_amount',
        'commission_amount',
        'purchase_price',
        'vat_type_id',
        'purchase_order_id',
      ]);

    $item->purchase_order_vehicle->vat_type = VatType::find($item->purchase_order_vehicle->vat_type_id, ['name']);
    $item->purchase_order_vehicle->purchase_order_uiid = PurchaseOrder::getUiid($item->purchase_order_vehicle->purchase_order_id);

    return $item;
  }
}
