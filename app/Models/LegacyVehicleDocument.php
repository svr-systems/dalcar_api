<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicleDocument extends Model {
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
      'document_type_id' => 'required|numeric',
      'is_scheduled' => 'required|boolean',
      'scheduled_at' => 'nullable|date',
      'received_at' => 'nullable|date',
      'note' => 'nullable|min:2',
    ];

    if (!$is_req) {
      array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
    }

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id) {
    return 'LVD-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req) {
    $items = LegacyVehicleDocument::
      where('legacy_vehicle_id', $req->legacy_vehicle_id)->
      where('is_active', boolval($req->is_active));

    $items = $items->
    get([
        'id',
        'is_active',
        'document_type_id',
        'is_scheduled',
        'scheduled_at',
        'received_at',
        'document_path',
        'note',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->document_type = DocumentType::find($item->document_type_id, ['name']);
      $item->uiid = LegacyVehicleDocument::getUiid($item->id);
    }

    return $items;
  }

  static public function getItem($req, $id) {
    $item = LegacyVehicleDocument::
      where('id', $id)->
      find($id, [
        'id',
        'is_active',
        'created_at',
        'updated_at',
        'created_by_id',
        'updated_by_id',
        'document_type_id',
        'is_scheduled',
        'scheduled_at',
        'received_at',
        'document_path',
        'note',
      ]);

    if ($item) {
      $item->created_by = User::find($item->created_by_id, ['email']);
      $item->updated_by = User::find($item->updated_by_id, ['email']);
      $item->document_type = DocumentType::find($item->document_type_id, ['name']);
      $item->uiid = LegacyVehicleDocument::getUiid($item->id);
    }

    return $item;
  }
}
