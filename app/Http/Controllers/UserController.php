<?php

namespace App\Http\Controllers;

use App\Models\UserBranche;
use Illuminate\Http\Request;
use App\Models\User;
use Throwable;
use Storage;
use DB;

class UserController extends Controller {
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => User::getItems($req)]
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
        ['item' => User::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id) {
    DB::beginTransaction();
    try {
      $item = User::find($id);

      if (!$item) {
        return $this->apiRsp(422, 'ID no existente');
      }

      $item->active = false;
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
      $email_current = null;
      $email = GenController::filter($req->email, 'l');

      $valid = User::validEmail(['email' => $email], $id);
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $valid = User::valid($req->all());
      if ($valid->fails()) {
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new User;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
      } else {
        $item = User::find($id);
        $email_current = $item->email;

        $item->updated_by_id = $req->user()->id;
      }

      $item = $this->saveItem($item, $req);

      if ($email_current != $email) {
        $item->email_verified_at = null;
        $item->save();

        EmailController::userAccountConfirmation($item->email, $item);
      }

      if ($req->user_branches) {
        foreach ($req->user_branches as $user_branch) {
          $user_branch_item = UserBranche::find($user_branch['id']);
          if (!$user_branch_item) {
            $user_branch_item = new UserBranche;
          }
          $user_branch_item->is_active = GenController::filter($user_branch['is_active'], 'b');
          $user_branch_item->branch_id = GenController::filter($user_branch['branch_id'], 'id');
          $user_branch_item->user_id = $item->id;
          $user_branch_item->save();
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

  public static function saveItem($item, $data, $is_req = true) {
    if (!$is_req) {
      $item->active = GenController::filter($data->active, 'b');
    }

    $item->name = GenController::filter($data->name, 'U');
    $item->paternal_surname = GenController::filter($data->paternal_surname, 'U');
    $item->maternal_surname = GenController::filter($data->maternal_surname, 'U');
    $item->email = GenController::filter($data->email, 'l');
    $item->role_id = GenController::filter($data->role_id, 'id');
    $item->phone = GenController::filter($data->phone, 'U');
    $item->avatar_path = DocMgrController::save(
      $data->avatar_path,
      DocMgrController::exist($data->avatar_doc),
      $data->avatar_dlt,
      'User'
    );
    $item->save();

    return $item;
  }

  public function getDni(Request $req) {
    try {
      $image_controller = new ImageController;
      $img_b64 = $image_controller->UserDNI($req->user_id);
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        [
          'img64'=> $img_b64['jpg64'],
          'ext' => '.jpg'
        ]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
  public function getUserFile() {
    try {
      $items = User::getUserFile();
      $file_name = time() . '.json';
      Storage::disk('temp')->put($file_name, json_encode($items));
      $json = file_get_contents(Storage::disk('temp')->path($file_name));
      $json64 = base64_encode($json);
      Storage::disk('temp')->delete($file_name);
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        $json64
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
