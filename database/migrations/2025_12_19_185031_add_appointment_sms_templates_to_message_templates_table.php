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
        Schema::table('message_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_type_id')->nullable()->after('location_id')->index();
            $table->text('appointment_confirmation_sms')->nullable()->after('recall_message_template');
            $table->text('appointment_rescheduling_sms')->nullable()->after('appointment_confirmation_sms');
            $table->text('appointment_cancel_sms')->nullable()->after('appointment_rescheduling_sms');
            
            $table->foreign('appointment_type_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropForeign(['appointment_type_id']);
            $table->dropColumn(['appointment_type_id', 'appointment_confirmation_sms', 'appointment_rescheduling_sms', 'appointment_cancel_sms']);
        });
    }
};
