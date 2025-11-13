<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\InvestorCompany;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Throwable;

class InvestorController extends Controller
{
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => Investor::getItems($req)]
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
        ['item' => Investor::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id) {
    DB::beginTransaction();
    try {
      $item = Investor::find($id);

      if (!$item) {
        return $this->apiRsp(422, 'ID no existente');
      }

      $user = User::find($item->user_id);
      $user->is_active = false;
      $user->updated_by_id = $req->user()->id;
      $user->save();

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
      $user_data = json_decode(json_encode($req->user));
      $user_data->role_id = 4;

      $email_current = null;
      $email = GenController::filter($user_data->email, 'l');

      $valid = User::validEmail(['email' => $email], $user_data->id);
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $valid = User::valid((array) $user_data);
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $user = new User;
        $user->created_by_id = $req->user()->id;
        $user->updated_by_id = $req->user()->id;
        
        $item = new Investor;
      } else {
        $item = Investor::find($id);
        $user = User::find($item->user_id);
        $email_current = $user->email;

        $user->updated_by_id = $req->user()->id;
      }

      $user = UserController::saveItem($user, $user_data);
      $item->user_id = $user->id;
      $item->investor_type_id = $req->investor_type_id;
      $item->save();

      if ($req->investor_companies) {
        foreach ($req->investor_companies as $investor_company) {
          $investor_company_item = InvestorCompany::find($investor_company['id']);
          if (!$investor_company_item) {
            $investor_company_item = new InvestorCompany;
          }
          $investor_company_item->is_active = GenController::filter($investor_company['is_active'], 'b');
          $investor_company_item->company_id = GenController::filter($investor_company['company_id'], 'id');
          $investor_company_item->floor_percentage = GenController::filter($investor_company['floor_percentage'], 'f');
          $investor_company_item->investor_id = $item->id;
          $investor_company_item->save();
        }
      }

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
}
