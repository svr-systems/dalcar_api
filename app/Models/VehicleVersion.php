<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;

class VehicleVersion extends Model {
  use HasFactory;
  public $timestamps = false;

  public static function valid($data, $is_req = true) {
    $rules = [
      'name' => 'required|min:2|max:60',
      'vehicle_model_id' => 'required|numeric',
      'model_year' => 'required|numeric',
    ];

    if (!$is_req) {
      array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
    }

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($req) {
    $items = VehicleVersion::
      orderBy('name')->
      where('vehicle_model_id', $req->vehicle_model_id)->
      where('model_year', $req->model_year)->
      where('is_active', boolval($req->is_active));

    $items = $items->
      get([
        'id',
        'is_active',
        'name',
        'model_year',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
    }

    return $items;
  }

  static public function getItem($req, $id) {
    $item = VehicleVersion::
      find($id, [
        'id',
        'is_active',
        'name',
        'model_year',
      ]);

    return $item;
  }
}
