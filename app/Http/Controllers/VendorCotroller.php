<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorBank;
use DB;
use Illuminate\Http\Request;
use Throwable;

class VendorCotroller extends Controller
{
  public function index(Request $req)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => Vendor::getItems($req)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function show(Request $req, int $id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => Vendor::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, int $id)
  {
    DB::beginTransaction();
    try {
      $item = Vendor::find($id);
      $item->is_active = 0;
      $item->updated_by_id = $req->user()->id;
      $item->save();

      DB::commit();
      return $this->apiRsp(200, 'Registro eliminado correctamente');
    } catch (Throwable $err) {
      DB::rollback();
      return $this->apiRsp(500, null, $err);
    }
  }

  public function restore(Request $req)
  {
    DB::beginTransaction();
    try {
      $item = Vendor::find($req->id);
      $item->is_active = 1;
      $item->updated_by_id = $req->user()->id;
      $item->save();

      DB::commit();
      return $this->apiRsp(200, 'Registro activado correctamente', [
        'item' => Vendor::getItem($req, $item->id)
      ]);
    } catch (Throwable $err) {
      DB::rollback();
      return $this->apiRsp(500, null, $err);
    }
  }

  public function store(Request $req)
  {
    return $this->storeUpdate($req, null);
  }

  public function update(Request $req, int $id)
  {
    return $this->storeUpdate($req, $id);
  }

  public function storeUpdate($req, int $id)
  {
    DB::beginTransaction();
    try {
      $valid = Vendor::valid($req->all());
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new Vendor;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
      } else {
        $item = Vendor::find($id);
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
    $item->vendor_type_id = GenController::filter($data->vendor_type_id, 'id');
    $item->payment_days = GenController::filter($data->payment_days, 'i');
    $item->save();

    $vendor_banks = json_decode(json_encode($data->vendor_banks));
    foreach ($vendor_banks as $vendor_bank) {
      $vendor_bank_item = VendorBank::find($vendor_bank->id);
      if (!$vendor_bank_item) {
        $vendor_bank_item = new VendorBank;
      }

      $vendor_bank_item->is_active = GenController::filter($vendor_bank->is_active, 'b');
      $vendor_bank_item->bank_id = GenController::filter($vendor_bank->bank_id, 'id');
      $vendor_bank_item->account_holder = GenController::filter($vendor_bank->account_holder, 'U');
      $vendor_bank_item->clabe_number = GenController::filter($vendor_bank->clabe_number, 'U');
      $vendor_bank_item->account_number = GenController::filter($vendor_bank->account_number, 'U');
      $vendor_bank_item->vendor_id = $item->id;
      $vendor_bank_item->save();
    }

    return $item;
  }
}
