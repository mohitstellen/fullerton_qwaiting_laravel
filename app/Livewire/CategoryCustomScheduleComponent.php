<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\AccountSetting;
use App\Models\CustomSlot;
use App\Models\Category;
use App\Models\SiteDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class CategoryCustomScheduleComponent extends Component
{
    #[Title('Category Custom Schedule')]

    public $level, $categoryId;
    public $teamId;
    public $locationId;
    public $record;
    public $type;
    public $activeTab = 'new_schedule';
    
    // Left section properties
    public $schedulePattern = ''; // default_duration or custom
    public $appointmentTypeId = null; // For storing selected appointment type category_id
    public $appointmentSearch = ''; // For search input
    public $filteredCategories = []; // For filtered search results
    public $durationMinutes = 30; // Initialize as integer
    public $capacity = 1; // Initialize as integer
    public $startDate;
    public $endDate;
    public $businessClosures = 'open'; // open or close
    public $availableCategories = []; // For appointment type dropdown
    public $availableLocations = []; // For location dropdown
    public $selectedLocationId = null; // For selected location
    
    // Right section properties - weekly time slots
    public $weeklySlots = [];
    public $scheduleList = [];
    public $scheduleListFilter = ''; // For filtering schedule list by appointment type
    public $scheduleFilterSuggestions = []; // For autocomplete suggestions
    public $editingScheduleId = null; // Track if we're editing an existing schedule
    
    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Schedule Settings')) {
            abort(403);
        }
        
        $this->teamId = tenant('id');
        $this->type = AccountSetting::CATEGORY_SLOT;
        
        // Get category record if categoryId exists
        if ($this->categoryId) {
            $this->record = Category::find($this->categoryId);
        }
        
        // Set default dates to selected_date from session or request
        $selectedDate = request()->get('selected_date', Carbon::now()->format('Y-m-d'));
        $this->startDate = $selectedDate;
        $this->endDate = $selectedDate;
        
        // Load available locations
        $this->loadAvailableLocations();
    
        $this->initializeWeeklySlots();
        $this->loadExistingSchedules();
    }
    
    public function initializeWeeklySlots()
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        foreach ($days as $index => $day) {
            $this->weeklySlots[$index] = [
                'day' => $day,
                'enabled' => false,
                'slots' => []
            ];
        }
    }
    
    public function loadAvailableLocations()
    {
        // Load locations for the current team
        $this->availableLocations = \App\Models\Location::where('team_id', $this->teamId)
            ->pluck('location_name', 'id')
            ->toArray();
    }
    
    public function loadAvailableCategories()
    {
        if ($this->selectedLocationId) {
            $this->availableCategories = Category::getFirstCategory($this->teamId, $this->selectedLocationId);
            $this->filteredCategories = $this->availableCategories;
        } else {
            $this->availableCategories = [];
            $this->filteredCategories = [];
        }
    }
    
    public function updatedAppointmentSearch($value)
    {
        if (empty($value)) {
            $this->filteredCategories = $this->availableCategories;
        } else {
            $this->filteredCategories = collect($this->availableCategories)
                ->filter(function ($name, $id) use ($value) {
                    return stripos($name, $value) !== false;
                })
                ->toArray();
        }
    }
    
    public function selectAppointmentType($id, $name)
    {
        $this->appointmentTypeId = $id;
        $this->appointmentSearch = $name;
        $this->filteredCategories = []; // Hide dropdown after selection
    }
    
    public function updatedSelectedLocationId($value)
    {
        // When location is selected, load categories for that location
        $this->loadAvailableCategories();
        // Reset appointment type when location changes (since categories are location-specific)
        $this->appointmentTypeId = null;
        $this->appointmentSearch = '';
        $this->filteredCategories = [];
        
        // Load schedules for the new location
        $this->loadExistingSchedules();
        
        // Don't reset time slots when location changes
        // Users should be able to keep their schedule configuration
    }
    
    public function loadExistingSchedules()
    {
        // Only load schedules if we have locationId
        if (!$this->selectedLocationId && $this->activeTab != 'schedule_list') {
            $this->scheduleList = [];
            return;
        }else{
            $this->selectedLocationId = Session::get('selectedLocation');
        }
        
        // Load all existing custom schedules from database for the location (not filtered by appointment type)
        $customSlots = CustomSlot::where('team_id', $this->teamId)
            ->where('location_id', $this->selectedLocationId)
            ->where('slots_type', $this->type)
            ->orderBy('selected_date')
            ->get();
            
        $this->scheduleList = [];
        
        foreach ($customSlots as $slot) {
            $businessHours = json_decode($slot->business_hours, true);
            
            // Get appointment type name
            $appointmentType = Category::find($slot->category_id);
            
            $this->scheduleList[] = [
                'id' => $slot->id,
                'selected_date' => $slot->selected_date,
                'is_closed' => $businessHours[0]['is_closed'] ?? 'open',
                'start_time' => $businessHours[0]['start_time'] ?? '',
                'end_time' => $businessHours[0]['end_time'] ?? '',
                'day_interval' => $businessHours[0]['day_interval'] ?? [],
                'schedule_pattern' => $slot->schedule_pattern ?? 'default_duration',
                'duration_minutes' => $businessHours[0]['duration_minutes'] ?? 30,
                'capacity' => $businessHours[0]['capacity'] ?? 1,
                'business_closures' => $businessHours[0]['business_closures'] ?? 'open',
                'appointment_type_id' => $slot->category_id,
                'appointment_type_name' => $appointmentType ? $appointmentType->name : 'Unknown'
            ];
        }
    }
    
    public function updatedScheduleListFilter($value)
    {
        if (strlen($value) >= 2) {
            $this->selectedLocationId = Session::get('selectedLocation');

            // Get first-level categories that match the search
            $this->scheduleFilterSuggestions = Category::where('team_id', $this->teamId)
                ->whereJsonContains('category_locations', (string) $this->selectedLocationId)
                ->whereNull('parent_id') // First-level categories only
                ->where('name', 'like', '%' . $value . '%')
                ->orderBy('name')
                ->limit(10)
                ->pluck('name', 'id')
                ->toArray();
        } else {
            $this->scheduleFilterSuggestions = [];
        }
    }
    
    public function selectScheduleFilter($categoryId, $categoryName)
    {
        $this->scheduleListFilter = $categoryName;
        $this->scheduleFilterSuggestions = []; // Hide suggestions after selection
         $this->loadExistingSchedules();
    }
    
    public function getFilteredScheduleListProperty()
    {
        if (empty($this->scheduleListFilter)) {
            return $this->scheduleList;
        }
 

        return collect($this->scheduleList)
            ->filter(function ($schedule) {
                return stripos($schedule['appointment_type_name'], $this->scheduleListFilter) !== false;
            })
            ->values()
            ->toArray();
    }
    
    public function updatedSchedulePattern($value)
    {
        $this->initializeWeeklySlots();

        if ($value === 'custom') {
            // Reset duration and capacity when switching to custom
            $this->durationMinutes = null;
            $this->capacity = null;
        } elseif ($value === 'default_duration') {
            // Set default values when switching to default duration
            $this->durationMinutes = 30; // Ensure integer
            $this->capacity = 1; // Ensure integer
        }

       
        
        // Update existing slots capacity when pattern changes
        $this->updateExistingSlotsCapacity();
    }
    
    public function updatedCapacity($value)
    {
          $this->initializeWeeklySlots();
        // Update existing slots capacity when main capacity changes
        $this->updateExistingSlotsCapacity();
    }
    
    private function updateExistingSlotsCapacity()
    {
        // Update capacity for all existing slots when using default_duration pattern
        if ($this->schedulePattern === 'default_duration') {
            foreach ($this->weeklySlots as $dayIndex => $day) {
                if ($day['enabled']) {
                    foreach ($day['slots'] as $slotIndex => $slot) {
                        $this->weeklySlots[$dayIndex]['slots'][$slotIndex]['capacity'] = $this->capacity;
                    }
                }
            }
        }
    }
    
    public function updatedAppointmentTypeId($value)
    {
          $this->initializeWeeklySlots();
       
    }
    
    public function updatedDurationMinutes($value)
    {
          $this->initializeWeeklySlots();
       
    }
    
    public function toggleDay($dayIndex)
    {
        $this->weeklySlots[$dayIndex]['enabled'] = !$this->weeklySlots[$dayIndex]['enabled'];
    }
    
    public function addTimeSlot($dayIndex)
    {
        $defaultCapacity = $this->schedulePattern === 'default_duration' ? (int)$this->capacity : 1;
        
        // Get the last slot's end time to suggest as start time
        $suggestedStartTime = '';
        $suggestedEndTime = '';
        $slots = $this->weeklySlots[$dayIndex]['slots'];
        
        if (!empty($slots)) {
            $lastSlot = end($slots);
            if (!empty($lastSlot['end_time'])) {
                $suggestedStartTime = $lastSlot['end_time'];
                
                // For default duration pattern, auto-calculate end time
                if ($this->schedulePattern === 'default_duration' && !empty($suggestedStartTime)) {
                    $start = Carbon::createFromFormat('H:i', $suggestedStartTime);
                    $duration = (int)$this->durationMinutes;
                    $suggestedEndTime = $start->addMinutes($duration)->format('H:i');
                }
            }
        }
        
        $newSlot = [
            'start_time' => $suggestedStartTime,
            'end_time' => $suggestedEndTime,
            'duration' => $this->schedulePattern === 'default_duration' ? (int)$this->durationMinutes : '',
            'capacity' => $defaultCapacity
        ];
        
        $this->weeklySlots[$dayIndex]['slots'][] = $newSlot;
    }
    
    public function updatedWeeklySlots($value, $nestedKey)
    {
        // Parse the nested key to get dayIndex and slotIndex
        $parts = explode('.', $nestedKey);
        
        if (count($parts) >= 3 && $parts[1] === 'slots') {
            $dayIndex = $parts[0];
            $slotIndex = $parts[2];
            $field = $parts[3] ?? '';
            
            if (isset($this->weeklySlots[$dayIndex]['slots'][$slotIndex])) {
                $slot = &$this->weeklySlots[$dayIndex]['slots'][$slotIndex];
                
                // Check for overlapping time slots when start_time or end_time changes
                if (in_array($field, ['start_time', 'end_time']) && !empty($slot['start_time']) && !empty($slot['end_time'])) {
                    if ($this->hasOverlappingTimeSlot($dayIndex, $slotIndex, $slot['start_time'], $slot['end_time'])) {
                        // Reset the field that caused the overlap
                        $slot[$field] = '';
                        $this->dispatch('showError', ['message' => 'Time slot overlaps with an existing slot.']);
                        return;
                    }
                    
                    // Check chronological order: slots must be in order within the day
                    if (!$this->isInChronologicalOrder($dayIndex, $slotIndex, $slot['start_time'], $slot['end_time'])) {
                        // Get the specific error message
                        $errorMessage = $this->getChronologicalOrderError($dayIndex, $slotIndex, $slot['start_time'], $slot['end_time']);
                        
                        // Reset the field that caused the order violation
                        $slot[$field] = '';
                        $this->dispatch('showError', ['message' => $errorMessage]);
                        return;
                    }
                }
                
                // Auto-calculate duration based on start and end time for custom pattern
                if ($this->schedulePattern === 'custom' && $field === 'end_time' && !empty($slot['start_time'])) {
                    $start = Carbon::createFromFormat('H:i', $slot['start_time']);
                    $end = Carbon::createFromFormat('H:i', $value);
                    
                    // Handle times that cross midnight
                    if ($end->lt($start)) {
                        // If end time is earlier than start time, it means it crosses midnight
                        $end->addDay(); // Add a day to the end time
                    }
                    
                    $duration = $start->diffInMinutes($end);
                    $slot['duration'] = $duration;
                }
                
                // For default duration pattern, enforce the duration
                if ($this->schedulePattern === 'default_duration' && $field === 'start_time') {
                    $start = Carbon::createFromFormat('H:i', $value);
                    $duration = (int)$this->durationMinutes; // Cast to integer
                    $slot['end_time'] = $start->addMinutes($duration)->format('H:i');
                    $slot['duration'] = $duration;
                    
                    // Check for overlapping after auto-calculation
                    if ($this->hasOverlappingTimeSlot($dayIndex, $slotIndex, $slot['start_time'], $slot['end_time'])) {
                        $slot['start_time'] = '';
                        $slot['end_time'] = '';
                        $slot['duration'] = '';
                        $this->dispatch('showError', ['message' => 'Time slot overlaps with an existing slot.']);
                        return;
                    }
                    
                    // Check chronological order after auto-calculation
                    if (!$this->isInChronologicalOrder($dayIndex, $slotIndex, $slot['start_time'], $slot['end_time'])) {
                        // Get the specific error message
                        $errorMessage = $this->getChronologicalOrderError($dayIndex, $slotIndex, $slot['start_time'], $slot['end_time']);
                        
                        $slot['start_time'] = '';
                        $slot['end_time'] = '';
                        $slot['duration'] = '';
                        $this->dispatch('showError', ['message' => $errorMessage]);
                        return;
                    }
                    
                    // Set capacity based on pattern
                    $slot['capacity'] = $this->capacity;
                }
            }
        }
    }
    
    public function removeTimeSlot($dayIndex, $slotIndex)
    {
        unset($this->weeklySlots[$dayIndex]['slots'][$slotIndex]);
        $this->weeklySlots[$dayIndex]['slots'] = array_values($this->weeklySlots[$dayIndex]['slots']);
    }
    
    private function hasOverlappingTimeSlot($dayIndex, $currentSlotIndex, $startTime, $endTime)
    {
        if (empty($startTime) || empty($endTime)) {
            return false;
        }
        
        $currentStart = Carbon::createFromFormat('H:i', $startTime);
        $currentEnd = Carbon::createFromFormat('H:i', $endTime);
        
        // Handle times that cross midnight
        if ($currentEnd->lt($currentStart)) {
            $currentEnd->addDay();
        }
        
        foreach ($this->weeklySlots[$dayIndex]['slots'] as $index => $slot) {
            // Skip the current slot being edited
            if ($index == $currentSlotIndex) {
                continue;
            }
            
            // Skip empty slots
            if (empty($slot['start_time']) || empty($slot['end_time'])) {
                continue;
            }
            
            $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
            $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time']);
            
            // Handle times that cross midnight for existing slots
            if ($slotEnd->lt($slotStart)) {
                $slotEnd->addDay();
            }
            
            // Check for overlap: current slot overlaps with existing slot if:
            // - current start time is before existing end time AND
            // - current end time is after existing start time
            if ($currentStart->lt($slotEnd) && $currentEnd->gt($slotStart)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function isInChronologicalOrder($dayIndex, $currentSlotIndex, $startTime, $endTime)
    {
        if (empty($startTime) || empty($endTime)) {
            return true; // Empty slots don't affect order
        }
        
        $currentStart = Carbon::createFromFormat('H:i', $startTime);
        $currentEnd = Carbon::createFromFormat('H:i', $endTime);
        
        // Handle times that cross midnight
        if ($currentEnd->lt($currentStart)) {
            $currentEnd->addDay();
        }
        
        // Get all slots sorted by their index (order of creation)
        $slots = $this->weeklySlots[$dayIndex]['slots'];
        
        foreach ($slots as $index => $slot) {
            // Skip the current slot being edited
            if ($index == $currentSlotIndex) {
                continue;
            }
            
            // Skip empty slots
            if (empty($slot['start_time']) || empty($slot['end_time'])) {
                continue;
            }
            
            $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
            $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time']);
            
            // Handle times that cross midnight for existing slots
            if ($slotEnd->lt($slotStart)) {
                $slotEnd->addDay();
            }
            
            // Check if current slot comes before this slot in the array
            if ($currentSlotIndex < $index) {
                if ($currentEnd->gt($slotStart)) {
                    // Current slot ends after next slot starts (strict overlap)
                    return false;
                }
                // Allow currentEnd == slotStart (adjacent slots)
            }
            
            // Check if current slot comes after this slot in the array
            if ($currentSlotIndex > $index) {
                if ($currentStart->lt($slotEnd)) {
                    // Current slot starts before previous slot ends (strict overlap)
                    return false;
                }
                // Allow currentStart == slotEnd (adjacent slots)
            }
        }
        
        return true;
    }
    
    private function getChronologicalOrderError($dayIndex, $currentSlotIndex, $startTime, $endTime)
    {
        if (empty($startTime) || empty($endTime)) {
            return 'Time slots must be in chronological order.';
        }
        
        $currentStart = Carbon::createFromFormat('H:i', $startTime);
        $currentEnd = Carbon::createFromFormat('H:i', $endTime);
        $slots = $this->weeklySlots[$dayIndex]['slots'];
        
        foreach ($slots as $index => $slot) {
            // Skip the current slot being edited
            if ($index == $currentSlotIndex) {
                continue;
            }
            
            // Skip empty slots
            if (empty($slot['start_time']) || empty($slot['end_time'])) {
                continue;
            }
            
            $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
            $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time']);
            
            // Check if current slot comes before this slot in the array
            if ($currentSlotIndex < $index) {
                if ($currentEnd->gt($slotStart)) {
                    return "Time slot must end before or at the same time as the next slot starts ({$slotStart->format('H:i')}). Current slot ends at {$currentEnd->format('H:i')}.";
                }
            }
            
            // Check if current slot comes after this slot in the array
            if ($currentSlotIndex > $index) {
                if ($currentStart->lt($slotEnd)) {
                    return "Time slot must start at or after the previous slot ends ({$slotEnd->format('H:i')}). Current slot starts at {$currentStart->format('H:i')}.";
                }
            }
        }
        
        return 'Time slots must be in chronological order. Each slot must start after the previous slot ends.';
    }
    
    public function saveSchedule()
    {
        // Debug: Log the current state
        \Log::info('saveSchedule called', [
            'schedulePattern' => $this->schedulePattern,
            'selectedLocationId' => $this->selectedLocationId,
            'appointmentTypeId' => $this->appointmentTypeId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'durationMinutes' => $this->durationMinutes,
            'capacity' => $this->capacity,
            'weeklySlots' => $this->weeklySlots
        ]);
        
        try {
            $rules = [
                'selectedLocationId' => 'required|exists:locations,id',
                'schedulePattern' => 'required|in:default_duration,custom',
                'appointmentTypeId' => 'required|exists:categories,id',
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate'
            ];
            
            // Only add duration and capacity validation for default_duration pattern
            if ($this->schedulePattern === 'default_duration') {
                $rules['durationMinutes'] = 'required|integer|min:1';
                $rules['capacity'] = 'required|integer|min:1';
            }
            
            $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            $errorMessages = [];
            foreach ($e->errors() as $field => $messages) {
                $errorMessages[] = $field . ': ' . implode(', ', $messages);
            }
            $this->dispatch('showError', ['message' => 'Validation failed: ' . implode('; ', $errorMessages)]);
            return;
        }
        
        \Log::info('Validation passed');

        try {
            // Use the selected appointment type ID for category_id
            $categoryId = $this->appointmentTypeId;
            // Use the selected location
            $locationId = $this->selectedLocationId;
            
            // Check for potential duplicates before processing
            $start = Carbon::parse($this->startDate);
            $end = Carbon::parse($this->endDate);
            
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $dayOfWeek = $date->format('l');
                $dayIndex = array_search($dayOfWeek, array_column($this->weeklySlots, 'day'));
                
                \Log::info('Processing date', [
                    'date' => $date->format('Y-m-d'),
                    'dayOfWeek' => $dayOfWeek,
                    'dayIndex' => $dayIndex,
                    'dayExists' => $dayIndex !== false,
                    'dayEnabled' => $dayIndex !== false ? ($this->weeklySlots[$dayIndex]['enabled'] ?? false) : false
                ]);
                
                if ($dayIndex !== false && $this->weeklySlots[$dayIndex]['enabled']) {
                    $daySlots = $this->weeklySlots[$dayIndex]['slots'];
                    
                    \Log::info('Day slots found', ['count' => count($daySlots), 'slots' => $daySlots]);
                    
                    if (!empty($daySlots)) {
                        $formattedSlots = [];
                        
                        foreach ($daySlots as $slotIndex => $slot) {
                            if (!empty($slot['start_time']) && !empty($slot['end_time'])) {
                                // Get duration for this slot
                                $duration = $this->schedulePattern === 'default_duration' ? (int)$this->durationMinutes : (int)$slot['duration'];
                                
                                // Validate duration
                                if ($duration <= 0) {
                                    $this->dispatch('showError', ['message' => 'Duration must be greater than 0 for time slot.']);
                                    return;
                                }
                                
                                if ($this->schedulePattern === 'default_duration') {
                                    // For default duration, generate interval slots
                                    $intervalSlots = $this->generateIntervalSlots(
                                        $slot['start_time'],
                                        $slot['end_time'],
                                        $duration,
                                        $slot['capacity']
                                    );
                                    
                                    $formattedSlots = array_merge($formattedSlots, $intervalSlots);
                                } else {
                                    // For custom pattern, skip the first slot (it goes in main fields)
                                    if ($slotIndex > 0) {
                                        $formattedSlots[] = [
                                            'start_time' => $this->formatTime($slot['start_time']),
                                            'end_time' => $this->formatTime($slot['end_time']),
                                            'duration_minutes' => (int)$duration,
                                            'capacity' => (int)$slot['capacity']
                                        ];
                                    }
                                }
                            }
                        }
                        
                        // Get duration and capacity from first slot for custom pattern
                        $firstSlotDuration = null;
                        $firstSlotCapacity = null;
                        
                        if ($this->schedulePattern === 'custom' && !empty($formattedSlots)) {
                            $firstSlotDuration = (string)$formattedSlots[0]['duration_minutes'];
                            $firstSlotCapacity = (string)$formattedSlots[0]['capacity'];
                        }
                        
                        $scheduleData = [
                            'day' => $dayOfWeek,
                            'is_closed' => $this->businessClosures === 'close' ? 'close' : 'open',
                            'start_time' => !empty($daySlots[0]['start_time']) ? $this->formatTime($daySlots[0]['start_time']) : '12:00 AM',
                            'end_time' => !empty($daySlots[0]['end_time']) ? $this->formatTime($daySlots[0]['end_time']) : '12:00 PM',
                            'day_interval' => $formattedSlots,
                            'duration_minutes' => $this->schedulePattern === 'default_duration' ? (string)$this->durationMinutes : $firstSlotDuration,
                            'capacity' => $this->schedulePattern === 'default_duration' ? (string)$this->capacity : $firstSlotCapacity
                        ];
                        
                        \Log::info('Created schedule data for save', [
                            'businessClosures' => $this->businessClosures,
                            'is_closed' => $scheduleData['is_closed'],
                            'schedulePattern' => $this->schedulePattern,
                            'dayOfWeek' => $dayOfWeek
                        ]);
                        
                        // Check if record already exists
                        $existingSlot = CustomSlot::where('team_id', $this->teamId)
                            ->where('location_id', $locationId)
                            ->where('category_id', $categoryId)
                            ->where('selected_date', $date->format('Y-m-d'))
                            ->where('slots_type', $this->type)
                            ->first();
                        
                        // Skip duplicate check if we're editing the same schedule
                        if ($this->editingScheduleId && $existingSlot && $existingSlot->id == $this->editingScheduleId) {
                            // This is the schedule we're editing, update it
                            $existingSlot->update([
                                'business_hours' => json_encode([$scheduleData]),
                                'schedule_pattern' => $this->schedulePattern,
                                'updated_at' => now()
                            ]);
                            
                            \Log::info('Updated existing schedule', [
                                'scheduleId' => $existingSlot->id,
                                'businessClosures' => $this->businessClosures,
                                'is_closed' => $scheduleData['is_closed']
                            ]);
                            
                            continue; // Skip to next date since we updated the existing record
                        }
                        
                        // Also check for duplicates with different category_id
                        $potentialDuplicate = CustomSlot::where('team_id', $this->teamId)
                            ->where('location_id', $locationId)
                            ->where('selected_date', $date->format('Y-m-d'))
                            ->where('slots_type', $this->type)
                            ->where('category_id', '!=', $categoryId)
                            ->first();
                        
                        if ($existingSlot) {
                            $this->dispatch('showError', [
                                'message' => "A schedule already exists for {$date->format('M d, Y')} with this appointment type. Please edit the existing schedule or choose a different date."
                            ]);
                            return;
                        }
                        
                        if ($potentialDuplicate) {
                            $this->dispatch('showError', [
                                'message' => "A schedule already exists for {$date->format('M d, Y')} with a different appointment type. Each date can only have one schedule per location."
                            ]);
                            return;
                        }
                    }
                }
            }
            
            // Generate date range
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $dayOfWeek = $date->format('l'); // Get day name (Sunday, Monday, etc.)
                $dayIndex = array_search($dayOfWeek, array_column($this->weeklySlots, 'day'));
                
                \Log::info('Processing date', [
                    'date' => $date->format('Y-m-d'),
                    'dayOfWeek' => $dayOfWeek,
                    'dayIndex' => $dayIndex,
                    'dayExists' => $dayIndex !== false,
                    'dayEnabled' => $dayIndex !== false ? ($this->weeklySlots[$dayIndex]['enabled'] ?? false) : false
                ]);
                
                if ($dayIndex !== false && $this->weeklySlots[$dayIndex]['enabled']) {
                    $daySlots = $this->weeklySlots[$dayIndex]['slots'];
                    
                    \Log::info('Day slots found', ['count' => count($daySlots), 'slots' => $daySlots]);
                    
                    if (!empty($daySlots)) {
                        $formattedSlots = [];
                        
                        foreach ($daySlots as $slotIndex => $slot) {
                            if (!empty($slot['start_time']) && !empty($slot['end_time'])) {
                                // Get duration for this slot
                                $duration = $this->schedulePattern === 'default_duration' ? (int)$this->durationMinutes : (int)$slot['duration'];
                                
                                // Validate duration
                                if ($duration <= 0) {
                                    $this->dispatch('showError', ['message' => 'Duration must be greater than 0 for time slot.']);
                                    return;
                                }
                                
                                if ($this->schedulePattern === 'default_duration') {
                                    // For default duration, generate interval slots
                                    $intervalSlots = $this->generateIntervalSlots(
                                        $slot['start_time'],
                                        $slot['end_time'],
                                        $duration,
                                        $slot['capacity']
                                    );
                                    
                                    $formattedSlots = array_merge($formattedSlots, $intervalSlots);
                                } else {
                                    // For custom pattern, skip the first slot (it goes in main fields)
                                    if ($slotIndex > 0) {
                                        $formattedSlots[] = [
                                            'start_time' => $this->formatTime($slot['start_time']),
                                            'end_time' => $this->formatTime($slot['end_time']),
                                            'duration_minutes' => (int)$duration,
                                            'capacity' => (int)$slot['capacity']
                                        ];
                                    }
                                }
                            }
                        }
                        
                        // Check if record already exists
                        $existingSlot = CustomSlot::where('team_id', $this->teamId)
                            ->where('location_id', $locationId)
                            ->where('category_id', $categoryId)
                            ->where('selected_date', $date->format('Y-m-d'))
                            ->where('slots_type', $this->type)
                            ->first();
                        
                        // If no existing slot found, try a broader search to find potential duplicates
                        if (!$existingSlot) {
                            $potentialDuplicate = CustomSlot::where('team_id', $this->teamId)
                                ->where('location_id', $locationId)
                                ->where('selected_date', $date->format('Y-m-d'))
                                ->where('slots_type', $this->type)
                                ->first();
                            
                            if ($potentialDuplicate) {
                                // Update the existing record with the new category_id instead of creating duplicate
                                $potentialDuplicate->update([
                                    'category_id' => $categoryId,
                                    'business_hours' => json_encode([$scheduleData]),
                                    'schedule_pattern' => $this->schedulePattern,
                                    'updated_at' => now()
                                ]);
                                continue; // Skip to next date since we updated the existing record
                            }
                        }
                        
                        // Get duration and capacity from first slot for custom pattern
                        $firstSlotDuration = null;
                        $firstSlotCapacity = null;
                        
                        if ($this->schedulePattern === 'custom' && !empty($formattedSlots)) {
                            $firstSlotDuration = (string)$formattedSlots[0]['duration_minutes'];
                            $firstSlotCapacity = (string)$formattedSlots[0]['capacity'];
                        }
                        
                        $scheduleData = [
                            'day' => $dayOfWeek,
                            'is_closed' => $this->businessClosures === 'close' ? 'close' : 'open',
                            'start_time' => !empty($daySlots[0]['start_time']) ? $this->formatTime($daySlots[0]['start_time']) : '12:00 AM',
                            'end_time' => !empty($daySlots[0]['end_time']) ? $this->formatTime($daySlots[0]['end_time']) : '12:00 PM',
                            'day_interval' => $formattedSlots,
                            'duration_minutes' => $this->schedulePattern === 'default_duration' ? (string)$this->durationMinutes : $firstSlotDuration,
                            'capacity' => $this->schedulePattern === 'default_duration' ? (string)$this->capacity : $firstSlotCapacity
                        ];
                        
                        if ($existingSlot) {
                            // Update existing record
                            $existingSlot->update([
                                'business_hours' => json_encode([$scheduleData]),
                                'schedule_pattern' => $this->schedulePattern,
                                'updated_at' => now()
                            ]);
                        } else {
                            // Create new record
                            CustomSlot::create([
                                'team_id' => $this->teamId,
                                'location_id' => $locationId,
                                'category_id' => $categoryId,
                                'slots_type' => $this->type,
                                'selected_date' => $date->format('Y-m-d'),
                                'business_hours' => json_encode([$scheduleData]),
                                'schedule_pattern' => $this->schedulePattern
                            ]);
                        }
                    }
                }
            }
            
            \Log::info('About to load existing schedules and dispatch success');
            $this->loadExistingSchedules();
            $this->dispatch('saved', ['message' => 'Schedule saved successfully']);
            
            // Reset form
            $this->initializeWeeklySlots();
            $this->editingScheduleId = null; // Reset editing flag
            
            \Log::info('Save completed successfully');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            \Log::error('Validation Exception', ['error' => $e->getMessage()]);
            $this->dispatch('showError', ['message' => 'Please fill in all required fields correctly.']);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (like duplicates)
            \Log::error('Database Exception', ['error' => $e->getMessage()]);
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $this->dispatch('showError', ['message' => 'A schedule for this date and location already exists. Please edit the existing schedule instead.']);
            } else {
                $this->dispatch('showError', ['message' => 'Database error: Unable to save schedule.']);
            }
        } catch (\Exception $e) {
            // Handle general errors
            $errorMessage = $e->getMessage();
            \Log::error('General Exception', ['error' => $errorMessage]);
            
            // Provide user-friendly messages for common errors
            if (str_contains($errorMessage, 'Carbon')) {
                $this->dispatch('showError', ['message' => 'Invalid date format. Please check your date selection.']);
            } elseif (str_contains($errorMessage, 'formatTime')) {
                $this->dispatch('showError', ['message' => 'Invalid time format. Please check your time slots.']);
            } elseif (str_contains($errorMessage, 'duration')) {
                $this->dispatch('showError', ['message' => 'Invalid duration. Please check your time slot settings.']);
            } else {
                $this->dispatch('showError', ['message' => 'Error saving schedule: ' . $errorMessage]);
            }
        }
    }
    
    private function generateIntervalSlots($startTime, $endTime, $duration, $capacity)
    {
        $slots = [];
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);
        
        while ($start->lt($end)) {
            $slotEnd = $start->copy()->addMinutes($duration);
            
            if ($slotEnd->lte($end)) {
                $slots[] = [
                    'start_time' => $this->formatTime($start->format('H:i')),
                    'end_time' => $this->formatTime($slotEnd->format('H:i')),
                    'duration_minutes' => (int)$duration,
                    'capacity' => (int)$capacity
                ];
            }
            
            $start->addMinutes($duration);
        }
        
        return $slots;
    }
    
    private function formatTime($time)
    {
        if (empty($time)) {
            return null;
        }
        return date("h:i A", strtotime($time));
    }
    
    public function confirmDelete($scheduleId)
    {
        $this->dispatch('confirmDelete', ['id' => $scheduleId]);
    }
    
    public function editSchedule($scheduleId)
    {
        // Set the editing flag
        $this->editingScheduleId = $scheduleId;
        
        // Find the schedule to edit
        $schedule = CustomSlot::find($scheduleId);
        
        if (!$schedule) {
            $this->dispatch('showError', ['message' => 'Schedule not found']);
            return;
        }
        
        try {
            // Parse the business hours to get schedule data
            $businessHours = json_decode($schedule->business_hours, true);
            $scheduleData = $businessHours[0] ?? [];
            
            // Set the form data
            $this->selectedLocationId = $schedule->location_id;
            session(['selectedLocation' => $this->selectedLocationId]); // Store in session
            $this->appointmentTypeId = $schedule->category_id;
            $this->schedulePattern = $schedule->schedule_pattern ?? 'default_duration';
            
            // Set the appointment search text
            if ($this->selectedLocationId) {
                $this->loadAvailableCategories();
                if (isset($this->availableCategories[$this->appointmentTypeId])) {
                    $this->appointmentSearch = $this->availableCategories[$this->appointmentTypeId];
                }
            }
            
            // Set date range (use the selected_date for both start and end)
            $this->startDate = $schedule->selected_date;
            $this->endDate = $schedule->selected_date;
            
            // Set business closures
            $this->businessClosures = $scheduleData['is_closed'] === 'close' ? 'close' : 'open';
            
            // Set duration and capacity for default_duration pattern
            if ($this->schedulePattern === 'default_duration') {
                $this->durationMinutes = (int)($scheduleData['duration_minutes'] ?? 30);
                $this->capacity = (int)($scheduleData['capacity'] ?? 1);
            }
          
            // Load the weekly slots based on the schedule data
            $this->loadWeeklySlotsFromSchedule($scheduleData);
            
            // Switch to new schedule tab
            $this->activeTab = 'new_schedule';
            
            $this->dispatch('saved', ['message' => 'Schedule loaded for editing']);
            
            // Clear autocomplete dropdown after loading is complete
            $this->filteredCategories = [];
            
        } catch (\Exception $e) {
            \Log::error('Edit schedule error', ['error' => $e->getMessage()]);
            $this->dispatch('showError', ['message' => 'Error loading schedule: ' . $e->getMessage()]);
        }
    }
    
    private function loadWeeklySlotsFromSchedule($scheduleData)
    {
        // Debug: Log the incoming data
        \Log::info('loadWeeklySlotsFromSchedule called', [
            'schedulePattern' => $this->schedulePattern,
            'scheduleData' => $scheduleData
        ]);
        
        // Reset weekly slots
        $this->initializeWeeklySlots();
        

        // Find the day index - try multiple approaches
        $dayName = $scheduleData['day'] ?? '';
        $dayIndex = false;
        
        // Try exact match first
        $dayIndex = array_search($dayName, array_column($this->weeklySlots, 'day'));
        
        // If not found, try case-insensitive match
        if ($dayIndex === false) {
            foreach ($this->weeklySlots as $index => $dayData) {
                if (strcasecmp($dayData['day'], $dayName) === 0) {
                    $dayIndex = $index;
                    break;
                }
            }
        }
        
        \Log::info('Day lookup', [
            'dayName' => $dayName,
            'dayIndex' => $dayIndex,
            'availableDays' => array_column($this->weeklySlots, 'day')
        ]);
        
        if ($dayIndex === false) {
            \Log::info('Day not found, returning');
            return;
        }
        
        // Enable the day
        $this->weeklySlots[$dayIndex]['enabled'] = true;
        
        // Handle custom pattern differently
        if ($this->schedulePattern === 'custom') {
            // For custom pattern, reconstruct slots from the stored data
            $dayInterval = $scheduleData['day_interval'] ?? [];
            
            \Log::info('Custom pattern loading', [
                'dayInterval' => $dayInterval,
                'startTime' => $scheduleData['start_time'],
                'endTime' => $scheduleData['end_time']
            ]);
            
            // First, add the main slot from start_time/end_time
            if (!empty($scheduleData['start_time']) && !empty($scheduleData['end_time'])) {
                $mainSlot = [
                    'start_time' => date('H:i', strtotime($scheduleData['start_time'])),
                    'end_time' => date('H:i', strtotime($scheduleData['end_time'])),
                    'duration' => $scheduleData['duration_minutes'] ?? null,
                    'capacity' => $scheduleData['capacity'] ?? 1
                ];
                
                $this->weeklySlots[$dayIndex]['slots'][] = $mainSlot;
                \Log::info('Added main slot', ['mainSlot' => $mainSlot]);
            }
            
            // Then add additional slots from day_interval (excluding the main slot if it's already there)
            foreach ($dayInterval as $index => $interval) {
                // Skip if this interval matches the main slot to avoid duplication
                $intervalStart = date('H:i', strtotime($interval['start_time']));
                $intervalEnd = date('H:i', strtotime($interval['end_time']));
                $mainStart = date('H:i', strtotime($scheduleData['start_time']));
                $mainEnd = date('H:i', strtotime($scheduleData['end_time']));
                
                if ($intervalStart === $mainStart && $intervalEnd === $mainEnd) {
                    \Log::info('Skipping duplicate main slot', ['index' => $index]);
                    continue; // Skip duplicate main slot
                }
                
                $slot = [
                    'start_time' => $intervalStart,
                    'end_time' => $intervalEnd,
                    'duration' => $interval['duration_minutes'] ?? null,
                    'capacity' => $interval['capacity'] ?? 1
                ];
                
                $this->weeklySlots[$dayIndex]['slots'][] = $slot;
                \Log::info('Added additional slot', ['slot' => $slot]);
            }
        } else {
            // For default_duration pattern, load all slots from day_interval
            $dayInterval = $scheduleData['day_interval'] ?? [];
            
            \Log::info('Default pattern loading', ['dayInterval' => $dayInterval]);
            
            foreach ($dayInterval as $interval) {
                $slot = [
                    'start_time' => date('H:i', strtotime($interval['start_time'])),
                    'end_time' => date('H:i', strtotime($interval['end_time'])),
                    'duration' => $interval['duration_minutes'] ?? null,
                    'capacity' => $interval['capacity'] ?? 1
                ];
                
                $this->weeklySlots[$dayIndex]['slots'][] = $slot;
                \Log::info('Added default slot', ['slot' => $slot]);
            }
        }
        
        \Log::info('Final weekly slots', ['weeklySlots' => $this->weeklySlots]);
        
        // Force Livewire to update the UI
        $this->dispatch('refresh');
    }
    
    public function deleteSchedule($scheduleId)
    {
        try {
            $schedule = CustomSlot::find($scheduleId);
            
            if (!$schedule) {
                $this->dispatch('showError', ['message' => 'Schedule not found']);
                return;
            }
            
            $schedule->delete();
            $this->loadExistingSchedules();
            
            $this->dispatch('saved', ['message' => 'Schedule deleted successfully']);
            
        } catch (\Exception $e) {
            $this->dispatch('showError', ['message' => 'Error deleting schedule: ' . $e->getMessage()]);
        }
    }
    
    public function clearForm()
    {
        // Reset all form fields
        $this->selectedLocationId = null;
        $this->schedulePattern = '';
        $this->appointmentTypeId = null;
        $this->appointmentSearch = '';
        $this->filteredCategories = [];
        $this->durationMinutes = 30;
        $this->capacity = 1;
        $this->businessClosures = 'open';
        $this->editingScheduleId = null; // Reset editing flag
        
        // Reset dates to selected_date
        $selectedDate = request()->get('selected_date', Carbon::now()->format('Y-m-d'));
        $this->startDate = $selectedDate;
        $this->endDate = $selectedDate;
        
        // Reset weekly slots
        $this->initializeWeeklySlots();
        
        // Clear validation errors
        $this->resetValidation();
        
        // Clear categories
        $this->availableCategories = [];
        $this->filteredCategories = [];
    }
    
    public function render()
    {
        return view('livewire.category-custom-schedule-component');
    }
}
