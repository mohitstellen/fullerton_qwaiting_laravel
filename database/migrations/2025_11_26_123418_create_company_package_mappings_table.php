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
        Schema::create('company_packages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('appointment_type_id')->index();
            $table->unsignedBigInteger('package_id')->index();

            $table->json('modes_of_identification')->nullable();
            $table->json('clinic_ids')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'appointment_type_id', 'package_id'], 'company_packages_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_packages');
    }
};
