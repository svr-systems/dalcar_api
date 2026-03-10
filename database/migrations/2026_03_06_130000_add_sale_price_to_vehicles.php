<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('vehicles', function (Blueprint $table) {
      $table->decimal('sale_price', 12, 2)->nullable();
      $table->timestamp('sale_price_updated_at')->nullable();
      $table->foreignId('sale_price_updated_by_id')->nullable()->constrained('users');
    });
  }

  public function down(): void
  {
    Schema::table('vehicles', function (Blueprint $table) {
      $table->dropForeign(['sale_price_updated_by_id']);
      $table->dropColumn([
        'sale_price_updated_by_id',
        'sale_price_updated_at',
        'sale_price',
      ]);
    });
  }
};
