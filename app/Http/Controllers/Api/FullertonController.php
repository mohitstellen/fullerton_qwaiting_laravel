<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{Company,CompanyAppointmentType,CompanyPackage,Category,Location,CustomSlot,Booking,Team,Level,ActivityLog,AccountSetting};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FullertonController extends Controller
{
    public function getCompanyList(Request $request)
    {
        try {
        $validated = $request->validate([
                'location_id' => 'required|integer|exists:locations,id'
            ]);

            $companies = Company::where('location_id', $validated['location_id'])
                ->where('status', 'active')
                ->get([
                    'id as CompanyID',
                    'company_name as CompanyName'
                ]);

            return response()->json($companies);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while fetching company list'
            ], 500);
        }
    }
    
    public function getAppointmentList(Request $request)
    {
        try {
            $validated = $request->validate([
                // 'CompanyID' => 'nullable|integer',
                'locationId' => 'required|integer|exists:locations,id'
            ]);

    $appointments = CompanyPackage::with('appointmentType')
    ->select('company_id', 'appointment_type_id')
    ->when(isset($validated['CompanyID']), function ($query) use ($validated) {
        $query->where('company_id', $validated['CompanyID']);
    })
    ->whereJsonContains('clinic_ids', (int)$validated['locationId'])
    ->whereHas('appointmentType', function ($query) {
        $query->whereNull('deleted_at');
    })
    ->groupBy('company_id', 'appointment_type_id')
    ->get()
    ->map(function ($appointment) {
        return [
            'CompanyID' => $appointment->company_id,
            'AppointmentID' => $appointment->appointment_type_id,
            'AppointmentName' => $appointment->appointmentType->name ?? null,
        ];
    })
    ->filter(fn ($appointment) => !is_null($appointment['AppointmentName']))
    ->values();

            return response()->json($appointments);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while fetching appointment list'
            ], 500);
        }
    }
    
    public function getPackageList(Request $request)
    {
        try {
            $validated = $request->validate([
                'AppointmentType' => 'required|string',
                // 'MemberType' => 'required|string|in:Corporate,Individual',
                'UserType' => 'required|string|in:Both,Self,Dependent',
                'CompanyID' => 'nullable|integer',
                 'locationId' => 'required|integer|exists:locations,id'
            ]);

            // Get appointment type by name
            $appointmentType = Category::where('name', $validated['AppointmentType'])
                 ->whereJsonContains('category_locations', (string)$validated['locationId'])
                ->select('id', 'name')
                ->first();

            if (!$appointmentType) {
                    return response()->json([
                        'error' => 'Appointment type not found',
                        'message' => 'The specified appointment type does not exist for this location',
                        'debug' => [
                            'AppointmentType' => $validated['AppointmentType'],
                            'locationId' => $validated['locationId']
                        ]
                    ], 404);
            }

            try {
                 $companyPackages = CompanyPackage::query()
            ->when(isset($validated['CompanyID']) && $validated['CompanyID'] !== null, function ($query) use ($validated) {
                $query->where('company_packages.company_id', $validated['CompanyID']);
            })
            ->where('company_packages.appointment_type_id', $appointmentType->id)
            ->whereJsonContains('company_packages.clinic_ids', (int) $validated['locationId'])
            ->whereHas('package', function ($q) use ($validated) {
                $q->where(function ($sub) use ($validated) {
                    $sub->where('package_for', 'Both')
                        ->orWhere('package_for', $validated['UserType']);
                });
            })
            ->with(['package:id,name,description,amount'])
            // ->groupBy('company_packages.package_id')
            ->get();

        $response = $companyPackages->map(function ($companyPackage) {
            return [
                'ValueField' => $companyPackage->package_id,
                'TextField'  => $companyPackage->package->name ?? null,
                'DesField'   => $companyPackage->package->description ?? null,
                'Amount'     => $companyPackage->package->amount ?? null,
            ];
        });

        return response()->json(
            $response
        );

            } catch (\Exception $queryException) {
                return response()->json([
                    'error' => 'Query error',
                    'message' => $queryException->getMessage(),
                    'debug' => [
                        'appointment_type_id' => $appointmentType->id,
                        'locationId' => $validated['locationId'],
                        'CompanyID' => $validated['CompanyID'] ?? 'null',
                        'UserType' => $validated['UserType']
                    ]
                ], 500);
            }

          

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while fetching package list'
            ], 500);
        }
    }
    
    public function getClinicList(Request $request)
    {
        try {
            $validated = $request->validate([
                'PackageID' => 'required|integer|exists:categories,id',
                'CompanyID' => 'nullable|integer'
            ]);

            // Get company packages for the specific package
            $query = CompanyPackage::where('package_id', (int)$validated['PackageID']);
            
            // Add company filter only if CompanyID is provided
            if (isset($validated['CompanyID'])) {
                $query->where('company_id', (int)$validated['CompanyID']);
            }
            
            $companyPackages = $query->get();

            if ($companyPackages->isEmpty()) {
                return response()->json([
                    'error' => 'No clinics found',
                    'message' => 'No clinics found for the specified package'
                ], 404);
            }

            // Extract all clinic_ids from the packages
            $clinicIds = [];
            foreach ($companyPackages as $package) {
                if (is_array($package->clinic_ids)) {
                    $clinicIds = array_merge($clinicIds, $package->clinic_ids);
                }
            }

            // Get unique clinic IDs
            $uniqueClinicIds = array_unique($clinicIds);

            // Get location details for these clinic IDs
            $clinics = Location::whereIn('id', $uniqueClinicIds)
                ->where('status', 1)
                ->get()
                ->map(function ($location) {
                    return [
                        'OrgAddressID' => $location->id,
                        'Address' => $location->address ?? '',
                        'PostalCode' => $location->zip ?? '54321',
                        'WorkingHours' => $location->remarks ?? 'Mon-Fri : 07:30am to 1:00pm ,Sat: 7.30am to 1.00pm',
                        'Map' => $location->map_link ?? '',
                        'ClinicName' => $location->location_name ?? ''
                    ];
                });

            return response()->json($clinics);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while fetching clinic list'
            ], 500);
        }
    }
    
    public function getAvailableDates(Request $request)
    {
        try {
            $validated = $request->validate([
                'PackageID' => 'required|integer|exists:categories,id',
                'LocationID' => 'required|integer|exists:locations,id',
                'StartDate' => 'required|date',
                'EndDate' => 'required|date|after_or_equal:StartDate',
                'CompanyID' => 'nullable|integer'
            ]);

            // First check if company has that package and location
            $query = CompanyPackage::where('package_id', (int)$validated['PackageID'])
                ->whereJsonContains('clinic_ids', (int)$validated['LocationID']);
            
            // Add company filter only if CompanyID is provided
            if (isset($validated['CompanyID'])) {
                $query->where('company_id', (int)$validated['CompanyID']);
            }
            
            $companyPackage = $query->first();

            if (!$companyPackage) {
                return response()->json([
                    'error' => 'Package not available',
                    'message' => 'The specified package is not available for this location'
                ], 404);
            }

            // Generate date range
            $startDate = \Carbon\Carbon::parse($validated['StartDate']);
            $endDate = \Carbon\Carbon::parse($validated['EndDate']);
            $dates = [];

            // Get all custom slots for the date range in one query
            $customSlots = CustomSlot::where('category_id', (int)$validated['PackageID'])
                ->where('location_id', (int)$validated['LocationID'])
                ->where('slots_type', CustomSlot::CATEGORY_SLOT)
                ->whereBetween('selected_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get()
                ->keyBy('selected_date'); // Key by date for easy lookup

            // Check each date in the range
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $currentDate = $date->format('Y-m-d');
                $dateStatus = 'Unavailable'; // Default status
                
                // Get the custom slot for this date (if exists)
                $customSlot = $customSlots->get($currentDate);
                
                if ($customSlot) {
                    // Parse business_hours JSON to check if service is open or closed
                    $businessHours = json_decode($customSlot->business_hours, true);
                    
                    if ($businessHours && is_array($businessHours)) {
                        // Check if any day has is_closed set to 'open'
                        foreach ($businessHours as $daySchedule) {
                            if (isset($daySchedule['is_closed']) && $daySchedule['is_closed'] === CustomSlot::SERVICE_OPEN) {
                                $dateStatus = 'Available';
                                break;
                            }
                        }
                    }
                }

                $dates[] = [
                    'DateStatus' => $dateStatus,
                    'DateTime' => $currentDate . 'T00:00:00'
                ];
            }

            return response()->json($dates);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while fetching available dates'
            ], 500);
        }
    }
    
    public function getAvailableTimeslots(Request $request)
    {
        try {
            $validated = $request->validate([
                'Date' => 'required|date',
                'LocationID' => 'required|integer|exists:locations,id',
                'PackageID' => 'required|integer|exists:categories,id',
                'CompanyID' => 'nullable|integer'
            ]);

            // First check if the date is available (reuse logic from getAvailableDates)
            $customSlot = CustomSlot::where('category_id', (int)$validated['PackageID'])
                ->where('location_id', (int)$validated['LocationID'])
                ->where('selected_date', $validated['Date'])
                ->where('slots_type', CustomSlot::CATEGORY_SLOT)
                ->first();

            $dateStatus = 'Unavailable';
            
            if ($customSlot) {
                $businessHours = json_decode($customSlot->business_hours, true);
                
                if ($businessHours && is_array($businessHours)) {
                    foreach ($businessHours as $daySchedule) {
                        if (isset($daySchedule['is_closed']) && $daySchedule['is_closed'] === CustomSlot::SERVICE_OPEN) {
                            $dateStatus = 'Available';
                            break;
                        }
                    }
                }
            }

            // If date is not available, return empty response
            if ($dateStatus !== 'Available') {
                return response()->json([]);
            }

            // Get location details
            $location = Location::find((int)$validated['LocationID']);
            $locationName = $location ? $location->location_name : '';

            // Get package details
            $package = Category::find((int)$validated['PackageID']);
            $serviceName = $package ? $package->name : '';

            // Generate time slots from business hours
            $timeSlots = [];
            
            if ($businessHours && is_array($businessHours)) {
                $slotNumber = 1;
                
                foreach ($businessHours as $daySchedule) {
                    if (isset($daySchedule['is_closed']) && $daySchedule['is_closed'] === CustomSlot::SERVICE_OPEN) {
                        $slotDuration = (int)($customSlot->slot_period ?? 30);
                        
                        // Process the main time interval
                        $this->generateTimeSlotsForInterval(
                            $timeSlots,
                            $daySchedule['start_time'],
                            $daySchedule['end_time'],
                            $slotDuration,
                            $customSlot,
                            $validated,
                            $serviceName,
                            $daySchedule['day'] ?? 'Monday',
                            $locationName,
                            $daySchedule['capacity'] ?? null,
                            $slotNumber
                        );

                        // Process day_interval if it exists
                        if (isset($daySchedule['day_interval']) && is_array($daySchedule['day_interval'])) {
                            foreach ($daySchedule['day_interval'] as $interval) {
                                $this->generateTimeSlotsForInterval(
                                    $timeSlots,
                                    $interval['start_time'],
                                    $interval['end_time'],
                                    $slotDuration,
                                    $customSlot,
                                    $validated,
                                    $serviceName,
                                    $daySchedule['day'] ?? 'Monday',
                                    $locationName,
                                    $interval['capacity'] ?? null,
                                    $slotNumber
                                );
                            }
                        }
                    }
                }
            }

            return response()->json($timeSlots);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while fetching available timeslots'
            ], 500);
        }
    }
    
    public function bookAppointment(Request $request)
    {
        try {
            $validated = $request->validate([
                'location_id' => 'required|integer|exists:locations,id',
                'appointment_type' => 'required|string',
                'company_id' => 'nullable|integer', // Made optional without exists validation
                'Date' => 'required|date', // Format: "2026-01-26"
                'Stmin' => 'required|date_format:H:i', // Start time in HH:MM format (13:00)
                'Etmin' => 'required|date_format:H:i', // End time in HH:MM format (14:00)
                'nric_fin_passport' => 'required|string|max:50',
                'full_name' => 'required|string|max:255',
                'title' => 'required|string|in:Mr,Mrs,Ms,Dr,Prof',
                'date_of_birth' => 'required|date_format:Y-m-d',
                'gender' => 'required|string|in:Male,Female,Other',
                'mobile_country_code' => 'required|string|max:5',
                'mobile_number' => 'required|string|max:20',
                'email_address' => 'required|email|max:255',
                'nationality' => 'required|string',
                'additional_comments' => 'nullable|string|max:1000',
                'is_vip' => 'boolean',
                'is_private_customer' => 'boolean',
                'payment_status' => 'nullable|string|in:Pending,Paid,Refunded',
               
            ], [
                'location_id.required' => 'Location ID is required',
                'appointment_type.required' => 'Appointment type is required',
                'Date.required' => 'Date is required',
                'Stmin.required' => 'Start time is required',
                'Etmin.required' => 'End time is required',
                'nric_fin_passport.required' => 'NRIC/FIN/Passport is required',
                'full_name.required' => 'Full name is required',
                'mobile_number.required' => 'Mobile number is required',
                'email_address.required' => 'Email address is required',
                'email_address.email' => 'Please enter a valid email address',
                'date_of_birth.required' => 'Date of birth is required',

            ]);

            // Parse date and time from new format
            $bookingDate = $validated['Date'];
            
            // Use HH:MM format directly
            $startTime = $validated['Stmin'];
            $endTime = $validated['Etmin'];

            // Get the category ID for the selected appointment type
            $category = Category::where('name', $validated['appointment_type'])
                ->whereJsonContains('category_locations', (string)$validated['location_id'])
                ->first();

            if (!$category) {
                return response()->json([
                    'error' => 'Appointment type not found',
                    'message' => 'The specified appointment type does not exist for this location',
                ], 404);
            }

             $validated['team_id'] = $category->team_id;
            // Check if slot is available
            $existingBooking = Booking::where('team_id', $validated['team_id'])
                ->where('location_id', $validated['location_id'])
                ->where('category_id', $category->id)
                ->where('booking_date', $bookingDate)
                ->where('start_time', $startTime)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'error' => 'Slot not available',
                    'message' => 'chosen slot is already booked'
                ], 422);
            }

            // Prepare JSON data
            $jsonData = [
                'nric' => $validated['nric_fin_passport'],
                'gender' => $validated['gender'],
                'nationality' => $validated['nationality'],
                'title' => $validated['title'],
                'is_vip' => $validated['is_vip'] ?? false,
                'is_private_customer' => $validated['is_private_customer'] ?? false,
            ];

            // Create the booking
            $booking = Booking::create([
                'team_id' => $validated['team_id'],
                'refID' => $validated['nric_fin_passport'], // Use NRIC as refID like web module
                'name' => $validated['full_name'],
                'title' => $validated['title'],
                'identification_type' => 'NRIC / FIN',
                'phone' => $validated['mobile_number'],
                'phone_code' => $validated['mobile_country_code'],
                'email' => $validated['email_address'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'nationality' => $validated['nationality'],
                'booking_date' => $bookingDate,
                'booking_time' => $startTime . '-' . $endTime, // Same format as web module
                'start_time' => $startTime,
                'end_time' => $endTime,
                'location_id' => $validated['location_id'],
                'category_id' => $category->id,
                'company_id' => $validated['company_id'] ?? null,
                'sub_category_id' => null,
                'child_category_id' => null,
                'level_id' => Level::getFirstRecord()?->id,
                'additional_comments' => $validated['additional_comments'] ?? '',
                'payment_status' => $validated['payment_status'] ?? 'Pending',
                'is_vip' => $validated['is_vip'] ?? false,
                'is_private_customer' => $validated['is_private_customer'] ?? false,
                'status' => Booking::STATUS_RESERVED,
                'json' => json_encode($jsonData),
                'created_by' => null, // API booking
                'last_category' => $category->id,
                'count' => 1, // Default count for new booking
            ]);

            // Store activity log for appointment booking creation
            $location = Location::find($validated['location_id']);
            $locationName = $location ? $location->location_name : 'N/A';
            $categoryName = $category->name;
            $formattedDateTime = Carbon::parse($bookingDate . ' ' . $startTime)->format('d/m/Y h:iA');
            $phoneWithCode = !empty($validated['mobile_country_code']) ? "+{$validated['mobile_country_code']} {$validated['mobile_number']}" : $validated['mobile_number'];

            // Build comprehensive log text
            $logText = "Appointment Booking Created (API) - Patient: {$validated['full_name']} (NRIC: {$validated['nric_fin_passport']}), " .
                "Appointment Type: {$categoryName}, " .
                "Location: {$locationName}, " .
                "Date/Time: {$formattedDateTime}, " .
                "Phone: {$phoneWithCode}, " .
                "Email: {$validated['email_address']}";

            // Build detailed remark
            $remark = "Status: " . Booking::STATUS_RESERVED . ", " .
                "VIP: " . (($validated['is_vip'] ?? false) ? 'Yes' : 'No') . ", " .
                "Private Customer: " . (($validated['is_private_customer'] ?? false) ? 'Yes' : 'No') . ", " .
                "Gender: {$validated['gender']}, " .
                "Nationality: {$validated['nationality']}, " .
                "DOB: " . Carbon::parse($validated['date_of_birth'])->format('d/m/Y') . ", " .
                "Title: {$validated['title']}";

            if ($validated['company_id'] ?? null) {
                $company = Company::find($validated['company_id']);
                if ($company) {
                    $remark .= ", Company: {$company->company_name}";
                }
            }

            if ($validated['additional_comments'] ?? null) {
                $remark .= ", Comments: {$validated['additional_comments']}";
            }

            ActivityLog::storeLog(
                $validated['team_id'],
                null, // No user for API
                $booking->id,
                null,
                $logText,
                $validated['location_id'],
                ActivityLog::APPOINTMENT_BOOKING,
                $remark,
                null, // No user details for API
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'booking_id' => $booking->id,
                'booking_reference' => $booking->refID,
                'appointment_details' => [
                    'booking_id' => $booking->id,
                    'reference_id' => $booking->refID,
                    'patient_name' => $booking->name,
                    'appointment_type' => $categoryName,
                    'location' => $locationName,
                    'date' => $bookingDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'An error occurred while booking appointment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function generateTimeSlotsForInterval(
        array &$timeSlots,
        string $startTimeStr,
        string $endTimeStr,
        int $slotDuration,
        $customSlot,
        array $validated,
        string $serviceName,
        string $day,
        string $locationName,
        ?int $intervalCapacity,
        int &$slotNumber
    ) {
        $startTime = \Carbon\Carbon::parse($startTimeStr);
        $endTime = \Carbon\Carbon::parse($endTimeStr);
        $capacity = (int)($intervalCapacity ?? ($customSlot->pax_per_service ?? 2));
        
        // Calculate actual slot duration in minutes from start and end time
        $actualSlotDuration = $startTime->diffInMinutes($endTime);
        
        // Create single slot for this time interval (not multiple slots)
        $timeSlots[] = [
            'ScheduleID' => $customSlot->id ?? 204,
            'ServiceID' => (int)$validated['PackageID'],
            'ServiceName' => $serviceName,
            'StartDate' => $validated['Date'] . 'T00:00:00',
            'day' => $day,
            'SlotDuration' => $actualSlotDuration,
            'Value' => $startTime->format('g:iA'),
            'SlotNumber' => $slotNumber++,
            'BookingCount' => 0,
            'Location' => $locationName,
            'Capacity' => $capacity,
            'Stmin' => $startTime->hour * 60 + $startTime->minute,
            'Etmin' => $endTime->hour * 60 + $endTime->minute
        ];
    }
}
