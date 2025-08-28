<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorType extends Model
{
  use HasFactory;
  public $timestamps = false;

  static public function getItems($req) {
    $items = VendorType::
    where('is_active', true);

    $items = $items->get([
      'id',
      'name',
    ]);

    return $items;
  }
}
