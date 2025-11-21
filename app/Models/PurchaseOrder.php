<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class PurchaseOrder extends Model
{
  protected function serializeDate(DateTimeInterface $date)
  {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  // public static function valid($data, $id)
  // {
  //   $rules = [
  //     // 'branch_id' => 'required|numeric',
  //     // 'purchase_date' => 'required|date',
  //     // 'vehicle_version_id' => 'required|numeric',
  //     // 'vehicle_color_id' => 'required|numeric',
  //     // 'vehicle_transmission_id' => 'required|numeric',
  //     // 'vin' => 'required|min:2|max:17|unique:legacy_vehicles,vin,' . $id,
  //     // 'engine_number' => 'nullable|min:2|max:30',
  //     // 'repuve' => 'nullable|min:2|max:25',
  //     // 'vehicle_key' => 'nullable|min:2|max:20',
  //     // 'notes' => 'nullable',
  //     // 'origin_type_id' => 'required|numeric',
  //     // 'pediment_number' => 'nullable|min:2|max:30',
  //     // 'pediment_date' => 'nullable|date',
  //     // 'custom_office_id' => 'nullable|numeric',
  //     // 'pediment_notes' => 'nullable',
  //   ];

  //   $msgs = ['vin.unique' => 'El VIN ya ha sido registrado'];

  //   return Validator::make($data, $rules, $msgs);
  // }

  static public function getUiid($id)
  {
    return 'OC-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req)
  {
    $items = PurchaseOrder::query()
      ->where('is_active', boolval($req->is_active))
      ->orderByDesc('id')
      ->get([
        'id',
        'is_active',
        'created_at',
        'vendor_id',
        'order_date',
        'due_date',
        'total_amount',
        'paid_at',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = LegacyVehicle::getUiid($item->id);
      // $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
      // $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
      // $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
      // $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
    }

    return $items;
  }

  // static public function getItem($id)
  // {
  //   $item = LegacyVehicle::find($id);
  //   $item->uiid = LegacyVehicle::getUiid($item->id);
  //   $item->created_by = User::find($item->created_by_id, ['email']);
  //   $item->updated_by = User::find($item->updated_by_id, ['email']);
  //   $item->branch = Branch::find($item->branch_id, ['name']);
  //   $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
  //   $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
  //   $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
  //   $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
  //   $item->vehicle_transmission = VehicleTransmission::find($item->vehicle_transmission_id, ['name']);
  //   $item->origin_type = OriginType::find($item->origin_type_id, ['name']);
  //   $item->custom_office = CustomOffice::find($item->custom_office_id, ['name']);

  //   return $item;
  // }

  static public function getPurchaseOrderPayments($req, $vendor_id)
  {
    $vendor_banks = VendorBank::query()
      ->where('vendor_id', $vendor_id)
      ->where('is_active', 1)
      ->get();

    $items = [];

    foreach ($vendor_banks as $vendor_bank) {
      $items[] = [
        'id' => null,
        'is_active' => 1,
        'bank_id' => $vendor_bank->bank_id,
        'account_holder' => $vendor_bank->account_holder,
        'clabe_number' => $vendor_bank->clabe_number,
        'account_number' => $vendor_bank->account_number,
        'amount' => null,
        'bank' => Bank::find($vendor_bank->bank_id, ['name'])
      ];
    }

    return $items;
  }
}
