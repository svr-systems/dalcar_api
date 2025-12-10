<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('vendors', function (Blueprint $table) {
      $table->boolean('uses_payment_link')->default(0);
      $table->boolean('requires_reference')->default(1);
      $table->boolean('requires_statement')->default(1);
    });
  }

  public function down(): void
  {
    Schema::table('vendors', function (Blueprint $table) {
      $table->dropColumn('uses_payment_link');
      $table->dropColumn('requires_reference');
      $table->dropColumn('requires_statement');
    });
  }
};
