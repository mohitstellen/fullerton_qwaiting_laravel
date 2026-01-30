<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add BookingName template variable.
     */
    public function up(): void
    {
        if (!Schema::hasTable('templates_variables')) {
            return;
        }

        if (!DB::table('templates_variables')->where('variable_name', '{{BookingName}}')->exists()) {
            $hasTimestamps = Schema::hasColumns('templates_variables', ['created_at', 'updated_at']);
            $row = [
                'variable_name' => '{{BookingName}}',
                'description' => 'BookingName',
                'example_value' => 'Customer/patient name',
            ];
            if ($hasTimestamps) {
                $row['created_at'] = $row['updated_at'] = now();
            }
            DB::table('templates_variables')->insert($row);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('templates_variables')) {
            return;
        }

        DB::table('templates_variables')
            ->where('variable_name', '{{BookingName}}')
            ->delete();
    }
};
