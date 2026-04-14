<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('vehicle_reservations', function (Blueprint $table) {
      $table->renameColumn('customer_last_name', 'customer_paternal_surname');
    });

    Schema::table('vehicle_reservations', function (Blueprint $table) {
      $table->string('customer_name', 191)->change();
      $table->string('customer_paternal_surname', 25)->change();
      $table->string('customer_maternal_surname', 25)->nullable()->after('customer_paternal_surname');
      $table->string('customer_email', 191)->change();
    });
  }

  public function down(): void
  {
    Schema::table('vehicle_reservations', function (Blueprint $table) {
      $table->string('customer_name', 100)->change();
      $table->string('customer_paternal_surname', 191)->change();
      $table->dropColumn('customer_maternal_surname');
      $table->string('customer_email', 255)->change();
    });

    Schema::table('vehicle_reservations', function (Blueprint $table) {
      $table->renameColumn('customer_paternal_surname', 'customer_last_name');
    });
  }
};