<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Financier extends Model
{
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
  ];

  public static function valid($data)
  {
    $rules = [
      'name' => 'required|min:2|max:100',
      'website' => 'nullable|max:120',
      'note' => 'nullable|max:255',
      'contact_name' => 'nullable|max:100',
      'contact_email' => 'nullable|email|max:255',
      'contact_phone' => 'nullable|max:15',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'F-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($request)
  {
    $items = self::query()
      ->where('is_active', boolval($request->is_active))
      ->get([
        'id',
        'is_active',
        'name',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
    }

    return $items;
  }

  static public function getItem($request, $id)
  {
    $item = self::query()->find($id);

    $item->uiid = self::getUiid($item->id);
    $item->created_by = User::query()->find($item->created_by_id, ['email']);
    $item->updated_by = User::query()->find($item->updated_by_id, ['email']);

    return $item;
  }
}