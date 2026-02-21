<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vehicle_expenses', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');

      $table->foreignId('vehicle_id')->constrained('vehicles');
      $table->foreignId('expense_type_id')->constrained('expense_types');

      $table->string('note', 255)->nullable();
      $table->date('expense_date');
      $table->decimal('amount', 12, 2);
      $table->string('document_path', 50)->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vehicle_expenses');
  }
};
