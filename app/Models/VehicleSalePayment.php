<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;

class VehicleSalePayment extends Model
{
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
    'is_active' => 'boolean',
    'refund_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function validStore($data)
  {
    $rules = [
      'vehicle_sale_id' => 'required|numeric',
      'sale_payment_type_id' => 'required|numeric',
      'payment_method_id' => 'required|numeric',
      'amount' => 'required|numeric|min:0.01',
      'voucher_path' => 'nullable|max:50',
      'notes' => 'nullable',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  public static function getUiid($id)
  {
    return 'P-' . str_pad($id, 6, '0', STR_PAD_LEFT);
  }

  public static function getItem($id)
  {
    $item = self::query()->find($id);

    if (!$item) {
      return null;
    }

    $item->uiid = self::getUiid($item->id);
    $item->created_by = User::query()->find($item->created_by_id, ['id', 'email']);
    $item->updated_by = User::query()->find($item->updated_by_id, ['id', 'email']);
    $item->refund_by = User::query()->find($item->refund_by_id, ['id', 'email']);

    $item->vehicle_sale = VehicleSale::getItem($item->vehicle_sale_id);
    $item->sale_payment_type = SalePaymentType::query()->find($item->sale_payment_type_id, ['id', 'name']);
    $item->payment_method = PaymentMethod::query()->find($item->payment_method_id, ['id', 'name']);

    return $item;
  }

  public static function getItemsByVehicleSaleId($vehicle_sale_id)
  {
    $items = self::query()
      ->where('vehicle_sale_id', $vehicle_sale_id)
      ->orderBy('id')
      ->get([
        'id',
        'is_active',
        'vehicle_sale_id',
        'sale_payment_type_id',
        'payment_method_id',
        'amount',
        'voucher_path',
        'notes',
        'refund_at',
        'refund_by_id',
        'refund_note',
        'created_at',
      ]);

    foreach ($items as $item) {
      $item->uiid = self::getUiid($item->id);
      $item->sale_payment_type = SalePaymentType::query()->find($item->sale_payment_type_id, ['id', 'name']);
      $item->payment_method = PaymentMethod::query()->find($item->payment_method_id, ['id', 'name']);
      $item->refund_by = User::query()->find($item->refund_by_id, ['id', 'email']);
    }

    return $items;
  }
}