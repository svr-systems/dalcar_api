<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class role extends Model
{
  use HasFactory;
  public $timestamps = false;

  static public function getItems($req)
  {
    $items = Role::query()
      ->where('is_active', 1)
      ->where('id', '!=', 4)
      ->get([
        'id',
        'name',
      ]);

    return $items;
  }
}
