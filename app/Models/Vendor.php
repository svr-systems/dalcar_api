<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Vendor extends Model
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
      'name' => 'required|min:2|max:100',
      'vendor_type_id' => 'required|numeric',
      'payment_days' => 'required|numeric',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'V-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req)
  {
    $items = Vendor::query()
      ->where('is_active', boolval($req->is_active))
      ->get([
        'id',
        'is_active',
        'name',
        'vendor_type_id',
        'payment_days',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->vendor_type = VendorType::find($item->vendor_type_id, ['name']);
    }

    return $items;
  }

  static public function getItem($req, $id)
  {
    $item = Vendor::find($id);
    $item->uiid = Vendor::getUiid($item->id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->uses_payment_link = (bool) $item->uses_payment_link;
    $item->requires_reference = (bool) $item->requires_reference;
    $item->requires_statement = (bool) $item->requires_statement;
    $item->vendor_type = VendorType::find($item->vendor_type_id, ['name']);
    $item->vendor_banks = VendorBank::query()
      ->where('vendor_id', $item->id)
      ->where('is_active', true)
      ->get();

    foreach ($item->vendor_banks as $vendor_bank) {
      $vendor_bank->bank = Bank::find($vendor_bank->bank_id, ['name']);
    }

    return $item;
  }



  static public function getItemToPurchaseOrder($req)
  {
    $vendor = Vendor::find($req->id, [
      'id',
      'payment_days',
      'uses_payment_link',
      'requires_reference',
      'requires_statement',
    ]);

    $due_date = Carbon::createFromFormat('Y-m-d', $req->order_date)->addDays((int) $vendor->payment_days)->toDateString();

    $vendor_banks = VendorBank::query()
      ->where('vendor_id', $req->id)
      ->where('is_active', 1)
      ->get();

    $purchase_order_payments = [];

    foreach ($vendor_banks as $vendor_bank) {
      $purchase_order_payments[] = [
        'id' => null,
        'is_active' => 1,
        'bank_id' => $vendor_bank->bank_id,
        'account_holder' => $vendor_bank->account_holder,
        'clabe_number' => $vendor_bank->clabe_number,
        'account_number' => $vendor_bank->account_number,
        'amount' => (float) $req->total_amount / count($vendor_banks),
        'bank' => Bank::find($vendor_bank->bank_id, ['name'])
      ];
    }

    return [
      'vendor' => $vendor,
      'purchase_order_payments' => $purchase_order_payments,
      'due_date' => $due_date,
    ];
  }
}
