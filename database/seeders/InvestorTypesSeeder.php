<?php

namespace Database\Seeders;

use App\Models\InvestorType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvestorTypesSeeder extends Seeder
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

    InvestorType::insert($items);
  }
}
