<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 1. Customer Name → Name
     * 2. Booking Date → BookingDate
     * 3. Add Location if not exists
     * 4. Add Clinic if not exists
     * 5. Add CreatedBy if not exists
     * 6. Service 1 → Appointmenttype
     */
    public function up(): void
    {
        if (!Schema::hasTable('templates_variables')) {
            return;
        }

        $hasTimestamps = Schema::hasColumns('templates_variables', ['created_at', 'updated_at']);

        // 1. Customer Name → Name (update by description or variable_name)
        $update1 = ['description' => 'Name', 'variable_name' => '{{Name}}'];
        if ($hasTimestamps) {
            $update1['updated_at'] = now();
        }
        DB::table('templates_variables')
            ->where(function ($q) {
                $q->where('description', 'Customer Name')
                    ->orWhere('variable_name', 'Customer Name')
                    ->orWhere('variable_name', '{{customer_name}}');
            })
            ->update($update1);

        // 2. Booking Date → BookingDate
        $update2 = ['description' => 'BookingDate', 'variable_name' => '{{BookingDate}}'];
        if ($hasTimestamps) {
            $update2['updated_at'] = now();
        }
        DB::table('templates_variables')
            ->where(function ($q) {
                $q->where('description', 'Booking Date')
                    ->orWhere('variable_name', 'Booking Date')
                    ->orWhere('variable_name', '{{booking_date}}');
            })
            ->update($update2);

        // 3. Service 1 → Appointmenttype
        $update3 = ['description' => 'Appointmenttype', 'variable_name' => '{{Appointmenttype}}'];
        if ($hasTimestamps) {
            $update3['updated_at'] = now();
        }
        DB::table('templates_variables')
            ->where(function ($q) {
                $q->where('description', 'Service 1')
                    ->orWhere('variable_name', 'Service 1')
                    ->orWhere('variable_name', '{{service_1}}');
            })
            ->update($update3);

        // 4. Add Location if not exists
        if (!DB::table('templates_variables')->where('variable_name', '{{Location}}')->exists()) {
            $locationRow = [
                'variable_name' => '{{Location}}',
                'description' => 'Location',
                'example_value' => 'Clinic address',
            ];
            if ($hasTimestamps) {
                $locationRow['created_at'] = $locationRow['updated_at'] = now();
            }
            DB::table('templates_variables')->insert($locationRow);
        }

        // 5. Add Clinic if not exists
        if (!DB::table('templates_variables')->where('variable_name', '{{Clinic}}')->exists()) {
            $clinicRow = [
                'variable_name' => '{{Clinic}}',
                'description' => 'Clinic',
                'example_value' => 'Clinic name',
            ];
            if ($hasTimestamps) {
                $clinicRow['created_at'] = $clinicRow['updated_at'] = now();
            }
            DB::table('templates_variables')->insert($clinicRow);
        }

        // 6. Add CreatedBy if not exists
        if (!DB::table('templates_variables')->where('variable_name', '{{CreatedBy}}')->exists()) {
            $createdByRow = [
                'variable_name' => '{{CreatedBy}}',
                'description' => 'CreatedBy',
                'example_value' => 'Staff name',
            ];
            if ($hasTimestamps) {
                $createdByRow['created_at'] = $createdByRow['updated_at'] = now();
            }
            DB::table('templates_variables')->insert($createdByRow);
        }

        // 7. Add BookingName if not exists
        if (!DB::table('templates_variables')->where('variable_name', '{{BookingName}}')->exists()) {
            $bookingNameRow = [
                'variable_name' => '{{BookingName}}',
                'description' => 'BookingName',
                'example_value' => 'Customer/patient name',
            ];
            if ($hasTimestamps) {
                $bookingNameRow['created_at'] = $bookingNameRow['updated_at'] = now();
            }
            DB::table('templates_variables')->insert($bookingNameRow);
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

        // Revert Name → Customer Name
        DB::table('templates_variables')
            ->where('variable_name', '{{Name}}')
            ->update([
                'description' => 'Customer Name',
                'variable_name' => '{{customer_name}}',
                'updated_at' => now(),
            ]);

        // Revert BookingDate → Booking Date
        DB::table('templates_variables')
            ->where('variable_name', '{{BookingDate}}')
            ->update([
                'description' => 'Booking Date',
                'variable_name' => '{{booking_date}}',
                'updated_at' => now(),
            ]);

        // Revert Appointmenttype → Service 1
        DB::table('templates_variables')
            ->where('variable_name', '{{Appointmenttype}}')
            ->update([
                'description' => 'Service 1',
                'variable_name' => '{{service_1}}',
                'updated_at' => now(),
            ]);

        // Remove Location, Clinic, CreatedBy, BookingName
        DB::table('templates_variables')
            ->whereIn('variable_name', ['{{Location}}', '{{Clinic}}', '{{CreatedBy}}', '{{BookingName}}'])
            ->delete();
    }
};
