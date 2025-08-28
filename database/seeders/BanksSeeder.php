<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BanksSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run(): void {
    $items = [
      [
        'name' => 'BBVA MÉXICO',
        'code' => '40012'
      ],
      [
        'name' => 'ABC CAPITAL',
        'code' => '40138'
      ],
      [
        'name' => 'AMERICAN EXPRESS BANK (MÉXICO)',
        'code' => 'NULL'
      ],
      [
        'name' => 'BANCA AFIRME',
        'code' => '40062'
      ],
      [
        'name' => 'BANCA MIFEL',
        'code' => '40034'
      ],
      [
        'name' => 'BANCO ACTINVER',
        'code' => '40142'
      ],
      [
        'name' => 'BANCO AUTOFIN MÉXICO',
        'code' => '40128'
      ],
      [
        'name' => 'BANCO AZTECA',
        'code' => '40127'
      ],
      [
        'name' => 'BANCO BANCREA',
        'code' => '40153'
      ],
      [
        'name' => 'BANCO BASE',
        'code' => '40145'
      ], [
        'name' => 'BANCO COVALTO',
        'code' => '40154'
      ],
      [
        'name' => 'BANCO COMPARTAMOS',
        'code' => '40136'
      ],
      [
        'name' => 'BANCO CREDIT SUISSE (MÉXICO)',
        'code' => 'NULL'
      ],
      [
        'name' => 'BANCO DE INVERSIÓN AFIRME',
        'code' => 'NULL'
      ],
      [
        'name' => 'BANCO DEL BAJÍO',
        'code' => '40030'
      ],
      [
        'name' => 'BANCO FORJADORES',
        'code' => 'NULL'
      ],
      [
        'name' => 'BANCO INBURSA',
        'code' => '40058'
      ],
      [
        'name' => 'BANCO INMOBILIARIO MEXICANO',
        'code' => '40150'
      ],
      [
        'name' => 'BANCO INVEX',
        'code' => '40059'
      ],
      [
        'name' => 'BANCO JP MORGAN',
        'code' => '40111'
      ], [
        'name' => 'BANCO KEB HANA MÉXICO',
        'code' => '40146'
      ],
      [
        'name' => 'BANCO MONEX',
        'code' => '40112'
      ],
      [
        'name' => 'BANCO MULTIVA',
        'code' => '40132'
      ],
      [
        'name' => 'BANCO PAGATODO',
        'code' => '40148'
      ],
      [
        'name' => 'BANCO REGIONAL DE MONTERREY',
        'code' => 'NULL'
      ],
      [
        'name' => 'BANCO S3 CACEIS MÉXICO',
        'code' => '40155'
      ],
      [
        'name' => 'BANCO SABADELL',
        'code' => '40156'
      ],
      [
        'name' => 'BANCO SANTANDER',
        'code' => '40014'
      ],
      [
        'name' => 'BANCO SHINHAN DE MÉXICO',
        'code' => '40157'
      ],
      [
        'name' => 'BANCO VE POR MÁS',
        'code' => '40105'
      ], [
        'name' => 'BANCOPPEL',
        'code' => '40137'
      ],
      [
        'name' => 'BANK OF AMERICA MEXICO',
        'code' => '40109'
      ],
      [
        'name' => 'BANK OF CHINA MEXICO',
        'code' => '40159'
      ],
      [
        'name' => 'BANKAOOL',
        'code' => '40116'
      ],
      [
        'name' => 'BANORTE',
        'code' => '40072'
      ],
      [
        'name' => 'BANSI',
        'code' => '40060'
      ],
      [
        'name' => 'BARCLAYS BANK MÉXICO',
        'code' => '40102'
      ],
      [
        'name' => 'BNP PARIBAS',
        'code' => '40100'
      ],
      [
        'name' => 'CITIBANAMEX',
        'code' => '40002'
      ],
      [
        'name' => 'CIBANCO',
        'code' => '40143'
      ], [
        'name' => 'CONSUBANCO',
        'code' => '40140'
      ],
      [
        'name' => 'DEUTSCHE BANK MÉXICO',
        'code' => 'NULL'
      ],
      [
        'name' => 'FUNDACIÓN DONDÉ BANCO',
        'code' => '40151'
      ],
      [
        'name' => 'HSBC MÉXICO',
        'code' => '40021'
      ],
      [
        'name' => 'INDUSTRIAL AND COMMERCIAL BANK OF CHINA',
        'code' => '40152'
      ],
      [
        'name' => 'INTERCAM BANCO',
        'code' => '40138'
      ],
      [
        'name' => 'MIZUHO BANK',
        'code' => '40118'
      ],
      [
        'name' => 'MUFG BANK MEXICO',
        'code' => '40048'
      ],
      [
        'name' => 'SCOTIABANK',
        'code' => '40044'
      ],
      [
        'name' => 'BANREGIO',
        'code' => '40062'
      ],
      [
        'name' => 'BANJERCITO',
        'code' => '37019'
      ],
    ];

    Bank::insert($items);
  }
}
