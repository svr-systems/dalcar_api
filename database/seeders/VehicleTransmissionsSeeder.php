<?php

namespace Database\Seeders;

use App\Models\VehicleTransmission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTransmissionsSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    $items = [
      [
        'name' => 'ESTANDAR',
      ],
      [
        'name' => 'AUTOMATICA',
      ],
    ];

    VehicleTransmission::insert($items);
  }
}
