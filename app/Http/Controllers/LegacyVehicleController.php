<?php

namespace App\Http\Controllers;

use App\Models\LegacyVehicle;
use App\Models\LegacyVehicleExpense;
use App\Models\LegacyVehicleInvestor;
use DB;
use Illuminate\Http\Request;
use Throwable;

class LegacyVehicleController extends Controller
{
  public function index(Request $req)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => LegacyVehicle::getItems($req)]
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
        ['item' => LegacyVehicle::getItem($id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id)
  {
    DB::beginTransaction();
    try {
      $item = LegacyVehicle::find($id);

      if (!$item) {
        return $this->apiRsp(422, 'ID no existente');
      }

      $item->is_active = 0;
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

  public function restore(Request $req)
  {
    DB::beginTransaction();
    try {
      $item = LegacyVehicle::find($req->id);

      if (!$item) {
        return $this->apiRsp(422, 'ID no existente');
      }

      $item->is_active = 1;
      $item->updated_by_id = $req->user()->id;
      $item->save();

      DB::commit();
      return $this->apiRsp(
        200,
        'Registro activado correctamente',
        ['item' => LegacyVehicle::getItem($item->id)]
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

      $valid = LegacyVehicle::valid($req->all(), $id);

      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new LegacyVehicle;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
      } else {
        $item = LegacyVehicle::find($id);
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

    $item->branch_id = GenController::filter($data->branch_id, 'id');
    $item->purchase_date = GenController::filter($data->purchase_date, 'd');
    $item->vehicle_version_id = GenController::filter($data->vehicle_version_id, 'id');
    $item->vehicle_color_id = GenController::filter($data->vehicle_color_id, 'id');
    $item->vehicle_transmission_id = GenController::filter($data->vehicle_transmission_id, 'id');
    $item->vin = GenController::filter($data->vin, 'U');
    $item->engine_number = GenController::filter($data->engine_number, 'U');
    $item->repuve = GenController::filter($data->repuve, 'U');
    $item->vehicle_key = GenController::filter($data->vehicle_key, 'U');
    $item->notes = GenController::filter($data->notes, 'U');
    $item->origin_type_id = GenController::filter($data->origin_type_id, 'id');
    $item->pediment_number = GenController::filter($data->pediment_number, 'i');
    $item->pediment_date = GenController::filter($data->pediment_date, 'd');
    $item->custom_office_id = GenController::filter($data->custom_office_id, 'id');
    $item->pediment_notes = GenController::filter($data->pediment_notes, 'U');
    $item->passenger_capacity = GenController::filter($data->passenger_capacity, 'i');
    $item->save();

    return $item;
  }
}
