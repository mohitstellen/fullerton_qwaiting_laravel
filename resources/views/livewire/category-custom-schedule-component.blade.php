<div class="border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="p-6">
        <style>
            .tab-active {
                background-color: #3b82f6;
                color: white;
            }
            .tab-inactive {
                background-color: #f3f4f6;
                color: #374151;
            }
            .time-slot-row {
                display: flex;
                gap: 10px;
                align-items: center;
                margin-bottom: 10px;
            }
            .time-input {
                width: 120px;
                padding: 8px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
            }
            .duration-input {
                width: 100px;
                padding: 8px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
            }
            .capacity-input {
                width: 80px;
                padding: 8px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
            }
        </style>
        
        <h3 class="text-2xl font-semibold leading-2 text-gray-950 dark:text-white mb-6">
            {{ $record->name ?? '' }} - Custom Schedule Settings
        </h3>

        <!-- Tabs -->
        <div class="flex space-x-4 mb-6">
            <button 
                wire:click="$set('activeTab', 'new_schedule')"
                class="px-6 py-3 rounded-lg font-medium transition-colors {{ $activeTab === 'new_schedule' ? 'tab-active' : 'tab-inactive' }}"
            >
                New Schedule
            </button>
            <button 
                wire:click="$set('activeTab', 'schedule_list')"
                class="px-6 py-3 rounded-lg font-medium transition-colors {{ $activeTab === 'schedule_list' ? 'tab-active' : 'tab-inactive' }}"
            >
                Schedule List
            </button>
        </div>

        <!-- New Schedule Tab -->
        @if($activeTab === 'new_schedule')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h4 class="text-lg font-semibold mb-4">Schedule Configuration</h4>
                    
                    <!-- Location Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Clinic <span class="text-red-500">*</span>
                        </label>
                        <select 
                            wire:model.live="selectedLocationId"
                            class="w-full p-2 border border-gray-300 rounded-md"
                        >
                            <option value="">Select Location</option>
                            @foreach($availableLocations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($errors->first('selectedLocationId'))
                            <p class="text-red-500 text-xs mt-1">{{ $errors->first('selectedLocationId') }}</p>
                        @endif
                    </div>
                    
                    <!-- Schedule Pattern -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Schedule Pattern <span class="text-red-500">*</span>
                        </label>
                        <select 
                            wire:model.live="schedulePattern"
                            class="w-full p-2 border border-gray-300 rounded-md"
                            @if(!$selectedLocationId) disabled @endif
                        >
                            <option value="">Select Schedule Pattern</option>
                            <option value="default_duration">Duration (Minutes) based time slot</option>
                            <option value="custom">Custom</option>
                        </select>
                        @if($errors->first('schedulePattern'))
                            <p class="text-red-500 text-xs mt-1">{{ $errors->first('schedulePattern') }}</p>
                        @endif
                        @if(!$selectedLocationId)
                            <p class="text-gray-500 text-xs mt-1">Please select a location first</p>
                        @endif
                    </div>

                    <!-- Appointment Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Appointment Type <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live="appointmentSearch"
                                placeholder="Search Service..."
                                class="w-full p-2 border border-gray-300 rounded-md"
                                @if(!$selectedLocationId) disabled @endif
                            >
                            @if($selectedLocationId && count($filteredCategories) > 0 && !empty($appointmentSearch))
                                <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 max-h-60 overflow-y-auto shadow-lg">
                                    @foreach($filteredCategories as $id => $name)
                                        <div 
                                            wire:click="selectAppointmentType({{ $id }}, '{{ $name }}')"
                                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                                        >
                                            {{ $name }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @if($appointmentTypeId && isset($availableCategories[$appointmentTypeId]))
                            <div class="mt-2 text-sm text-gray-600">
                                Selected: {{ $availableCategories[$appointmentTypeId] }}
                            </div>
                        @endif
                        @if($errors->first('appointmentTypeId'))
                            <p class="text-red-500 text-xs mt-1">{{ $errors->first('appointmentTypeId') }}</p>
                        @endif
                        @if(!$selectedLocationId)
                            <p class="text-gray-500 text-xs mt-1">Please select a location to load services</p>
                        @endif
                    </div>

                    <!-- Duration and Capacity (only for default_duration) -->
                    @if($schedulePattern === 'default_duration')
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Minutes)</label>
                                <input 
                                    type="number" 
                                    wire:model.live="durationMinutes"
                                    value="30"
                                    min="1"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                                <input 
                                    type="number" 
                                    wire:model.live="capacity"
                                    value="1"
                                    min="1"
                                    class="w-full p-2 border border-gray-300 rounded-md"
                                >
                            </div>
                        </div>
                    @endif

                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input 
                                type="date" onclick="this.showPicker()"
                                wire:model="startDate"
                                class="w-full p-2 border border-gray-300 rounded-md"
                            >
                            @if($errors->first('startDate'))
                                <p class="text-red-500 text-xs mt-1">{{ $errors->first('startDate') }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input 
                                type="date" 
                                wire:model="endDate" onclick="this.showPicker()"
                                class="w-full p-2 border border-gray-300 rounded-md"
                            >
                            @if($errors->first('endDate'))
                                <p class="text-red-500 text-xs mt-1">{{ $errors->first('endDate') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Business Closures -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Business Closures</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input 
                                    type="radio" 
                                    wire:model="businessClosures"
                                    value="open"
                                    class="mr-2"
                                >
                                <span>Observe</span>
                            </label>
                            <label class="flex items-center">
                                <input 
                                    type="radio" 
                                    wire:model="businessClosures"
                                    value="close"
                                    class="mr-2"
                                >
                                <span>Do Not Observe</span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-4">
                        <button 
                            wire:click="saveSchedule"
                            class="flex-1 bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 transition-colors font-medium"
                        >
                            Submit
                        </button>
                        <button 
                            wire:click="clearForm"
                            class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium"
                        >
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Right Section -->
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <h4 class="text-lg font-semibold mb-4">Weekly Time Slots</h4>
                    
                    @foreach($weeklySlots as $index => $day)
                        <div class="mb-6 p-4 border border-gray-100 rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="weeklySlots.{{ $index }}.enabled"
                                        class="mr-3"
                                    >
                                    <span class="font-medium">{{ $day['day'] }}</span>
                                    @if(!$day['enabled'])
                                        <span class="ml-3 text-sm text-gray-500">Not Available</span>
                                    @endif
                                </div>
                                
                                @if($day['enabled'])
                                    <button 
                                        wire:click="addTimeSlot({{ $index }})"
                                        class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600"
                                    >
                                        + Add
                                    </button>
                                @endif
                            </div>

                            @if($day['enabled'])
                                <div class="space-y-2">
                                    @foreach($day['slots'] as $slotIndex => $slot)
                                        <div class="time-slot-row">
                                            <select 
                                                wire:model.live="weeklySlots.{{ $index }}.slots.{{ $slotIndex }}.start_time"
                                                class="time-input"
                                            >
                                                <option value="">Start Time</option>
                                                @for($hour = 0; $hour < 24; $hour++)
                                                    @for($minute = 0; $minute < 60; $minute += 5)
                                                        <option value="{{ sprintf('%02d:%02d', $hour, $minute) }}">
                                                            {{ sprintf('%02d:%02d', $hour, $minute) }}
                                                        </option>
                                                    @endfor
                                                @endfor
                                            </select>
                                            
                                            <select 
                                                wire:model.live="weeklySlots.{{ $index }}.slots.{{ $slotIndex }}.end_time"
                                                class="time-input {{ $schedulePattern === 'default_duration' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                @if($schedulePattern === 'default_duration') disabled @endif
                                            >
                                                <option value="">End Time</option>
                                                @for($hour = 0; $hour < 24; $hour++)
                                                    @for($minute = 0; $minute < 60; $minute += 5)
                                                        <option value="{{ sprintf('%02d:%02d', $hour, $minute) }}">
                                                            {{ sprintf('%02d:%02d', $hour, $minute) }}
                                                        </option>
                                                    @endfor
                                                @endfor
                                            </select>
                                           
                                            
                                            @if($schedulePattern === 'custom')
                                                <input 
                                                    type="number" 
                                                    wire:model="weeklySlots.{{ $index }}.slots.{{ $slotIndex }}.duration"
                                                    class="duration-input"
                                                    placeholder="Duration"
                                                    readonly
                                                >
                                            
                                            
                                            <!-- Always show capacity input -->
                                            <input 
                                                type="number" 
                                                wire:model="weeklySlots.{{ $index }}.slots.{{ $slotIndex }}.capacity"
                                                class="capacity-input"
                                                min="1"
                                                placeholder="Cap"
                                                value="{{ $schedulePattern === 'default_duration' ? $capacity : ($slot['capacity'] ?? 1) }}"
                                            >
                                            @endif
                                            <button 
                                                wire:click="removeTimeSlot({{ $index }}, {{ $slotIndex }})"
                                                class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600"
                                            >
                                                ✖
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Schedule List Tab -->
        @if($activeTab === 'schedule_list')
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Saved Schedules</h4>
                    
                    <!-- Appointment Type Filter -->
                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Appointment Type</label>
                        <input 
                            type="text" 
                            wire:model.live="scheduleListFilter"
                            placeholder="Type to filter by appointment type..."
                            class="w-full p-2 border border-gray-300 rounded-md"
                        >
                        
                        <!-- Autocomplete Suggestions -->
                        @if(!empty($scheduleFilterSuggestions))
                            <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @foreach($scheduleFilterSuggestions as $categoryId => $categoryName)
                                    <div 
                                        wire:click="selectScheduleFilter({{ $categoryId }}, '{{ $categoryName }}')"
                                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                                    >
                                        <div class="font-medium">{{ $categoryName }}</div>
                                        <div class="text-xs text-gray-500">First-level category</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    @if(count($this->filteredScheduleList) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="border border-gray-200 px-4 py-2 text-left">Date</th>
                                        <th class="border border-gray-200 px-4 py-2 text-left">Day</th>
                                        <th class="border border-gray-200 px-4 py-2 text-left">Appointment Type</th>
                                        <th class="border border-gray-200 px-4 py-2 text-left">Status</th>
                                        <th class="border border-gray-200 px-4 py-2 text-left">Time Range</th>
                                        <th class="border border-gray-200 px-4 py-2 text-left">Pattern</th>
                                        <th class="border border-gray-200 px-4 py-2 text-left">Slots</th>
                                        <th class="border border-gray-200 px-4 py-2 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->filteredScheduleList as $schedule)
                                        <tr class="hover:bg-gray-50">
                                            <td class="border border-gray-200 px-4 py-2">
                                                {{ \Carbon\Carbon::parse($schedule['selected_date'])->format('M d, Y') }}
                                            </td>
                                            <td class="border border-gray-200 px-4 py-2">
                                                {{ \Carbon\Carbon::parse($schedule['selected_date'])->format('l') }}
                                            </td>
                                            <td class="border border-gray-200 px-4 py-2">
                                                <span class="font-medium text-blue-600">{{ $schedule['appointment_type_name'] }}</span>
                                            </td>
                                            <td class="border border-gray-200 px-4 py-2">
                                                @if($schedule['is_closed'] === 'close')
                                                    <span class="text-red-500 font-medium">Closed</span>
                                                @else
                                                    <span class="text-green-500 font-medium">Open</span>
                                                @endif
                                            </td>
                                            <td class="border border-gray-200 px-4 py-2">
                                                @if($schedule['is_closed'] !== 'closed')
                                                    {{ $schedule['start_time'] }} - {{ $schedule['end_time'] }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="border border-gray-200 px-4 py-2">
                                                {{ $schedule['schedule_pattern'] === 'default_duration' ? 'Default Duration' : 'Custom' }}
                                            </td>
                                            <td class="border border-gray-200 px-4 py-2">
                                                {{ count($schedule['day_interval']) }} slots
                                            </td>
                                            <td class="border border-gray-200 px-4 py-2">
                                                <div class="flex space-x-2">
                                                    <button 
                                                        wire:click="editSchedule({{ $schedule['id'] }})"
                                                        class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button 
                                                        wire:click="confirmDelete({{ $schedule['id'] }})"
                                                        class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Show detailed slots -->
                                        @if($schedule['is_closed'] !== 'close' && count($schedule['day_interval']) > 0)
                                            <tr>
                                                <td colspan="8" class="border border-gray-200 px-4 py-2 bg-gray-50">
                                                    <div class="text-sm text-gray-600">
                                                        <strong>Time Slots:</strong>
                                                        <div class="grid grid-cols-4 gap-2 mt-2">
                                                            @foreach($schedule['day_interval'] as $slot)
                                                                <div class="flex justify-between bg-white p-2 rounded border">
                                                                    <span>{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                                                                    <span class="text-blue-600">Cap: {{ $slot['capacity'] }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            @if(!empty($scheduleListFilter))
                                <p>No schedules found matching "{{ $scheduleListFilter }}". Try a different search term.</p>
                            @else
                                <p>No schedules found. Create your first schedule in the "New Schedule" tab.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('saved', (response) => {
                Swal.fire({
                    title: "Success",
                    text: response[0].message,
                    icon: "success",
                    timer: 3000,
                    showConfirmButton: false
                });
            });
            
            // Listen for edit schedule events to hide autocomplete
            Livewire.on('saved', (response) => {
                if (response[0].message && response[0].message.includes('loaded for editing')) {
                    // Hide autocomplete dropdown after edit loads
                    setTimeout(() => {
                        const autocompleteDropdown = document.querySelector('[data-autocomplete="appointment-type"]');
                        if (autocompleteDropdown) {
                            autocompleteDropdown.style.display = 'none';
                        }
                    }, 100);
                }
            });
            
            Livewire.on('showError', (response) => {
                console.log('error',response);
                Swal.fire({
                    title: "Error",
                    text: response[0].message,
                    icon: "error",
                    timer: 4000,
                    showConfirmButton: false
                });
            });
            
            Livewire.on('confirmDelete', (response) => {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Call Livewire method to delete
                        @this.call('deleteSchedule', response[0].id);
                    }
                });
            });
        });
    </script>
</div>
