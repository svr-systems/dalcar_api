<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_order_receipts', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->foreignId('purchase_order_id')->constrained('purchase_orders');
      $table->string('file_path', 50);
      $table->string('note', 255)->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('purchase_order_receipts');
  }
};
