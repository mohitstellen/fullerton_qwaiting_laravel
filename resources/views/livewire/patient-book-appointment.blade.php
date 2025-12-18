<div class="px-4 py-6 sm:px-0">
    <div class="max-w-7xl mx-auto">
        <!-- Success Message -->
        @if($successMessage)
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <p class="font-semibold">{{ $successMessage }}</p>
            </div>
        @endif

        <!-- Step 1: Choose Appointment Type & Package -->
        @if($step == 1)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left: Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 pb-2 border-b-2 border-yellow-400">
                        Choose Appointment Type & Package
                    </h2>

                    <form wire:submit.prevent="nextToLocation" class="space-y-6">
                        <!-- Appointment Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Appointment Type <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select wire:model.live="appointmentTypeId" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Please Choose</option>
                                    @foreach($appointmentTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <span class="absolute right-3 top-2.5 text-gray-400 pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </span>
                            </div>
                            @error('appointmentTypeId') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Booking For (Self/Dependent) -->
                        @if($appointmentTypeId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Booking For <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-6">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="bookingFor" value="Self" 
                                            class="w-4 h-4 text-yellow-600 focus:ring-yellow-500 border-gray-300">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Self</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="bookingFor" value="Dependent" 
                                            class="w-4 h-4 text-yellow-600 focus:ring-yellow-500 border-gray-300">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Dependent</span>
                                    </label>
                                </div>
                                @error('bookingFor') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                @enderror
                            </div>
                        @endif

                        <!-- Package -->
                        @if($appointmentTypeId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Package <span class="text-red-500">*</span>
                                </label>
                                @if(count($packages) > 0)
                                    <div class="relative">
                                        <select wire:model.live="packageId" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Please Choose</option>
                                            @foreach($packages as $package)
                                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="absolute right-3 top-2.5 text-gray-400 pointer-events-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                    </div>
                                @else
                                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">No packages available for this appointment type.</p>
                                    </div>
                                @endif
                                @error('packageId') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                @enderror
                            </div>
                        @endif

                        <!-- Next Button -->
                        <div class="pt-4">
                            <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                                Next
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Package Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                        <h3 class="text-lg font-semibold">Package Details</h3>
                    </div>
                    <div class="p-6 max-h-[600px] overflow-y-auto">
                        @if($selectedPackage)
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ $selectedPackage->name }}
                                        @if($selectedPackage->amount)
                                            <span class="text-blue-600 dark:text-blue-400 block mt-1">${{ number_format($selectedPackage->amount, 2) }} before GST</span>
                                        @endif
                                    </h4>
                                </div>
                                
                                @if($selectedPackage->description)
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white mb-2">Package includes:</p>
                                        <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                                            {!! nl2br(e($selectedPackage->description)) !!}
                                        </div>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                        No description available for this package.
                                    </div>
                                @endif
                                
                                @if($selectedPackage->note)
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                            {{ $selectedPackage->note }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Select a package to view details</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 2: Choose Location & Schedule -->
        @if($step == 2)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left: Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 pb-2 border-b-2 border-yellow-400">
                        Choose Location & Schedule
                    </h2>

                    <form wire:submit.prevent="bookAppointment" class="space-y-6">
                        <!-- Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Location
                            </label>
                            <div class="relative">
                                <select wire:model.live="locationId" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Please Choose</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('locationId') 
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Location Details Card -->
                        @if($locationId && $selectedLocation)
                            <div class="border-2 border-red-500 rounded-lg p-6 bg-white dark:bg-gray-800">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                                    <!-- Left Section: Clinic Details -->
                                    <div class="space-y-4">
                                        <!-- Clinic Name with Icon -->
                                        <div class="flex items-start">
                                            <svg class="w-6 h-6 text-gray-700 dark:text-gray-300 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                            </svg>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                                {{ $selectedLocation->location_name }}
                                            </h3>
                                        </div>

                                        <!-- Address -->
                                        <div>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                                @if($selectedLocation->address)
                                                    @php
                                                        // Split address by comma or newline
                                                        $addressParts = preg_split('/[,\n]/', $selectedLocation->address);
                                                    @endphp
                                                    @foreach($addressParts as $part)
                                                        @if(trim($part))
                                                            {{ trim($part) }}<br>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if($selectedLocation->city || $selectedLocation->state || $selectedLocation->zip)
                                                    @if($selectedLocation->city){{ $selectedLocation->city }}@endif
                                                    @if($selectedLocation->state){{ $selectedLocation->city ? ', ' : '' }}{{ $selectedLocation->state }}@endif
                                                    @if($selectedLocation->zip){{ ($selectedLocation->city || $selectedLocation->state) ? ' ' : '' }}{{ $selectedLocation->zip }}@endif
                                                @endif
                                            </p>
                                        </div>

                                        <!-- Contact -->
                                        @if($locationPhone)
                                            <div>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                                    <span class="font-semibold">Tel :</span> {{ $locationPhone }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Operating Hours -->
                                        @if($locationBusinessHours && is_array($locationBusinessHours))
                                            <div>
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Operating Hours</p>
                                                        @php
                                                            $daysOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                                            $groupedDays = [];
                                                            foreach($locationBusinessHours as $day) {
                                                                if(isset($day['is_closed']) && $day['is_closed'] == \App\Models\ServiceSetting::SERVICE_OPEN) {
                                                                    $key = ($day['start_time'] ?? '') . '-' . ($day['end_time'] ?? '');
                                                                    if(!isset($groupedDays[$key])) {
                                                                        $groupedDays[$key] = ['times' => ($day['start_time'] ?? '') . ' - ' . ($day['end_time'] ?? ''), 'days' => []];
                                                                    }
                                                                    $groupedDays[$key]['days'][] = $day['day'] ?? '';
                                                                }
                                                            }
                                                        @endphp
                                                        @foreach($groupedDays as $group)
                                                            @php
                                                                $daysStr = implode(', ', $group['days']);
                                                                // Format: Mon-Fri or individual days
                                                                if(count($group['days']) > 1) {
                                                                    $firstDay = reset($group['days']);
                                                                    $lastDay = end($group['days']);
                                                                    $daysStr = $firstDay . '-' . $lastDay;
                                                                }
                                                            @endphp
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                {{ $daysStr }} : {{ $group['times'] }}
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Right Section: Map -->
                                    @if($selectedLocation && ($selectedLocation->longitude && $selectedLocation->latitude))
                                        <div class="w-full">
                                            <div class="w-full rounded-lg overflow-hidden border border-gray-300" style="height: 250px;">
                                                @php
                                                    // Build Google Maps embed URL using coordinates
                                                    $mapUrl = "https://www.google.com/maps?q={$selectedLocation->latitude},{$selectedLocation->longitude}&hl=en&z=15&output=embed";
                                                @endphp
                                                <iframe 
                                                    width="100%" 
                                                    height="100%" 
                                                    style="border:0; height: 250px;" 
                                                    loading="lazy" 
                                                    allowfullscreen
                                                    referrerpolicy="no-referrer-when-downgrade"
                                                    src="{{ $mapUrl }}">
                                                </iframe>
                                            </div>
                                        </div>
                                    @elseif($selectedLocation && $selectedLocation->map_link)
                                        <div class="w-full">
                                            <div class="w-full rounded-lg overflow-hidden border border-gray-300" style="height: 250px;">
                                                <iframe 
                                                    src="{{ $selectedLocation->map_link }}" 
                                                    width="100%" 
                                                    height="100%" 
                                                    style="border:0; height: 250px;" 
                                                    allowfullscreen="" 
                                                    loading="lazy" 
                                                    referrerpolicy="no-referrer-when-downgrade">
                                                </iframe>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Date & Time -->
                        @if($locationId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Date & Time
                                </label>
                                
                                <!-- Date Picker -->
                                <div class="mb-4">
                                    <input type="date" 
                                        wire:model.live="appointmentDate" 
                                        onclick="this.showPicker()"
                                        min="{{ date('Y-m-d') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('appointmentDate') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>

                                <!-- Time Slots -->
                                @if($appointmentDate)
                                    @if(count($availableTimeSlots) > 0)
                                        <div class="mb-2">
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Available time slots:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($availableTimeSlots as $slot)
                                                    <button type="button" 
                                                        wire:click="selectTimeSlot('{{ $slot }}')"
                                                        class="px-4 py-2 rounded-lg border-2 transition {{ $appointmentTime === $slot ? 'bg-green-500 text-white border-green-600' : 'bg-green-100 hover:bg-green-200 border-green-300 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600' }}">
                                                        {{ $slot }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('appointmentTime') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    @else
                                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                            <p class="text-sm text-yellow-800 dark:text-yellow-200">No available time slots for this date. Please select another date.</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        <!-- Additional Comments -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Additional Comments
                            </label>
                            <textarea wire:model="additionalComments" 
                                rows="4"
                                placeholder="Comments"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none"></textarea>
                        </div>

                        <!-- Booking Availability Legend -->
                        <div class="flex items-center gap-4 text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Booking availability:</span>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-green-500 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">Available</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-gray-300 rounded"></div>
                                <span class="text-gray-600 dark:text-gray-400">Fully booked</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4 pt-4">
                            <button type="button" 
                                wire:click="goBack"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg transition">
                                Go Back
                            </button>
                            @if($isPrivateCustomer)
                                <button type="button" 
                                    wire:click="addToCart"
                                    wire:loading.attr="disabled"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="addToCart">Add To Cart</span>
                                    <span wire:loading wire:target="addToCart">Adding...</span>
                                </button>
                            @else
                                <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="bookAppointment">Book Appointment</span>
                                    <span wire:loading wire:target="bookAppointment">Booking...</span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Right: Map (if location selected) -->
                @if($selectedLocation && $selectedLocation->map_link)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Location Map</h3>
                        <div class="aspect-video rounded-lg overflow-hidden">
                            <iframe 
                                src="{{ $selectedLocation->map_link }}" 
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Step 3: Success -->
        @if($step == 3)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Booking Successful!</h2>
                <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">{{ $successMessage }}</p>
                <div class="flex gap-4 justify-center">
                    <a href="{{ route('tenant.patient.dashboard') }}" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                        Back to Dashboard
                    </a>
                    <a href="{{ route('tenant.patient.appointments') }}" 
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                        View My Appointments
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('booking-success', (data) => {
            if (typeof Swal !== 'undefined') {
                // Extract message from event data
                const message = Array.isArray(data) 
                    ? (data[0]?.message || data[0] || 'Your appointment has been booked successfully!')
                    : (data?.message || 'Your appointment has been booked successfully!');
                
                Swal.fire({
                    title: 'Success!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to My Appointments page
                        window.location.href = '{{ route("tenant.patient.appointments") }}';
                    }
                });
            }
        });
    });
</script>
@endpush

