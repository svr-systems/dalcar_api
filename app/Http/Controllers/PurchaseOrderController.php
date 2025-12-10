<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use DB;
use Illuminate\Http\Request;
use Throwable;

class PurchaseOrderController extends Controller
{
  public function index(Request $req)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => PurchaseOrder::getItems($req)]
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

  // public function destroy(Request $req, $id)
  // {
  //   DB::beginTransaction();
  //   try {
  //     $item = PurchaseOrder::find($id);

  //     if (!$item) {
  //       return $this->apiRsp(422, 'ID no existente');
  //     }

  //     $item->is_active = 0;
  //     $item->updated_by_id = $req->user()->id;
  //     $item->save();

  //     DB::commit();
  //     return $this->apiRsp(
  //       200,
  //       'Registro inactivado correctamente'
  //     );
  //   } catch (Throwable $err) {
  //     DB::rollback();
  //     return $this->apiRsp(500, null, $err);
  //   }
  // }

  // public function restore(Request $req)
  // {
  //   DB::beginTransaction();
  //   try {
  //     $item = PurchaseOrder::find($req->id);

  //     if (!$item) {
  //       return $this->apiRsp(422, 'ID no existente');
  //     }

  //     $item->is_active = 1;
  //     $item->updated_by_id = $req->user()->id;
  //     $item->save();

  //     DB::commit();
  //     return $this->apiRsp(
  //       200,
  //       'Registro activado correctamente',
  //       ['item' => PurchaseOrder::getItem($item->id)]
  //     );
  //   } catch (Throwable $err) {
  //     DB::rollback();
  //     return $this->apiRsp(500, null, $err);
  //   }
  // }

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
    $item->order_date = GenController::filter($data->order_date, 'd');
    $item->total_amount = GenController::filter($data->total_amount, 'f');
    $item->vendor_id = GenController::filter($data->vendor_id, 'id');
    $item->due_date = GenController::filter($data->due_date, 'd');
    $item->reference = is_null($data->reference) ? null : trim($data->reference);
    $item->statement_path = DocMgrController::save(
      $data->statement_path,
      DocMgrController::exist($data->statement_doc),
      $data->statement_dlt,
      'PurchaseOrder'
    );
    $item->note = GenController::filter($data->note, 'U');
    $item->save();

    return $item;
  }
}
