<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicleDocument extends Model
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
      'document_type_id' => 'required|numeric',
      'note' => 'nullable|min:2',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($req)
  {
    $items = LegacyVehicleDocument::query()
      ->where('legacy_vehicle_id', $req->legacy_vehicle_id)
      ->where('is_active', boolval($req->is_active))
      ->get([
        'id',
        'document_type_id',
        'document_path',
        'note',
      ]);

    foreach ($items as $item) {
      $item->document_type = DocumentType::find($item->document_type_id, ['name']);
      $item->document_b64 = DocMgrController::getB64($item->document_path, 'LegacyVehicleDocument');
      $item->document_doc = null;
      $item->document_dlt = false;
    }

    return $items;
  }

  static public function getItem($req, $id)
  {
    $item = LegacyVehicleDocument::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->document_type = DocumentType::find($item->document_type_id, ['name']);

    return $item;
  }
}
