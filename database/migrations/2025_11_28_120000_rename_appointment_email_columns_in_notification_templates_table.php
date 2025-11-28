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
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->renameColumn('confirmation_email', 'appointment_confirmation_email');
            $table->renameColumn('rescheduling_email', 'appointment_rescheduling_email');
            $table->renameColumn('cancel_email', 'appointment_cancel_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->renameColumn('appointment_confirmation_email', 'confirmation_email');
            $table->renameColumn('appointment_rescheduling_email', 'rescheduling_email');
            $table->renameColumn('appointment_cancel_email', 'cancel_email');
        });
    }
};

