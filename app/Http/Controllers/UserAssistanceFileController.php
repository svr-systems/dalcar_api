<?php

namespace App\Http\Controllers;

use App\Models\AssistanceType;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\UserAssistanceFile;
use App\Models\UserAssistance;
use App\Models\User;
use Throwable;
use Storage;
use File;
use DB;

class UserAssistanceFileController extends Controller {
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => UserAssistanceFile::getItems($req)]
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
        ['item' => UserAssistanceFile::getItem($req, $id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function destroy(Request $req, $id) {
    DB::beginTransaction();
    try {
      $item = UserAssistanceFile::find($id);

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

      $store_mode = is_null($id);

      if ($store_mode) {
        $item = new UserAssistanceFile;
        $item->created_by_id = $req->user()->id;
        $item->updated_by_id = $req->user()->id;
      } else {
        $item = UserAssistanceFile::find($id);
        $item->updated_by_id = $req->user()->id;
      }

      $item = $this->saveItem($item, $req);

      $assistance_file = File::json(Storage::disk('Assistance')->path($item->assistance_file));

      $this->readAssistanceFile($assistance_file, $req->user()->id, $item->id);

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

    // $item->assistance_file = 'asistencias.json';
    $item->assistance_file = DocMgrController::save(
      $data->assistance_file,
      DocMgrController::exist($data->assistance_file_doc),
      $data->assistance_file_dlt,
      'Assistance'
    );
    $item->save();

    return $item;
  }
  public static function readAssistanceFile($assistance_file, $user_id, $user_assistance_files_id) {
    foreach ($assistance_file as $assistance) {
      $item = new UserAssistance;

      $item->registered_at = $assistance['timestamp'];
      $item->user_id = $assistance['id'];
      $item->user_assistance_files_id = $user_assistance_files_id;
      $item->updated_by_id = $user_id;

      $item->save();
    }
  }

  public function showAssistanceFileRegisters(Request $req) {
    try {
      // $file_name = time() . '.json';
      $file_name = 'asistencias.json';
      // Storage::disk('temp')->put($file_name, file_get_contents($req->assistance_file_doc));
      $assistance_file = File::json(Storage::disk('temp')->path($file_name));

      $assistance_file = $this->groupByDate($assistance_file);
      $assistance_file = $this->setAssistanceType($assistance_file);
      $assistance_file = GenController::deleteKeyFromArray($assistance_file);


      // return $assistance_file;
      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        $assistance_file
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public static function groupByDate($assistance_file) {
    $date_assistances = [];

    foreach ($assistance_file as $key => $assistance) {
      $key_date_assistances = date('Y-m-d', strtotime($assistance['timestamp']));
      if (!array_key_exists($key_date_assistances, $date_assistances)) {
        $date_assistances[$key_date_assistances] = new \stdClass;
        $date_assistances[$key_date_assistances]->users = [];
        $date_assistances[$key_date_assistances]->date = $key_date_assistances;
      }
      if (!array_key_exists($assistance['id'], $date_assistances[$key_date_assistances]->users)) {
        $day_id = date('w', strtotime($assistance['timestamp']));
        $user_id = $assistance['id'];
        $date_assistances[$key_date_assistances]->users[$assistance['id']] = new \stdClass;
        $date_assistances[$key_date_assistances]->users[$assistance['id']]->records = [];
        $date_assistances[$key_date_assistances]->users[$assistance['id']]->user_id = $assistance['id'];
        $date_assistances[$key_date_assistances]->users[$assistance['id']]->day = $day_id;
        $date_assistances[$key_date_assistances]->users[$assistance['id']]->schedule = Schedule::getScheduleByDayUserAndDay($user_id, $day_id);
      }

      $assistance_record = new \stdClass;
      $assistance_record->record = date('Y-m-d H:i:s', strtotime($assistance['timestamp']));
      array_push($date_assistances[$key_date_assistances]->users[$assistance['id']]->records, $assistance_record);
    }

    return $date_assistances;
  }

  public static function setAssistanceType($assistance_file) {
    foreach ($assistance_file as $key_date =>$date) {
      foreach ($date->users as $key_user => $user) {
        $total_records = sizeof($user->records);
        $entrada = false;
        $schedule_counter = 0;
        foreach ($user->records as $key_record => $record) {
          if($key_record === 0){
            if(sizeof($user->schedule) > 0){
              $record->assistance_type_id = UserAssistanceFileController::getCheckInType($user->schedule,$record,$schedule_counter);
            }else{
              $record->assistance_type_id = 1;
            }
          }elseif($key_record === $total_records - 1){
            if(sizeof($user->schedule) > 0){
              $record->assistance_type_id = UserAssistanceFileController::getCheckOutType($user->schedule,$record,$schedule_counter);
            }else{
              $record->assistance_type_id = 2;
            }
          }else{
            $record->assistance_type_id = ($entrada)?3:4;
            $entrada = !$entrada;
          }
          $record->assistance_type = AssistanceType::find($record->assistance_type_id,['name']);
        }
        $date->users = GenController::deleteKeyFromArray($date->users);
      }
    }
    return $assistance_file;
  }

  public static function getCheckInType($schedule,$record,$schedule_counter){
    $time_record = date('H:i:s', strtotime($record->record));
    $time_limit = date('H:i:s', strtotime("+15 minutes", strtotime($schedule[$schedule_counter]->check_in)));
    if($time_record < $time_limit){
      return 1;
    }
    return 6;
  }

  public static function getCheckOutType($schedule,$record,$schedule_counter){
    $time_record = date('H:i:s', strtotime($record->record));
    $time_limit = date('H:i:s', strtotime("-15 minutes", strtotime($schedule[$schedule_counter]->check_out)));
    if($time_record > $time_limit){
      return 2;
    }
    return 7;
  }
}
