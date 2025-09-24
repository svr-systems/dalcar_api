<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('legacy_vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->foreignId('legacy_vehicle_id')->constrained('legacy_vehicles');
            $table->foreignId('document_type_id')->constrained('document_types');
            $table->boolean('is_scheduled')->default(0);
            $table->date('scheduled_at')->nullable();
            $table->date('received_at')->nullable();
            $table->string('document_path',50);
            $table->string('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_vehicle_documents');
    }
};
