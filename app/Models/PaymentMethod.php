<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
  use HasFactory;

  public $timestamps = false;

  static public function getItems($request)
  {
    $items = self::query()
      ->where('is_active', 1)
      ->orderBy('name')
      ->get([
        'id',
        'name',
      ]);

    return $items;
  }
}