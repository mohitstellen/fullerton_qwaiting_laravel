<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change variable_name from double braces {{Name}} to single braces {Name} for appointment type templates.
     */
    public function up(): void
    {
        if (!Schema::hasTable('templates_variables')) {
            return;
        }

        $hasTimestamps = Schema::hasColumns('templates_variables', ['created_at', 'updated_at']);

        // Map of current double-brace to new single-brace format
        $mappings = [
            '{{Name}}' => '{Name}',
            '{{BookingDate}}' => '{BookingDate}',
            '{{Appointmenttype}}' => '{Appointmenttype}',
            '{{Location}}' => '{Location}',
            '{{Clinic}}' => '{Clinic}',
            '{{CreatedBy}}' => '{CreatedBy}',
            '{{BookingName}}' => '{BookingName}',
            '{{Package}}' => '{Package}',
        ];

        foreach ($mappings as $oldName => $newName) {
            $update = ['variable_name' => $newName];
            if ($hasTimestamps) {
                $update['updated_at'] = now();
            }
            
            DB::table('templates_variables')
                ->where('variable_name', $oldName)
                ->update($update);
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

        $hasTimestamps = Schema::hasColumns('templates_variables', ['created_at', 'updated_at']);

        // Revert single-brace to double-brace format
        $mappings = [
            '{Name}' => '{{Name}}',
            '{BookingDate}' => '{{BookingDate}}',
            '{Appointmenttype}' => '{{Appointmenttype}}',
            '{Location}' => '{{Location}}',
            '{Clinic}' => '{{Clinic}}',
            '{CreatedBy}' => '{{CreatedBy}}',
            '{BookingName}' => '{{BookingName}}',
            '{Package}' => '{{Package}}',
        ];

        foreach ($mappings as $oldName => $newName) {
            $update = ['variable_name' => $newName];
            if ($hasTimestamps) {
                $update['updated_at'] = now();
            }
            
            DB::table('templates_variables')
                ->where('variable_name', $oldName)
                ->update($update);
        }
    }
};
