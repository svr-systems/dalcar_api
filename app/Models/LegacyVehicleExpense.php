<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicleExpense extends Model
{
  protected function serializeDate(DateTimeInterface $date)
  {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data)
  {
    $rules = [
      'legacy_vehicle_id' => 'required|numeric',
      'expense_type_id' => 'required|numeric',
      'note' => 'required|min:2',
      'expense_date' => 'required|date',
      'amount' => 'required|numeric',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'VHG-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req)
  {
    $items = LegacyVehicleExpense::query()
      ->where('legacy_vehicle_id', $req->legacy_vehicle_id)
      ->where('is_active', boolval($req->is_active))
      ->get([
        'id',
        'expense_type_id',
        'note',
        'expense_date',
        'amount',
      ]);

    foreach ($items as $item) {
      $item->expense_type = ExpenseType::find($item->expense_type_id, ['name']);
    }

    return $items;
  }

  static public function getItem($req, $id)
  {
    $item = LegacyVehicleExpense::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->expense_type = ExpenseType::find($item->expense_type_id, ['name']);

    return $item;
  }
}
