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
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->tinyInteger('juspay_enable')->default(0)->after('stripe_enable');
            $table->string('juspay_merchant_id', 255)->nullable()->after('juspay_enable');
            $table->string('juspay_api_key', 255)->nullable()->after('juspay_merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->dropColumn(['juspay_enable', 'juspay_merchant_id', 'juspay_api_key']);
        });
    }
};
