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
        Schema::table('allowed_countries', function (Blueprint $table) {
            $table->string('country_code')->nullable()->after('iso_code');
            $table->integer('mobile_length')->nullable()->after('country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowed_countries', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'mobile_length']);
        });
    }
};
