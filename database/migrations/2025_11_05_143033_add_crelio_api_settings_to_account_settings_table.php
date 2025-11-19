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
        Schema::table('account_settings', function (Blueprint $table) {
            $table->string('crelio_auth_key')->nullable()->after('checkin_qrcode');
            $table->string('crelio_lab_user_id')->nullable()->after('crelio_auth_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_settings', function (Blueprint $table) {
            $table->dropColumn(['crelio_auth_key', 'crelio_lab_user_id']);
        });
    }
};
