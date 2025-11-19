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
    Schema::create('purchase_order_payments', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);

      $table->timestamps();
      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');

      $table->foreignId('purchase_order_id')->constrained('purchase_orders');
      $table->foreignId('bank_id')->constrained('banks');

      $table->string('account_holder', 100);
      $table->string('clabe_number', 18);
      $table->string('account_number', 20);

      $table->decimal('amount', 12, 2);

      $table->string('receipt_path', 50)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchase_order_payments');
  }
};
