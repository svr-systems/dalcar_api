<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vendor_document_types', function (Blueprint $table) {
      $table->id();
      $table->boolean('is_active')->default(1);
      $table->foreignId('vendor_id')->constrained('vendors');
      $table->foreignId('document_type_id')->constrained('document_types');
      $table->unsignedTinyInteger('delivery_days');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vendor_document_types');
  }
};
