<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrderVehicle;
use App\Models\VehicleInvestor;
use DB;
use Illuminate\Http\Request;
use Throwable;

class VehicleInvestorController extends Controller
{
  public function index(Request $request, $vehicle_id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        VehicleInvestor::getItems($request, $vehicle_id)
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
        ['item' => VehicleInvestor::getItem($id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $request, $id)
  {
    DB::beginTransaction();

    try {
      $item = VehicleInvestor::find($id);
      $item->is_active = 0;
      $item->updated_by_id = $request->user()->id;
      $item->save();

      DB::commit();

      return $this->apiRsp(
        200,
        'Inversionista eliminado correctamente'
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
      $validator = VehicleInvestor::valid($request->all());
      if ($validator->fails()) {
        return $this->apiRsp(422, $validator->errors()->first());
      }

      $is_store_mode = is_null($id);

      if ($is_store_mode) {
        $item = new VehicleInvestor();
        $item->vehicle_id = GenController::filter($request->vehicle_id, 'id');
        $item->created_by_id = $request->user()->id;
        $item->updated_by_id = $request->user()->id;
      } else {
        $item = VehicleInvestor::query()->find($id);
        $item->updated_by_id = $request->user()->id;
      }

      $investor_id = GenController::filter($request->investor_id, 'id');
      $new_percentage = (float) GenController::filter($request->percentages, 'f');

      // 1) Validar que el inversionista no esté repetido para este vehículo
      $investor_exists = VehicleInvestor::query()
        ->where('vehicle_id', $item->vehicle_id)
        ->where('investor_id', $investor_id)
        ->where('is_active', 1)
        ->when(!$is_store_mode, function ($query) use ($item) {
          $query->where('id', '!=', $item->id);
        })
        ->exists();

      if ($investor_exists) {
        return $this->apiRsp(
          422,
          'El inversionista ya está asignado a este vehículo.'
        );
      }

      // 2) Validar que la suma de porcentajes no exceda el 100%
      $current_percentages_total = VehicleInvestor::query()
        ->where('vehicle_id', $item->vehicle_id)
        ->where('is_active', 1)
        ->when(!$is_store_mode, function ($query) use ($item) {
          $query->where('id', '!=', $item->id);
        })
        ->sum('percentages');

      if (($current_percentages_total + $new_percentage) > 100.00001) {
        $available_percentage = max(0, 100 - $current_percentages_total);

        return $this->apiRsp(
          422,
          'El porcentaje total excede el 100%. Disponible: '
          . number_format($available_percentage, 2) . '%.'
        );
      }

      // 3) Calcular el monto de inversión con base en el precio de compra
      $purchase_order_vehicle = PurchaseOrderVehicle::query()
        ->where('vehicle_id', $item->vehicle_id)
        ->first(['purchase_price']);
      $calculated_amount = round(
        ($purchase_order_vehicle->purchase_price * $new_percentage) / 100,
        2
      );
      $item->amount = $calculated_amount;

      $item = $this->saveItem($item, $request);

      DB::commit();

      return $this->apiRsp(
        $is_store_mode ? 201 : 200,
        'Inversionista ' . ($is_store_mode ? 'agregado' : 'editado') . ' correctamente',
        $is_store_mode ? ['item' => ['id' => $item->id]] : null
      );
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }

  public static function saveItem($item, $data)
  {
    $item->investor_id = GenController::filter($data->investor_id, 'id');
    $item->percentages = GenController::filter($data->percentages, 'f');
    $item->save();

    return $item;
  }
}
