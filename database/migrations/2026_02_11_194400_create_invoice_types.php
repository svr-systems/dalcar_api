<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('invoice_types', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');

      $table->string('name', 40);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('invoice_types');
  }
};
