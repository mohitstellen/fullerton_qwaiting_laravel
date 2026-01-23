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
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('terms_and_conditions')->default(0)->after('status');
            $table->boolean('consent_data_collection')->default(0)->after('terms_and_conditions');
            $table->boolean('consent_marketing')->default(0)->after('consent_data_collection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['terms_and_conditions', 'consent_data_collection', 'consent_marketing']);
        });
    }
};
