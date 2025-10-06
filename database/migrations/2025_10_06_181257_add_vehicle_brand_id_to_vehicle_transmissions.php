<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::table('vehicle_transmissions', function (Blueprint $table) {
      $table->foreignId('vehicle_brand_id')->default(1)->constrained('vehicle_brands');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::table('vehicle_transmissions', function (Blueprint $table) {
      $table->dropConstrainedForeignId('vehicle_brand_id');
    });
  }
};
