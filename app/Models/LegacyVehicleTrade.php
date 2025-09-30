<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicleTrade extends Model {
  protected function serializeDate(DateTimeInterface $date) {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data, $is_req = true) {
    $rules = [
      'legacy_vehicle_id' => 'required|numeric',
      'is_purchase' => 'required|boolean',
      'vendor_id' => 'required|numeric',
      'purchase_price' => 'required|numeric',
      'commission_amount' => 'required|numeric',
      'vat_type_id' => 'required|numeric',
      'invoice_amount' => 'nullable|numeric',
      'sale_price' => 'nullable|numeric',
      'note' => 'nullable|min:2',
    ];

    if (!$is_req) {
      array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
    }

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id) {
    return 'LVT-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req) {
    $items = LegacyVehicleTrade::query()->
      where('legacy_vehicle_id', $req->legacy_vehicle_id)->
      where('is_active', boolval($req->is_active))->
      get([
        'id',
        'is_active',
        'is_purchase',
        'vendor_id',
        'purchase_price',
        'commission_amount',
        'vat_type_id',
        'invoice_amount',
        'sale_price',
        'note',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = LegacyVehicleTrade::getUiid($item->id);

      $item->vendor = Vendor::find($item->vendor_id, ['name']);
      $item->vat_type = VatType::find($item->vat_type_id, ['name']);
    }

    return $items;
  }

  static public function getItem($req, $id) {
    $item = LegacyVehicleTrade::find($id, [
      'id',
      'is_active',
      'created_at',
      'updated_at',
      'created_by_id',
      'updated_by_id',
      'is_purchase',
      'vendor_id',
      'purchase_price',
      'commission_amount',
      'vat_type_id',
      'invoice_amount',
      'sale_price',
      'note',
    ]);

    $item->uiid = LegacyVehicleTrade::getUiid($item->id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);

    $item->vendor = Vendor::find($item->vendor_id, ['name']);
    $item->vat_type = VatType::find($item->vat_type_id, ['name']);

    return $item;
  }
}
