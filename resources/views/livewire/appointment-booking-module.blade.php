<div class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    @assets
    <link href="{{asset('/css/app/call.css?v=3.1.0.0')}}" rel="stylesheet" data-navigate-track />
    @endassets
    
    <div class="p-3">
        <style>
            @media (min-width:992px){
                .d-block{
                    display: block !important;
                }
            }
        </style>
        
        <!-- Top Header (Same style as Calls page) -->
        <div style="background-color: white; display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <!-- Logo -->
            <div style="display: flex; align-items: center; gap: 12px;">
                @php
                    $url = request()->url();
                    $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;
                    if (strpos($url, 'mobile/queue') !== false) {
                        $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
                    }
                    $logo = App\Models\SiteDetail::viewImage($headerPage);
                @endphp
                <a href="{{ route('tenant.dashboard') }}" style="display: flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer;">
                    <img src="{{ url($logo) }}" alt="Logo" style="max-height: 60px; max-width: 110px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22%3E%3Ccircle cx=%2220%22 cy=%2220%22 r=%2218%22 fill=%22%233b82f6%22/%3E%3Ctext x=%2220%22 y=%2225%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2216%22 font-weight=%22bold%22%3EW%3C/text%3E%3C/svg%3E'">
                </a>
                
                <!-- Dashboard Button -->
                <a href="{{ route('tenant.dashboard') }}" style="background-color: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <!-- Sign Out Button -->
            <form method="POST" action="{{ route('tenant.logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background-color: #14b8a6; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
        
        <!-- Main Content -->
        <div style="display: flex; gap: 12px; height: calc(100vh - 120px);">
        <!-- Left Sidebar -->
        <aside style="background-color: #1e293b; width: 280px; padding: 16px; border-radius: 8px; overflow-y: auto; max-height: calc(100vh - 100px); flex-shrink: 0;">
            <!-- Clinics Section -->
            <div style="margin-bottom: 24px;">
                <h2 style="color: white; font-size: 18px; font-weight: bold; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <svg style="width: 18px; height: 18px; color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        {{ __('sidebar.Clinics') }}
                    </span>
                    <span style="color: #60a5fa; font-size: 13px; font-weight: normal;">({{ count($availableClinics) }} total)</span>
                </h2>
                <div class="space-y-2">
                    @php
                        $selectedClinicsData = $this->getSelectedClinicsData();
                    @endphp
                    @if(count($availableClinics) === 0)
                        <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; border-radius: 6px; padding: 12px; margin-bottom: 8px;">
                            <p style="color: #fca5a5; font-size: 12px; margin: 0;">
                                <strong>No locations found.</strong><br>
                                Team ID: {{ $teamId ?? 'Not set' }}<br>
                                Please check if there are active locations in the database for this team.
                            </p>
                        </div>
                    @endif
                    @foreach($selectedClinicsData as $clinic)
                        <div style="background-color: #2563eb; padding: 10px; border-radius: 6px; margin-bottom: 8px;" class="flex items-center justify-between">
                            <span style="color: white; font-size: 15px; font-weight: 600;" class="flex-1">{{ $clinic['name'] }}</span>
                                <button 
                                wire:click="removeClinic({{ $clinic['id'] }})"
                                style="color: white; background-color: rgba(239, 68, 68, 0.3); padding: 4px; border-radius: 50%;"
                                    title="Remove clinic"
                                >
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                    @endforeach
                    
                    @php
                        $availableClinicsForDropdown = $this->getAvailableClinicsForDropdown();
                    @endphp
                    @if(count($availableClinicsForDropdown) > 0)
                        <select 
                            id="clinic-select-dropdown"
                            wire:change="addClinic($event.target.value)"
                            style="background-color: #374151; color:rgb(255, 255, 255); padding: 10px; border-radius: 6px; border: 1px solid #4b5563; font-size: 15px; font-weight: 600; width: 100%;"
                        >
                            <option value="" style="color: rgb(255, 255, 255);">Select Clinics...</option>
                            @foreach($availableClinicsForDropdown as $clinic)
                                <option value="{{ $clinic['id'] }}" style="color: rgb(255, 255, 255);">{{ $clinic['name'] }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <!-- Appointment Types Section -->
            <div>
                <h2 style="color: white; font-size: 18px; font-weight: bold; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <svg style="width: 18px; height: 18px; color: #c084fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    {{ __('sidebar.Appointment Type') }}
                </h2>
                <div style="margin-top: 8px;">
                    @if(count($appointmentTypes) > 0)
                        @foreach($appointmentTypes as $type => $isAvailable)
                            @php
                                $isSelected = in_array($type, $selectedAppointmentTypes);
                            @endphp
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-700 rounded border border-gray-600 mb-2">
                                <span class="text-base text-white font-medium" style="font-size: 15px;">{{ $type }}</span>
                                <div class="flex gap-2 ml-2">
                                    @if($isSelected)
                                        <button 
                                            wire:click="toggleAppointmentType('{{ $type }}')"
                                            style="background-color: #22c55e; color: white; border: 2px solid #16a34a;"
                                            class="px-3 py-1 text-xs font-bold rounded"
                                        >
                                            On
                                        </button>
                                        <button 
                                            wire:click="toggleAppointmentType('{{ $type }}')"
                                            style="background-color: #4b5563; color: #9ca3af;"
                                            class="px-3 py-1 text-xs font-bold rounded"
                                        >
                                            Off
                                        </button>
                                    @else
                                        <button 
                                            wire:click="toggleAppointmentType('{{ $type }}')"
                                            style="background-color: #4b5563; color: #9ca3af;"
                                            class="px-3 py-1 text-xs font-bold rounded"
                                        >
                                            On
                                        </button>
                            <button 
                                wire:click="toggleAppointmentType('{{ $type }}')"
                                            style="background-color: #ef4444; color: white; border: 2px solid #dc2626;"
                                            class="px-3 py-1 text-xs font-bold rounded"
                            >
                                            Off
                            </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="background-color: rgba(55, 65, 81, 0.5); border: 2px dashed #4b5563; border-radius: 6px; padding: 12px; text-align: center;">
                            <svg style="width: 40px; height: 40px; color: #9ca3af; margin: 0 auto 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <p style="font-size: 12px; color: #d1d5db;">Please select at least one clinic to see available appointment types.</p>
                        </div>
                    @endif
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main style="flex: 1; background-color: white; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 24px; overflow-y: auto; min-width: 0;">
            <!-- Top Control Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; gap: 12px;">
                <!-- Search Button -->
                <button 
                    wire:click="toggleSearchFilters"
                    style="background-color: #f97316; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; font-size: 15px; white-space: nowrap; flex-shrink: 0;"
                >
                    Appointment Search
                </button>

                <!-- Date Navigation -->
                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                    <button wire:click="previousDay" style="color: #4b5563; padding: 4px; background: transparent; border: none; cursor: pointer;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <!-- Hidden date input for date picker -->
                    @php
                        // Calculate current date in Y-m-d format
                        $currentDateValue = Carbon\Carbon::createFromDate($selectedYear, Carbon\Carbon::createFromFormat('M', $selectedMonth)->month, $selectedDay)->format('Y-m-d');
                    @endphp
                    <input 
                        type="date" 
                        id="appointment-date-picker"
                        wire:model.live="selectedDate"
                        value="{{ $selectedDate ?: $currentDateValue }}"
                        style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: auto;"
                    />
                    <div style="display: flex; align-items: center; gap: 6px; position: relative;">
                        <button 
                            type="button"
                            onclick="toggleDatePicker(); return false;"
                            style="border: 2px solid #3b82f6 !important; padding: 3px 8px; border-radius: 4px; text-align: center; min-width: 50px; background: white; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#f0f9ff'; this.style.borderColor='#2563eb';"
                            onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#3b82f6';"
                        >
                            <div style="font-size: 10px; color: #4b5563; line-height: 1; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px; margin-bottom: 2px;">DAY</div>
                            <div style="font-weight: bold; color: #2563eb; font-size: 14px; line-height: 1.2;">{{ $selectedDay }}</div>
                        </button>
                        <button 
                            type="button"
                            onclick="toggleDatePicker(); return false;"
                            style="border: 2px solid #3b82f6 !important; padding: 3px 8px; border-radius: 4px; text-align: center; min-width: 60px; background: white; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#f0f9ff'; this.style.borderColor='#2563eb';"
                            onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#3b82f6';"
                        >
                            <div style="font-size: 10px; color: #4b5563; line-height: 1; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px; margin-bottom: 2px;">MONTH</div>
                            <div style="font-weight: bold; color: #2563eb; font-size: 14px; line-height: 1.2;">{{ $selectedMonth }}</div>
                        </button>
                        <button 
                            type="button"
                            onclick="toggleDatePicker(); return false;"
                            style="border: 2px solid #3b82f6 !important; padding: 3px 8px; border-radius: 4px; text-align: center; min-width: 60px; background: white; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#f0f9ff'; this.style.borderColor='#2563eb';"
                            onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#3b82f6';"
                        >
                            <div style="font-size: 10px; color: #4b5563; line-height: 1; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px; margin-bottom: 2px;">YEAR</div>
                            <div style="font-weight: bold; color: #2563eb; font-size: 14px; line-height: 1.2;">{{ $selectedYear }}</div>
                        </button>
                        
                        <!-- Custom Calendar Picker -->
                        <div id="custom-date-picker" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 1000; padding: 16px; min-width: 280px;">
                            <div id="calendar-container"></div>
                        </div>
                    </div>
                    <div style="font-size: 15px; font-weight: 600; color: #374151; padding: 0 8px; white-space: nowrap;">{{ $selectedDayName }}</div>
                    <button wire:click="nextDay" style="color: #4b5563; padding: 4px; background: transparent; border: none; cursor: pointer;">
                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>

                <!-- View Mode Buttons -->
                <div style="display: flex; gap: 6px; flex-shrink: 0; align-items: center;">
                    @php
                        $calendarBg = $viewMode === 'calendar' ? '#f97316' : '#f97316';
                        $timelineBg = $viewMode === 'timeline' ? '#ef4444' : '#ef4444';
                    @endphp
                    <button 
                        wire:click="toggleViewMode"
                        style="background-color: {{ $calendarBg }}; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; font-size: 15px; white-space: nowrap;"
                    >
                        CALENDAR
                    </button>
                    <button 
                        wire:click="toggleViewMode"
                        style="background-color: {{ $timelineBg }}; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; font-size: 15px; white-space: nowrap;"
                    >
                        TIMELINE
                    </button>
                    
                </div>
            </div>

            <!-- Search Filters Form -->
            @if($showSearchFilters)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 p-4 mb-4 shadow-sm">
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NRIC / FIN</label>
                            <input 
                                type="text" 
                                wire:model="searchNric"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Enter NRIC/FIN"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile Number</label>
                            <input 
                                type="text" 
                                wire:model="searchMobile"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Enter mobile"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input 
                                type="email" 
                                wire:model="searchEmail"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Enter email"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                            <input 
                                type="text" 
                                wire:model="searchCompany"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Enter company"
                            />
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button 
                            wire:click="searchAppointments"
                            style="background-color: #2563eb; color: white; padding: 8px 24px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600;"
                        >
                            Go
                        </button>
                        <button 
                            wire:click="clearSearch"
                            style="background-color: #d1d5db; color: #374151; padding: 8px 24px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600;"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            @endif

            @if($viewMode === 'timeline')
                <!-- Gender Filter - Above Table, Right Aligned -->
                <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                    <select 
                        wire:model.live="timelineGender"
                        style="padding: 6px 12px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 13px; background: white; cursor: pointer; min-width: 100px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" 
                    >
                        <option value="All">All</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Unisex">Unisex</option>
                    </select>
                </div>
                
                <!-- Timeline View - Grouped by Appointment Type -->
                @if(count($timelineAppointments) > 0)
                    @foreach($timelineAppointments as $group)
                        <!-- Clinic Name and Appointment Type Header -->
                        <div style="margin-bottom: 24px;">
                            <h2 style="font-size: 22px; font-weight: 600; margin-bottom: 8px; color: #111827;">
                                {{ $group['location_name'] }}
                            </h2>
                            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: #374151;">
                                {{ $group['appointment_type'] }}
                            </h3>
                            
                            <!-- Appointments Table for this Appointment Type -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-blue-600">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Time</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Gender</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Created on</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Company / Package</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">National Id No</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Phone</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($group['appointments'] as $appointment)
                                                <tr 
                                                    wire:click="openBookingModal({{ $appointment['id'] }})"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                                >
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['time'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['gender'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['name'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['created_on'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['company_package'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['national_id'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['phone'] }}</td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['remarks'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="background-color: white; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 32px; text-align: center;">
                        <p style="color: #6b7280;">
                            No appointments found for {{ $selectedDay }}/{{ $selectedMonth }}/{{ $selectedYear }}
                            @if($timelineGender !== 'All')
                                ({{ $timelineGender }})
                            @endif
                        </p>
                    </div>
                @endif
            @elseif($showSearchFilters)
                <!-- Appointments Table (shown when search filters are open) -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gender</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created on</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Company / Package</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">National Id No</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($appointments as $appointment)
                                    <tr 
                                        wire:click="openBookingModal({{ $appointment['id'] }})"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                    >
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['time'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['gender'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['name'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['created_on'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['company_package'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['national_id'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['phone'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-base text-gray-900 dark:text-gray-100" style="font-size: 15px;">{{ $appointment['location'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No appointments found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Clinics with Time Slots Display -->
                @php
                    // Get time slots for currently selected appointment types
                    $timeSlotsData = $this->getTimeSlotsForDisplay();
                @endphp
                
                @if(count($timeSlotsData) > 0)
                    @foreach($timeSlotsData as $slotData)
                        <div style="background-color: white; border-radius: 8px; border-left: 4px solid #60a5fa; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 24px; margin-bottom: 24px;">
                            <h3 style="font-size: 22px; font-weight: 600; margin-bottom: 8px; color: #111827;">{{ $slotData['clinic_name'] }}</h3>
                            <h4 style="font-size: 20px; font-weight: 600; margin-bottom: 16px; color: #374151;">{{ $slotData['appointment_type'] }}</h4>
                            
                            <div>
                                @foreach($slotData['time_slots'] as $time => $slotInfo)
                                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                                        <span style="color: #374151; font-weight: 500; min-width: 100px; font-size: 16px;">{{ $time }}</span>
                                        
                                        @if(is_array($slotInfo) && isset($slotInfo['status']) && $slotInfo['status'] !== 'empty')
                                            {{-- Booked slot - show booking info --}}
                                            <button 
                                                wire:click="openBookingModal({{ $slotInfo['booking_id'] }})"
                                                style="padding: 8px 16px; border-radius: 6px; min-width: 300px; text-align: left; border: 2px solid; cursor: pointer; font-weight: 500; font-size: 13px; 
                                                @if($slotInfo['status'] === 'Reserved') background-color: #dbeafe; border-color: #60a5fa; color: #1e40af;
                                                @elseif($slotInfo['status'] === 'SMSCalled') background-color: #f3e8ff; border-color: #a855f7; color: #6b21a8;
                                                @elseif($slotInfo['status'] === 'Arrived') background-color: #fef9c3; border-color: #eab308; color: #854d0e;
                                                @elseif($slotInfo['status'] === 'Completed') background-color: #dcfce7; border-color: #22c55e; color: #166534;
                                                @elseif($slotInfo['status'] === 'Cancelled') background-color: #f3f4f6; border-color: #6b7280; color: #374151;
                                                @elseif($slotInfo['status'] === 'NoShow') background-color: #fee2e2; border-color: #ef4444; color: #991b1b;
                                                @else background-color: #e0e7ff; border-color: #6366f1; color: #3730a3;
                                                @endif"
                                            >
                                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                                    <div style="font-weight: 600; font-size: 15px;">{{ $slotInfo['patient_name'] ?? 'N/A' }}</div>
                                                    <div style="font-size: 13px; opacity: 0.8;">NRIC: {{ $slotInfo['nric'] ?? 'N/A' }} | Status: {{ $slotInfo['status'] }}</div>
                                                </div>
                                            </button>
                                        @else
                                            {{-- Empty slot - allow booking --}}
                                            <button 
                                                wire:click="bookAppointment({{ $slotData['clinic_id'] }}, '{{ $slotData['appointment_type'] }}', '{{ $time }}')"
                                                style="background-color:rgb(255, 255, 255); color: white; padding: 8px 16px; border-radius: 6px; min-width: 150px; text-align: left; border: none; cursor: pointer; font-weight: 500; font-size: 16px; border: rgb(0,0,0) solid; color: rgb(0, 0, 0);"
                                            >
                                                Empty
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            </div>
                    @endforeach
                @else
                    <div style="background-color: white; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 32px; text-align: center;">
                        <p style="color: #6b7280; font-size: 16px;">Please select at least one clinic and one appointment type to view available slots.</p>
                    </div>
                @endif
            @endif
        </main>

        <!-- Right Sidebar -->
        <aside style="background-color: white; width: 200px; padding: 16px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); flex-shrink: 0;">
            <div>
                <h3 style="font-size: 18px; font-weight: bold; color: #1f2937; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <svg style="width: 18px; height: 18px; color: #6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    STATUS
                </h3>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px; margin-bottom: 4px;">
                        <div style="width: 18px; height: 18px; border-radius: 50%; background-color: #3b82f6;"></div>
                        <span style="font-size: 14px; font-weight: 600; color: #374151;">Reserved</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px; margin-bottom: 4px;">
                        <div style="width: 18px; height: 18px; border-radius: 50%; background-color: #a855f7;"></div>
                        <span style="font-size: 14px; font-weight: 600; color: #374151;">SMSCalled</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px; margin-bottom: 4px;">
                        <div style="width: 18px; height: 18px; border-radius: 50%; background-color: #eab308;"></div>
                        <span style="font-size: 14px; font-weight: 600; color: #374151;">Arrived</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px; margin-bottom: 4px;">
                        <div style="width: 18px; height: 18px; border-radius: 50%; background-color: #6b7280;"></div>
                        <span style="font-size: 14px; font-weight: 600; color: #374151;">Cancelled</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px; margin-bottom: 4px;">
                        <div style="width: 18px; height: 18px; border-radius: 50%; background-color: #dc2626;"></div>
                        <span style="font-size: 14px; font-weight: 600; color: #374151;">NoShow</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px; margin-bottom: 4px;">
                        <div style="width: 18px; height: 18px; border-radius: 50%; background-color: #22c55e;"></div>
                        <span style="font-size: 14px; font-weight: 600; color: #374151;">Completed</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Booking Modal -->
    @if($showBookingModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4" wire:click="closeBookingModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-y-auto mx-auto" @click.stop>
                <!-- Modal Header -->
                <div class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between rounded-t-lg sticky top-0 z-10">
                    <h2 class="text-xl font-semibold">{{ $selectedAppointmentType ?? 'Doctor Review Consult' }}</h2>
                    <button 
                        wire:click="closeBookingModal" 
                        type="button"
                        class="text-white hover:bg-blue-700 transition-colors p-2 rounded-lg flex items-center justify-center"
                        style="background-color: rgba(255, 255, 255, 0.1); min-width: 36px; min-height: 36px;"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Success/Error Messages -->
                <div class="px-6 pt-4">
                    @if (session()->has('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Modal Tabs -->
                <div class="border-b-2 border-gray-300 bg-gray-50 px-6 pt-4 pb-0" style="background-color: #f9fafb; border-bottom: 2px solid #d1d5db;">
                    <div class="flex items-center gap-6">
                        <button 
                            wire:click="setActiveTab('booking-details')"
                            type="button"
                            class="pb-3 px-4 font-semibold text-sm transition-colors {{ $activeTab === 'booking-details' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-800 hover:text-orange-500' }}"
                            style="border-bottom-width: 3px; padding-bottom: 12px; color: {{ $activeTab === 'booking-details' ? '#ea580c' : '#1f2937' }}; border-bottom: 3px solid {{ $activeTab === 'booking-details' ? '#f97316' : 'transparent' }};"
                        >
                            Booking Details
                        </button>
                        <button 
                            wire:click="setActiveTab('audit-trail')"
                            type="button"
                            class="pb-3 px-4 font-semibold text-sm transition-colors {{ $activeTab === 'audit-trail' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-800 hover:text-orange-500' }}"
                            style="border-bottom-width: 3px; padding-bottom: 12px; color: {{ $activeTab === 'audit-trail' ? '#ea580c' : '#1f2937' }}; border-bottom: 3px solid {{ $activeTab === 'audit-trail' ? '#f97316' : 'transparent' }};"
                        >
                            Audit Trail
                        </button>
                        
                      
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6 dark:bg-gray-800">
                    @if($activeTab === 'booking-details')
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column - Personal Particulars -->
                            <div>
                                <div class="flex items-center gap-4 mb-4">
                                    <h3 class="text-lg font-semibold border-b-2 border-orange-500 pb-1">Personal Particulars</h3>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="isVip" wire:key="vip-checkbox-{{ $isVip ? 'checked' : 'unchecked' }}" class="w-4 h-4">
                                        <span class="text-sm">VIP</span>
                                    </label>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Identification Type <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model="identificationType" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option>NRIC / FIN</option>
                                            <option>Passport</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            NRIC / FIN / Passport <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative" id="nric-search-container">
                                            <input 
                                                type="text" 
                                                id="nric-search-input"
                                                wire:model.live.debounce.500ms="nricFinPassport"
                                                placeholder="Search Nric / Name / Mobile Number"
                                                class="w-full px-10 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                autocomplete="off"
                                            />
                                            <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            
                                            <!-- Autocomplete Dropdown -->
                                            @if($showNricDropdown)
                                                <div id="nric-dropdown" class="absolute z-[9999] w-full mt-1" x-data wire:click.outside="$wire.closeNricDropdown()">
                                                    @if(count($nricSearchResults) > 0)
                                                        <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl max-h-80 overflow-y-auto">
                                                            @foreach($nricSearchResults as $index => $result)
                                                                <div 
                                                                    wire:key="member-{{ $result['id'] }}"
                                                                    wire:click="selectMember({{ $result['id'] }})"
                                                                    class="w-full text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none cursor-pointer member-result-item"
                                                                    style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb; transition: background-color 0.2s; display: block; background: white;"
                                                                >
                                                                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                                                                        <div style="flex: 1; min-width: 0;">
                                                                            <div style="font-weight: 600; color: #111827; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                                                {{ $result['full_name'] }}
                                                                            </div>
                                                                            <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                                                                <div style="margin-bottom: 2px;">
                                                                                    <span style="font-weight: 500;">NRIC:</span> {{ $result['nric_fin'] }}
                                                                                </div>
                                                                                <div style="margin-bottom: 2px;">
                                                                                    <span style="font-weight: 500;">Mobile:</span> {{ $result['mobile_number'] }}
                                                                                </div>
                                                                                @if(!empty($result['email']))
                                                                                    <div>
                                                                                        <span style="font-weight: 500;">Email:</span> {{ $result['email'] }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                            @if(!empty($result['company_name']))
                                                                                <div style="font-size: 12px; color: #2563eb; margin-top: 6px; font-weight: 500;">
                                                                                    🏢 {{ $result['company_name'] }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <svg style="width: 20px; height: 20px; color: #9ca3af; flex-shrink: 0; margin-top: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif(strlen($nricFinPassport) >= 2)
                                                        <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl" style="padding: 12px 16px;">
                                                            <div style="text-align: center; font-size: 14px; color: #6b7280;">
                                                                <svg style="width: 32px; height: 32px; margin: 0 auto 8px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                No members found
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex gap-2">
                                            <select wire:model.live="title" class="w-24 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option>Mr</option>
                                                <option>Mrs</option>
                                                <option>Ms</option>
                                                <option>Dr</option>
                                            </select>
                                            <input 
                                                type="text" 
                                                wire:model.live="fullName"
                                                placeholder="Full Name"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Date of Birth <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="date" 
                                                wire:model.live="dateOfBirth"
                                                placeholder="05/12/2025"
                                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                onclick="this.showPicker()"
                                            />
                                            <svg class="w-5 h-5 absolute right-3 top-2.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Gender <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model.live="gender" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Country Code
                                        </label>
                                        <select 
                                            wire:model="mobileCountryCode"
                                            readonly
                                            onfocus="this.blur()"
                                            class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 dark:bg-gray-600 dark:border-gray-600 dark:text-white cursor-not-allowed"
                                            style="background-color: #f3f4f6; cursor: not-allowed; pointer-events: none;"
                                            tabindex="-1"
                                        >
                                            @if(!empty($allowedCountries))
                                                @foreach($allowedCountries as $country)
                                                    <option value="{{ $country->phonecode }}" {{ $mobileCountryCode == $country->phonecode ? 'selected' : '' }}>
                                                        (+{{ $country->phonecode }}) {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Mobile Number (Login ID) <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="tel" 
                                            wire:model.live="mobileNumber"
                                            placeholder="Mobile Number"
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="email" 
                                            wire:model.live="emailAddress"
                                            placeholder="Email Address"
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Nationality <span class="text-red-500">*</span>
                                        </label>
                                        <select 
                                            wire:model="nationality" 
                                            readonly
                                            onfocus="this.blur()"
                                            class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 dark:bg-gray-600 dark:border-gray-600 dark:text-white cursor-not-allowed"
                                            style="background-color: #f3f4f6; cursor: not-allowed; pointer-events: none;"
                                            tabindex="-1"
                                        >
                                            @foreach($availableNationalities as $nat)
                                                <option value="{{ $nat }}" {{ $nationality == $nat ? 'selected' : '' }}>{{ $nat }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Appointment Details -->
                            <div>
                                <div class="flex items-center gap-4 mb-4">
                                    <h3 class="text-lg font-semibold border-b-2 border-orange-500 pb-1">Appointment Details</h3>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model.live="isPrivateCustomer" wire:key="private-customer-checkbox-{{ $isPrivateCustomer ? 'checked' : 'unchecked' }}" class="w-4 h-4">
                                        <span class="text-sm">Private Customer</span>
                                    </label>
                                </div>

                                <div class="space-y-4">
                                    @if(!$isPrivateCustomer && $memberSelectedFromSearch)
                                    <div wire:key="company-name-field-{{ $isPrivateCustomer ? 'hidden' : 'visible' }}-{{ $memberSelectedFromSearch ? 'selected' : 'not-selected' }}">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Company Name
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model.live="companyName"
                                            placeholder="Company Name"
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            readonly
                                        />
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Package <span class="text-red-500">*</span>
                                            <svg class="w-4 h-4 inline-block text-blue-500 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </label>
                                        <select wire:model="package" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Please Choose</option>
                                            <option>Not Applicable</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Location <span class="text-red-500">*</span>
                                        </label>
                                        <select wire:model.live="locationId" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Select Location</option>
                                            @foreach($availableLocations as $loc)
                                                <option value="{{ $loc['id'] }}">{{ $loc['location_name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Date and time <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="dateTime"
                                            placeholder="05/12/2025 9:30AM"
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            onclick="this.showPicker()"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Additional Comments
                                        </label>
                                        <textarea 
                                            wire:model.defer="additionalComments"
                                            rows="4"
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Payment Status
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="paymentStatus"
                                            value="Pending"
                                            readonly
                                            class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Selection Buttons (Only show in Edit Mode) -->
                        @if($selectedAppointment)
                            <div style="margin-top: 24px;">
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 12px;">
                                    Booking Status <span style="color: #ef4444;">*</span>
                                </label>
                                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                    <button 
                                        type="button"
                                        wire:click="setBookingStatus('Reserved')"
                                        style="padding: 8px 16px; border: 2px solid #60a5fa; border-radius: 8px; cursor: pointer; font-weight: 500; 
                                        {{ $bookingStatus === 'Reserved' ? 'background-color: #60a5fa; color: white;' : 'background-color: white; color: #2563eb;' }}"
                                    >
                                        Reserved
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="setBookingStatus('SMSCalled')"
                                        style="padding: 8px 16px; border: 2px solid #a855f7; border-radius: 8px; cursor: pointer; font-weight: 500;
                                        {{ $bookingStatus === 'SMSCalled' ? 'background-color: #a855f7; color: white;' : 'background-color: white; color: #7c3aed;' }}"
                                    >
                                        SMSCalled
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="setBookingStatus('Arrived')"
                                        style="padding: 8px 16px; border: 2px solid #eab308; border-radius: 8px; cursor: pointer; font-weight: 500;
                                        {{ $bookingStatus === 'Arrived' ? 'background-color: #eab308; color: white;' : 'background-color: white; color: #ca8a04;' }}"
                                    >
                                        Arrived
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="setBookingStatus('Cancelled')"
                                        style="padding: 8px 16px; border: 2px solid #6b7280; border-radius: 8px; cursor: pointer; font-weight: 500;
                                        {{ $bookingStatus === 'Cancelled' ? 'background-color: #6b7280; color: white;' : 'background-color: white; color: #4b5563;' }}"
                                    >
                                        Cancelled
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="setBookingStatus('NoShow')"
                                        style="padding: 8px 16px; border: 2px solid #ef4444; border-radius: 8px; cursor: pointer; font-weight: 500;
                                        {{ $bookingStatus === 'NoShow' ? 'background-color: #ef4444; color: white;' : 'background-color: white; color: #dc2626;' }}"
                                    >
                                        NoShow
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="setBookingStatus('Completed')"
                                        style="padding: 8px 16px; border: 2px solid #22c55e; border-radius: 8px; cursor: pointer; font-weight: 500;
                                        {{ $bookingStatus === 'Completed' ? 'background-color: #22c55e; color: white;' : 'background-color: white; color: #16a34a;' }}"
                                    >
                                        Completed
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons (Always Visible) -->
                        <div class="mt-6 flex gap-3 flex-wrap">
                            @if($selectedAppointment)
                                <button 
                                    wire:click="updateAppointment"
                                    type="button"
                                    style="background-color: #2563eb; color: white; padding: 8px 24px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer;"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50"
                                >
                                    <span wire:loading.remove wire:target="updateAppointment">Update Appointment</span>
                                    <span wire:loading wire:target="updateAppointment">Updating...</span>
                                </button>
                            @else
                                <button 
                                    wire:click="submitAppointment"
                                    type="button"
                                    style="background-color: #16a34a; color: white; padding: 8px 24px; border-radius: 6px; font-weight: 700; border: none; cursor: pointer;"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50"
                                >
                                    <span wire:loading.remove wire:target="submitAppointment">Confirm Appointment</span>
                                    <span wire:loading wire:target="submitAppointment">Saving...</span>
                                </button>
                            @endif
                            <button 
                                wire:click="closeBookingModal" 
                                type="button"
                                style="background-color: #d1d5db; color: #374151; padding: 8px 24px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer;"
                            >
                                Cancel
                            </button>
                        </div>
                    @elseif($activeTab === 'audit-trail')
                        <!-- Audit Trail Content -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-blue-600 text-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">S.No</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Modified At</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Modified By</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Booking Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">NRIC / FIN</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Sex</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">DOB</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Appointment Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Package</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Start Date Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">End Date Time</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if(count($auditTrailLogs) > 0)
                                        @foreach($auditTrailLogs as $index => $log)
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['modified_at'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['modified_by'] }}</td>
                                                <td class="px-4 py-3 text-sm">
                                                    @php
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                        if ($log['status'] === 'Reserved') {
                                                            $statusClass = 'bg-blue-100 text-blue-800';
                                                        } elseif ($log['status'] === 'SMSCalled') {
                                                            $statusClass = 'bg-purple-100 text-purple-800';
                                                        } elseif ($log['status'] === 'Arrived') {
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                        } elseif ($log['status'] === 'Completed') {
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                        } elseif ($log['status'] === 'Cancelled') {
                                                            $statusClass = 'bg-gray-100 text-gray-800';
                                                        } elseif ($log['status'] === 'NoShow') {
                                                            $statusClass = 'bg-red-100 text-red-800';
                                                        }
                                                    @endphp
                                                    <span class="px-2 py-1 rounded text-xs {{ $statusClass }}">{{ $log['status'] }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['nric'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['name'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['gender'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['dob'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['appointment_type'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['package'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['start_date_time'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $log['end_date_time'] }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500">
                                                No audit trail logs found for this appointment.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
    </div>
</div>

@push('scripts')
<script>
    // Theme handling
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark')
    }
    
    // Fullscreen function
    function toggleFullScreen(element) {
        if (!document.fullscreenElement && !document.mozFullScreenElement && 
            !document.webkitFullscreenElement && !document.msFullscreenElement) {
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        }
    }
    
    // Handle click outside to close NRIC dropdown
    function handleClickOutside(event) {
        const container = document.getElementById('nric-search-container');
        const dropdown = document.getElementById('nric-dropdown');
        
        // Only proceed if container exists
        if (!container) return;
        
        // Check if click is outside the container
        // Also check if dropdown is visible (exists in DOM)
        if (!container.contains(event.target)) {
            // Click is outside - close the dropdown
            if (typeof Livewire !== 'undefined') {
                // Try to find the Livewire component and call method directly
                const wireElement = container.closest('[wire\\:id]');
                if (wireElement) {
                    const wireId = wireElement.getAttribute('wire:id');
                    if (wireId) {
                        const component = Livewire.find(wireId);
                        if (component) {
                            component.call('closeNricDropdown');
                            return;
                        }
                    }
                }
                // Fallback: dispatch event
                Livewire.dispatch('closeNricDropdown');
            }
        }
    }
    
    // Setup click outside handler after Livewire is ready
    document.addEventListener('livewire:init', function() {
        document.addEventListener('click', handleClickOutside);
    });
    
    // Also setup immediately if Livewire is already loaded
    if (typeof Livewire !== 'undefined' && Livewire.all().length > 0) {
        document.addEventListener('click', handleClickOutside);
    } else {
        // Fallback: setup on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                document.addEventListener('click', handleClickOutside);
            });
        } else {
            document.addEventListener('click', handleClickOutside);
        }
    }
    
    // Listen for member selected event
    document.addEventListener('livewire:init', () => {
        Livewire.on('member-selected', (data) => {
            // In Livewire 3, event data is passed as first element of array
            const eventData = Array.isArray(data) ? data[0] : data;
            
            // Show a brief success notification
            console.log('✅ Member selected successfully:', eventData);
            console.log('Member data:', eventData?.memberData);
            console.log('Member name:', eventData?.memberName);
            
            // Directly update input values as fallback
            if (eventData?.memberData) {
                const memberData = eventData.memberData;
                
                // Update form fields directly
                setTimeout(() => {
                    // Mobile Number
                    const mobileInput = document.querySelector('input[wire\\:model\\.live="mobileNumber"]');
                    if (mobileInput && memberData.mobile) {
                        mobileInput.value = memberData.mobile;
                        mobileInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    // Email Address
                    const emailInput = document.querySelector('input[wire\\:model\\.live="emailAddress"]');
                    if (emailInput && memberData.email) {
                        emailInput.value = memberData.email;
                        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    // Full Name
                    const nameInput = document.querySelector('input[wire\\:model\\.live="fullName"]');
                    if (nameInput && memberData.name) {
                        nameInput.value = memberData.name;
                        nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    // Company Name
                    const companyInput = document.querySelector('input[wire\\:model\\.live="companyName"]');
                    if (companyInput && memberData.company) {
                        companyInput.value = memberData.company;
                        companyInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    // NRIC
                    const nricInput = document.querySelector('input[wire\\:model\\.live\\.debounce\\.500ms="nricFinPassport"]');
                    if (nricInput && memberData.nric) {
                        nricInput.value = memberData.nric;
                    }
                    
                    // Date of Birth
                    const dobInput = document.querySelector('input[wire\\:model\\.live="dateOfBirth"]');
                    if (dobInput && memberData.dateOfBirth) {
                        dobInput.value = memberData.dateOfBirth;
                        dobInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    // Title/Salutation
                    const titleSelect = document.querySelector('select[wire\\:model\\.live="title"]');
                    if (titleSelect && memberData.salutation) {
                        titleSelect.value = memberData.salutation;
                        titleSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    // Gender
                    const genderSelect = document.querySelector('select[wire\\:model\\.live="gender"]');
                    if (genderSelect && memberData.gender) {
                        genderSelect.value = memberData.gender;
                        genderSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    // Mobile Country Code (readonly field - update via Livewire)
                    const mobileCountryCodeSelect = document.querySelector('select[wire\\:model="mobileCountryCode"]');
                    if (mobileCountryCodeSelect) {
                        const countryCode = memberData.mobileCountryCode || memberData.mobile_country_code || '65';
                        // Temporarily allow pointer events to set value
                        mobileCountryCodeSelect.style.pointerEvents = 'auto';
                        mobileCountryCodeSelect.value = countryCode;
                        mobileCountryCodeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        // Re-disable pointer events to keep readonly
                        mobileCountryCodeSelect.style.pointerEvents = 'none';
                    }
                    
                    // Nationality (readonly field - update via Livewire)
                    const nationalitySelect = document.querySelector('select[wire\\:model="nationality"]');
                    if (nationalitySelect) {
                        const nationality = memberData.nationality || 'Singaporean';
                        // Temporarily allow pointer events to set value
                        nationalitySelect.style.pointerEvents = 'auto';
                        nationalitySelect.value = nationality;
                        nationalitySelect.dispatchEvent(new Event('change', { bubbles: true }));
                        // Re-disable pointer events to keep readonly
                        nationalitySelect.style.pointerEvents = 'none';
                    }
                    
                    console.log('✅ Form fields updated via JavaScript fallback');
                }, 100);
            }
            
            // Show a brief visual feedback
            const nricInput = document.querySelector('input[wire\\:model\\.live\\.debounce\\.500ms="nricFinPassport"]');
            if (nricInput) {
                nricInput.style.borderColor = '#22c55e';
                nricInput.style.borderWidth = '2px';
                setTimeout(() => {
                    nricInput.style.borderColor = '';
                    nricInput.style.borderWidth = '';
                }, 1500);
            }
        });
    });
    
    // Handle ESC key to close modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            Livewire.dispatch('closeBookingModal');
        }
    });
    
    // Custom Calendar Picker
    @php
        $monthNum = Carbon\Carbon::createFromFormat('M', $selectedMonth)->month - 1;
    @endphp
    let currentCalendarDate = new Date({{ $selectedYear }}, {{ $monthNum }}, {{ $selectedDay }});
    let calendarVisible = false;
    const selectedYearValue = {{ $selectedYear }};
    const selectedMonthValue = {{ $monthNum }};
    const selectedDayValue = {{ $selectedDay }};
    
    function toggleDatePicker() {
        const picker = document.getElementById('custom-date-picker');
        if (!picker) return;
        
        calendarVisible = !calendarVisible;
        picker.style.display = calendarVisible ? 'block' : 'none';
        
        if (calendarVisible) {
            renderCalendar();
        }
    }
    
    function renderCalendar() {
        const container = document.getElementById('calendar-container');
        if (!container) return;
        
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth();
        
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        
        // Get current selected date
        const selectedDate = new Date(selectedYearValue, selectedMonthValue, selectedDayValue);
        
        let html = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <button type="button" onclick="changeMonth(-1)" style="background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; color: #374151;">&lt;</button>
                <div style="font-weight: 600; font-size: 16px; color: #111827;">${monthNames[month]} ${year}</div>
                <button type="button" onclick="changeMonth(1)" style="background: none; border: none; cursor: pointer; padding: 4px 8px; font-size: 18px; color: #374151;">&gt;</button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 8px;">
        `;
        
        // Day headers
        dayNames.forEach(day => {
            html += `<div style="text-align: center; font-weight: 600; font-size: 12px; color: #6b7280; padding: 8px;">${day}</div>`;
        });
        
        // Empty cells for days before month starts
        for (let i = 0; i < firstDay; i++) {
            html += `<div style="padding: 8px;"></div>`;
        }
        
        // Days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const isSelected = date.toDateString() === selectedDate.toDateString();
            const isToday = date.toDateString() === new Date().toDateString();
            
            html += `
                <button 
                    type="button"
                    onclick="selectDate(${year}, ${month + 1}, ${day})"
                    style="
                        border: ${isSelected ? '2px solid #3b82f6' : '1px solid #e5e7eb'};
                        background: ${isSelected ? '#dbeafe' : isToday ? '#fef3c7' : 'white'};
                        color: ${isSelected ? '#1e40af' : '#374151'};
                        padding: 8px;
                        border-radius: 4px;
                        cursor: pointer;
                        font-weight: ${isSelected || isToday ? '600' : '400'};
                        transition: all 0.2s;
                    "
                    onmouseover="this.style.backgroundColor='${isSelected ? '#dbeafe' : '#f3f4f6'}'; this.style.borderColor='#3b82f6';"
                    onmouseout="this.style.backgroundColor='${isSelected ? '#dbeafe' : isToday ? '#fef3c7' : 'white'}'; this.style.borderColor='${isSelected ? '#3b82f6' : '#e5e7eb'}';"
                >
                    ${day}
                </button>
            `;
        }
        
        html += `</div>`;
        container.innerHTML = html;
    }
    
    function changeMonth(direction) {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + direction);
        renderCalendar();
    }
    
    function selectDate(year, month, day) {
        // Update Livewire component
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const date = new Date(year, month - 1, day);
        const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
        
        // Format date for Livewire
        const formattedDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        // Update via Livewire
        @this.set('selectedDate', formattedDate);
        @this.set('selectedDay', day);
        @this.set('selectedMonth', monthNames[month - 1]);
        @this.set('selectedYear', year);
        @this.set('selectedDayName', dayName);
        
        // Hide calendar
        toggleDatePicker();
    }
    
    // Close calendar when clicking outside
    document.addEventListener('click', function(event) {
        const picker = document.getElementById('custom-date-picker');
        const dateButtons = event.target.closest('[onclick*="toggleDatePicker"]');
        
        if (picker && calendarVisible && !picker.contains(event.target) && !dateButtons) {
            calendarVisible = false;
            picker.style.display = 'none';
        }
    });
    
    // Make functions globally accessible
    window.toggleDatePicker = toggleDatePicker;
    window.changeMonth = changeMonth;
    window.selectDate = selectDate;
    
    // Listen for date changes and ensure Livewire updates
    document.addEventListener('livewire:init', () => {
        const datePicker = document.getElementById('appointment-date-picker');
        if (datePicker) {
            datePicker.addEventListener('change', function(e) {
                // The wire:model.live will handle the update automatically
                console.log('Date changed to:', e.target.value);
            });
        }
        
        // Listen for clinic selection to reset dropdown
        Livewire.on('clinic-selected', () => {
            const clinicDropdown = document.getElementById('clinic-select-dropdown');
            if (clinicDropdown) {
                clinicDropdown.value = '';
            }
        });
    });
    
    // Also initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            const datePicker = document.getElementById('appointment-date-picker');
            if (datePicker) {
                datePicker.addEventListener('change', function(e) {
                    console.log('Date changed to:', e.target.value);
                });
            }
        });
    }
</script>
@endpush


