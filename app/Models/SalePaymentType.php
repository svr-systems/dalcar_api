<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePaymentType extends Model
{
  public $timestamps = false;

  public static function getItems($request)
  {
    return self::query()
      ->where('is_active', 1)
      ->orderBy('id')
      ->get([
        'id',
        'name',
      ]);
  }
}