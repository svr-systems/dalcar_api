<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::table('legacy_vehicles', function (Blueprint $table) {
      $table->dropColumn('model_year');
      $table->foreignId('vehicle_version_id')->after('vehicle_model_id')->constrained('vehicle_versions');
      $table->string('engine_number', 30)->after('vin')->nullable();
      $table->string('repuve', 25)->after('engine_number')->nullable();
      $table->string('vehicle_key', 20)->after('repuve')->nullable();
      $table->string('notes')->after('invoice_amount')->nullable();
      $table->foreignId('origin_type_id')->after('notes')->constrained('origin_types');
      $table->string('pediment_number', 30)->after('origin_type_id')->nullable();
      $table->date('pediment_date')->after('pediment_number')->nullable();
      $table->foreignId('custom_office_id')->after('pediment_date')->nullable()->constrained('custom_offices');
      $table->string('pediment_notes')->after('custom_office_id')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::table('legacy_vehicles', function (Blueprint $table) {
      $table->smallInteger('model_year');
      $table->dropConstrainedForeignId('vehicle_version_id');
      $table->dropColumn('engine_number');
      $table->dropColumn('repuve');
      $table->dropColumn('vehicle_key');
      $table->dropColumn('notes');
      $table->dropConstrainedForeignId('origin_type_id');
      $table->dropColumn('pediment_number');
      $table->dropColumn('pediment_date');
      $table->dropConstrainedForeignId('custom_office_id');
      $table->dropColumn('pediment_notes');
    });
  }
};
