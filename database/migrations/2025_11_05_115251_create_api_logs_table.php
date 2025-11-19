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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('team_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('api_name')->nullable(); // e.g., 'Organization List', 'Create Appointment'
            $table->string('api_url')->nullable();
            $table->string('method')->default('POST'); // GET, POST, PUT, DELETE
            $table->text('request_headers')->nullable();
            $table->longText('request_data')->nullable(); // Sent data
            $table->longText('response_data')->nullable(); // Received data
            $table->integer('http_code')->nullable();
            $table->string('status')->default('success'); // success, error
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('booking_id');
            $table->index('team_id');
            $table->index('api_name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
