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
    Schema::create('vehicles', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);

      $table->timestamps();
      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');

      $table->foreignId('branch_id')->constrained('branches');

      $table->foreignId('vehicle_model_id')->constrained('vehicle_models');
      $table->foreignId('vehicle_version_id')->constrained('vehicle_versions');
      $table->foreignId('vehicle_transmission_id')->constrained('vehicle_transmissions');
      $table->foreignId('vehicle_color_id')->constrained('vehicle_colors');

      $table->char('vin', 17)->unique()->nullable();
      $table->string('engine_number', 30)->nullable();
      $table->string('repuve', 25)->nullable();
      $table->string('vehicle_key', 20)->nullable();
      $table->tinyInteger('passenger_capacity')->nullable();
      $table->text('notes')->nullable();

      $table->foreignId('origin_type_id')->nullable()->constrained('origin_types');
      $table->string('pediment_number', 30)->nullable();
      $table->date('pediment_date')->nullable();
      $table->foreignId('custom_office_id')->nullable()->constrained('custom_offices');
      $table->text('pediment_notes')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('vehicles');
  }
};
