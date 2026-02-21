<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use App\Http\Controllers\GenController;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class VehicleExpense extends Model
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
      'expense_type_id' => 'required|numeric',
      'note' => 'nullable|min:2',
      'expense_date' => 'required|date',
      'amount' => 'required|numeric',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($request, $vehicle_id)
  {
    $items = self::query()
      ->where('vehicle_id', $vehicle_id)
      ->where('is_active', 1)
      ->orderByDesc('id')
      ->get([
        'id',
        'expense_type_id',
        'note',
        'expense_date',
        'amount',
        'document_path',
      ]);

    foreach ($items as $item) {
      $item->expense_type = ExpenseType::find($item->expense_type_id, ['name']);
      $item->document_b64 = DocMgrController::getB64($item->document_path, 'VehicleExpense');
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
    $item->expense_type = ExpenseType::find($item->expense_type_id, ['name']);
    $item->document_b64 = DocMgrController::getB64($item->document_path, 'VehicleExpense');
    $item->document_doc = null;

    return $item;
  }
}
