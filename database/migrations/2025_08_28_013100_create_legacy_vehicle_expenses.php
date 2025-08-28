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
        Schema::create('legacy_vehicle_expenses', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->foreignId('legacy_vehicle_id')->constrained('legacy_vehicles');
            $table->foreignId('expense_type_id')->constrained('expense_types');
            $table->string('note');
            $table->date('expense_date');
            $table->decimal('amount',12,2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_vehicle_expenses');
    }
};
