<?php

namespace Database\Seeders;

use App\Models\VendorType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorTypesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $items = [
      [
        'name' => 'TIPO 1',
      ],
    ];

    VendorType::insert($items);
  }
}
