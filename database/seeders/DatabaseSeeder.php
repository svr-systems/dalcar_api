<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(StateSeeder::class);
        // $this->call(MunicipalitiesSeeder::class);
        // $this->call(RoleSeeder::class);
        // $this->call(UserSeeder::class);
        // $this->call(InvestorTypesSeeder::class);
        // $this->call(VendorTypesSeeder::class);
        // $this->call(BanksSeeder::class);
        // $this->call(VehicleTransmissionsSeeder::class);
        // $this->call(VatTypesSeeder::class);
        // $this->call(VehicleBrandsSeeder::class);
        // $this->call(VehicleModelsSeeder::class);
        // $this->call(VehicleColorsSeeder::class);
        // $this->call(ExpenseTypesSeeder::class);
        $this->call(CustomOfficesSeeder::class);
        $this->call(OriginTypesSeeder::class);
    }
}
