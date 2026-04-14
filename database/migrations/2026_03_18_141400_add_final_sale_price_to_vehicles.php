<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('vehicles', function (Blueprint $table) {
      $table->decimal('final_sale_price', 12, 2)->nullable();
      $table->decimal('sale_commission_amount', 12, 2)->nullable();
    });
  }

  public function down(): void
  {
    Schema::table('vehicles', function (Blueprint $table) {
      $table->dropColumn([
        'final_sale_price',
        'sale_commission_amount',
      ]);
    });
  }
};
