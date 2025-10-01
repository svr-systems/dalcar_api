<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legacy_vehicle_trades', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('updated_by_id')->constrained('users');
            $table->foreignId('legacy_vehicle_id')->constrained('legacy_vehicles');
            $table->boolean('is_purchase')->default(1);
            $table->foreignId('vendor_id')->nullable()->default(null)->constrained('vendors');
            $table->decimal('purchase_price', 12, 2)->nullable()->default(null);
            $table->decimal('commission_amount', 12, 2)->nullable()->default(null);
            $table->foreignId('vat_type_id')->constrained('vat_types');
            $table->decimal('invoice_amount', 12, 2)->nullable()->default(null);
            $table->decimal('sale_price', 12, 2)->nullable()->default(null);
            $table->string('note')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_vehicle_trades');
    }
};
