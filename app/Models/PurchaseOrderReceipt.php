<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;

class PurchaseOrderReceipt extends Model
{
  public $timestamps = false;

  protected function serializeDate(DateTimeInterface $date)
  {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  static public function getItems($request, $purchase_order_id)
  {
    $purchase_order = PurchaseOrder::find($purchase_order_id, ['id', 'is_active', 'total_amount', 'paid_at']);

    $purchase_order->purchase_order_payments = PurchaseOrderPayment::query()
      ->where('purchase_order_id', $purchase_order->id)
      ->where('is_active', 1)
      ->get([
        'bank_id',
        'amount',
      ]);
    foreach ($purchase_order->purchase_order_payments as $purchase_order_payment) {
      $purchase_order_payment->bank = Bank::find($purchase_order_payment->bank_id, 'name');
    }

    $items = PurchaseOrderReceipt::query()
      ->where('purchase_order_id', $purchase_order->id)
      ->where('is_active', 1)
      ->get([
        'id',
        'is_active',
        'purchase_order_id',
        'note',
        'file_path',
      ]);

    foreach ($items as $item) {
      $item->file_b64 = DocMgrController::getB64($item->file_path, 'PurchaseOrderReceipt');
    }

    return [
      'items' => $items,
      'purchase_order' => $purchase_order,
    ];
  }
}
