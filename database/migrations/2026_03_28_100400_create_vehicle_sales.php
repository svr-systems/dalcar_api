<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vehicle_sales', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->foreignId('created_by_id')->nullable()->constrained('users');
      $table->foreignId('updated_by_id')->nullable()->constrained('users');

      $table->foreignId('vehicle_id')->constrained('vehicles');
      $table->foreignId('customer_id')->constrained('customers');
      $table->foreignId('vehicle_reservation_id')->nullable()->constrained('vehicle_reservations');
      $table->foreignId('seller_user_id')->constrained('users');

      $table->boolean('is_finance')->default(0);
      $table->foreignId('financier_id')->nullable()->constrained('financiers');

      $table->text('notes')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vehicle_sales');
  }
};