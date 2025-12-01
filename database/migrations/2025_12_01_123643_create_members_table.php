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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            
            // Tenant / team reference
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            
            // Identification
            $table->enum('identification_type', ['NRIC', 'FIN', 'Passport'])->nullable();
            $table->string('nric_fin')->nullable()->index();
            
            // Personal Information
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            
            // Contact Information
            $table->string('mobile_country_code', 10)->default('65');
            $table->string('mobile_number')->index();
            $table->string('email')->index();
            
            // Status & Company
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->string('nationality')->nullable();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            
            // Login credentials
            $table->string('password')->nullable();
            $table->rememberToken();
            
            // Approval tracking
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_active')->default(0); // 0 = pending, 1 = active/approved
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for search optimization
            $table->index(['team_id', 'status']);
            $table->index(['team_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
