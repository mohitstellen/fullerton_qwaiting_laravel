<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Location;
use App\Models\MessageTemplate;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class ExcelImportService
{
    /**
     * Import categories from Excel (Level 1)
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    public function importCategories($file)
    {
        Log::info('Starting category import', ['file_name' => $file->getClientOriginalName()]);

        // Read the file into a collection
        $data = Excel::toCollection(new class implements \Maatwebsite\Excel\Concerns\ToCollection, \Maatwebsite\Excel\Concerns\WithHeadingRow {
            public function collection(Collection $rows)
            {
                return $rows;
            }
        }, $file);

        $rows = $data->first(); // Get the first sheet
        Log::info('Excel data loaded', ['total_rows' => $rows->count()]);

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            Log::info('Processing row', ['row_index' => $index, 'row_data' => $row->toArray()]);

            // Ensure we have a name
            if (empty($row['appointmenttype'])) {
                Log::warning('Skipping row - missing AppointmentType', ['row_index' => $index]);
                $skippedCount++;
                continue;
            }

            // Map Excel columns to DB columns variables
            // flexible mapping: look for keys
            $name = $row['appointmenttype'];
            $status = $row['status'] == 'Active' ? 1 : 0;
            $availableBooking = $row['availablebooking'] == 'Yes' ? 1 : 0;
            $confirmEmailTemplate = $row['confirmemailtemplate'] ?? null;
            $rescheduleEmailTemplate = $row['rescheduleemailtemplate'] ?? null;
            $cancelEmailTemplate = $row['cancelemailtemplate'] ?? null;
            $confirmEmailSubject = $row['confirmemailsubject'] ?? null;
            $rescheduleEmailSubject = $row['rescheduleemailsubject'] ?? null;
            $cancelEmailSubject = $row['cancelemailsubject'] ?? null;
            $confirmSmsTemplate = $row['confirmsmstemplate'] ?? null;
            $rescheduleSmsTemplate = $row['reschedulesmstemplate'] ?? null;
            $cancelSmsTemplate = $row['cancelsmstemplate'] ?? null;
            $amount = $row['amount'] ?? 0;
            $leadTime = $row['leadtimevalue'] ?? 0;

            $img = $row['img'] ?? null;

            Log::info('Mapped data for category', [
                'name' => $name,
                'status' => $status,
                'amount' => $amount,
                'team_id' => tenant('id')
            ]);

            // Check if exists and update, or create
            $category = Category::updateOrCreate(
                [
                    'name' => $name,
                    'team_id' => tenant('id'),
                    'level_id' => 1 // Forced to level 1 as requested
                ],
                [
                    'status' => $status,
                    'display_on' => $availableBooking == 1 ? 'Display on Transfer & Ticket Screen' : '',
                    'cancel_sms_template' => $cancelSmsTemplate,
                    'amount' => $amount,
                    'lead_time_value' => $leadTime,
                    'img' => $img,
                    'category_locations' => [] // Prevent CategoryObserver foreach error
                ]
            );

            Log::info('Category saved', ['category_id' => $category->id, 'category_name' => $category->name]);

            if ($category) {
                try {
                    // Prepare email templates with null safety
                    $confirmEmail = ($confirmEmailSubject || $confirmEmailTemplate) ? [
                        'subject' => $confirmEmailSubject ?? '',
                        'template' => $confirmEmailTemplate ?? ''
                    ] : null;

                    $rescheduleEmail = ($rescheduleEmailSubject || $rescheduleEmailTemplate) ? [
                        'subject' => $rescheduleEmailSubject ?? '',
                        'template' => $rescheduleEmailTemplate ?? ''
                    ] : null;

                    $cancelEmail = ($cancelEmailSubject || $cancelEmailTemplate) ? [
                        'subject' => $cancelEmailSubject ?? '',
                        'template' => $cancelEmailTemplate ?? ''
                    ] : null;

                    NotificationTemplate::updateOrCreate([
                        'team_id' => tenant('id'),
                        'location_id' => 101,
                        'appointment_type_id' => $category->id,
                    ], array_filter([
                        'appointment_confirmation_email' => $confirmEmail,
                        'appointment_rescheduling_email' => $rescheduleEmail,
                        'appointment_cancel_email' => $cancelEmail,
                    ]));

                    MessageTemplate::updateOrCreate([
                        'team_id' => tenant('id'),
                        'location_id' => 101,
                        'appointment_type_id' => $category->id,
                    ], [
                        'appointment_confirmation_sms' => $confirmSmsTemplate,
                        'appointment_rescheduling_sms' => $rescheduleSmsTemplate,
                        'appointment_cancel_sms' => $cancelSmsTemplate
                    ]);

                    Log::info('Templates saved for category', ['category_id' => $category->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to save templates for category', [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue processing other categories
                }
            }

            $processedCount++;
        }

        Log::info('Category import completed', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total' => $rows->count()
        ]);
    }

    /**
     * Import locations from Excel
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    public function importLocations($file)
    {
        Log::info('Starting location import', ['file_name' => $file->getClientOriginalName()]);

        $data = Excel::toCollection(new class implements \Maatwebsite\Excel\Concerns\ToCollection, \Maatwebsite\Excel\Concerns\WithHeadingRow {
            public function collection(Collection $rows)
            {
                return $rows;
            }
        }, $file);

        $rows = $data->first();
        Log::info('Excel data loaded', ['total_rows' => $rows->count()]);

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            Log::info('Processing row', ['row_index' => $index, 'row_data' => $row->toArray()]);

            // Ensure we have a location name
            if (empty($row['clinicname'])) {
                Log::warning('Skipping row - missing Clinicname', ['row_index' => $index]);
                $skippedCount++;
                continue;
            }

            $locationName = $row['clinicname'];
            $status = $row['status'] == 'Active' ? 1 : 0;

            Log::info('Mapped data for location', [
                'location_name' => $locationName,
                'status' => $status,
                'team_id' => tenant('id')
            ]);

            $location = Location::updateOrCreate(
                [
                    'location_name' => $locationName,
                    'team_id' => tenant('id'),
                ],
                [
                    'address' => $row['address'] ?? null,
                    'phone_number' => $row['phonenumber'] ?? null,
                    'sms_number' => $row['smsno'] ?? null,
                    'status' => $status,
                    'map' => $row['map'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                    'available_for_public_booking' => $row['availableforpublicbooking'] == 'Yes' ? 1 : 0
                ]
            );

            Log::info('Location saved', ['location_id' => $location->id, 'location_name' => $location->location_name]);
            $processedCount++;
        }

        Log::info('Location import completed', [
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'total' => $rows->count()
        ]);
    }
}
