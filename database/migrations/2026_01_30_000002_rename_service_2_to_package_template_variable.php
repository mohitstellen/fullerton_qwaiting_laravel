<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename Service 2 to Package.
     */
    public function up(): void
    {
        if (!Schema::hasTable('templates_variables')) {
            return;
        }

        $hasTimestamps = Schema::hasColumns('templates_variables', ['created_at', 'updated_at']);
        $update = ['description' => 'Package', 'variable_name' => '{{Package}}'];
        if ($hasTimestamps) {
            $update['updated_at'] = now();
        }

        DB::table('templates_variables')
            ->where(function ($q) {
                $q->where('description', 'Service 2')
                    ->orWhere('variable_name', 'Service 2')
                    ->orWhere('variable_name', '{{service_2}}');
            })
            ->update($update);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('templates_variables')) {
            return;
        }

        $hasTimestamps = Schema::hasColumns('templates_variables', ['created_at', 'updated_at']);
        $update = ['description' => 'Service 2', 'variable_name' => '{{service_2}}'];
        if ($hasTimestamps) {
            $update['updated_at'] = now();
        }

        DB::table('templates_variables')
            ->where('variable_name', '{{Package}}')
            ->update($update);
    }
};
