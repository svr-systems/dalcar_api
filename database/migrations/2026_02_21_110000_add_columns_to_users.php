<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->boolean('receives_po_emails')->default(0);
      $table->boolean('receives_vehicle_emails')->default(0);
      $table->boolean('receives_invoice_calendar_emails')->default(0);
      $table->boolean('receives_document_calendar_emails')->default(0);
    });
  }

  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn([
        'receives_po_emails',
        'receives_vehicle_emails',
        'receives_invoice_calendar_emails',
        'receives_document_calendar_emails',
      ]);
    });
  }
};
