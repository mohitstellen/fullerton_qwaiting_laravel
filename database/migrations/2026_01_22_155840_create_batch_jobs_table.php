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
        Schema::create('batch_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');
            $table->integer('total_added')->default(0);
            $table->integer('total_updated')->default(0);
            $table->integer('total_records')->default(0);
            $table->decimal('run_time', 10, 2)->nullable()->comment('Runtime in seconds');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_jobs');
    }
};
