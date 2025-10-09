<?php

namespace Database\Seeders;

use App\Models\OriginType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OriginTypesSeeder extends Seeder
{
  public function run(): void
  {
    $items = [
      [
        'name' => 'NACIONAL',
      ],
      [
        'name' => 'IMPORTADO',
      ],
      [
        'name' => 'EXPORTADO',
      ],
    ];

    OriginType::insert($items);
  }
}
