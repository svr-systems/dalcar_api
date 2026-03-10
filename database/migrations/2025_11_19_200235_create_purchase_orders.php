<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_orders', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);

      $table->timestamps();
      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');

      $table->foreignId('branch_id')->constrained('branches');
      $table->decimal('subtotal_amount', 12, 2);
      $table->decimal('commission_amount', 12, 2);
      $table->decimal('warranty_amount', 12, 2);
      $table->decimal('total_amount', 12, 2);
      $table->date('order_date');
      $table->foreignId('vendor_id')->constrained('vendors');
      $table->date('due_date');
      $table->string('reference', 100)->nullable();
      $table->string('statement_path', 50)->nullable();
      $table->string('note', 255)->nullable();

      $table->datetime('paid_at')->nullable();
      $table->foreignId('paid_by_id')->nullable()->constrained('users');
      // $table->string('receipt_path', 50)->nullable(); CREAR OTRA TABLA PARA GUARDAR VARIAS
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('purchase_orders');
  }
};
