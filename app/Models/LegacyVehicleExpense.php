<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicleExpense extends Model {
  protected function serializeDate(DateTimeInterface $date) {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data, $is_req = true) {
    $rules = [
      'legacy_vehicle_id' => 'required|numeric',
      'expense_type_id' => 'required|numeric',
      'note' => 'required|min:2',
      'expense_date' => 'required|date',
      'amount' => 'required|numeric',
    ];

    if (!$is_req) {
      array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
    }

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id) {
    return 'LVE-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req) {
    $items = LegacyVehicleExpense::
      where('legacy_vehicle_id', $req->legacy_vehicle_id)->
      where('is_active', boolval($req->is_active));

    $items = $items->
    get([
        'id',
        'is_active',
        'expense_type_id',
        'note',
        'expense_date',
        'amount',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->expense_type = ExpenseType::find($item->expense_type_id);
      $item->uiid = LegacyVehicleExpense::getUiid($item->id);
    }

    return $items;
  }

  static public function getItem($req, $id) {
    $item = LegacyVehicleExpense::
      where('id', $id)->
      find($id, [
        'id',
        'is_active',
        'created_at',
        'updated_at',
        'created_by_id',
        'updated_by_id',
        'legacy_vehicle_id',
        'expense_type_id',
        'note',
        'expense_date',
        'amount',
      ]);

    if ($item) {
      $item->created_by = User::find($item->created_by_id, ['email']);
      $item->updated_by = User::find($item->updated_by_id, ['email']);
      $item->expense_type = ExpenseType::find($item->expense_type_id);
      $item->uiid = LegacyVehicleExpense::getUiid($item->id);
    }

    return $item;
  }
}
