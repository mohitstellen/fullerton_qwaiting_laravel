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
        // Remove order_id from bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });

        // Create booking_order pivot table
        Schema::create('booking_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('order_id');
            $table->timestamps();

            $table->index('booking_id');
            $table->index('order_id');
            $table->unique(['booking_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop pivot table
        Schema::dropIfExists('booking_order');

        // Restore order_id in bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('team_id');
            $table->index('order_id');
        });
    }
};

