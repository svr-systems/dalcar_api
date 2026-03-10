<?php

namespace App\Http\Controllers;

use App\Models\Financier;
use DB;
use Illuminate\Http\Request;
use Throwable;

class FinancierController extends Controller
{
  public function index(Request $request)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => Financier::getItems($request)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function show(Request $request, int $id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => Financier::getItem($request, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $request, int $id)
  {
    DB::beginTransaction();

    try {
      $item = Financier::find($id);
      $item->is_active = 0;
      $item->updated_by_id = $request->user()->id;
      $item->save();

      DB::commit();

      return $this->apiRsp(200, 'Registro eliminado correctamente');
    } catch (Throwable $err) {
      DB::rollBack();

      return $this->apiRsp(500, null, $err);
    }
  }

  public function restore(Request $request)
  {
    DB::beginTransaction();

    try {
      $item = Financier::find($request->id);
      $item->is_active = 1;
      $item->updated_by_id = $request->user()->id;
      $item->save();

      DB::commit();

      return $this->apiRsp(200, 'Registro activado correctamente', [
        'item' => Financier::getItem($request, $item->id),
      ]);
    } catch (Throwable $err) {
      DB::rollBack();

      return $this->apiRsp(500, null, $err);
    }
  }

  public function store(Request $request)
  {
    return $this->storeUpdate($request, null);
  }

  public function update(Request $request, int $id)
  {
    return $this->storeUpdate($request, $id);
  }

  public function storeUpdate($request, $id)
  {
    DB::beginTransaction();

    try {
      $valid = Financier::valid($request->all());

      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new Financier;
        $item->created_by_id = $request->user()->id;
        $item->updated_by_id = $request->user()->id;
      } else {
        $item = Financier::find($id);
        $item->updated_by_id = $request->user()->id;
      }

      $item = $this->saveItem($item, $request);

      DB::commit();

      return $this->apiRsp(
        $store_mode ? 201 : 200,
        'Registro ' . ($store_mode ? 'agregado' : 'editado') . ' correctamente',
        $store_mode ? ['item' => ['id' => $item->id]] : null
      );
    } catch (Throwable $err) {
      DB::rollBack();

      return $this->apiRsp(500, null, $err);
    }
  }

  public static function saveItem($item, $data)
  {
    $item->name = GenController::filter($data->name, 'U');
    $item->website = GenController::trim($data->website);
    $item->note = GenController::trim($data->note);
    $item->contact_name = GenController::filter($data->contact_name, 'U');
    $item->contact_email = GenController::trim($data->contact_email);
    $item->contact_phone = GenController::trim($data->contact_phone);
    $item->save();

    return $item;
  }
}
