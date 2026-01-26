<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Vehicle extends Model
{
  protected function serializeDate(DateTimeInterface $date)
  {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data, $id)
  {
    $rules = [
      'branch_id' => 'required|numeric',
      'vehicle_version_id' => 'required|numeric',
      'vehicle_transmission_id' => 'required|numeric',
      'vehicle_color_id' => 'required|numeric',
      'vin' => 'nullable|min:2|max:17|unique:vehicles,vin,' . $id,
      'engine_number' => 'nullable|min:2|max:30',
      'repuve' => 'nullable|min:2|max:25',
      'vehicle_key' => 'nullable|min:2|max:20',
      'passenger_capacity' => 'nullable|min:1|max:70',
      'notes' => 'nullable',
      'origin_type_id' => 'nullable|numeric',
      'pediment_number' => 'nullable|min:2|max:30',
      'pediment_date' => 'nullable|date',
      'custom_office_id' => 'nullable|numeric',
      'pediment_notes' => 'nullable',
    ];

    $msgs = ['vin.unique' => 'El VIN ya ha sido registrado'];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'A-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }
}
