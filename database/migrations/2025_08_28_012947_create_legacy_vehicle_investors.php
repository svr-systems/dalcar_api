<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legacy_vehicle_investors', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->foreignId('legacy_vehicle_id')->constrained('legacy_vehicles');
            $table->foreignId('investor_id')->constrained('investors');
            $table->decimal('percentages', 5, 2);
            $table->decimal('amount', 12, 2)->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_vehicle_investors');
    }
};
