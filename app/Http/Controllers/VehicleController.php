<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use DB;
use Illuminate\Http\Request;
use Throwable;

class VehicleController extends Controller
{
  public function index(Request $request)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => Vehicle::getItems($request)]
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
        ['item' => Vehicle::getItem($id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function update(Request $request, $id)
  {
    return $this->storeUpdate($request, $id);
  }

  public function storeUpdate($request, $id)
  {
    DB::beginTransaction();

    try {
      $valid = Vehicle::valid($request->all(), $id);
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $item = Vehicle::find($id);
      $item->updated_by_id = $request->user()->id;

      $item = $this->saveItem($item, $request);

      DB::commit();
      return $this->apiRsp(200, 'Vehiculo editado correctamente');
    } catch (Throwable $err) {
      DB::rollback();
      return $this->apiRsp(500, null, $err);
    }
  }

  public static function saveItem($item, $data)
  {
    // $item->branch_id = GenController::filter($data->branch_id, 'id');
    // $item->vehicle_version_id = GenController::filter($data->vehicle_version_id, 'id');
    $item->vehicle_transmission_id = GenController::filter($data->vehicle_transmission_id, 'id');
    $item->vehicle_color_id = GenController::filter($data->vehicle_color_id, 'id');
    $item->vin = GenController::removeSpaces(GenController::filter($data->vin, 'U'));
    $item->engine_number = GenController::removeSpaces(GenController::filter($data->engine_number, 'U'));
    $item->repuve = GenController::removeSpaces(GenController::filter($data->repuve, 'U'));
    $item->vehicle_key = GenController::removeSpaces(GenController::filter($data->vehicle_key, 'U'));
    $item->passenger_capacity = GenController::filter($data->passenger_capacity, 'i');
    $item->notes = GenController::filter($data->notes, 'U');
    $item->origin_type_id = GenController::filter($data->origin_type_id, 'id');
    $item->pediment_number = GenController::removeSpaces(GenController::filter($data->pediment_number, 'U'));
    $item->pediment_date = GenController::filter($data->pediment_date, 'd');
    $item->custom_office_id = GenController::filter($data->custom_office_id, 'id');
    $item->pediment_notes = GenController::filter($data->pediment_notes, 'U');
    $item->save();

    return $item;
  }
}
