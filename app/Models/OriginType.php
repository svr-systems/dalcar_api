<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OriginType extends Model
{
  use HasFactory;
  public $timestamps = false;

  static public function getItems($req)
  {
    $items = OriginType::query()
      ->where('is_active', 1)
      ->get([
        'id',
        'name',
      ]);

    return $items;
  }
}
