<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderVehicle;
use DB;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Throwable;

class PurchaseOrderVehicleController extends Controller
{
  public function index(Request $request, $purchase_order_id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        PurchaseOrderVehicle::getItems($request, $purchase_order_id)
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
        ['item' => PurchaseOrderVehicle::getItem($id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $request, $id)
  {
    DB::beginTransaction();

    try {
      $item = PurchaseOrderVehicle::find($id);
      $item->is_active = 0;
      $item->updated_by_id = $request->user()->id;
      $item->save();

      DB::commit();

      return $this->apiRsp(
        200,
        'Auto eliminado correctamente'
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
      $req->vehicle = json_decode(json_encode($req->vehicle));
      $valid = Vehicle::valid((array) $req->vehicle, $req->vehicle_id);
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $valid = PurchaseOrderVehicle::valid($req->all());
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $purchase_order = PurchaseOrder::find($req->purchase_order_id, ['id', 'total_amount']);

      $sum = PurchaseOrderVehicle::query()
        ->where('purchase_order_id', $purchase_order->id)
        ->where('id', '!=', $id)
        ->where('is_active', 1)
        ->sum('purchase_price');

      $sum = (float) $sum + GenController::filter($req->purchase_price, 'f');

      if ($sum > (float) $purchase_order->total_amount) {
        return $this->apiRsp(
          422,
          'La sumatoria del total de precio de compra de los vehiculos ('
          . GenController::toAmountFormat($sum)
          . ') no puede exceder al monto total te la orden ('
          . GenController::toAmountFormat($purchase_order->total_amount)
          . ')'
        );
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new PurchaseOrderVehicle;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
        $item->purchase_order_id = GenController::filter($req->purchase_order_id, 'id');
      } else {
        $item = PurchaseOrderVehicle::find($id);
        $item->updated_by_id = $req->user()->id;
      }

      $item = $this->saveItem($item, $req);

      DB::commit();
      return $this->apiRsp(
        $store_mode ? 201 : 200,
        'Vehiculo ' . ($store_mode ? 'agregado' : 'editado') . ' correctamente',
        $store_mode ? ['item' => ['id' => $item->id]] : null
      );
    } catch (Throwable $err) {
      DB::rollback();
      return $this->apiRsp(500, null, $err);
    }
  }

  public static function saveItem($item, $data)
  {
    $vehicle = Vehicle::find($data->vehicle_id);
    if (!$vehicle) {
      $vehicle = new Vehicle;
      $vehicle->created_by_id = $data->user()->id;
    }

    $vehicle->updated_by_id = $data->user()->id;
    $vehicle->branch_id = GenController::filter($data->vehicle->branch_id, 'id');
    $vehicle->vehicle_version_id = GenController::filter($data->vehicle->vehicle_version_id, 'id');
    $vehicle->vehicle_transmission_id = GenController::filter($data->vehicle->vehicle_transmission_id, 'id');
    $vehicle->vehicle_color_id = GenController::filter($data->vehicle->vehicle_color_id, 'id');
    $vehicle->vin = GenController::removeSpaces(GenController::filter($data->vehicle->vin, 'U'));
    $vehicle->engine_number = GenController::removeSpaces(GenController::filter($data->vehicle->engine_number, 'U'));
    $vehicle->repuve = GenController::removeSpaces(GenController::filter($data->vehicle->repuve, 'U'));
    $vehicle->vehicle_key = GenController::removeSpaces(GenController::filter($data->vehicle->vehicle_key, 'U'));
    $vehicle->passenger_capacity = GenController::filter($data->vehicle->passenger_capacity, 'i');
    $vehicle->notes = GenController::filter($data->vehicle->notes, 'U');
    $vehicle->origin_type_id = GenController::filter($data->vehicle->origin_type_id, 'id');
    $vehicle->pediment_number = GenController::removeSpaces(GenController::filter($data->vehicle->pediment_number, 'U'));
    $vehicle->pediment_date = GenController::filter($data->vehicle->pediment_date, 'd');
    $vehicle->custom_office_id = GenController::filter($data->vehicle->custom_office_id, 'id');
    $vehicle->pediment_notes = GenController::filter($data->vehicle->pediment_notes, 'U');
    $vehicle->save();

    $item->vehicle_id = $vehicle->id;
    $item->purchase_price = GenController::filter($data->purchase_price, 'f');
    $item->commission_amount = GenController::filter($data->commission_amount, 'f');
    $item->vat_type_id = GenController::filter($data->vat_type_id, 'id');
    $item->invoice_amount = GenController::filter($data->invoice_amount, 'f');
    $item->save();

    return $item;
  }
}
