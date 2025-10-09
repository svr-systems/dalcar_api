<?php

namespace Database\Seeders;

use App\Models\VatType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VatTypesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $items = [
      [
        'name' => 'UTILIDAD',
      ],
      [
        'name' => 'SIN IVA',
      ],
    ];

    VatType::insert($items);
  }
}
