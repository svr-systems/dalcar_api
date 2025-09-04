<?php

namespace App\Http\Controllers;

use App\Models\LegacyVehicle;
use App\Models\LegacyVehicleExpense;
use App\Models\LegacyVehicleInvestor;
use DB;
use Illuminate\Http\Request;
use Throwable;

class LegacyVehicleController extends Controller {
  public function index(Request $req) {
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

  public function show(Request $req, $id) {
    try {
      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => LegacyVehicle::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id) {
    DB::beginTransaction();
    try {
      $item = LegacyVehicle::find($id);

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

  public function store(Request $req) {
    return $this->storeUpdate($req, null);
  }

  public function update(Request $req, $id) {
    return $this->storeUpdate($req, $id);
  }

  public function storeUpdate($req, $id) {
    DB::beginTransaction();
    try {

      $valid = LegacyVehicle::valid($req->all());

      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $valid_values = $this->validValues($req);

      if(!$valid_values['response']){
        return $this->apiRsp(500, $valid_values['message'],null);
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

  public static function saveItem($item, $data, $is_req = true) {
    if (!$is_req) {
      $item->active = GenController::filter($data->active, 'b');
    }

    $item->branch_id = GenController::filter($data->branch_id, 'id');
    $item->vendor_id = GenController::filter($data->vendor_id, 'id');
    $item->purchase_date = GenController::filter($data->purchase_date, 'd');
    $item->vehicle_model_id = GenController::filter($data->vehicle_model_id, 'id');
    $item->model_year = GenController::filter($data->model_year, 'i');
    $item->vehicle_transmission_id = GenController::filter($data->vehicle_transmission_id, 'id');
    $item->vehicle_color_id = GenController::filter($data->vehicle_color_id, 'id');
    $item->vin = GenController::filter($data->vin, 'U');
    $item->purchase_price = GenController::filter($data->purchase_price, 'f');
    $item->commission_amount = GenController::filter($data->commission_amount, 'f');
    $item->vat_type_id = GenController::filter($data->vat_type_id, 'id');
    $item->invoice_amount = GenController::filter($data->invoice_amount, 'f');
    $item->save();

    if ($data->legacy_vehicle_investors) {
      foreach ($data->legacy_vehicle_investors as $legacy_vehicle_investor) {
        $legacy_vehicle_investor_item = LegacyVehicleInvestor::find($legacy_vehicle_investor['id']);
        if (!$legacy_vehicle_investor_item) {
          $legacy_vehicle_investor_item = new LegacyVehicleInvestor;
        }
        $legacy_vehicle_investor_item->is_active = GenController::filter($legacy_vehicle_investor['is_active'], 'b');
        $legacy_vehicle_investor_item->investor_id = GenController::filter($legacy_vehicle_investor['investor_id'], 'id');
        $legacy_vehicle_investor_item->percentages = GenController::filter($legacy_vehicle_investor['percentages'], 'f');
        $legacy_vehicle_investor_item->amount = GenController::filter($legacy_vehicle_investor['amount'], 'f');
        $legacy_vehicle_investor_item->legacy_vehicle_id = $item->id;
        $legacy_vehicle_investor_item->save();
      }
    }

    if ($data->legacy_vehicle_expenses) {
      foreach ($data->legacy_vehicle_expenses as $legacy_vehicle_expense) {
        $legacy_vehicle_expense_item = LegacyVehicleExpense::find($legacy_vehicle_expense['id']);
        if (!$legacy_vehicle_expense_item) {
          $legacy_vehicle_expense_item = new LegacyVehicleExpense;
        }
        $legacy_vehicle_expense_item->is_active = GenController::filter($legacy_vehicle_expense['is_active'], 'b');
        $legacy_vehicle_expense_item->expense_type_id = GenController::filter($legacy_vehicle_expense['expense_type_id'], 'id');
        $legacy_vehicle_expense_item->note = GenController::filter($legacy_vehicle_expense['note'], 'U');
        $legacy_vehicle_expense_item->expense_date = GenController::filter($legacy_vehicle_expense['expense_date'], 'd');
        $legacy_vehicle_expense_item->amount = GenController::filter($legacy_vehicle_expense['amount'], 'f');
        $legacy_vehicle_expense_item->legacy_vehicle_id = $item->id;
        $legacy_vehicle_expense_item->save();
      }
    }

    return $item;
  }

  public static function validValues($req){
    $purchase_price = GenController::filter($req->purchase_price, 'f');
    $commission_amount = GenController::filter($req->commission_amount, 'f');

    if($purchase_price < $commission_amount){
      return ['response' => false, 'message' => 'La comisión no puede superar al preció de compra.'];
    }

    $vehicle_investor_percentages = 0;

    if ($req->legacy_vehicle_investors) {
      foreach ($req->legacy_vehicle_investors as $legacy_vehicle_investor) {
        $vehicle_investor_percentages += GenController::filter($legacy_vehicle_investor['percentages'], 'f');
      }
    }

    if($vehicle_investor_percentages !== floatval(100)){
      return ['response' => false, 'message' => 'La suma de los porcentajes debe ser del 100%.'];
    }

    return ['response' => true];
  }
}
