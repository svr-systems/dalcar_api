<?php

namespace Database\Seeders;

use App\Models\VehicleColor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleColorsSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    $items = [
      [
        'name' => 'BLANCO',
      ],
      [
        'name' => 'NEGRO',
      ],
      [
        'name' => 'GRIS',
      ],
      [
        'name' => 'PLATA',
      ],
      [
        'name' => 'ROJO',
      ],
      [
        'name' => 'AZUL',
      ],
      [
        'name' => 'VERDE',
      ],
      [
        'name' => 'AMARILLO',
      ],
      [
        'name' => 'NARANJA',
      ],
      [
        'name' => 'CAFÉ',
      ],
      [
        'name' => 'BEIGE',
      ],
      [
        'name' => 'DORADO',
      ],
      [
        'name' => 'VINO',
      ],
      [
        'name' => 'TURQUESA',
      ],
      [
        'name' => 'MORADO',
      ],
      [
        'name' => 'AZUL MARINO',
      ],
      [
        'name' => 'AZUL CLARO',
      ],
      [
        'name' => 'VERDE OLIVO',
      ],
      [
        'name' => 'VERDE LIMÓN',
      ],
      [
        'name' => 'GRIS OSCURO',
      ],
      [
        'name' => 'GRIS CLARO',
      ],
      [
        'name' => 'PLATA METÁLICO',
      ],
      [
        'name' => 'ROJO CEREZA',
      ],
      [
        'name' => 'ROJO VINO',
      ],
      [
        'name' => 'PERLA BLANCO',
      ],
      [
        'name' => 'PERLA NEGRO',
      ],
      [
        'name' => 'CHAMPÁN',
      ],
      [
        'name' => 'BRONCE',
      ],
      [
        'name' => 'CELESTE',
      ],
      [
        'name' => 'LILA',
      ],
      [
        'name' => 'ARENA',
      ],
      [
        'name' => 'MARFIL',
      ],
    ];

    VehicleColor::insert($items);
  }
}
