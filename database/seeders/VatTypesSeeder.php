<?php

namespace Database\Seeders;

use App\Models\VatType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VatTypesSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    $items = [
      [
        'name' => 'TIPO 1',
      ],
      [
        'name' => 'TIPO 2',
      ],
      [
        'name' => 'TIPO 3',
      ],
    ];

    VatType::insert($items);
  }
}
