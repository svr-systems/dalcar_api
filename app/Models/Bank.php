<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
  use HasFactory;
  public $timestamps = false;

  static public function getItems($req) {
    $items = Bank::
      orderBy('name')->
      where('is_active', true);

    $items = $items->get([
      'id',
      'name',
      'code'
    ]);

    return $items;
  }
}
