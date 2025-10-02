<?php

namespace App\Models;

use App\Http\Controllers\GenController;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicleInvestor extends Model
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
      'investor_id' => 'required|numeric',
      'percentages' => 'required|numeric',
      'amount' => 'nullable|numeric',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'VHI-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req)
  {
    $items = LegacyVehicleInvestor::query()
      ->where('legacy_vehicle_id', $req->legacy_vehicle_id)
      ->where('is_active', boolval($req->is_active));

    $items = $items->
      get([
        'id',
        'investor_id',
        'percentages',
      ]);

    foreach ($items as $item) {
      $item->investor = Investor::find($item->investor_id, ['user_id']);
      $item->investor->user = User::find($item->investor->user_id, ['name', 'paternal_surname', 'maternal_surname']);
      $item->investor->user->full_name = GenController::getFullName($item->investor->user);
    }

    return $items;
  }

  static public function getItem($req, $id)
  {
    $item = LegacyVehicleInvestor::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->investor = Investor::find($item->investor_id, ['user_id']);
    $item->investor->user = User::find($item->investor->user_id, ['name', 'paternal_surname', 'maternal_surname']);
    $item->investor->user->full_name = GenController::getFullName($item->investor->user);

    return $item;
  }
}
