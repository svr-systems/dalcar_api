<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('vendor_banks', function (Blueprint $table) {
      $table->string('cie_code', 20)->nullable();
      $table->boolean('is_commission')->default(0);
    });
  }

  public function down(): void
  {
    Schema::table('vendor_banks', function (Blueprint $table) {
      $table->dropColumn('cie_code');
      $table->dropColumn('is_commission');
    });
  }
};
