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

  public function updateSalePrice(Request $request, $vehicle_id)
  {
    DB::beginTransaction();

    try {
      $user_id = (int) $request->user()->id;

      $item = Vehicle::find($vehicle_id);
      if (!$item) {
        DB::rollBack();
        return $this->apiRsp(404, 'Registro no encontrado');
      }

      $sale_price = $request->input('sale_price');

      if (is_null($sale_price) || $sale_price === '') {
        DB::rollBack();
        return $this->apiRsp(422, 'sale_price es requerido');
      }

      $sale_price = (float) $sale_price;

      if ($sale_price < 0) {
        DB::rollBack();
        return $this->apiRsp(422, 'sale_price no puede ser negativo');
      }

      if ((bool) $item->is_published && $sale_price <= 0) {
        DB::rollBack();
        return $this->apiRsp(422, 'No se puede establecer un precio de venta en 0 mientras el vehículo esté publicado');
      }

      $item->updated_by_id = $user_id;
      $item->sale_price = $sale_price;
      $item->sale_price_updated_at = now();
      $item->sale_price_updated_by_id = $user_id;
      $item->is_published = $sale_price > 0;
      $item->save();

      DB::commit();

      return $this->apiRsp(200, 'Precio de venta actualizado correctamente', [
        'item' => [
          'id' => $item->id,
          'sale_price' => $item->sale_price,
          'sale_price_updated_at' => $item->sale_price_updated_at,
          'sale_price_updated_by_id' => $item->sale_price_updated_by_id,
          'is_published' => (bool) $item->is_published,
        ],
      ]);
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }

  public function togglePublishedStatus(Request $request, $vehicle_id)
  {
    DB::beginTransaction();

    try {
      $user_id = (int) $request->user()->id;

      $item = Vehicle::find($vehicle_id);
      if (!$item) {
        DB::rollBack();
        return $this->apiRsp(404, 'Registro no encontrado');
      }

      $is_published = !$item->is_published;

      if ($is_published && ((float) $item->sale_price) <= 0) {
        DB::rollBack();
        return $this->apiRsp(422, 'No se puede publicar un vehículo sin precio de venta válido');
      }

      $item->updated_by_id = $user_id;
      $item->is_published = $is_published;
      $item->save();

      DB::commit();

      $message = $item->is_published
        ? 'Vehículo publicado correctamente'
        : 'Vehículo ocultado correctamente';

      return $this->apiRsp(200, $message, [
        'item' => [
          'id' => $item->id,
          'is_published' => (bool) $item->is_published,
        ],
      ]);
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }

  public function sellerIndex(Request $request)
  {
    try {
      $seller_user_id = (int) $request->user()->id;

      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => Vehicle::getItemsSeller($seller_user_id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function sellerShow(Request $request, int $vehicle_id)
  {
    try {
      $seller_user_id = (int) $request->user()->id;
      $item = Vehicle::getItemSeller($vehicle_id, $seller_user_id);

      if (!$item) {
        return $this->apiRsp(404, 'Registro no encontrado');
      }

      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => $item]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
