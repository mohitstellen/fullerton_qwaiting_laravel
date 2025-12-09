<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Location;
use App\Models\Category;
use App\Models\Level;
use App\Models\Booking;
use App\Models\Team;
use App\Models\Member;
use App\Models\Company;
use App\Models\AllowedCountry;
use App\Models\Country;
use App\Models\SiteDetail;
use App\Models\AccountSetting;
use App\Models\ActivityLog;
use Carbon\Carbon;

#[Layout('components.layouts.appointment-layout')]
#[Title('Appointment Booking Module')]
class AppointmentBookingModule extends Component
{
    // Selected clinics
    public $selectedClinics = []; // Array of clinic IDs
    
    // Clinics with time slots data
    public $clinics = [];
    
    // All available clinics for dropdown
    public $availableClinics = [];
    
    // Appointment types based on selected clinics
    public $appointmentTypes = [];
    
    // Selected appointment types (array of appointment type names)
    public $selectedAppointmentTypes = [];
    
    // Date navigation
    public $selectedDay = 5;
    public $selectedMonth = 'Dec';
    public $selectedYear = 2025;
    public $selectedDayName = 'Friday';
    public $selectedDate = '';
    
    // View mode
    public $viewMode = 'calendar'; // 'calendar' or 'timeline'
    
    // Timeline filter
    public $timelineGender = 'All'; // 'All', 'Male', 'Female', 'Unisex'
    
    // Search filters form
    public $showSearchFilters = false;
    public $searchNric = '';
    public $searchMobile = '';
    public $searchEmail = '';
    public $searchCompany = '';
    
    // Timeline appointments
    public $timelineAppointments = [];
    
    // Sample appointments data
    public $appointments = [];
    
    // Modal state
    public $showBookingModal = false;
    public $selectedAppointment = null;
    public $selectedAppointmentType = 'Doctor Review Consult';
    public $auditTrailLogs = [];
    
    // Form fields for booking modal
    public $identificationType = 'NRIC / FIN';
    public $nricFinPassport = '';
    public $title = 'Mr';
    public $fullName = '';
    public $dateOfBirth = '';
    public $gender = 'Male';
    public $mobileCountryCode = '65';
    public $mobileNumber = '';
    public $emailAddress = '';
    public $nationality = 'Singaporean';
    public $package = '';
    public $locationId = null;
    public $dateTime = '';
    public $additionalComments = '';
    public $paymentStatus = 'Pending';
    public $isVip = false;
    public $isPrivateCustomer = false;
    public $companyName = '';
    public $companyId = null;
    
    // Available options
    public $availableLocations = [];
    public $availableNationalities = [];
    
    // Country Code options
    public $allowedCountries = []; // Collection of AllowedCountry models
    public $countryPhoneMode = 1; // 1 = Single country mode, 2 = Multiple countries mode
    
    // NRIC Search Results
    public $nricSearchResults = [];
    public $showNricDropdown = false;
    public $skipNricSearch = false; // Flag to skip search when programmatically setting value
    
    // Modal tabs
    public $activeTab = 'booking-details'; // 'booking-details', 'audit-trail', 'tester-testing'
    
    // Booking status
    public $bookingStatus = 'Reserved';
    
    // Team ID
    public $teamId = null;
    
    public function mount()
    {
        // Get team ID from authenticated user
        $user = Auth::user();
        $this->teamId = $user->team_id ?? null;
        
        // If team_id is not found, try getting from domain slug
        if (!$this->teamId) {
            $domainSlug = Team::getSlug();
            $this->teamId = Team::getTeamId($domainSlug);
        }
        
        // Initialize with current date
        $today = Carbon::now();
        $this->selectedDay = $today->day;
        $this->selectedMonth = $today->format('M');
        $this->selectedYear = $today->year;
        $this->selectedDayName = $today->format('l');
        $this->selectedDate = $today->format('Y-m-d');
        $this->selectedDate = $today->format('Y-m-d');
        
        // Load available locations for the form
        $this->loadAvailableLocations();
        
        // Load nationalities from config
        $this->availableNationalities = config('nationalities', []);
        
        // Load country codes
        $this->loadCountryCodes();
        
        // Initialize clinics with time slots
        $this->loadClinics();
        
        // Don't load appointments initially - only load when search is performed
        // $this->loadAppointments();
    }
    
    public function loadCountryCodes()
    {
        // Load ALL countries from Country model (not just allowed countries)
        $this->allowedCountries = Country::select('id', 'name', 'phonecode')
            ->orderBy('name')
            ->get();
        
        // Always set default country code to 65 (Singapore)
        $this->mobileCountryCode = '65';
    }
    
    public function updatedLocationId($value)
    {
        // Reload country codes when location changes
        $this->loadCountryCodes();
    }
    
    public function loadAvailableLocations()
    {
        if (!$this->teamId) {
            $this->availableLocations = [];
            return;
        }
        
        $this->availableLocations = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->orderBy('location_name')
            ->get(['id', 'location_name'])
            ->toArray();
    }
    
    public function loadClinics()
    {
        // If no team ID, return empty
        if (!$this->teamId) {
            $this->availableClinics = [];
            return;
        }
        
        // Get all active locations from database
        $locations = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->get(['id', 'location_name']);
        
        // Get level 1 categories (appointment types)
        $firstLevel = Level::getFirstRecord();
        
        $this->availableClinics = [];
        
        foreach ($locations as $location) {
            // Get categories for this location
            $categories = Category::where('team_id', $this->teamId)
                ->where('level_id', $firstLevel->id)
                ->whereNotNull('booking_category_show_for')
                ->where('booking_category_show_for', '!=', '')
                ->whereIn('booking_category_show_for', [
                    'Backend & Online Appointment Screen',
                    'Backend',
                ])
                ->whereJsonContains('category_locations', (string)$location->id)
                ->get(['id', 'name']);
            
            $appointmentTypes = [];
            
            // Store appointment types (slots will be generated dynamically in getTimeSlotsForDisplay)
            foreach ($categories as $category) {
                $appointmentTypes[$category->name] = []; // Empty array, slots generated on demand
            }
            
            $this->availableClinics[] = [
                'id' => $location->id,
                'name' => $location->location_name,
                'appointmentTypes' => $appointmentTypes
            ];
        }
     
        // Load appointment types based on selected clinics
        $this->loadAppointmentTypes();
    }
    
    public function loadAppointmentTypes()
    {
        // Get all unique appointment types from selected clinics
        $appointmentTypes = [];
        
        foreach ($this->availableClinics as $clinic) {
            if (in_array($clinic['id'], $this->selectedClinics)) {
                foreach ($clinic['appointmentTypes'] as $type => $slots) {
                    if (!isset($appointmentTypes[$type])) {
                        $appointmentTypes[$type] = true;
                    }
                }
            }
        }
        
        $this->appointmentTypes = $appointmentTypes;
    }
    
    public function loadAppointments()
    {
        // If no team ID, return empty
        if (!$this->teamId) {
            $this->appointments = [];
            return;
        }
        
        // Query bookings from database
        $query = Booking::where('team_id', $this->teamId);
        
        // Apply search filters
        if (!empty($this->searchNric)) {
            $query->where(function($q) {
                $q->where('refID', 'like', '%' . $this->searchNric . '%')
                  ->orWhereJsonContains('json->nric', $this->searchNric)
                  ->orWhereJsonContains('json->passport', $this->searchNric);
            });
        }
        
        if (!empty($this->searchMobile)) {
            $query->where('phone', 'like', '%' . $this->searchMobile . '%');
        }
        
        if (!empty($this->searchEmail)) {
            $query->where('email', 'like', '%' . $this->searchEmail . '%');
        }
        
        if (!empty($this->searchCompany)) {
            $query->whereHas('categories.company', function($q) {
                $q->where('name', 'like', '%' . $this->searchCompany . '%');
            });
        }
        
        // Get bookings with relationships
        $bookings = $query->with(['location', 'categories'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(100)
            ->get();
        
        // Transform to match the expected format
        $this->appointments = $bookings->map(function($booking) {
            $jsonData = json_decode($booking->json, true);
            
            return [
                'id' => $booking->id,
                'time' => Carbon::parse($booking->booking_date . ' ' . $booking->start_time)->format('d/m/Y h:iA'),
                'gender' => $jsonData['gender'] ?? $booking->gender ?? 'N/A',
                'name' => $booking->name,
                'created_on' => $booking->created_at->format('d/m/Y h:iA'),
                'company_package' => ($booking->categories->company->name ?? '') . ' - ' . ($booking->categories->name ?? ''),
                'national_id' => $jsonData['nric'] ?? $jsonData['passport'] ?? $booking->refID,
                'phone' => $booking->phone,
                'location' => $booking->location->location_name ?? 'N/A',
                'location_id' => $booking->location_id,
                'status' => $booking->status ?? 'Pending',
            ];
        })->toArray();
    }
    
    public function toggleSearchFilters()
    {
        $this->showSearchFilters = !$this->showSearchFilters;
    }
    
    public function searchAppointments()
    {
        // Filter appointments based on search criteria
        // This will be implemented with actual database query later
        $this->loadAppointments();
    }
    
    public function clearSearch()
    {
        $this->searchNric = '';
        $this->searchMobile = '';
        $this->searchEmail = '';
        $this->searchCompany = '';
        // Clear appointments when clearing search - don't load all
        $this->appointments = [];
    }
    
    public function openBookingModal($appointmentId)
    {
        // Fetch the actual booking from database directly
        $booking = Booking::with(['location', 'categories', 'categories.company', 'company'])->find($appointmentId);
        
        if ($booking) {
            // Prepare appointment data
            $jsonData = json_decode($booking->json, true);
            
            $this->selectedAppointment = [
                'id' => $booking->id,
                'time' => Carbon::parse($booking->booking_date . ' ' . $booking->start_time)->format('d/m/Y h:iA'),
                'gender' => $jsonData['gender'] ?? $booking->gender ?? 'N/A',
                'name' => $booking->name,
                'created_on' => $booking->created_at->format('d/m/Y h:iA'),
                'company_package' => ($booking->categories?->company?->name ?? '') . ' - ' . ($booking->categories?->name ?? ''),
                'national_id' => $jsonData['nric'] ?? $jsonData['passport'] ?? $booking->refID,
                'phone' => $booking->phone,
                'location' => $booking->location?->location_name ?? 'N/A',
                'location_id' => $booking->location_id,
                'status' => $booking->status ?? 'Pending',
            ];
            
            // Populate form fields from booking data
            $this->nricFinPassport = $booking->refID ?? '';
            $this->fullName = $booking->name ?? '';
            $this->title = $booking->title ?? 'Mr';
            $this->gender = $booking->gender ?? 'Male';
            // Set default country code to 65 (Singapore) - editable field
            $this->mobileCountryCode = '65';
            // Set phone number from booking
            $this->mobileNumber = $booking->phone ?? '';
            $this->emailAddress = $booking->email ?? '';
            $this->dateOfBirth = $booking->date_of_birth ? Carbon::parse($booking->date_of_birth)->format('Y-m-d') : '';
            $this->nationality = $booking->nationality ?? 'Singaporean';
            $this->dateTime = $this->selectedAppointment['time'];
            $this->locationId = $booking->location_id ?? null;
            // Ensure boolean values for checkboxes - check both database value and JSON
            $this->isVip = (bool)($jsonData['is_vip'] ?? $booking->is_vip ?? false);
            $this->isPrivateCustomer = (bool)($jsonData['is_private_customer'] ?? $booking->is_private_customer ?? false);
            
            // Get company name only if NOT a private customer
            if (!$this->isPrivateCustomer) {
                if ($booking->company) {
                    $this->companyName = $booking->company->company_name ?? '';
                    $this->companyId = $booking->company_id;
                } else {
                    // Fallback to category's company if booking doesn't have direct company
                    $this->companyName = $booking->categories?->company?->company_name ?? '';
                    $this->companyId = $booking->company_id ?? null;
                }
            } else {
                // Private customer - no company name
                $this->companyName = '';
                $this->companyId = null;
            }
            $this->additionalComments = $booking->additional_comments ?? '';
            $this->paymentStatus = $booking->payment_status ?? 'Pending';
            
            // Log for debugging
            Log::info('Edit form populated', [
                'is_vip' => $this->isVip,
                'is_private_customer' => $this->isPrivateCustomer,
                'company_name' => $this->companyName,
                'booking_is_vip' => $booking->is_vip,
                'booking_is_private_customer' => $booking->is_private_customer,
                'json_is_vip' => $jsonData['is_vip'] ?? null,
                'json_is_private_customer' => $jsonData['is_private_customer'] ?? null,
            ]);
            $this->bookingStatus = $booking->status;
            $this->selectedAppointmentType = $booking->categories?->name ?? 'Doctor Review Consult';
            $this->package = $this->selectedAppointment['company_package'] ?? '';
            
            $this->showBookingModal = true;
            $this->activeTab = 'booking-details';
            
            // Force Livewire to refresh the view to update checkboxes
            $this->dispatch('$refresh');
            
            // Load audit trail logs for this appointment
            $this->loadAuditTrailLogs($booking->id);
        }
    }
    
    public function loadAuditTrailLogs($bookingId)
    {
        // Load the booking to get current data
        $booking = Booking::with(['categories', 'categories.company'])->find($bookingId);
        
        // Load activity logs for this appointment booking
        $logs = ActivityLog::where('team_id', $this->teamId)
            ->where('call_book_id', $bookingId)
            ->where('type', ActivityLog::APPOINTMENT_BOOKING)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $this->auditTrailLogs = $logs->map(function($log) use ($booking) {
            $userDetails = json_decode($log->user_details, true);
            $userName = $userDetails['name'] ?? ($log->createdBy->name ?? 'System');
            
            // Parse the log text to extract information
            $logText = $log->text ?? '';
            
            // Parse remark to extract booking details
            $remark = $log->remark ?? '';
            
            // Default values - try to get from booking first, then parse from log
            $status = $booking->status ?? 'N/A';
            $nric = $booking->refID ?? '';
            $name = $booking->name ?? '';
            $gender = $booking->gender ?? 'N/A';
            $dob = $booking->date_of_birth ? Carbon::parse($booking->date_of_birth)->format('d/m/Y') : 'N/A';
            $appointmentType = $booking->categories->name ?? 'N/A';
            $package = ($booking->categories?->company?->name ?? '') . ($booking->categories?->company?->name && $booking->categories?->name ? ' - ' : '') . ($booking->categories?->name ?? '');
            $startDateTime = $booking ? Carbon::parse($booking->booking_date . ' ' . $booking->start_time)->format('d/m/Y h:iA') : 'N/A';
            $endDateTime = $booking ? Carbon::parse($booking->booking_date . ' ' . $booking->end_time)->format('d/m/Y h:iA') : 'N/A';
            
            // Extract status from remark if available
            if (preg_match('/Status:\s*([^,|]+)/', $remark, $matches)) {
                $status = trim($matches[1]);
            }
            
            // Extract data from log text if not available from booking
            if (empty($name) && preg_match('/Patient:\s*([^(]+)\s*\(NRIC:\s*([^)]+)\)/', $logText, $matches)) {
                $name = trim($matches[1]);
                $nric = trim($matches[2]);
            }
            
            if (empty($appointmentType) && preg_match('/Appointment Type:\s*([^,]+)/', $logText, $matches)) {
                $appointmentType = trim($matches[1]);
            }
            
            if ($startDateTime === 'N/A' && preg_match('/Date\/Time:\s*([^,]+)/', $logText, $matches)) {
                $startDateTime = trim($matches[1]);
                // Calculate end time (assuming 30 min slot, adjust if needed)
                try {
                    $startTime = Carbon::createFromFormat('d/m/Y h:iA', $startDateTime);
                    $endDateTime = $startTime->copy()->addMinutes(30)->format('d/m/Y h:iA');
                } catch (\Exception $e) {
                    $endDateTime = 'N/A';
                }
            }
            
            if ($gender === 'N/A' && preg_match('/Gender:\s*([^,|]+)/', $remark, $matches)) {
                $gender = trim($matches[1]);
            }
            
            if ($dob === 'N/A' && preg_match('/DOB:\s*([^,|]+)/', $remark, $matches)) {
                $dob = trim($matches[1]);
            }
            
            if (empty($package) && preg_match('/Company:\s*([^,|]+)/', $remark, $matches)) {
                $package = trim($matches[1]);
            }
            
            return [
                'id' => $log->id,
                'modified_at' => Carbon::parse($log->created_at)->format('d/m/Y h:iA'),
                'modified_by' => $userName,
                'status' => $status,
                'nric' => $nric,
                'name' => $name,
                'gender' => $gender,
                'dob' => $dob,
                'appointment_type' => $appointmentType,
                'package' => $package ?: 'N/A',
                'start_date_time' => $startDateTime,
                'end_date_time' => $endDateTime,
                'log_text' => $logText,
                'remark' => $remark,
            ];
        })->toArray();
    }
    
    public function removeClinic($clinicId)
    {
        $this->selectedClinics = array_values(array_filter($this->selectedClinics, function($id) use ($clinicId) {
            return $id != $clinicId;
        }));
        
        // Reload appointment types based on remaining selected clinics
        $this->loadAppointmentTypes();
        
        // Filter out selected appointment types that are no longer available
        $availableTypes = array_keys($this->appointmentTypes);
        $this->selectedAppointmentTypes = array_values(array_filter($this->selectedAppointmentTypes, function($type) use ($availableTypes) {
            return in_array($type, $availableTypes);
        }));
    }
    
    public function addClinic($clinicId)
    {
        if (!in_array($clinicId, $this->selectedClinics)) {
            $this->selectedClinics[] = $clinicId;
            $this->loadAppointmentTypes();
        }
    }
    
    public function toggleAppointmentType($type)
    {
        if (in_array($type, $this->selectedAppointmentTypes)) {
            // Remove from selected
            $this->selectedAppointmentTypes = array_values(array_filter($this->selectedAppointmentTypes, function($t) use ($type) {
                return $t != $type;
            }));
        } else {
            // Add to selected
            $this->selectedAppointmentTypes[] = $type;
        }
        
        // Force Livewire to refresh the view to update time slots
        $this->dispatch('$refresh');
    }
    
    
    #[On('closeBookingModal')]
    public function closeBookingModal()
    {
        $this->showBookingModal = false;
        $this->selectedAppointment = null;
        $this->auditTrailLogs = [];
        $this->resetForm();
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function setBookingStatus($status)
    {
        $this->bookingStatus = $status;
    }
    
    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'calendar' ? 'timeline' : 'calendar';
        
        // Load timeline appointments when switching to timeline view
        if ($this->viewMode === 'timeline') {
            $this->loadTimelineAppointments();
        }
    }
    
    public function loadTimelineAppointments()
    {
        // If no team ID, return empty
        if (!$this->teamId) {
            $this->timelineAppointments = [];
            return;
        }
        
        // Create date string from selected date
        $dateStr = $this->selectedYear . '-' . Carbon::createFromFormat('M', $this->selectedMonth)->format('m') . '-' . sprintf('%02d', $this->selectedDay);
        
        // Query bookings from database for the selected date
        $query = Booking::where('team_id', $this->teamId)
            ->where('booking_date', $dateStr);
        
        // Filter by gender if not 'All'
        if ($this->timelineGender !== 'All') {
            $query->where(function($q) {
                $q->where('gender', $this->timelineGender)
                  ->orWhereJsonContains('json->gender', $this->timelineGender);
            });
        }
        
        // Get bookings with relationships
        $bookings = $query->with(['location', 'categories', 'categories.company'])
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Get all unique location IDs to fetch locations in bulk
        $locationIds = $bookings->pluck('location_id')->filter()->unique()->toArray();
        $locations = Location::whereIn('id', $locationIds)
            ->pluck('location_name', 'id')
            ->toArray();
        
        // Group appointments by appointment type (category) and location
        $groupedAppointments = [];
        
        foreach ($bookings as $booking) {
            $jsonData = json_decode($booking->json, true);
            $categoryName = $booking->categories?->name ?? 'Other';
            $locationId = $booking->location_id;
            
            // Get location name from relationship or from bulk fetch
            if ($booking->location && $booking->location->location_name) {
                $locationName = $booking->location->location_name;
            } elseif (isset($locations[$locationId])) {
                $locationName = $locations[$locationId];
            } elseif ($locationId) {
                // Fallback: fetch location directly if not found
                $location = Location::find($locationId);
                $locationName = $location ? $location->location_name : 'Unknown Location';
            } else {
                $locationName = 'Unknown Location';
            }
            
            // Create a key for grouping by location and category
            $groupKey = $locationId . '_' . ($booking->categories?->id ?? 'other');
            
            if (!isset($groupedAppointments[$groupKey])) {
                $groupedAppointments[$groupKey] = [
                    'location_name' => $locationName,
                    'location_id' => $locationId,
                    'appointment_type' => $categoryName,
                    'appointments' => []
                ];
            }
            
            $groupedAppointments[$groupKey]['appointments'][] = [
                'id' => $booking->id,
                'time' => Carbon::parse($booking->booking_date . ' ' . $booking->start_time)->format('d/m/Y h:iA'),
                'gender' => $jsonData['gender'] ?? $booking->gender ?? 'N/A',
                'name' => $booking->name,
                'created_on' => $booking->created_at->format('d/m/Y h:iA'),
                'company_package' => ($booking->categories?->company?->name ?? '') . ($booking->categories?->company?->name && $booking->categories?->name ? ' - ' : '') . ($booking->categories?->name ?? ''),
                'national_id' => $jsonData['nric'] ?? $jsonData['passport'] ?? $booking->refID,
                'phone' => $booking->phone,
                'location' => $locationName,
                'location_id' => $locationId,
                'status' => $booking->status ?? 'Pending',
                'remarks' => $booking->additional_comments ?? '',
            ];
        }
        
        // Convert to array and sort by location name, then appointment type
        $this->timelineAppointments = collect($groupedAppointments)->values()->sortBy(function($group) {
            return $group['location_name'] . '_' . $group['appointment_type'];
        })->toArray();
    }
    
    public function updatedTimelineGender()
    {
        // Reload timeline appointments when gender filter changes
        if ($this->viewMode === 'timeline') {
            $this->loadTimelineAppointments();
        }
    }
    
    public function previousDay()
    {
        $currentDate = Carbon::createFromFormat('d M Y', $this->selectedDay . ' ' . $this->selectedMonth . ' ' . $this->selectedYear);
        $previousDate = $currentDate->subDay();
        
        $this->selectedDay = $previousDate->day;
        $this->selectedMonth = $previousDate->format('M');
        $this->selectedYear = $previousDate->year;
        $this->selectedDayName = $previousDate->format('l');
        $this->selectedDate = $previousDate->format('Y-m-d');
        
        // Reload timeline appointments if in timeline view
        if ($this->viewMode === 'timeline') {
            $this->loadTimelineAppointments();
        }
    }
    
    public function nextDay()
    {
        $currentDate = Carbon::createFromFormat('d M Y', $this->selectedDay . ' ' . $this->selectedMonth . ' ' . $this->selectedYear);
        $nextDate = $currentDate->addDay();
        
        $this->selectedDay = $nextDate->day;
        $this->selectedMonth = $nextDate->format('M');
        $this->selectedYear = $nextDate->year;
        $this->selectedDayName = $nextDate->format('l');
        $this->selectedDate = $nextDate->format('Y-m-d');
        
        // Reload timeline appointments if in timeline view
        if ($this->viewMode === 'timeline') {
            $this->loadTimelineAppointments();
        }
    }
    
    public function updatedSelectedDate($value)
    {
        if ($value) {
            try {
                $date = Carbon::parse($value);
                $this->selectedDay = $date->day;
                $this->selectedMonth = $date->format('M');
                $this->selectedYear = $date->year;
                $this->selectedDayName = $date->format('l');
            } catch (\Exception $e) {
                // Handle error if date parsing fails
            }
        }
        
        // Reload timeline appointments when date changes
        if ($this->viewMode === 'timeline') {
            $this->loadTimelineAppointments();
        }
    }
    
    public function updatedNricFinPassport()
    {
        // Skip search if we're programmatically setting the value after member selection
        if ($this->skipNricSearch) {
            $this->skipNricSearch = false;
            return;
        }
        
        // Show dropdown and search as user types
        if (strlen($this->nricFinPassport) >= 2) {
            $this->searchMembers();
            $this->showNricDropdown = true;
        } else {
            $this->nricSearchResults = [];
            $this->showNricDropdown = false;
        }
    }
    
    public function updatedIsPrivateCustomer($value)
    {
        // If private customer is checked, clear company name
        if ($value) {
            $this->companyName = '';
            $this->companyId = null;
        }
    }
    
    public function searchMembers()
    {
        if (empty($this->nricFinPassport) || !$this->teamId) {
            $this->nricSearchResults = [];
            $this->showNricDropdown = false;
            return;
        }
        
        $searchTerm = trim($this->nricFinPassport);
        
        // Search members table by NRIC, mobile number, or name
        $this->nricSearchResults = Member::where('team_id', $this->teamId)
            ->where(function($query) use ($searchTerm) {
                $query->where('nric_fin', 'like', '%' . $searchTerm . '%')
                      ->orWhere('mobile_number', 'like', '%' . $searchTerm . '%')
                      ->orWhere('full_name', 'like', '%' . $searchTerm . '%');
            })
            ->with('company')
            ->limit(10)
            ->get()
            ->map(function($member) {
                return [
                    'id' => $member->id,
                    'nric_fin' => $member->nric_fin,
                    'full_name' => $member->full_name,
                    'mobile_number' => $member->mobile_number,
                    'email' => $member->email,
                    'salutation' => $member->salutation,
                    'date_of_birth' => $member->date_of_birth,
                    'gender' => $member->gender,
                    'nationality' => $member->nationality,
                    'mobile_country_code' => $member->mobile_country_code,
                    'company_name' => $member->company?->company_name ?? '',
                    'company_id' => $member->company_id,
                    'display' => $member->nric_fin . ' - ' . $member->full_name . ' - ' . $member->mobile_number,
                ];
            })
            ->toArray();
        
        // Show dropdown if we have results
        $this->showNricDropdown = count($this->nricSearchResults) > 0;
        
        // Log for debugging
        Log::info('Member search results', [
            'search_term' => $searchTerm,
            'team_id' => $this->teamId,
            'results_count' => count($this->nricSearchResults),
        ]);
    }
    
    public function selectMember($memberId)
    {
        // Re-fetch the member from database to ensure fresh data
        $member = Member::with('company')->find($memberId);
        
        if ($member) {
            // Log for debugging
            Log::info('Selecting member', [
                'member_id' => $memberId,
                'nric' => $member->nric_fin,
                'name' => $member->full_name,
                'email' => $member->email,
                'mobile' => $member->mobile_number,
                'company_id' => $member->company_id,
                'company_name' => $member->company?->company_name,
            ]);
            
            // Set flag to skip the search when we set nricFinPassport
            $this->skipNricSearch = true;
            
            // Fill all form fields with member data
            $this->nricFinPassport = $member->nric_fin ?? '';
            $this->fullName = $member->full_name ?? '';
            // Set country code from member or default to 65 (Singapore) - editable field
            $this->mobileCountryCode = $member->mobile_country_code ?? '65';
            // Set phone number from member
            $this->mobileNumber = $member->mobile_number ?? '';
            $this->emailAddress = $member->email ?? '';
            $this->title = $member->salutation ?? 'Mr';
            
            // Format date of birth properly for HTML5 date input (Y-m-d format)
            if ($member->date_of_birth) {
                try {
                    $dob = Carbon::parse($member->date_of_birth);
                    $this->dateOfBirth = $dob->format('Y-m-d'); // HTML5 date input expects Y-m-d
                } catch (\Exception $e) {
                    $this->dateOfBirth = '';
                }
            } else {
                $this->dateOfBirth = '';
            }
            
            $this->gender = $member->gender ?? 'Male';
            $this->nationality = $member->nationality ?? 'Singaporean';
            
            // Safe null check for company relationship
            $this->companyName = $member->company?->company_name ?? '';
            $this->companyId = $member->company_id;
            
            // Set location if member has one
            if ($member->location_id) {
                $this->locationId = $member->location_id;
            }
            
            // Hide dropdown after selection
            $this->showNricDropdown = false;
            $this->nricSearchResults = [];
            
            // Log the values that were set
            Log::info('Form fields populated', [
                'nricFinPassport' => $this->nricFinPassport,
                'fullName' => $this->fullName,
                'mobileNumber' => $this->mobileNumber,
                'emailAddress' => $this->emailAddress,
                'gender' => $this->gender,
                'dateOfBirth' => $this->dateOfBirth,
                'companyName' => $this->companyName,
            ]);
            
            // Dispatch browser event to notify that fields have been updated
            $this->dispatch('member-selected', [
                'memberName' => $this->fullName,
                'memberData' => [
                    'nric' => $this->nricFinPassport,
                    'name' => $this->fullName,
                    'mobile' => $this->mobileNumber,
                    'email' => $this->emailAddress,
                    'company' => $this->companyName,
                    'dateOfBirth' => $this->dateOfBirth,
                    'salutation' => $this->title,
                    'gender' => $this->gender,
                    'nationality' => $this->nationality,
                    'mobileCountryCode' => $this->mobileCountryCode,
                    'mobile_country_code' => $this->mobileCountryCode,
                ]
            ]);
            
            // Force component to re-render and update DOM
            $this->dispatch('$refresh');
        } else {
            Log::warning('Member not found', ['member_id' => $memberId]);
        }
    }
    
    #[On('closeNricDropdown')]
    public function closeNricDropdown()
    {
        $this->showNricDropdown = false;
    }
    
    public function getStatusListProperty()
    {
        return [
            [
                'name' => 'Reserved',
                'value' => Booking::STATUS_RESERVED,
                'color' => '#60a5fa', // Blue
            ],
            [
                'name' => 'SMSCalled',
                'value' => Booking::STATUS_SMSCALLED,
                'color' => '#a855f7', // Purple
            ],
            [
                'name' => 'Arrived',
                'value' => Booking::STATUS_ARRIVED,
                'color' => '#eab308', // Yellow
            ],
            [
                'name' => 'Cancelled',
                'value' => Booking::STATUS_CANCELLED,
                'color' => '#6b7280', // Gray
            ],
            [
                'name' => 'NoShow',
                'value' => Booking::STATUS_NOSHOW,
                'color' => '#ef4444', // Red
            ],
            [
                'name' => 'Completed',
                'value' => Booking::STATUS_COMPLETED,
                'color' => '#22c55e', // Green
            ],
        ];
    }
    
    public function bookAppointment($clinicId, $appointmentType, $timeSlot)
    {
        // Open booking modal for new appointment
        $clinic = collect($this->availableClinics)->firstWhere('id', $clinicId);
        
        if ($clinic) {
            $this->selectedAppointment = null;
            $this->selectedAppointmentType = $appointmentType;
            $this->locationId = $clinic['id'];
            $this->dateTime = $this->selectedDay . '/' . $this->selectedMonth . '/' . $this->selectedYear . ' ' . $timeSlot;
            $this->showBookingModal = true;
            $this->activeTab = 'booking-details';
            
            // Reset form fields for new booking
            $this->nricFinPassport = '';
            $this->fullName = '';
            $this->gender = 'Male';
            // Set default country code to 65 (Singapore) - editable field
            $this->mobileCountryCode = '65';
            $this->mobileNumber = '';
            $this->emailAddress = '';
            $this->dateOfBirth = '';
            $this->package = '';
            $this->bookingStatus = 'Reserved';
            $this->isVip = false;
            $this->isPrivateCustomer = false;
            $this->companyName = '';
            $this->companyId = null;
        }
    }
    
    public function getSelectedClinicsData()
    {
        return collect($this->availableClinics)->whereIn('id', $this->selectedClinics)->values()->toArray();
    }
    
    public function getAvailableClinicsForDropdown()
    {
        return collect($this->availableClinics)->whereNotIn('id', $this->selectedClinics)->values()->toArray();
    }
    
    public function getTimeSlotsForDisplay()
    {
        $result = [];
        
        // Return empty if no clinics or appointment types selected
        if (empty($this->selectedClinics) || empty($this->selectedAppointmentTypes)) {
            return $result;
        }
        
        // Create date string for querying bookings
        $dateStr = $this->selectedYear . '-' . Carbon::createFromFormat('M', $this->selectedMonth)->format('m') . '-' . sprintf('%02d', $this->selectedDay);
        
        // Create Carbon date for selected date
        $selectedDate = Carbon::createFromDate($this->selectedYear, Carbon::createFromFormat('M', $this->selectedMonth)->month, $this->selectedDay);
        $dayOfWeek = $selectedDate->format('l'); // e.g., 'Monday', 'Tuesday' (capitalized)
        
        foreach ($this->selectedClinics as $clinicId) {
            $clinic = collect($this->availableClinics)->firstWhere('id', $clinicId);
            
            if ($clinic) {
                // Only process appointment types that are turned ON (in selectedAppointmentTypes)
                foreach ($this->selectedAppointmentTypes as $appointmentType) {
                    // Get the category ID for this appointment type
                    $category = Category::where('team_id', $this->teamId)
                        ->where('name', $appointmentType)
                        ->first();
                    
                    // Get account settings for location slots for this location
                    $accountSetting = AccountSetting::where('team_id', $this->teamId)
                        ->where('location_id', $clinicId)
                        ->where('slot_type', AccountSetting::LOCATION_SLOT)
                        ->first();
                    
                    // Get slot period from account settings (default to 30 minutes)
                    $slotPeriod = $accountSetting->slot_period ?? 30;
                    
                    // Get business hours from account settings
                    $businessHours = [];
                    if ($accountSetting && $accountSetting->business_hours) {
                        $businessHours = json_decode($accountSetting->business_hours, true);
                    }
                    
                    // Generate time slots based on selected date's day of week
                    $timeSlots = [];
                    
                    if (!empty($businessHours)) {
                        // Find business hours for the selected day of week
                        $dayBusinessHours = null;
                        foreach ($businessHours as $dayHours) {
                            // Match day name (can be "Monday" or "monday")
                            if (strtolower($dayHours['day']) === strtolower($dayOfWeek)) {
                                $dayBusinessHours = $dayHours;
                                break;
                            }
                        }
                        
                        // Check if day is open (is_closed can be 0, "open", or "closed")
                        $isOpen = false;
                        if ($dayBusinessHours && isset($dayBusinessHours['is_closed'])) {
                            $isClosedValue = $dayBusinessHours['is_closed'];
                            $isOpen = ($isClosedValue === 0 || $isClosedValue === 'open' || $isClosedValue === '0');
                        }
                        
                        // If business hours found and not closed, generate slots
                        if ($dayBusinessHours && $isOpen) {
                            // Generate slots for main business hours
                            $mainSlots = AccountSetting::generateSlots(
                                $dayBusinessHours['start_time'],
                                $dayBusinessHours['end_time'],
                                $slotPeriod
                            );
                            
                            // Convert slot format from "09:00 AM-09:30 AM" to "9:00AM" for matching
                            foreach ($mainSlots as $slotRange => $slotValue) {
                                // Extract start time from range (e.g., "09:00 AM-09:30 AM" -> "09:00 AM")
                                $startTimeStr = trim(explode('-', $slotRange)[0]);
                                // Convert to format used by bookings: "9:00AM"
                                try {
                                    $startTime = Carbon::parse($startTimeStr);
                                    $timeKey = $startTime->format('g:iA');
                                    $timeSlots[$timeKey] = 'empty';
                                } catch (\Exception $e) {
                                    // Skip invalid time formats
                                    continue;
                                }
                            }
                            
                            // Generate slots for day intervals if any
                            if (!empty($dayBusinessHours['day_interval'])) {
                                foreach ($dayBusinessHours['day_interval'] as $interval) {
                                    $intervalSlots = AccountSetting::generateSlots(
                                        $interval['start_time'],
                                        $interval['end_time'],
                                        $slotPeriod
                                    );
                                    foreach ($intervalSlots as $slotRange => $slotValue) {
                                        // Extract start time from range
                                        $startTimeStr = trim(explode('-', $slotRange)[0]);
                                        try {
                                            $startTime = Carbon::parse($startTimeStr);
                                            $timeKey = $startTime->format('g:iA');
                                            $timeSlots[$timeKey] = 'empty';
                                        } catch (\Exception $e) {
                                            // Skip invalid time formats
                                            continue;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // Fallback to default 9AM-5PM with configured slot period
                        $startTime = Carbon::createFromTime(9, 0);
                        $endTime = Carbon::createFromTime(17, 0);
                        
                        while ($startTime < $endTime) {
                            $timeSlots[$startTime->format('g:iA')] = 'empty';
                            $startTime->addMinutes($slotPeriod);
                        }
                    }
                    
                    // Fetch bookings for this location, category, and date
                    $bookings = [];
                    if ($category && !empty($timeSlots)) {
                        $bookings = Booking::where('team_id', $this->teamId)
                            ->where('location_id', $clinicId)
                            ->where('category_id', $category->id)
                            ->where('booking_date', $dateStr)
                            ->get()
                            ->keyBy(function($booking) {
                                return Carbon::parse($booking->start_time)->format('g:iA');
                            });
                    }
                    
                    // Populate time slots with booking data
                    $finalTimeSlots = [];
                    foreach ($timeSlots as $time => $status) {
                        if (isset($bookings[$time])) {
                            $booking = $bookings[$time];
                            $finalTimeSlots[$time] = [
                                'status' => $booking->status,
                                'booking_id' => $booking->id,
                                'patient_name' => $booking->name,
                                'nric' => $booking->refID,
                            ];
                        } else {
                            $finalTimeSlots[$time] = ['status' => 'empty'];
                        }
                    }
                    
                    if (!empty($finalTimeSlots)) {
                        $result[] = [
                            'clinic_id' => $clinic['id'],
                            'clinic_name' => $clinic['name'],
                            'appointment_type' => $appointmentType,
                            'category_id' => $category ? $category->id : null,
                            'time_slots' => $finalTimeSlots
                        ];
                    }
                }
            }
        }
        
        return $result;
    }
    
    public function submitAppointment()
    {
        // Validate form data
        $this->validate([
            'nricFinPassport' => 'required|string|max:50',
            'fullName' => 'required|string|max:255',
            'mobileNumber' => 'required|string|max:20',
            'emailAddress' => 'required|email|max:255',
            'dateOfBirth' => 'required|string',
            'gender' => 'required|string',
            'nationality' => 'required|string',
            'locationId' => 'required|integer|exists:locations,id',
            'dateTime' => 'required|string',
        ], [
            'nricFinPassport.required' => 'NRIC/FIN/Passport is required',
            'fullName.required' => 'Full name is required',
            'mobileNumber.required' => 'Mobile number is required',
            'emailAddress.required' => 'Email address is required',
            'emailAddress.email' => 'Please enter a valid email address',
            'dateOfBirth.required' => 'Date of birth is required',
            'locationId.required' => 'Location is required',
            'dateTime.required' => 'Date and time is required',
        ]);
        
        try {
            // Parse date and time
            $dateTimeStr = $this->dateTime;
            // Expected format: "8/Dec/2025 9:00AM"
            $dateTimeParts = explode(' ', $dateTimeStr);
            $datePart = $dateTimeParts[0] ?? '';
            $timePart = $dateTimeParts[1] ?? '';
            
            // Parse the date
            $bookingDate = Carbon::parse(str_replace('/', ' ', $datePart))->format('Y-m-d');
            $startTime = Carbon::parse($timePart)->format('H:i:s');
            $endTime = Carbon::parse($timePart)->addMinutes(30)->format('H:i:s');
            
            // Parse date of birth
            $dob = null;
            if ($this->dateOfBirth) {
                try {
                    // Try Y-m-d format first (from date input)
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dateOfBirth)) {
                        $dob = $this->dateOfBirth;
                    } else {
                        // Try d/m/Y format
                        $dob = Carbon::createFromFormat('d/m/Y', $this->dateOfBirth)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $dob = null;
                }
            }
            
            // Get the category ID for the selected appointment type
            $category = Category::where('team_id', $this->teamId)
                ->where('name', $this->selectedAppointmentType)
                ->first();
            
            // Prepare additional data as JSON
            $jsonData = [
                'nric' => $this->nricFinPassport,
                'gender' => $this->gender,
                'nationality' => $this->nationality,
                'title' => $this->title,
                'is_vip' => $this->isVip,
                'is_private_customer' => $this->isPrivateCustomer,
            ];
            
            // Create the booking
            $booking = Booking::create([
                'team_id' => $this->teamId,
                'refID' => $this->nricFinPassport,
                'name' => $this->fullName,
                'title' => $this->title,
                'identification_type' => $this->identificationType,
                'phone' => $this->mobileNumber,
                'email' => $this->emailAddress,
                'date_of_birth' => $dob,
                'gender' => $this->gender,
                'nationality' => $this->nationality,
                'booking_date' => $bookingDate,
                'booking_time' => $startTime,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'location_id' => $this->locationId,
                'category_id' => $category ? $category->id : null,
                'company_id' => $this->companyId,
                'additional_comments' => $this->additionalComments,
                'payment_status' => $this->paymentStatus,
                'is_vip' => $this->isVip,
                'is_private_customer' => $this->isPrivateCustomer,
                'status' => $this->bookingStatus ?? Booking::STATUS_RESERVED,
                'json' => json_encode($jsonData),
                'created_by' => Auth::id(),
            ]);
            
            // Store activity log for appointment booking creation
            $userAuth = Auth::user();
            
            // Get dynamic data from booking and related models
            $location = Location::find($this->locationId);
            $locationName = $location ? $location->location_name : 'N/A';
            $categoryName = $category ? $category->name : ($this->selectedAppointmentType ?? 'N/A');
            $formattedDateTime = Carbon::parse($bookingDate . ' ' . $startTime)->format('d/m/Y h:iA');
            $phoneWithCode = !empty($this->mobileCountryCode) ? "+{$this->mobileCountryCode} {$this->mobileNumber}" : $this->mobileNumber;
            
            // Build comprehensive log text with all dynamic data
            $logText = "Appointment Booking Created - Patient: {$this->fullName} (NRIC: {$this->nricFinPassport}), " .
                      "Appointment Type: {$categoryName}, " .
                      "Location: {$locationName}, " .
                      "Date/Time: {$formattedDateTime}, " .
                      "Phone: {$phoneWithCode}, " .
                      "Email: {$this->emailAddress}";
            
            // Build detailed remark with all dynamic fields
            $remark = "Status: " . ($this->bookingStatus ?? Booking::STATUS_RESERVED) . ", " .
                     "VIP: " . ($this->isVip ? 'Yes' : 'No') . ", " .
                     "Private Customer: " . ($this->isPrivateCustomer ? 'Yes' : 'No') . ", " .
                     "Gender: {$this->gender}, " .
                     "Nationality: {$this->nationality}, " .
                     "DOB: " . ($dob ? Carbon::parse($dob)->format('d/m/Y') : 'N/A') . ", " .
                     "Title: {$this->title}";
            
            if ($this->companyName) {
                $remark .= ", Company: {$this->companyName}";
            }
            
            if ($this->additionalComments) {
                $remark .= ", Comments: {$this->additionalComments}";
            }
            
            ActivityLog::storeLog(
                $this->teamId,
                Auth::id(),
                $booking->id, // call_book_id (using booking ID)
                null, // queues_storage_id
                $logText,
                $this->locationId,
                ActivityLog::APPOINTMENT_BOOKING, // type
                $remark, // remark with all dynamic details
                $userAuth, // user details
                null // country_id
            );
            
            // Show success message
            session()->flash('success', 'Appointment booked successfully!');
            
            // Close modal and reset form
            $this->closeBookingModal();
            $this->resetForm();
            
            // Reload appointments
            $this->loadAppointments();
            
        } catch (\Exception $e) {
            // Show error message
            session()->flash('error', 'Failed to book appointment: ' . $e->getMessage());
        }
    }
    
    public function updateAppointment()
    {
        // Similar validation
        $this->validate([
            'nricFinPassport' => 'required|string|max:50',
            'fullName' => 'required|string|max:255',
            'mobileNumber' => 'required|string|max:20',
            'emailAddress' => 'required|email|max:255',
            'dateOfBirth' => 'required|string',
            'gender' => 'required|string',
            'nationality' => 'required|string',
            'locationId' => 'required|integer|exists:locations,id',
        ]);
        
        try {
            if ($this->selectedAppointment) {
                // Find and update the booking with relationships
                $booking = Booking::with('categories')->find($this->selectedAppointment['id']);
                
                if ($booking) {
                // Parse date of birth
                $dob = null;
                if ($this->dateOfBirth) {
                    try {
                        // Try Y-m-d format first (from date input)
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dateOfBirth)) {
                            $dob = $this->dateOfBirth;
                        } else {
                            // Try d/m/Y format
                            $dob = Carbon::createFromFormat('d/m/Y', $this->dateOfBirth)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $dob = $booking->date_of_birth;
                    }
                }
                    
                    // Prepare JSON data
                    $existingJson = json_decode($booking->json, true) ?? [];
                    $jsonData = array_merge($existingJson, [
                        'nric' => $this->nricFinPassport,
                        'gender' => $this->gender,
                        'nationality' => $this->nationality,
                        'title' => $this->title,
                        'is_vip' => $this->isVip,
                        'is_private_customer' => $this->isPrivateCustomer,
                    ]);
                    
                    // Update the booking
                    $booking->update([
                        'refID' => $this->nricFinPassport,
                        'name' => $this->fullName,
                        'title' => $this->title,
                        'identification_type' => $this->identificationType,
                        'phone' => $this->mobileNumber,
                        'email' => $this->emailAddress,
                        'date_of_birth' => $dob,
                        'gender' => $this->gender,
                        'nationality' => $this->nationality,
                        'location_id' => $this->locationId,
                        'company_id' => $this->companyId,
                        'additional_comments' => $this->additionalComments,
                        'payment_status' => $this->paymentStatus,
                        'is_vip' => $this->isVip,
                        'is_private_customer' => $this->isPrivateCustomer,
                        'status' => $this->bookingStatus,
                        'json' => json_encode($jsonData),
                    ]);
                    
                    // Store activity log for appointment booking update
                    $userAuth = Auth::user();
                    
                    // Get dynamic data from booking and related models
                    $location = Location::find($this->locationId);
                    $locationName = $location ? $location->location_name : 'N/A';
                    $categoryName = $booking->categories ? $booking->categories->name : 'N/A';
                    $bookingDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->start_time)->format('d/m/Y h:iA');
                    $phoneWithCode = !empty($this->mobileCountryCode) ? "+{$this->mobileCountryCode} {$this->mobileNumber}" : $this->mobileNumber;
                    
                    // Get old values for comparison (if needed)
                    $oldName = $booking->getOriginal('name') ?? $booking->name;
                    $oldStatus = $booking->getOriginal('status') ?? $booking->status;
                    $oldPhone = $booking->getOriginal('phone') ?? $booking->phone;
                    $oldEmail = $booking->getOriginal('email') ?? $booking->email;
                    
                    // Build comprehensive log text with all dynamic data
                    $logText = "Appointment Booking Updated - Patient: {$this->fullName} (NRIC: {$this->nricFinPassport}), " .
                              "Appointment Type: {$categoryName}, " .
                              "Location: {$locationName}, " .
                              "Date/Time: {$bookingDateTime}, " .
                              "Phone: {$phoneWithCode}, " .
                              "Email: {$this->emailAddress}";
                    
                    // Build detailed remark with all dynamic fields and changes
                    $remark = "Status: {$this->bookingStatus}" . 
                             ($oldStatus != $this->bookingStatus ? " (Changed from: {$oldStatus})" : "") . ", " .
                             "VIP: " . ($this->isVip ? 'Yes' : 'No') . ", " .
                             "Private Customer: " . ($this->isPrivateCustomer ? 'Yes' : 'No') . ", " .
                             "Gender: {$this->gender}, " .
                             "Nationality: {$this->nationality}, " .
                             "DOB: " . ($dob ? Carbon::parse($dob)->format('d/m/Y') : ($booking->date_of_birth ? Carbon::parse($booking->date_of_birth)->format('d/m/Y') : 'N/A')) . ", " .
                             "Title: {$this->title}";
                    
                    if ($this->companyName) {
                        $remark .= ", Company: {$this->companyName}";
                    }
                    
                    if ($this->additionalComments) {
                        $remark .= ", Comments: {$this->additionalComments}";
                    }
                    
                    // Add change indicators if values changed
                    $changes = [];
                    if ($oldName != $this->fullName) {
                        $changes[] = "Name: {$oldName} → {$this->fullName}";
                    }
                    if ($oldPhone != $this->mobileNumber) {
                        $changes[] = "Phone: {$oldPhone} → {$this->mobileNumber}";
                    }
                    if ($oldEmail != $this->emailAddress) {
                        $changes[] = "Email: {$oldEmail} → {$this->emailAddress}";
                    }
                    
                    if (!empty($changes)) {
                        $remark .= " | Changes: " . implode(", ", $changes);
                    }
                    
                    ActivityLog::storeLog(
                        $this->teamId,
                        Auth::id(),
                        $booking->id, // call_book_id (using booking ID)
                        null, // queues_storage_id
                        $logText,
                        $this->locationId,
                        ActivityLog::APPOINTMENT_BOOKING, // type
                        $remark, // remark with all dynamic details
                        $userAuth, // user details
                        null // country_id
                    );
                    
                    session()->flash('success', 'Appointment updated successfully!');
                    
                    // Close modal and reload
                    $this->closeBookingModal();
                    $this->resetForm();
                    $this->loadAppointments();
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update appointment: ' . $e->getMessage());
        }
    }
    
    public function resetForm()
    {
        $this->nricFinPassport = '';
        $this->fullName = '';
        $this->title = 'Mr';
        // Always set country code to 65 (Singapore) - readonly field
        $this->mobileCountryCode = '65';
        $this->mobileNumber = '';
        $this->emailAddress = '';
        $this->dateOfBirth = '';
        $this->gender = 'Male';
        $this->nationality = 'Singaporean';
        $this->locationId = null;
        $this->companyName = '';
        $this->companyId = null;
        $this->additionalComments = '';
        $this->paymentStatus = 'Pending';
        $this->isVip = false;
        $this->isPrivateCustomer = false;
        $this->bookingStatus = 'Reserved';
    }
    
    public function render()
    {
        return view('livewire.appointment-booking-module');
    }
}
