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
        Schema::create('member_imports', function (Blueprint $table) {
            $table->id();
            $table->string('team_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('file_name');
            $table->string('created_by');
            $table->timestamp('created_date_time');
            $table->timestamp('imported_date_time')->nullable();
            $table->integer('status')->default(1); // 1 = in-progress
            $table->boolean('enforce_password_change')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_imports');
    }
};
