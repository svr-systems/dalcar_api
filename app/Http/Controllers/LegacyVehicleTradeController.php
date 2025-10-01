<?php

namespace App\Http\Controllers;

use App\Models\LegacyVehicleTrade;
use DB;
use Illuminate\Http\Request;
use Throwable;

class LegacyVehicleTradeController extends Controller
{
  public function index(Request $req)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => LegacyVehicleTrade::getItems($req)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function show(Request $req, $id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => LegacyVehicleTrade::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id)
  {
    DB::beginTransaction();
    try {
      $item = LegacyVehicleTrade::find($id);

      if (!$item) {
        return $this->apiRsp(422, 'ID no existente');
      }

      $item->is_active = false;
      $item->updated_by_id = $req->user()->id;
      $item->save();

      DB::commit();
      return $this->apiRsp(
        200,
        'Registro inactivado correctamente'
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

      $valid = LegacyVehicleTrade::valid($req->all());

      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new LegacyVehicleTrade;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
      } else {
        $item = LegacyVehicleTrade::find($id);
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

  public static function saveItem($item, $data, $is_req = true)
  {
    if (!$is_req) {
      $item->active = GenController::filter($data->active, 'b');
    }

    $item->legacy_vehicle_id = GenController::filter($data->legacy_vehicle_id, 'id');
    $item->is_purchase = GenController::filter($data->is_purchase, 'b');
    $item->vendor_id = GenController::filter($data->vendor_id, 'id');
    $item->purchase_price = is_null($data->purchase_price) ? null : GenController::filter($data->purchase_price, 'f');
    $item->commission_amount = is_null($data->commission_amount) ? null : GenController::filter($data->commission_amount, 'f');
    $item->vat_type_id = GenController::filter($data->vat_type_id, 'id');
    $item->invoice_amount = is_null($data->invoice_amount) ? null : GenController::filter($data->invoice_amount, 'f');
    $item->sale_price = is_null($data->sale_price) ? null : GenController::filter($data->sale_price, 'f');
    $item->note = GenController::filter($data->note, 'U');
    $item->save();

    return $item;
  }
}
