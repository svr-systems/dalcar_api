<?php

namespace Database\Seeders;

use App\Models\CustomOffice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomOfficesSeeder extends Seeder {
  public function run(): void {
    $items = [
      [
        'name' => 'TIJUANA, BAJA CALIFORNIA',
      ],
      [
        'name' => 'MEXICALI, BAJA CALIFORNIA',
      ],
      [
        'name' => 'NOGALES, SONORA',
      ],
      [
        'name' => 'AGUA PRIETA, SONORA',
      ],
      [
        'name' => 'CIUDAD JUÁREZ, CHIHUAHUA',
      ],
      [
        'name' => 'OJINAGA, CHIHUAHUA',
      ],
      [
        'name' => 'PIEDRAS NEGRAS, COAHUILA',
      ],
      [
        'name' => 'CIUDAD ACUÑA, COAHUILA',
      ],
      [
        'name' => 'COLOMBIA, NUEVO LEÓN',
      ],
      [
        'name' => 'NUEVO LAREDO, TAMAULIPAS',
      ],
      [
        'name' => 'REYNOSA, TAMAULIPAS',
      ],
      [
        'name' => 'MATAMOROS, TAMAULIPAS',
      ],
    ];

    CustomOffice::insert($items);
  }
}
