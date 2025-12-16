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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('team_id', 20);
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('order_number')->unique();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('gst_amount', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['team_id', 'member_id']);
            $table->index('order_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
