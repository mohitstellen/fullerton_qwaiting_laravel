<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Location;
use App\Models\Category;
use App\Models\Level;
use App\Models\Booking;
use App\Models\Team;
use App\Models\Member;
use App\Models\Company;
use Carbon\Carbon;

#[Layout('components.layouts.appointment-layout')]
#[Title('Appointment Booking Module')]
class AppointmentBookingModule extends Component
{
    // Selected clinics
    public $selectedClinics = [1, 2]; // Array of clinic IDs
    
    // Clinics with time slots data
    public $clinics = [];
    
    // All available clinics for dropdown
    public $availableClinics = [];
    
    // Appointment types based on selected clinics
    public $appointmentTypes = [];
    
    // Selected appointment types (array of appointment type names)
    public $selectedAppointmentTypes = ['Draw My Blood'];
    
    // Date navigation
    public $selectedDay = 5;
    public $selectedMonth = 'Dec';
    public $selectedYear = 2025;
    public $selectedDayName = 'Friday';
    
    // View mode
    public $viewMode = 'calendar'; // 'calendar' or 'timeline'
    
    // Search filters form
    public $showSearchFilters = false;
    public $searchNric = '';
    public $searchMobile = '';
    public $searchEmail = '';
    public $searchCompany = '';
    
    // Sample appointments data
    public $appointments = [];
    
    // Modal state
    public $showBookingModal = false;
    public $selectedAppointment = null;
    public $selectedAppointmentType = 'Doctor Review Consult';
    
    // Form fields for booking modal
    public $identificationType = 'NRIC / FIN';
    public $nricFinPassport = '';
    public $title = 'Mr';
    public $fullName = '';
    public $dateOfBirth = '';
    public $gender = 'Male';
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
    
    // NRIC Search Results
    public $nricSearchResults = [];
    public $showNricDropdown = false;
    
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
        
        // Load available locations for the form
        $this->loadAvailableLocations();
        
        // Load nationalities from config
        $this->availableNationalities = config('nationalities', []);
        
        // Initialize clinics with time slots
        $this->loadClinics();
        
        // Initialize sample appointments data
        $this->loadAppointments();
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
            
            // Generate time slots for each category (9AM to 5PM, 30min intervals)
            foreach ($categories as $category) {
                $timeSlots = [];
                $startTime = Carbon::createFromTime(9, 0);
                $endTime = Carbon::createFromTime(17, 0);
                
                while ($startTime < $endTime) {
                    $timeSlots[$startTime->format('g:iA')] = 'empty';
                    $startTime->addMinutes(30);
                }
                
                $appointmentTypes[$category->name] = $timeSlots;
            }
            
            $this->availableClinics[] = [
                'id' => $location->id,
                'name' => $location->location_name,
                'appointmentTypes' => $appointmentTypes
            ];
        }
        
        // Initialize with first 2 clinics selected if available
        if (count($this->availableClinics) > 0) {
            $this->selectedClinics = array_slice(array_column($this->availableClinics, 'id'), 0, 2);
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
        $this->loadAppointments();
    }
    
    public function openBookingModal($appointmentId)
    {
        // Find appointment by ID
        $appointment = collect($this->appointments)->firstWhere('id', $appointmentId);
        
        if ($appointment) {
            $this->selectedAppointment = $appointment;
            
            // Fetch the actual booking from database to get all fields
            $booking = Booking::find($appointmentId);
            
            if ($booking) {
                // Populate form fields from booking data
                $this->nricFinPassport = $booking->refID ?? '';
                $this->fullName = $booking->name ?? '';
                $this->title = $booking->title ?? 'Mr';
                $this->gender = $booking->gender ?? 'Male';
                $this->mobileNumber = $booking->phone ?? '';
                $this->emailAddress = $booking->email ?? ''; // Use actual email
                $this->dateOfBirth = $booking->date_of_birth ? Carbon::parse($booking->date_of_birth)->format('Y-m-d') : '';
                $this->nationality = $booking->nationality ?? 'Singaporean';
                $this->dateTime = $appointment['time'];
                $this->locationId = $booking->location_id ?? null;
                $this->companyName = $booking->company->company_name ?? '';
                $this->companyId = $booking->company_id;
                $this->additionalComments = $booking->additional_comments ?? '';
                $this->paymentStatus = $booking->payment_status ?? 'Pending';
                $this->isVip = $booking->is_vip ?? false;
                $this->isPrivateCustomer = $booking->is_private_customer ?? false;
                $this->bookingStatus = $booking->status;
                $this->selectedAppointmentType = $booking->categories->name ?? 'Doctor Review Consult';
                $this->package = $appointment['company_package'] ?? '';
            }
            
            $this->showBookingModal = true;
            $this->activeTab = 'booking-details';
        }
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
    }
    
    
    public function closeBookingModal()
    {
        $this->showBookingModal = false;
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
    }
    
    public function previousDay()
    {
        $currentDate = Carbon::createFromFormat('d M Y', $this->selectedDay . ' ' . $this->selectedMonth . ' ' . $this->selectedYear);
        $previousDate = $currentDate->subDay();
        
        $this->selectedDay = $previousDate->day;
        $this->selectedMonth = $previousDate->format('M');
        $this->selectedYear = $previousDate->year;
        $this->selectedDayName = $previousDate->format('l');
    }
    
    public function nextDay()
    {
        $currentDate = Carbon::createFromFormat('d M Y', $this->selectedDay . ' ' . $this->selectedMonth . ' ' . $this->selectedYear);
        $nextDate = $currentDate->addDay();
        
        $this->selectedDay = $nextDate->day;
        $this->selectedMonth = $nextDate->format('M');
        $this->selectedYear = $nextDate->year;
        $this->selectedDayName = $nextDate->format('l');
    }
    
    public function updatedNricFinPassport()
    {
        // Show dropdown and search as user types
        if (strlen($this->nricFinPassport) >= 2) {
            $this->searchMembers();
            $this->showNricDropdown = true;
        } else {
            $this->nricSearchResults = [];
            $this->showNricDropdown = false;
        }
    }
    
    public function searchMembers()
    {
        if (empty($this->nricFinPassport) || !$this->teamId) {
            $this->nricSearchResults = [];
            return;
        }
        
        // Search members table by NRIC, mobile number, or name
        $this->nricSearchResults = Member::where('team_id', $this->teamId)
            ->where(function($query) {
                $query->where('nric_fin', 'like', '%' . $this->nricFinPassport . '%')
                      ->orWhere('mobile_number', 'like', '%' . $this->nricFinPassport . '%')
                      ->orWhere('full_name', 'like', '%' . $this->nricFinPassport . '%');
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
                    'company_name' => $member->company->company_name ?? '',
                    'company_id' => $member->company_id,
                    'display' => $member->nric_fin . ' - ' . $member->full_name . ' - ' . $member->mobile_number,
                ];
            })
            ->toArray();
    }
    
    public function selectMember($memberId)
    {
        // Re-fetch the member from database to ensure fresh data
        $member = Member::with('company')->find($memberId);
        
        if ($member) {
            // Fill all form fields with member data
            $this->nricFinPassport = $member->nric_fin ?? '';
            $this->fullName = $member->full_name ?? '';
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
            $this->companyName = $member->company->company_name ?? '';
            $this->companyId = $member->company_id;
            
            // Set location if member has one
            if ($member->location_id) {
                $this->locationId = $member->location_id;
            }
            
            // Hide dropdown after selection
            $this->showNricDropdown = false;
            $this->nricSearchResults = [];
        }
    }
    
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
        
        // Create date string for querying bookings
        $dateStr = $this->selectedYear . '-' . Carbon::createFromFormat('M', $this->selectedMonth)->format('m') . '-' . sprintf('%02d', $this->selectedDay);
        
        foreach ($this->selectedClinics as $clinicId) {
            $clinic = collect($this->availableClinics)->firstWhere('id', $clinicId);
            
            if ($clinic) {
                foreach ($this->selectedAppointmentTypes as $appointmentType) {
                    if (isset($clinic['appointmentTypes'][$appointmentType])) {
                        // Get the category ID for this appointment type
                        $category = Category::where('team_id', $this->teamId)
                            ->where('name', $appointmentType)
                            ->first();
                        
                        // Fetch bookings for this location, category, and date
                        $bookings = [];
                        if ($category) {
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
                        $timeSlots = [];
                        foreach ($clinic['appointmentTypes'][$appointmentType] as $time => $status) {
                            if (isset($bookings[$time])) {
                                $booking = $bookings[$time];
                                $timeSlots[$time] = [
                                    'status' => $booking->status,
                                    'booking_id' => $booking->id,
                                    'patient_name' => $booking->name,
                                    'nric' => $booking->refID,
                                ];
                            } else {
                                $timeSlots[$time] = ['status' => 'empty'];
                            }
                        }
                        
                        $result[] = [
                            'clinic_id' => $clinic['id'],
                            'clinic_name' => $clinic['name'],
                            'appointment_type' => $appointmentType,
                            'category_id' => $category ? $category->id : null,
                            'time_slots' => $timeSlots
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
                // Find and update the booking
                $booking = Booking::find($this->selectedAppointment['id']);
                
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
