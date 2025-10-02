<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;

class ExpenseType extends Model
{
  use HasFactory;
  public $timestamps = false;

  public static function valid($data)
  {
    $rules = [
      'name' => 'required|min:2|max:60',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($req)
  {
    $items = ExpenseType::query()
      ->where('is_active', boolval($req->is_active))
      ->orderBy('name')
      ->get([
        'id',
        'is_active',
        'name',
      ]);

    return $items;
  }

  static public function getItem($req, $id)
  {
    $item = ExpenseType::find($id, [
      'id',
      'is_active',
      'name',
    ]);

    return $item;
  }
}
