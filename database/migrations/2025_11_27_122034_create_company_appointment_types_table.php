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
        Schema::create('company_appointment_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('appointment_type_id');
            $table->date('valid_from');
            $table->date('valid_to');
            $table->enum('applicable_for', ['Both', 'Self'])->default('Both');
            $table->timestamps();
            
            // Prevent duplicate entries for the same company, appointment type, and date range
            $table->unique(['company_id', 'appointment_type_id', 'valid_from', 'valid_to'], 'company_appt_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_appointment_types');
    }
};
