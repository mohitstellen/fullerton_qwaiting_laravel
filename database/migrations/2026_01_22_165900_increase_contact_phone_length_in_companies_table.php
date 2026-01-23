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
        Schema::table('companies', function (Blueprint $table) {
            // Increase phone field lengths from 30 to 255 characters
            $table->string('contact_person1_phone', 255)->nullable()->change();
            $table->string('contact_person2_phone', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Revert back to original length
            $table->string('contact_person1_phone', 30)->nullable()->change();
            $table->string('contact_person2_phone', 30)->nullable()->change();
        });
    }
};
