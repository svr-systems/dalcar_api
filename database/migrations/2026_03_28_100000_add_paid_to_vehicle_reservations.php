<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('vehicle_reservations', function (Blueprint $table) {
      $table->timestamp('paid_at')->nullable();
      $table->foreignId('paid_by_id')->nullable()->constrained('users');
    });
  }

  public function down(): void
  {
    Schema::table('vehicle_reservations', function (Blueprint $table) {
      $table->dropConstrainedForeignId('paid_by_id');
      $table->dropColumn([
        'paid_at',
      ]);
    });
  }
};