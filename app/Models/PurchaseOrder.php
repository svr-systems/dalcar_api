<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use App\Http\Controllers\GenController;
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

  static public function getItems($request)
  {
    $items = PurchaseOrder::query()
      ->where('is_active', boolval($request->is_active));


    switch ((int) $request->filter) {
      case 1:
        $items
          ->whereNull('paid_at')
          ->orderByDesc('id');
        break;
      case 2:
        $items
          ->whereNotNull('paid_at')
          ->orderByDesc('paid_at');
        break;
    }

    $items = $items
      ->get([
        'id',
        'is_active',
        'order_date',
        'vendor_id',
        'due_date',
        'total_amount',
        'paid_at'
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = self::getUiid($item->id);
      $item->vendor = Vendor::find($item->vendor_id, ['name']);

      $item->days_remaining = null;
      if (is_null($item->paid_at)) {
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
    $item->statement_doc = null;
    $item->statement_dlt = false;
    $item->other_b64 = DocMgrController::getB64($item->other_path, 'PurchaseOrder');
    $item->other_doc = null;
    $item->other_dlt = false;
    $item->total_amount = (float) $item->total_amount;

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
        'cie_code',
        'is_commission',
        'amount',
      ]);

    foreach ($item->purchase_order_payments as $purchase_order_payment) {
      $purchase_order_payment->bank = Bank::find($purchase_order_payment->bank_id, 'name');
      $purchase_order_payment->receipt_b64 = GenController::docToB64Object('PurchaseOrderPayment', $purchase_order_payment->receipt_path);

      if (!$item->purchase_order_payments_pending && is_null($purchase_order_payment->receipt_path)) {
        $item->purchase_order_payments_pending = true;
      }
    }

    $purchase_order_vehicles = PurchaseOrderVehicle::query()
      ->where('purchase_order_id', $item->id)
      ->where('is_active', 1)
      ->get([
        'id',
        'purchase_price',
      ]);

    $item->purchase_order_vehicles_amount = 0;

    foreach ($purchase_order_vehicles as $purchase_order_vehicle) {
      $item->purchase_order_vehicles_amount += (float) $purchase_order_vehicle->purchase_price;
    }

    $item->total_amount_pending = ((float) $item->total_amount + (float) $item->warranty_amount) != $item->purchase_order_vehicles_amount;

    return $item;
  }
}
