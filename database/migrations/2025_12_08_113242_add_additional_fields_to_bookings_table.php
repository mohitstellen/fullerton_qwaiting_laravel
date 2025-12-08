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
        Schema::table('bookings', function (Blueprint $table) {
            // Add missing columns for appointment booking form
            $table->string('title', 10)->nullable()->after('name'); // Mr, Mrs, Ms, Dr
            $table->string('identification_type', 50)->nullable()->after('refID'); // NRIC / FIN, Passport
            $table->date('date_of_birth')->nullable()->after('name');
            $table->string('gender', 20)->nullable()->after('date_of_birth');
            $table->string('nationality', 100)->nullable()->after('gender');
            $table->text('additional_comments')->nullable()->after('meeting_link');
            $table->string('payment_status', 50)->nullable()->after('status'); // Pending Payment, Paid, etc.
            $table->boolean('is_vip')->default(false)->after('status');
            $table->boolean('is_private_customer')->default(false)->after('is_vip');
            $table->unsignedBigInteger('company_id')->nullable()->after('category_id'); // Link to company
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'identification_type',
                'date_of_birth',
                'gender',
                'nationality',
                'additional_comments',
                'payment_status',
                'is_vip',
                'is_private_customer',
                'company_id'
            ]);
        });
    }
};
