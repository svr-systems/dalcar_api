<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleBrandsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $items = [
      [
        'name' => 'TOYOTA'
      ],
      [
        'name' => 'HONDA'
      ],
      [
        'name' => 'NISSAN'
      ],
      [
        'name' => 'MAZDA'
      ],
      [
        'name' => 'MITSUBISHI'
      ],
      [
        'name' => 'KIA'
      ],
      [
        'name' => 'HYUNDAI'
      ],
      [
        'name' => 'CHEVROLET'
      ],
      [
        'name' => 'FORD'
      ],
      [
        'name' => 'CHRYSLER'
      ],
      [
        'name' => 'VOLKSWAGEN'
      ],
      [
        'name' => 'SEAT'
      ],
      [
        'name' => 'AUDI'
      ],
      [
        'name' => 'BMW'
      ],
      [
        'name' => 'MERCEDES-BENZ'
      ],
      [
        'name' => 'PEUGEOT'
      ],
      [
        'name' => 'RENAULT'
      ],
      [
        'name' => 'FIAT'
      ],
      [
        'name' => 'SUZUKI'
      ],
      [
        'name' => 'JEEP'
      ],
      [
        'name' => 'TESLA'
      ],
      [
        'name' => 'INFINITI'
      ],
      [
        'name' => 'ACURA'
      ],
      [
        'name' => 'LEXUS'
      ],
      [
        'name' => 'DODGE'
      ],
    ];

    VehicleBrand::insert($items);
  }
}
