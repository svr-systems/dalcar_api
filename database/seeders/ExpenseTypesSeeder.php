<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseTypesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $items = [
      [
        'name' => 'LLANTAS',
      ],
      [
        'name' => 'ALINEACIÓN Y BALANCEO',
      ],
      [
        'name' => 'SUSPENSIÓN',
      ],
      [
        'name' => 'FRENOS',
      ],
      [
        'name' => 'MECÁNICA GENERAL',
      ],
      [
        'name' => 'AFINACIÓN',
      ],
      [
        'name' => 'ACEITE Y FILTROS',
      ],
      [
        'name' => 'BATERÍA',
      ],
      [
        'name' => 'HOJALATERÍA',
      ],
      [
        'name' => 'PINTURA',
      ],
      [
        'name' => 'PULIDO Y ENCERADO',
      ],
      [
        'name' => 'DETALLADO INTERIOR',
      ],
      [
        'name' => 'LAVADO DE MOTOR',
      ],
      [
        'name' => 'CRISTALES',
      ],
      [
        'name' => 'TAPICERÍA',
      ],
      [
        'name' => 'CLIMATIZACIÓN (A/C)',
      ],
      [
        'name' => 'REVISIÓN ELÉCTRICA',
      ],
      [
        'name' => 'LIMPIEZA PROFUNDA',
      ],
      [
        'name' => 'TENENCIA Y DERECHOS',
      ],
      [
        'name' => 'VERIFICACIÓN VEHICULAR',
      ],
      [
        'name' => 'GESTORÍA',
      ],
    ];

    ExpenseType::insert($items);
  }
}
