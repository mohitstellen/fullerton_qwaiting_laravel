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
        Schema::table('orders', function (Blueprint $table) {
            // Remove indexes first
            $table->dropIndex(['booking_id']);
            $table->dropIndex(['appointment_type_id']);
            $table->dropIndex(['package_id']);
            $table->dropIndex(['location_id']);
            $table->dropIndex(['booking_date']);
            
            // Remove appointment-related columns
            $table->dropColumn([
                'booking_id',
                'refID',
                'service_name',
                'appointment_type',
                'appointment_type_id',
                'package',
                'package_id',
                'location',
                'location_id',
                'booking_date',
                'booking_time',
                'start_time',
                'end_time',
                'booking_date_time',
                'name',
                'email',
                'phone',
                'phone_code',
                'date_of_birth',
                'gender',
                'nationality',
                'identification_type',
                'additional_comments',
                'booking_for',
                'nric_fin',
                'passport',
                'appointment_status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Restore appointment columns
            $table->unsignedBigInteger('booking_id')->nullable()->after('member_id');
            $table->string('refID')->nullable()->after('booking_id');
            $table->string('service_name')->nullable()->after('refID');
            $table->string('appointment_type')->nullable()->after('service_name');
            $table->unsignedBigInteger('appointment_type_id')->nullable()->after('appointment_type');
            $table->string('package')->nullable()->after('appointment_type_id');
            $table->unsignedBigInteger('package_id')->nullable()->after('package');
            $table->string('location')->nullable()->after('package_id');
            $table->unsignedBigInteger('location_id')->nullable()->after('location');
            $table->date('booking_date')->nullable()->after('location_id');
            $table->string('booking_time')->nullable()->after('booking_date');
            $table->time('start_time')->nullable()->after('booking_time');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('booking_date_time')->nullable()->after('end_time');
            $table->string('name')->nullable()->after('booking_date_time');
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('phone_code')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('phone_code');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('nationality')->nullable()->after('gender');
            $table->string('identification_type')->nullable()->after('nationality');
            $table->text('additional_comments')->nullable()->after('identification_type');
            $table->string('booking_for')->nullable()->after('additional_comments');
            $table->string('nric_fin')->nullable()->after('booking_for');
            $table->string('passport')->nullable()->after('nric_fin');
            $table->string('appointment_status')->nullable()->after('passport');
            
            // Restore indexes
            $table->index('booking_id');
            $table->index('appointment_type_id');
            $table->index('package_id');
            $table->index('location_id');
            $table->index('booking_date');
        });
    }
};

