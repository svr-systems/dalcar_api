<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Throwable;
use DB;

class ScheduleController extends Controller {
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => Schedule::getItems($req)]
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
        ['item' => Schedule::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id) {
    DB::beginTransaction();
    try {
      $item = Schedule::find($id);

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
    return $this->storeUpdate($req);
  }

  public function update(Request $req, $id) {
    return null;
  }

  public function storeUpdate($req) {
    DB::beginTransaction();
    try {

      // foreach ($req as $schedule) {
      //   $valid = Schedule::valid($schedule);

      //   if ($valid->fails()) {
      //     return $this->apiRsp(422, $valid->errors()->first());
      //   }
      // }

      foreach ($req->schedule as $schedule) {
        if (!$schedule['id']) {
          $item = new Schedule;
          $item->created_by_id = $req->user()->id;
          $item->updated_by_id = $req->user()->id;

          $item = $this->saveItem($item, $schedule);
        } else {
          $item = Schedule::find($schedule['id']);
          $item->active = true;
          $item->updated_by_id = $req->user()->id;
          $item->save();
        }
      }

      DB::commit();
      return $this->apiRsp(
        201,
        'Registros agregado correctamente',
        null
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

    $item->check_in = GenController::filter($data['check_in'], 'U');
    $item->check_out = GenController::filter($data['check_out'], 'U');
    $item->user_id = GenController::filter($data['user_id'], 'id');
    $item->day_id = GenController::filter($data['day_id'], 'id');
    $item->save();

    return $item;
  }
}
