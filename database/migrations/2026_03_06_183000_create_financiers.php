<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('financiers', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->timestamps();
      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');
      $table->string('name', 100);
      $table->string('website', 120)->nullable();
      $table->string('note', 255)->nullable();
      $table->string('contact_name', 100)->nullable();
      $table->string('contact_email', 255)->nullable();
      $table->string('contact_phone', 15)->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('financiers');
  }
};
