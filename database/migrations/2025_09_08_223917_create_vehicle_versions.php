<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vehicle_versions', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(true);
      $table->string('name', 100);
      $table->foreignId('vehicle_model_id')->constrained('vehicle_models');
      $table->smallInteger('model_year');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vehicle_versions');
  }
};
