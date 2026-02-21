<?php

namespace App\Http\Controllers;

use App\Models\InvoiceType;
use DB;
use Illuminate\Http\Request;
use Throwable;

class InvoiceTypeController extends Controller
{
  public function index(Request $req)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => InvoiceType::getItems($req)]
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
        ['item' => InvoiceType::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id)
  {
    DB::beginTransaction();
    try {
      $item = InvoiceType::find($id);

      if (!$item) {
        return $this->apiRsp(422, 'ID no existente');
      }

      $item->is_active = false;
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

      $valid = InvoiceType::valid($req->all());

      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new InvoiceType;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
      } else {
        $item = InvoiceType::find($id);
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
    $item->name = GenController::filter($data->name, 'U');
    $item->save();

    return $item;
  }
}
