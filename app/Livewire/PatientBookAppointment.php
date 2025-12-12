<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Category,
    Booking,
    Location,
    AccountSetting,
    SiteDetail,
    Member,
    Level
};
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.patient-layout')]
#[Title('Book Appointment')]
class PatientBookAppointment extends Component
{
    public $teamId;
    public $member;
    
    // Step 1: Appointment Type & Package
    public $appointmentTypeId = null;
    public $bookingFor = 'Self'; // Self or Dependent
    public $packageId = null;
    public $showBookingFor = false;
    public $showPackage = false;
    
    // Step 2: Location & Schedule
    public $locationId = null;
    public $appointmentDate = null;
    public $appointmentTime = null;
    public $additionalComments = '';
    
    // Data
    public $appointmentTypes = [];
    public $packages;
    public $locations = [];
    public $availableTimeSlots = [];
    public $selectedPackage = null;
    public $selectedLocation = null;
    public $locationBusinessHours = null;
    public $locationPhone = null;
    
    // UI States
    public $step = 1; // 1: Type & Package, 2: Location & Schedule, 3: Confirmation
    public $successMessage = '';
    
    public function mount()
    {
        // Check if patient is logged in
        if (!Session::has('patient_member_id')) {
            $this->redirect(route('tenant.patient.login'), navigate: true);
            return;
        }

        $this->teamId = tenant('id');
        $memberId = Session::get('patient_member_id');
        
        $this->member = Member::where('team_id', $this->teamId)
            ->where('id', $memberId)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->first();

        if (!$this->member) {
            Session::forget(['patient_member_id', 'patient_member', 'patient_customer_type']);
            $this->redirect(route('tenant.patient.login'), navigate: true);
            return;
        }

        // Get appointment types (level 1 categories)
        $level1 = Level::getFirstRecord();
        if ($level1) {
            $this->appointmentTypes = Category::where('team_id', $this->teamId)
                ->where('level_id', $level1->id)
                ->whereNull('deleted_at')
                ->get();
        }
        
        // Get locations
        $this->locations = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->get();
            
        // Set timezone
        $siteDetail = SiteDetail::where('team_id', $this->teamId)->first();
        if ($siteDetail && $siteDetail->select_timezone) {
            Config::set('app.timezone', $siteDetail->select_timezone);
            date_default_timezone_set($siteDetail->select_timezone);
        }
    }
    
    public function updatedAppointmentTypeId($value)
    {
        $this->appointmentTypeId = $value;
        $this->packageId = null;
        $this->selectedPackage = null;
        $this->showPackage = false;
        $this->packages = collect([]);
        
        if ($value) {
            $appointmentType = Category::find($value);
            if ($appointmentType && $appointmentType->package_for) {
                $this->showBookingFor = true;
                // If package_for is 'Both', show booking for options
                // If 'Self' only, set to Self
                // If 'Dependent' only, set to Dependent
                if ($appointmentType->package_for === 'Both') {
                    $this->bookingFor = 'Self'; // Default
                } elseif ($appointmentType->package_for === 'Self') {
                    $this->bookingFor = 'Self';
                } elseif ($appointmentType->package_for === 'Dependent') {
                    $this->bookingFor = 'Dependent';
                }
            } else {
                $this->showBookingFor = false;
            }
            
            // Load packages immediately when appointment type is selected
            $this->loadPackages();
        } else {
            $this->showBookingFor = false;
            $this->packages = collect([]);
        }
    }
    
    public function updatedBookingFor($value)
    {
        $this->loadPackages();
    }
    
    public function loadPackages()
    {
        $this->packages = collect([]);
        $this->packageId = null;
        $this->selectedPackage = null;
        $this->showPackage = false;
        
        if (!$this->appointmentTypeId) {
            return;
        }
        
        // Use the existing Category method that handles booking filters correctly
        // This method filters by booking_category_show_for = 'Online' or 'Backend & Online Appointment Screen'
        $packagesResult = Category::getchildDetailBooking($this->appointmentTypeId, null);
        
        // getchildDetailBooking returns a Collection, ensure we have it
        $this->packages = $packagesResult ?: collect([]);
        
        // Also try direct query if the method doesn't return results
        if ($this->packages->isEmpty()) {
            $level2 = Level::getSecondRecord();
            if ($level2) {
                $this->packages = Category::where('team_id', $this->teamId)
                    ->where('level_id', $level2->id)
                    ->where('parent_id', $this->appointmentTypeId)
                    ->whereNull('deleted_at')
                    ->get();
            }
        }
        
        // Show package field if packages exist
        if ($this->packages->count() > 0) {
            $this->showPackage = true;
        }
    }
    
    public function updatedPackageId($value)
    {
        if ($value) {
            // Fetch the package with all fields including description
            $this->selectedPackage = Category::where('id', $value)
                ->select('id', 'name', 'description', 'note', 'amount', 'other_name', 'img')
                ->first();
        } else {
            $this->selectedPackage = null;
        }
    }
    
    public function nextToLocation()
    {
        $rules = [
            'appointmentTypeId' => 'required|exists:categories,id',
        ];
        
        $messages = [
            'appointmentTypeId.required' => 'Please select an appointment type.',
        ];
        
        // Only require package if packages are available
        if (count($this->packages) > 0) {
            $rules['packageId'] = 'required|exists:categories,id';
            $messages['packageId.required'] = 'Please select a package.';
        }
        
        $this->validate($rules, $messages);
        
        $this->step = 2;
        $this->loadTimeSlots();
    }
    
    public function updatedLocationId($value)
    {
        if ($value && !empty($value)) {
            $this->selectedLocation = Location::find($value);
            $this->appointmentTime = null;
            $this->availableTimeSlots = [];
            
            // Load business hours for the selected location
            if ($this->selectedLocation) {
                $this->loadLocationDetails($value);
            }
            
            // Keep the date if already selected, so we can reload slots for new location
            // Reload slots if date is already selected
            if ($this->appointmentDate) {
                $this->loadTimeSlots();
            }
        } else {
            $this->selectedLocation = null;
            $this->locationBusinessHours = null;
            $this->locationPhone = null;
            $this->appointmentDate = null;
            $this->appointmentTime = null;
            $this->availableTimeSlots = [];
        }
    }
    
    public function loadLocationDetails($locationId)
    {
        // Get business hours from AccountSetting
        $locationSlot = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::LOCATION_SLOT)
            ->first();
        
        if ($locationSlot && $locationSlot->business_hours) {
            $this->locationBusinessHours = is_array($locationSlot->business_hours) 
                ? $locationSlot->business_hours 
                : json_decode($locationSlot->business_hours, true);
        }
        
        // Get phone from location's user if available
        if ($this->selectedLocation && $this->selectedLocation->user) {
            $this->locationPhone = $this->selectedLocation->user->phone ?? null;
        }
    }
    
    public function updatedAppointmentDate($value)
    {
        if ($value && $this->locationId) {
            $this->appointmentTime = null;
            $this->loadTimeSlots();
        }
    }
    
    public function loadTimeSlots()
    {
        $this->availableTimeSlots = [];
        
        if (!$this->locationId || !$this->appointmentDate) {
            return;
        }
        
        try {
            // Convert appointment date to Carbon object
            $carbonDate = Carbon::parse($this->appointmentDate);
            $dayOfWeek = $carbonDate->format('l');
            
            // Get location slot settings (not booking slot)
            $locationSlot = AccountSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->where('slot_type', AccountSetting::LOCATION_SLOT)
                ->first();
            
            if (!$locationSlot) {
                return;
            }
            
            // Get advance booking dates
            $getAdvanceBookingDates = AccountSetting::datesGet($locationSlot->allow_req_before ?? 30);
            
            // Check for custom slots first
            $customSlot = \App\Models\CustomSlot::whereDate('selected_date', $carbonDate)
                ->where('slots_type', AccountSetting::LOCATION_SLOT)
                ->where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->first();
            
            $businessHoursArray = null;
            if ($customSlot) {
                $businessHoursArray = json_decode($customSlot->business_hours, true);
            } else {
                $businessHoursArray = json_decode($locationSlot->business_hours, true);
            }
            
            if (!$businessHoursArray || !is_array($businessHoursArray)) {
                return;
            }
            
            // Index business hours by day name
            $indexedBusinessHours = array_column($businessHoursArray, null, 'day');
            
            // Check if the day is open
            if (!isset($indexedBusinessHours[$dayOfWeek]) || 
                $indexedBusinessHours[$dayOfWeek]['is_closed'] !== \App\Models\ServiceSetting::SERVICE_OPEN) {
                return;
            }
            
            // Check if date is within advance booking range
            $dateFormatted = date('d-m-Y', strtotime($carbonDate));
            if (!in_array($dateFormatted, $getAdvanceBookingDates)) {
                return;
            }
            
            // Get available slots using location slot settings
            $breakHours = [];
            $availableSlots = AccountSetting::getAvailableSlots(
                $carbonDate,
                $indexedBusinessHours[$dayOfWeek],
                $breakHours,
                $locationSlot,
                null,
                'location',
                $locationSlot
            );
            
            // Extract slots from result
            if ($availableSlots instanceof \Illuminate\Support\Collection) {
                $this->availableTimeSlots = $availableSlots->toArray();
            } elseif (is_array($availableSlots)) {
                $this->availableTimeSlots = $availableSlots;
            } else {
                $this->availableTimeSlots = [];
            }
            
            // Filter out past time slots if the selected date is today
            if ($carbonDate->isToday()) {
                $now = Carbon::now();
                $this->availableTimeSlots = array_filter($this->availableTimeSlots, function ($slot) use ($now) {
                    [$startTime] = explode('-', $slot);
                    try {
                        $slotStart = Carbon::createFromFormat('h:i A', trim($startTime))
                            ->setDate($now->year, $now->month, $now->day);
                        return $slotStart->greaterThanOrEqualTo($now);
                    } catch (\Exception $e) {
                        return false;
                    }
                });
                $this->availableTimeSlots = array_values($this->availableTimeSlots);
            }
            
        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Error loading time slots for location ' . $this->locationId . ' and date ' . $this->appointmentDate . ': ' . $e->getMessage());
            $this->availableTimeSlots = [];
        }
    }
    
    public function selectTimeSlot($timeSlot)
    {
        $this->appointmentTime = $timeSlot;
    }
    
    public function goBack()
    {
        if ($this->step > 1) {
            $this->step--;
            if ($this->step == 1) {
                $this->locationId = null;
                $this->appointmentDate = null;
                $this->appointmentTime = null;
                $this->availableTimeSlots = [];
            }
        }
    }
    
    public function bookAppointment()
    {
        $this->validate([
            'locationId' => 'required|exists:locations,id',
            'appointmentDate' => 'required|date|after_or_equal:today',
            'appointmentTime' => 'required',
        ], [
            'locationId.required' => 'Please select a location.',
            'appointmentDate.required' => 'Please select a date.',
            'appointmentDate.after_or_equal' => 'Please select a valid date.',
            'appointmentTime.required' => 'Please select a time slot.',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Parse time slot and convert to 24-hour format
            $timeParts = explode('-', $this->appointmentTime);
            $startTime12h = trim($timeParts[0] ?? '');
            $endTime12h = trim($timeParts[1] ?? $startTime12h);
            
            // Convert from 12-hour format (e.g., "2:30PM") to 24-hour format (e.g., "14:30")
            try {
                $startTimeCarbon = Carbon::createFromFormat('h:i A', $startTime12h);
                $startTime = $startTimeCarbon->format('H:i');
            } catch (\Exception $e) {
                // Fallback: try without space
                $startTimeCarbon = Carbon::createFromFormat('h:iA', $startTime12h);
                $startTime = $startTimeCarbon->format('H:i');
            }
            
            try {
                $endTimeCarbon = Carbon::createFromFormat('h:i A', $endTime12h);
                $endTime = $endTimeCarbon->format('H:i');
            } catch (\Exception $e) {
                // Fallback: try without space
                $endTimeCarbon = Carbon::createFromFormat('h:iA', $endTime12h);
                $endTime = $endTimeCarbon->format('H:i');
            }
              
            // Format booking_time in 24-hour format (e.g., "14:30-15:30")
            $bookingTime24h = $startTime . ($endTime !== $startTime ? '-' . $endTime : '');
            
            // Prepare booking data
            $bookingData = [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'booking_date' => $this->appointmentDate,
                'booking_time' => $bookingTime24h,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'category_id' => $this->appointmentTypeId,
                'sub_category_id' => $this->packageId,
                'refID' => time(),
                'status' => Booking::STATUS_RESERVED,
                'name' => ($this->member->salutation ? $this->member->salutation . ' ' : '') . $this->member->full_name,
                'email' => $this->member->email,
                'phone' => $this->member->mobile_number,
                'phone_code' => $this->member->mobile_country_code ?? '+65',
                'date_of_birth' => $this->member->date_of_birth,
                'gender' => $this->member->gender,
                'nationality' => $this->member->nationality,
                'identification_type' => $this->member->identification_type,
                'additional_comments' => $this->additionalComments,
                'json' => json_encode([
                    'booking_for' => $this->bookingFor,
                    'member_id' => $this->member->id,
                    'nric_fin' => $this->member->nric_fin,
                    'passport' => $this->member->passport,
                ]),
            ];
            
            // Create booking
            $booking = Booking::create($bookingData);
            
            DB::commit();
            
            $this->successMessage = 'Appointment booked successfully! Your order number is ' . time();
            $this->step = 3;
            
            // Reset form after 3 seconds and redirect
            $this->dispatch('booking-success', ['refID' => time(), 'booking_id' => $booking->id]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('booking', 'Failed to book appointment. Please try again.');
        }
    }
    
    public function render()
    {
        return view('livewire.patient-book-appointment');
    }
}

