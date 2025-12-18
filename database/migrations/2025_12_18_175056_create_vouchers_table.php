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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            
            // Tenant / team reference
            $table->unsignedBigInteger('team_id')->index();
            
            // Voucher fields
            $table->string('voucher_name');
            $table->string('voucher_code')->unique();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->decimal('discount_percentage', 5, 2);
            $table->unsignedInteger('no_of_redemption')->nullable();
            $table->json('card_types')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
