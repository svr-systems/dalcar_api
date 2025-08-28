<?php

namespace Database\Seeders;

use App\Models\VehicleModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleModelsSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    $items = [
      [
        'name' => 'COROLLA',
        'vehicle_brand_id' => 1
      ],
      [
        'name' => 'CAMRY',
        'vehicle_brand_id' => 1
      ],
      [
        'name' => 'RAV4',
        'vehicle_brand_id' => 1
      ],
      [
        'name' => 'HILUX',
        'vehicle_brand_id' => 1
      ],
      [
        'name' => 'YARIS',
        'vehicle_brand_id' => 1
      ],
      [
        'name' => 'CIVIC',
        'vehicle_brand_id' => 2
      ],
      [
        'name' => 'ACCORD',
        'vehicle_brand_id' => 2
      ],
      [
        'name' => 'CR-V',
        'vehicle_brand_id' => 2
      ],
      [
        'name' => 'HR-V',
        'vehicle_brand_id' => 2
      ],
      [
        'name' => 'FIT',
        'vehicle_brand_id' => 2
      ],
      [
        'name' => 'VERSA',
        'vehicle_brand_id' => 3
      ],
      [
        'name' => 'SENTRA',
        'vehicle_brand_id' => 3
      ],
      [
        'name' => 'ALTIMA',
        'vehicle_brand_id' => 3
      ],
      [
        'name' => 'X-TRAIL',
        'vehicle_brand_id' => 3
      ],
      [
        'name' => 'FRONTIER',
        'vehicle_brand_id' => 3
      ],
      [
        'name' => 'MAZDA 2',
        'vehicle_brand_id' => 4
      ],
      [
        'name' => 'MAZDA 3',
        'vehicle_brand_id' => 4
      ],
      [
        'name' => 'CX-3',
        'vehicle_brand_id' => 4
      ],
      [
        'name' => 'CX-5',
        'vehicle_brand_id' => 4
      ],
      [
        'name' => 'CX-30',
        'vehicle_brand_id' => 4
      ],
      [
        'name' => 'LANCER',
        'vehicle_brand_id' => 5
      ],
      [
        'name' => 'OUTLANDER',
        'vehicle_brand_id' => 5
      ],
      [
        'name' => 'XPANDER',
        'vehicle_brand_id' => 5
      ],
      [
        'name' => 'RIO',
        'vehicle_brand_id' => 6
      ],
      [
        'name' => 'FORTE',
        'vehicle_brand_id' => 6
      ],
      [
        'name' => 'SPORTAGE',
        'vehicle_brand_id' => 6
      ],
      [
        'name' => 'SORENTO',
        'vehicle_brand_id' => 6
      ],
      [
        'name' => 'SELTOS',
        'vehicle_brand_id' => 6
      ],
      [
        'name' => 'ACCENT',
        'vehicle_brand_id' => 7
      ],
      [
        'name' => 'ELANTRA',
        'vehicle_brand_id' => 7
      ],
      [
        'name' => 'TUCSON',
        'vehicle_brand_id' => 7
      ],
      [
        'name' => 'SANTA FE',
        'vehicle_brand_id' => 7
      ],
      [
        'name' => 'SPARK',
        'vehicle_brand_id' => 8
      ],
      [
        'name' => 'ONIX',
        'vehicle_brand_id' => 8
      ],
      [
        'name' => 'CRUZE',
        'vehicle_brand_id' => 8
      ],
      [
        'name' => 'TRAX',
        'vehicle_brand_id' => 8
      ],
      [
        'name' => 'SUBURBAN',
        'vehicle_brand_id' => 8
      ],
      [
        'name' => 'FIESTA',
        'vehicle_brand_id' => 9
      ],
      [
        'name' => 'FOCUS',
        'vehicle_brand_id' => 9
      ],
      [
        'name' => 'ESCAPE',
        'vehicle_brand_id' => 9
      ],
      [
        'name' => 'EXPLORER',
        'vehicle_brand_id' => 9
      ],
      [
        'name' => 'F-150',
        'vehicle_brand_id' => 9
      ],
      [
        'name' => 'PACIFICA',
        'vehicle_brand_id' => 10
      ],
      [
        'name' => '300',
        'vehicle_brand_id' => 10
      ],
      [
        'name' => 'GOL',
        'vehicle_brand_id' => 11
      ],
      [
        'name' => 'JETTA',
        'vehicle_brand_id' => 11
      ],
      [
        'name' => 'VENTO',
        'vehicle_brand_id' => 11
      ],
      [
        'name' => 'TIGUAN',
        'vehicle_brand_id' => 11
      ],
      [
        'name' => 'POLO',
        'vehicle_brand_id' => 11
      ],
      [
        'name' => 'IBIZA',
        'vehicle_brand_id' => 12
      ],
      [
        'name' => 'LEON',
        'vehicle_brand_id' => 12
      ],
      [
        'name' => 'ARONA',
        'vehicle_brand_id' => 12
      ],
      [
        'name' => 'ATECA',
        'vehicle_brand_id' => 12
      ],
      [
        'name' => 'A3',
        'vehicle_brand_id' => 13
      ],
      [
        'name' => 'A4',
        'vehicle_brand_id' => 13
      ],
      [
        'name' => 'A6',
        'vehicle_brand_id' => 13
      ],
      [
        'name' => 'Q3',
        'vehicle_brand_id' => 13
      ],
      [
        'name' => 'Q5',
        'vehicle_brand_id' => 13
      ],
      [
        'name' => 'SERIE 1',
        'vehicle_brand_id' => 14
      ],
      [
        'name' => 'SERIE 3',
        'vehicle_brand_id' => 14
      ],
      [
        'name' => 'SERIE 5',
        'vehicle_brand_id' => 14
      ],
      [
        'name' => 'X1',
        'vehicle_brand_id' => 14
      ],
      [
        'name' => 'X3',
        'vehicle_brand_id' => 14
      ],
      [
        'name' => 'A 200',
        'vehicle_brand_id' => 15
      ],
      [
        'name' => 'C 180',
        'vehicle_brand_id' => 15
      ],
      [
        'name' => 'E 300',
        'vehicle_brand_id' => 15
      ],
      [
        'name' => 'GLA',
        'vehicle_brand_id' => 15
      ],
      [
        'name' => 'GLC',
        'vehicle_brand_id' => 15
      ],
      [
        'name' => '208',
        'vehicle_brand_id' => 16
      ],
      [
        'name' => '301',
        'vehicle_brand_id' => 16
      ],
      [
        'name' => '2008',
        'vehicle_brand_id' => 16
      ],
      [
        'name' => '3008',
        'vehicle_brand_id' => 16
      ],
      [
        'name' => 'KWID',
        'vehicle_brand_id' => 17
      ],
      [
        'name' => 'LOGAN',
        'vehicle_brand_id' => 17
      ],
      [
        'name' => 'STEPWAY',
        'vehicle_brand_id' => 17
      ],
      [
        'name' => 'KOLEOS',
        'vehicle_brand_id' => 17
      ],
      [
        'name' => 'DUSTER',
        'vehicle_brand_id' => 17
      ],
      [
        'name' => 'MOBI',
        'vehicle_brand_id' => 18
      ],
      [
        'name' => 'ARGO',
        'vehicle_brand_id' => 18
      ],
      [
        'name' => 'CRONOS',
        'vehicle_brand_id' => 18
      ],
      [
        'name' => 'PULSE',
        'vehicle_brand_id' => 18
      ],
      [
        'name' => 'SWIFT',
        'vehicle_brand_id' => 19
      ],
      [
        'name' => 'IGNIS',
        'vehicle_brand_id' => 19
      ],
      [
        'name' => 'VITARA',
        'vehicle_brand_id' => 19
      ],
      [
        'name' => 'ERTIGA',
        'vehicle_brand_id' => 19
      ],
      [
        'name' => 'RENEGADE',
        'vehicle_brand_id' => 20
      ],
      [
        'name' => 'COMPASS',
        'vehicle_brand_id' => 20
      ],
      [
        'name' => 'CHEROKEE',
        'vehicle_brand_id' => 20
      ],
      [
        'name' => 'WRANGLER',
        'vehicle_brand_id' => 20
      ],
      [
        'name' => 'MODEL 3',
        'vehicle_brand_id' => 21
      ],
      [
        'name' => 'MODEL S',
        'vehicle_brand_id' => 21
      ],
      [
        'name' => 'MODEL X',
        'vehicle_brand_id' => 21
      ],
      [
        'name' => 'MODEL Y',
        'vehicle_brand_id' => 21
      ],
      [
        'name' => 'Q50',
        'vehicle_brand_id' => 22
      ],
      [
        'name' => 'Q60',
        'vehicle_brand_id' => 22
      ],
      [
        'name' => 'QX50',
        'vehicle_brand_id' => 22
      ],
      [
        'name' => 'QX60',
        'vehicle_brand_id' => 22
      ],
      [
        'name' => 'ILX',
        'vehicle_brand_id' => 23
      ],
      [
        'name' => 'TLX',
        'vehicle_brand_id' => 23
      ],
      [
        'name' => 'RDX',
        'vehicle_brand_id' => 23
      ],
      [
        'name' => 'MDX',
        'vehicle_brand_id' => 23
      ],
      [
        'name' => 'IS 300',
        'vehicle_brand_id' => 24
      ],
      [
        'name' => 'ES 350',
        'vehicle_brand_id' => 24
      ],
      [
        'name' => 'NX 300',
        'vehicle_brand_id' => 24
      ],
      [
        'name' => 'RX 350',
        'vehicle_brand_id' => 24
      ],
      [
        'name' => 'ATTITUDE',
        'vehicle_brand_id' => 25
      ],
      [
        'name' => 'CHARGER',
        'vehicle_brand_id' => 25
      ],
      [
        'name' => 'DURANGO',
        'vehicle_brand_id' => 25
      ],
      [
        'name' => 'JOURNEY',
        'vehicle_brand_id' => 25
      ],
    ];

    VehicleModel::insert($items);
  }
}
