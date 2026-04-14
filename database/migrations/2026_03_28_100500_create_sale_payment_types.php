<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('sale_payment_types', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->string('name', 25);
    });

    DB::table('sale_payment_types')->insert([
      ['id' => 1, 'is_active' => 1, 'name' => 'RESERVACION'],
      ['id' => 2, 'is_active' => 1, 'name' => 'ENGANCHE'],
      ['id' => 3, 'is_active' => 1, 'name' => 'ABONO'],
      ['id' => 4, 'is_active' => 1, 'name' => 'LIQUIDACION'],
    ]);
  }

  public function down(): void
  {
    Schema::dropIfExists('sale_payment_types');
  }
};