<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use App\Http\Controllers\GenController;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicle extends Model {
  protected function serializeDate(DateTimeInterface $date) {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data) {
    $rules = [
      'branch_id' => 'required|numeric',
      'purchase_date' => 'required|date',
      'vehicle_version_id' => 'required|numeric',
      'vehicle_color_id' => 'required|numeric',
      'vehicle_transmission_id' => 'required|numeric',
      'vin' => 'required|min:2|max:17',
      'engine_number' => 'nullable|min:2|max:30',
      'repuve' => 'nullable|min:2|max:25',
      'vehicle_key' => 'nullable|min:2|max:20',
      'notes' => 'nullable',
      'origin_type_id' => 'required|numeric',
      'pediment_number' => 'nullable|min:2|max:30',
      'pediment_date' => 'nullable|date',
      'custom_office_id' => 'nullable|numeric',
      'pediment_notes' => 'nullable',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id) {
    return 'AH-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req) {
    $items = LegacyVehicle::query()
      ->where('is_active', boolval($req->is_active))
      ->orderByDesc('purchase_date')
      ->get([
        'id',
        'is_active',
        'purchase_date',
        'vehicle_version_id',
        'vehicle_color_id',
        'vin',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = LegacyVehicle::getUiid($item->id);
      $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
      $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
      $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
      $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
    }

    return $items;
  }

  static public function getItem($id) {
    $item = LegacyVehicle::find($id, [
      'id',
      'is_active',
      'created_at',
      'updated_at',
      'created_by_id',
      'updated_by_id',
      'branch_id',
      'purchase_date',
      'vehicle_version_id',
      'vehicle_color_id',
      'vehicle_transmission_id',
      'vin',
      'engine_number',
      'repuve',
      'vehicle_key',
      'notes',
      'origin_type_id',
      'pediment_number',
      'pediment_date',
      'custom_office_id',
      'pediment_notes',
      'passenger_capacity'
    ]);

    $item->uiid = LegacyVehicle::getUiid($item->id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->branch = Branch::find($item->branch_id, ['name']);
    $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
    $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
    $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
    $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
    $item->vehicle_transmission = VehicleTransmission::find($item->vehicle_transmission_id, ['name']);
    $item->origin_type = OriginType::find($item->origin_type_id, ['name']);
    $item->custom_office = CustomOffice::find($item->custom_office_id, ['name']);

    return $item;
  }
}
