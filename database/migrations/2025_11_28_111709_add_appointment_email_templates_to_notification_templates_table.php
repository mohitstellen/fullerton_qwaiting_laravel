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
            $table->unsignedBigInteger('appointment_type_id')->nullable()->after('location_id')->index();
            $table->json('confirmation_email')->nullable()->after('booking_confirmed_admin_notification_status');
            $table->json('rescheduling_email')->nullable()->after('confirmation_email');
            $table->json('cancel_email')->nullable()->after('rescheduling_email');
            
            $table->foreign('appointment_type_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropForeign(['appointment_type_id']);
            $table->dropColumn(['appointment_type_id', 'confirmation_email', 'rescheduling_email', 'cancel_email']);
        });
    }
};
