<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class VehicleInvoice extends Model
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
      'invoice_type_id' => 'required|numeric',
      'registered_date' => 'nullable|date',
      'scheduled_date' => 'nullable|date',
      'note' => 'nullable|min:2',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($request, $vehicle_id)
  {
    $items = self::query()
      ->where('vehicle_id', $vehicle_id)
      ->where('is_active', 1)
      ->get([
        'id',
        'invoice_type_id',
        'registered_date',
        'scheduled_date',
        'document_path',
        'note',
      ]);

    foreach ($items as $item) {
      $item->invoice_type = InvoiceType::find($item->invoice_type_id, ['name']);
      $item->document_b64 = DocMgrController::getB64($item->document_path, 'VehicleInvoice');
    }

    return [
      'items' => $items
    ];
  }

  static public function getItem($id)
  {
    $item = self::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);
    $item->invoice_type = InvoiceType::find($item->invoice_type_id, ['name']);
    $item->document_b64 = DocMgrController::getB64($item->document_path, 'VehicleInvoice');
    $item->document_doc = null;

    return $item;
  }
}
