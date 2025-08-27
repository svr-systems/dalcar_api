<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->foreignId('company_id')->constrained('companies');
            $table->string('name', 100);
            $table->string('street', 100)->nullable();
            $table->string('exterior_number', 20)->nullable();
            $table->string('interior_number', 20)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->char('zip', 5);
            $table->foreignId('municipality_id')->constrained('municipalities');
            $table->string('email');
            $table->string('phone', 15)->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
