<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('legacy_vehicles', function (Blueprint $table) {
            $table->tinyInteger('passenger_capacity')->after('pediment_notes')->unsigned()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legacy_vehicles', function (Blueprint $table) {
            $table->dropColumn('passenger_capacity');
        });
    }
};
