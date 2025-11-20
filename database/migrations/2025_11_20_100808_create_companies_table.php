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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Tenant / team reference (keep consistent with other tables like customers)
            $table->unsignedBigInteger('team_id')->index();

            // Core company information
            $table->string('company_name');
            $table->text('address')->nullable();
            $table->text('billing_address')->nullable();
            $table->boolean('is_billing_same_as_company')->default(false);
            $table->text('remarks')->nullable();

            // Account manager (user responsible for the company)
            $table->unsignedBigInteger('account_manager_id')->nullable()->index();

            // Status & configuration
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('ehs_appointments_per_year')->default(1);

            // Primary contact person
            $table->string('contact_person1_name')->nullable();
            $table->string('contact_person1_phone', 30)->nullable();
            $table->string('contact_person1_email')->nullable();

            // Secondary contact person
            $table->string('contact_person2_name')->nullable();
            $table->string('contact_person2_phone', 30)->nullable();
            $table->string('contact_person2_email')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
