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
        Schema::create('legacy_vehicles', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->date('purchase_date');
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models');
            $table->smallInteger('model_year');
            $table->foreignId('vehicle_transmission_id')->constrained('vehicle_transmissions');
            $table->foreignId('vehicle_color_id')->constrained('vehicle_colors');
            $table->char('vin',17)->unique();
            $table->decimal('purchase_price',12,2);
            $table->decimal('commission_amount',12,2);
            $table->foreignId('vat_type_id')->constrained('vat_types');
            $table->decimal('invoice_amount',12,2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_vehicles');
    }
};
