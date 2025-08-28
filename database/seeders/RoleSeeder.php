<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder {
  public function run() {
    $items = [
      [
        'id' => 1,
        'name' => 'Admin',
      ],
    ];

    Role::insert($items);
  }
}
