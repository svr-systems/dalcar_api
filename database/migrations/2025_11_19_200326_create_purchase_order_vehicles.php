<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('purchase_order_vehicles', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);

      $table->timestamps();
      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');

      $table->foreignId('purchase_order_id')->constrained('purchase_orders');
      $table->foreignId('vehicle_id')->constrained('vehicles');

      $table->decimal('purchase_price', 12, 2);
      $table->decimal('commission_amount', 12, 2);

      $table->foreignId('vat_type_id')->constrained('vat_types');
      $table->decimal('invoice_amount', 12, 2)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchase_order_vehicles');
  }
};
