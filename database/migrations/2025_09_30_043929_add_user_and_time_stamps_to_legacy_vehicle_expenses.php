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
        Schema::table('legacy_vehicle_expenses', function (Blueprint $table) {
            $table->timestamps();
            $table->foreignId('created_by_id')->default(1)->constrained('users');
            $table->foreignId('updated_by_id')->default(1)->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legacy_vehicle_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_id');
            $table->dropConstrainedForeignId('updated_by_id');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
};
