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
        Schema::create('juspay_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('team_id', 20);
            $table->bigInteger('location_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('status', 50);
            $table->text('payment_url')->nullable();
            $table->json('response_json')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'location_id']);
            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('juspay_orders');
    }
};
