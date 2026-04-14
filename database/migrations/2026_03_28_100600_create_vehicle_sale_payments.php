<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vehicle_sale_payments', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->foreignId('created_by_id')->nullable()->constrained('users');
      $table->foreignId('updated_by_id')->nullable()->constrained('users');

      $table->foreignId('vehicle_sale_id')->constrained('vehicle_sales');
      $table->foreignId('sale_payment_type_id')->constrained('sale_payment_types');
      $table->foreignId('payment_method_id')->constrained('payment_methods');

      $table->decimal('amount', 12, 2)->default(0);
      $table->string('voucher_path', 50)->nullable();
      $table->text('notes')->nullable();

      $table->timestamp('refund_at')->nullable();
      $table->foreignId('refund_by_id')->nullable()->constrained('users');
      $table->text('refund_note')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vehicle_sale_payments');
  }
};