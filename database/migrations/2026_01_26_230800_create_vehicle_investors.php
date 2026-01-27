<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vehicle_investors', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->timestamps();
      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');
      $table->foreignId('vehicle_id')->constrained('vehicles');
      $table->foreignId('investor_id')->constrained('investors');
      $table->decimal('percentages', 5, 2);
      $table->decimal('amount', 12, 2);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vehicle_investors');
  }
};
