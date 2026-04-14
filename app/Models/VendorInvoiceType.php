<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorInvoiceType extends Model
{
  use HasFactory;
  public $timestamps = false;

  protected $casts = [
    'delivery_days' => 'string',
  ];
}
