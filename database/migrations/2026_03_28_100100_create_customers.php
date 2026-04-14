<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('customers', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->foreignId('created_by_id')->nullable()->constrained('users');
      $table->foreignId('updated_by_id')->nullable()->constrained('users');
      $table->foreignId('user_id')->nullable()->constrained('users');

      $table->string('name', 191);
      $table->string('paternal_surname', 25)->nullable();
      $table->string('maternal_surname', 25)->nullable();
      $table->string('email', 191)->nullable();
      $table->string('phone', 15)->nullable();
      $table->string('ine_path', 50)->nullable();
      $table->string('rfc', 13)->nullable();
      $table->text('notes')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('customers');
  }
};