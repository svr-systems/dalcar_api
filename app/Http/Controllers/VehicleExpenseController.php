<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrderVehicle;
use App\Models\VehicleExpense;
use DB;
use Illuminate\Http\Request;
use Throwable;

class VehicleExpenseController extends Controller
{
  public function index(Request $request, $vehicle_id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        VehicleExpense::getItems($request, $vehicle_id)
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
        ['item' => VehicleExpense::getItem($id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $request, $id)
  {
    DB::beginTransaction();

    try {
      $item = VehicleExpense::find($id);
      $item->is_active = 0;
      $item->updated_by_id = $request->user()->id;
      $item->save();

      DB::commit();

      return $this->apiRsp(
        200,
        'Gasto eliminado correctamente'
      );
    } catch (Throwable $err) {
      DB::rollback();

      return $this->apiRsp(500, null, $err);
    }

  }

  public function store(Request $request)
  {
    return $this->storeUpdate($request, null);
  }

  public function update(Request $request, $id)
  {
    return $this->storeUpdate($request, $id);
  }

  public function storeUpdate($request, $id)
  {
    DB::beginTransaction();

    try {
      $validator = VehicleExpense::valid($request->all());
      if ($validator->fails()) {
        return $this->apiRsp(422, $validator->errors()->first());
      }

      $is_store_mode = is_null($id);

      if ($is_store_mode) {
        $item = new VehicleExpense();
        $item->vehicle_id = GenController::filter($request->vehicle_id, 'id');
        $item->created_by_id = $request->user()->id;
        $item->updated_by_id = $request->user()->id;
      } else {
        $item = VehicleExpense::query()->find($id);
        $item->updated_by_id = $request->user()->id;
      }

      $item = $this->saveItem($item, $request);

      DB::commit();

      return $this->apiRsp(
        $is_store_mode ? 201 : 200,
        'Gasto ' . ($is_store_mode ? 'agregado' : 'editado') . ' correctamente',
        $is_store_mode ? ['item' => ['id' => $item->id]] : null
      );
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }

  public static function saveItem($item, $request)
  {
    $item->vehicle_id = GenController::filter($request->vehicle_id, 'id');
    $item->expense_type_id = GenController::filter($request->expense_type_id, 'id');
    $item->note = GenController::filter($request->note, 'U');
    $item->expense_date = GenController::filter($request->expense_date, 'd');
    $item->amount = GenController::filter($request->amount, 'f');
    $item->document_path = DocMgrController::replaceOrDelete($item->document_path, $request->file('document_doc'), 'VehicleExpense');
    $item->save();

    return $item;
  }
}
