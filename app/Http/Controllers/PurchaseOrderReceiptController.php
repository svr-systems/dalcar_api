<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class PurchaseOrderReceiptController extends Controller
{
  public function index(Request $request, $purchase_order_id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        PurchaseOrderReceipt::getItems($request, $purchase_order_id),
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function storeUpdate(Request $request)
  {
    DB::beginTransaction();

    try {
      $user_id = $request->user()->id;
      $purchase_order_id = (int) $request->purchase_order_id;

      $purchase_order = PurchaseOrder::query()->find($purchase_order_id);
      if (is_null($purchase_order)) {
        return $this->apiRsp(404, 'Orden de pago no encontrada');
      }

      $purchase_order_receipts = json_decode((string) $request->purchase_order_receipts);
      if (!is_array($purchase_order_receipts)) {
        return $this->apiRsp(422, 'purchase_order_receipts inválido');
      }

      foreach ($purchase_order_receipts as $key => $purchase_order_receipt) {
        $is_active = GenController::filter($purchase_order_receipt->is_active, 'b');
        $file_name = 'purchase_order_receipts_file_doc_' . $key;

        $purchase_order_receipt_item = PurchaseOrderReceipt::query()
          ->where('id', $purchase_order_receipt->id)
          ->where('purchase_order_id', $purchase_order->id)
          ->first();

        if (is_null($purchase_order_receipt_item)) {
          $purchase_order_receipt_item = new PurchaseOrderReceipt();
          $purchase_order_receipt_item->purchase_order_id = $purchase_order->id;
        }

        $purchase_order_receipt_item->is_active = $is_active;

        if ($is_active) {
          $purchase_order_receipt_item->note = GenController::trim($purchase_order_receipt->note);

          $file_doc = $request->file($file_name);

          if (!is_null($file_doc)) {
            $purchase_order_receipt_item->file_path = DocMgrController::replaceOrDelete(
              $purchase_order_receipt_item->file_path,
              $file_doc,
              'PurchaseOrderReceipt'
            );
          }
        }

        $purchase_order_receipt_item->save();
      }

      $has_active = PurchaseOrderReceipt::query()
        ->where('purchase_order_id', $purchase_order_id)
        ->where('is_active', 1)
        ->exists();

      $purchase_order->updated_by_id = $user_id;
      $purchase_order->paid_by_id = $has_active ? $user_id : null;
      $purchase_order->paid_at = $has_active ? Carbon::now() : null;
      $purchase_order->save();

      DB::commit();

      return $this->apiRsp(
        200,
        'Comprobantes actualizados correctamente',
        ['item' => ['id' => $purchase_order->id]]
      );
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }
}
