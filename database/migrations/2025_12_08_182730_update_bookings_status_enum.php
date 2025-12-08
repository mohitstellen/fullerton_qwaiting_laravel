<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include all booking statuses
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `status` ENUM(
            'Pending',
            'Confirmed',
            'In Progress',
            'Cancelled',
            'Completed',
            'Reserved',
            'SMSCalled',
            'Arrived',
            'NoShow'
        ) DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `status` ENUM(
            'Pending',
            'Confirmed',
            'In Progress',
            'Cancelled',
            'Completed'
        ) DEFAULT 'Pending'");
    }
};
