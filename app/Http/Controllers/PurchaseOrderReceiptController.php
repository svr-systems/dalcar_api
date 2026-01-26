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

  public function store(Request $request)
  {
    DB::beginTransaction();

    try {
      $purchase_order = PurchaseOrder::find($request->purchase_order_id);

      $purchase_order_receipts = json_decode($request->purchase_order_receipts);
      foreach ($purchase_order_receipts as $key => $purchase_order_receipt) {
        $file_name = 'purchase_order_receipts_file_doc_' . $key;

        $purchase_order_receipt_item = new PurchaseOrderReceipt;
        $purchase_order_receipt_item->purchase_order_id = $purchase_order->id;
        $purchase_order_receipt_item->file_path = DocMgrController::save(null, DocMgrController::exist($request->$file_name), false, 'PurchaseOrderReceipt');
        $purchase_order_receipt_item->note = GenController::trim($purchase_order_receipt->note);
        $purchase_order_receipt_item->save();
      }

      $purchase_order->paid_at = Carbon::now();
      $purchase_order->paid_by_id = $request->user()->id;
      $purchase_order->save();

      DB::commit();
      return $this->apiRsp(
        201,
        'Comprobantes agregados correctamente',
        ['item' => ['id' => $purchase_order->id]]
      );
    } catch (Throwable $err) {
      DB::rollback();
      return $this->apiRsp(500, null, $err);
    }
  }
}
