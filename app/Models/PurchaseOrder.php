<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
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

  public static function valid($data)
  {
    $rules = [
      'branch_id' => 'required|numeric',
      'order_date' => 'required|date',
      'total_amount' => 'required|numeric',
      'vendor_id' => 'required|numeric',
      'due_date' => 'required|date',
      'reference' => 'nullable|min:2|max:40',
      'note' => 'nullable',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

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
        'order_date',
        'vendor_id',
        'due_date',
        'total_amount',
        'paid_at',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = self::getUiid($item->id);
      $item->vendor = Vendor::find($item->vendor_id, ['name']);

      $item->days_remaining = null;
      if (!$item->paid_at) {
        $due_date = Carbon::parse($item->due_date)->startOfDay();
        $item->days_remaining = Carbon::today()->diffInDays($due_date, false);
      }
    }

    return $items;
  }

  static public function getItem($id)
  {
    $item = self::find($id);
    $item->uiid = self::getUiid($item->id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->branch = Branch::find($item->branch_id, ['name']);
    $item->vendor = Vendor::find($item->vendor_id, ['name']);
    $item->statement_b64 = DocMgrController::getB64($item->statement_path, 'PurchaseOrder');

    $item->days_remaining = null;
    if (!$item->paid_at) {
      $due_date = Carbon::parse($item->due_date)->startOfDay();
      $item->days_remaining = Carbon::today()->diffInDays($due_date, false);
    }

    $item->purchase_order_payments = PurchaseOrderPayment::query()
      ->where('purchase_order_id', $item->id)
      ->where('is_active', 1)
      ->get([
        'id',
        'bank_id',
        'account_holder',
        'clabe_number',
        'account_number',
        'amount',
      ]);

    foreach ($item->purchase_order_payments as $purchase_order_payment) {
      $purchase_order_payment->bank = Bank::find($purchase_order_payment->bank_id, 'name');
    }

    return $item;
  }
}
