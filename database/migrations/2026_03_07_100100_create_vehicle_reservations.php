<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('vehicle_reservations', function (Blueprint $table) {
      $table->id();

      $table->boolean('is_active')->default(1);

      $table->timestamps();

      $table->foreignId('created_by_id')->constrained('users');
      $table->foreignId('updated_by_id')->constrained('users');

      $table->foreignId('vehicle_id')->constrained('vehicles');
      $table->foreignId('seller_user_id')->constrained('users');

      $table->boolean('is_approved')->nullable();
      $table->dateTime('response_at')->nullable();
      $table->foreignId('response_by_id')->nullable()->constrained('users');
      $table->string('response_note', 255)->nullable();
      $table->date('expires_at')->nullable();

      $table->string('customer_name', 100);
      $table->string('customer_last_name', 100);
      $table->string('customer_email', 255);
      $table->string('customer_phone', 15)->nullable();
      $table->string('customer_ine_path', 50);

      $table->boolean('is_finance')->default(0);
      $table->foreignId('financier_id')->nullable()->constrained('financiers');
      $table->boolean('is_preapproved')->nullable();
      $table->string('preapproval_path', 50)->nullable();

      $table->smallInteger('reservation_days')->unsigned();
      $table->decimal('reservation_amount', 12, 2);
      $table->foreignId('payment_method_id')->constrained('payment_methods');

      $table->boolean('has_trade_in')->default(0);
      $table->string('trade_in_brand', 60)->nullable();
      $table->string('trade_in_model', 60)->nullable();
      $table->string('trade_in_version', 60)->nullable();
      $table->smallInteger('trade_in_model_year')->unsigned()->nullable();
      $table->string('trade_in_color', 40)->nullable();
      $table->integer('trade_in_km')->unsigned()->nullable();
      $table->string('trade_in_invoice_type', 40)->nullable();
      $table->boolean('trade_in_is_refactored')->nullable();

      $table->text('notes')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('vehicle_reservations');
  }
};