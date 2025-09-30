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
        Schema::table('legacy_vehicle_documents', function (Blueprint $table) {
            $table->dropColumn('is_scheduled');
            $table->dropColumn('scheduled_at');
            $table->dropColumn('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legacy_vehicle_documents', function (Blueprint $table) {
            $table->boolean('is_scheduled')->default(0);
            $table->date('scheduled_at')->nullable();
            $table->date('received_at')->nullable();
        });
    }
};
