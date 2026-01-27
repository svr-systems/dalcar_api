<?php

namespace App\Models;

use App\Http\Controllers\GenController;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class VehicleInvestor extends Model
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
      'vehicle_id' => 'required|numeric',
      'investor_id' => 'required|numeric',
      'percentages' => 'required|numeric',
      'amount' => 'nullable|numeric',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($request, $vehicle_id)
  {
    $vehicle = Vehicle::find($vehicle_id, ['id', 'is_active']);

    $items = self::query()
      ->where('vehicle_id', $vehicle->id)
      ->where('is_active', 1)
      ->get([
        'id',
        'investor_id',
        'percentages',
        'amount',
      ]);

    foreach ($items as $item) {
      $item->investor = Investor::find($item->investor_id, ['user_id']);
      $item->investor->user = User::find($item->investor->user_id, ['name', 'paternal_surname', 'maternal_surname']);
      $item->investor->user->full_name = GenController::getFullName($item->investor->user);
    }

    return [
      'items' => $items,
      'vehicle' => $vehicle,
    ];
  }

  static public function getItem($id)
  {
    $item = self::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->investor = Investor::find($item->investor_id, ['user_id']);
    $item->investor->user = User::find($item->investor->user_id, ['name', 'paternal_surname', 'maternal_surname']);
    $item->investor->user->full_name = GenController::getFullName($item->investor->user);

    return $item;
  }
}
