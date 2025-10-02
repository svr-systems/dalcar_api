<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicleTrade extends Model
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
      'legacy_vehicle_id' => 'required|numeric',
      'is_purchase' => 'required|boolean',
      'vendor_id' => 'nullable|numeric',
      'purchase_price' => 'nullable|numeric',
      'commission_amount' => 'nullable|numeric',
      'sale_price' => 'nullable|numeric',
      'vat_type_id' => 'required|numeric',
      'invoice_amount' => 'nullable|numeric',
      'note' => 'nullable|min:2',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'VHA-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req)
  {
    $items = LegacyVehicleTrade::query()
      ->where('legacy_vehicle_id', $req->legacy_vehicle_id)
      ->where('is_active', boolval($req->is_active))
      ->get([
        'id',
        'is_purchase',
        'vendor_id',
        'purchase_price',
        'commission_amount',
        'sale_price',
        'vat_type_id',
        'invoice_amount',
        'note',
      ]);

    foreach ($items as $item) {
      $item->vendor = Vendor::find($item->vendor_id, ['name']);
      $item->vat_type = VatType::find($item->vat_type_id, ['name']);
    }

    return $items;
  }

  static public function getItem($req, $id)
  {
    $item = LegacyVehicleTrade::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->vendor = Vendor::find($item->vendor_id, ['name']);
    $item->vat_type = VatType::find($item->vat_type_id, ['name']);

    return $item;
  }
}
