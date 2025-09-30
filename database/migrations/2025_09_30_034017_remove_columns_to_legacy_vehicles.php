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
        Schema::table('legacy_vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropConstrainedForeignId('vat_type_id');
            $table->dropColumn('purchase_price');
            $table->dropColumn('commission_amount');
            $table->dropColumn('invoice_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legacy_vehicles', function (Blueprint $table) {
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->foreignId('vat_type_id')->constrained('vat_types');
            $table->decimal('invoice_amount', 12, 2);
        });
    }
};
