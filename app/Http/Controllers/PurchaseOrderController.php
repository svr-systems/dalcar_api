<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use stdClass;
use Throwable;

class PurchaseOrderController extends Controller
{
  public function index(Request $request)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => PurchaseOrder::getItems($request)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function show($id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => PurchaseOrder::getItem($id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id)
  {
    DB::beginTransaction();
    try {
      $item = PurchaseOrder::find($id);
      $item->is_active = 0;
      $item->updated_by_id = $req->user()->id;
      $item->save();

      DB::commit();
      return $this->apiRsp(
        200,
        'Registro eliminado correctamente'
      );
    } catch (Throwable $err) {
      DB::rollback();
      return $this->apiRsp(500, null, $err);
    }
  }

  public function restore(Request $req)
  {
    DB::beginTransaction();
    try {
      $item = PurchaseOrder::find($req->id);
      $item->is_active = 1;
      $item->updated_by_id = $req->user()->id;
      $item->save();

      DB::commit();
      return $this->apiRsp(
        200,
        'Registro activado correctamente',
        ['item' => PurchaseOrder::getItem($item->id)]
      );
    } catch (Throwable $err) {
      DB::rollback();
      return $this->apiRsp(500, null, $err);
    }
  }

  public function store(Request $req)
  {
    return $this->storeUpdate($req, null);
  }

  public function update(Request $req, $id)
  {
    return $this->storeUpdate($req, $id);
  }

  public function storeUpdate($req, $id)
  {
    DB::beginTransaction();

    try {
      $valid = PurchaseOrder::valid($req->all());

      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new PurchaseOrder;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
      } else {
        $item = PurchaseOrder::find($id);
        $item->updated_by_id = $req->user()->id;
      }

      $item = $this->saveItem($item, $req);

      if ($store_mode) {
        $data = new stdClass;
        $data->uiid = PurchaseOrder::getUiid($item->id);

        $data = new stdClass;
        $data->uiid = PurchaseOrder::getUiid($item->id);

        $emails = User::query()
          ->where('is_active', 1)
          ->where('role_id', 3)
          ->where('receives_po_emails', 1)
          ->pluck('email')
          ->filter()
          ->unique()
          ->values();

        EmailController::orderPaymentStore($emails, $data);
      }

      DB::commit();

      return $this->apiRsp(
        $store_mode ? 201 : 200,
        'Registro ' . ($store_mode ? 'agregado' : 'editado') . ' correctamente',
        $store_mode ? ['item' => ['id' => $item->id]] : null
      );
    } catch (Throwable $err) {
      DB::rollback();

      return $this->apiRsp(500, null, $err);
    }
  }

  public static function saveItem($item, $data)
  {
    $item->branch_id = GenController::filter($data->branch_id, 'id');
    $item->subtotal_amount = GenController::filter($data->subtotal_amount, 'f');
    $item->commission_amount = GenController::filter($data->commission_amount, 'f');
    $item->warranty_amount = GenController::filter($data->warranty_amount, 'f');
    $item->total_amount = GenController::filter($data->total_amount, 'f');
    $item->order_date = GenController::filter($data->order_date, 'd');
    $item->vendor_id = GenController::filter($data->vendor_id, 'id');
    $item->due_date = GenController::filter($data->due_date, 'd');
    $item->reference = is_null($data->reference) ? null : trim($data->reference);
    $item->statement_path = DocMgrController::save(
      $data->statement_path,
      DocMgrController::exist($data->statement_doc),
      $data->statement_dlt,
      'PurchaseOrder'
    );
    $item->other_path = DocMgrController::save(
      $data->other_path,
      DocMgrController::exist($data->other_doc),
      $data->other_dlt,
      'PurchaseOrder'
    );
    $item->note = GenController::filter($data->note, 'U');
    $item->save();

    $purchase_order_payments = json_decode($data->purchase_order_payments);
    foreach ($purchase_order_payments as $purchase_order_payment) {
      $purchase_order_payment_item = PurchaseOrderPayment::find($purchase_order_payment->id);
      if (!$purchase_order_payment_item) {
        $purchase_order_payment_item = new PurchaseOrderPayment;
        $purchase_order_payment_item->created_by_id = $data->user()->id;
        $purchase_order_payment_item->purchase_order_id = $item->id;
      }

      $purchase_order_payment_item->updated_by_id = $data->user()->id;
      $purchase_order_payment_item->bank_id = $purchase_order_payment->bank_id;
      $purchase_order_payment_item->account_holder = $purchase_order_payment->account_holder;
      $purchase_order_payment_item->clabe_number = $purchase_order_payment->clabe_number;
      $purchase_order_payment_item->account_number = $purchase_order_payment->account_number;
      $purchase_order_payment_item->cie_code = $purchase_order_payment->cie_code;
      $purchase_order_payment_item->is_commission = GenController::filter($purchase_order_payment->is_commission, 'b');
      $purchase_order_payment_item->amount = GenController::filter($purchase_order_payment->amount, 'f');
      $purchase_order_payment_item->save();
    }

    return $item;
  }
}
