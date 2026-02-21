<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;

class InvoiceType extends Model
{
  use HasFactory;

  public static function valid($data)
  {
    $rules = [
      'name' => 'required|min:2|max:40',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getItems($req)
  {
    $items = InvoiceType::query()
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
    $item = InvoiceType::find($id, [
      'id',
      'is_active',
      'name',
    ]);

    return $item;
  }
}
