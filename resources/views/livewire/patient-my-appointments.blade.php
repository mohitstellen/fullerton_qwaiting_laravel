<div class="px-4 py-6 sm:px-0">
    <div class="max-w-7xl mx-auto">
        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-4 p-4 text-green-700 bg-green-100 border border-green-400 rounded-lg dark:bg-green-800 dark:text-green-200 dark:border-green-600">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 p-4 text-red-700 bg-red-100 border border-red-400 rounded-lg dark:bg-red-800 dark:text-red-200 dark:border-red-600">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white pb-2 border-b-2 border-orange-500">
                    My Appointments
                </h1>
            </div>

            @if(count($appointments) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-blue-600 dark:bg-blue-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">S.No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Appointment For</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Appointment Date / Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Appointment Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">My Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($appointments as $index => $appointment)
                                <tr class="{{ $index % 2 == 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700' }} cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition"
                                    wire:click="viewOrderDetails({{ $appointment['order_id'] }})"
                                    style="cursor: pointer;">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $appointment['appointment_for'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $appointment['appointment_date_time'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $appointment['appointment_status'] == 'Reserved' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $appointment['appointment_status'] == 'Confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $appointment['appointment_status'] == 'Cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $appointment['appointment_status'] == 'Completed' ? 'bg-blue-100 text-blue-800' : '' }}">
                                            {{ $appointment['appointment_status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $appointment['service'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button 
                                            wire:click.stop="viewOrderDetails({{ $appointment['order_id'] }})"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                            {{ $appointment['refID'] }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2" onclick="event.stopPropagation()">
                                            @if($appointment['appointment_status'] !== 'Cancelled')
                                                <button 
                                                    wire:click.stop="openRescheduleModal({{ $appointment['id'] }})"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 px-3 py-1 bg-blue-100 dark:bg-blue-900 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition">
                                                    Reschedule
                                                </button>
                                                <button 
                                                    wire:click.stop="confirmCancelAppointment({{ $appointment['id'] }})"
                                                    class="text-gray-600 hover:text-gray-900 dark:text-gray-400 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                                    Cancel
                                                </button>
                                            @else
                                                <button 
                                                    disabled
                                                    class="text-gray-400 dark:text-gray-500 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded cursor-not-allowed opacity-50">
                                                    Cancelled
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center">
                    <p class="text-gray-600 dark:text-gray-400">No appointments found.</p>
                    <a href="{{ route('tenant.patient.book-appointment') }}" 
                        class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        Book an Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Order Details Modal -->
    @if($showOrderDetails && $selectedBooking)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
            wire:click="closeOrderDetails">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" 
                wire:click.stop>
                <!-- Close Button -->
                <button wire:click="closeOrderDetails" 
                    class="absolute top-4 right-4 text-blue-600 hover:text-blue-800 text-xl font-bold">
                    ×
                </button>

                <div class="space-y-4">
                    <!-- Company Header -->
                    <div class="flex justify-between items-start border-b-2 border-blue-600 pb-4">
                        <div class="flex items-center">
                            <img src="{{ asset('images/logo-transparent.png') }}" alt="Logo" class="h-12 w-auto mr-3">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Fullerton Healthcare Group Pte Ltd</h4>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <p>Hotline: +65 6333 3636</p>
                                    <p>Email: <a href="mailto:ehs@fullertonhealth.com" class="text-blue-600 dark:text-blue-400">ehs@fullertonhealth.com</a></p>
                                    <p>Website: <a href="https://www.fullertonhealth.com" target="_blank" class="text-blue-600 dark:text-blue-400">www.fullertonhealth.com</a></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Title -->
                    <div class="text-center border-b-2 border-blue-600 pb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Order</h3>
                    </div>

                    <!-- Order Info -->
                    <div class="flex justify-between items-center border-b-2 border-blue-600 pb-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Name : {{ $selectedBooking->patient_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Order No : <span class="font-semibold">{{ $selectedBooking->order_number }}</span></p>
                        </div>
                    </div>

                    <!-- Service Details - Show all bookings for this order -->
                    <div>
                        <div class="bg-blue-600 text-white px-4 py-2 mb-2">
                            <h5 class="font-semibold">Service</h5>
                        </div>
                        
                        @foreach($selectedBookingOrders as $booking)
                            @php
                                // Build service name from booking categories (without location - shown separately)
                                $serviceName = '';
                                if ($booking->categories) {
                                    $serviceName = $booking->categories->name ?? '';
                                    if ($booking->sub_category) {
                                        $serviceName .= ' - ' . $booking->sub_category->name;
                                    }
                                }
                                
                                // Format booking date/time
                                $bookingDateTime = '';
                                if ($booking->booking_date) {
                                    $bookingDateTime = \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y');
                                    if ($booking->booking_time) {
                                        try {
                                            $timeParts = explode('-', $booking->booking_time);
                                            $startTime = trim($timeParts[0] ?? '');
                                            $bookingDateTime .= ' ' . \Carbon\Carbon::createFromFormat('H:i', $startTime)->format('h:iA');
                                        } catch (\Exception $e) {
                                            // If parsing fails, try to format as is
                                            $bookingDateTime .= ' ' . $booking->booking_time;
                                        }
                                    }
                                }
                                
                                // Get patient details from booking
                                $name = $booking->name ?? $selectedBooking->patient_name ?? '';
                                $dateOfBirth = $booking->date_of_birth;
                                $gender = $booking->gender ?? '';
                                
                                // Get location name from locations table using location_id
                                $location = '';
                                if ($booking->location_id) {
                                    if ($booking->location) {
                                        $location = $booking->location->location_name;
                                    } else {
                                        $locationModel = \App\Models\Location::find($booking->location_id);
                                        $location = $locationModel ? $locationModel->location_name : '';
                                    }
                                }
                                
                                // Get identification from booking (orders table no longer stores this)
                                // Note: bookings table may not have nric_fin/passport, get from member if needed
                                $nricFin = '';
                                $passport = '';
                                // If booking has member relationship, get from there, otherwise leave empty
                            @endphp
                            <div class="space-y-2 p-4 bg-gray-50 dark:bg-gray-700 rounded mb-4">
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    {{ $serviceName }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Booking Date/Time:</strong> {{ $bookingDateTime }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Name:</strong> {{ $name }}
                                </p>
                                @if($dateOfBirth)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Date of Birth:</strong> 
                                        {{ \Carbon\Carbon::parse($dateOfBirth)->format('d/m/Y') }}
                                    </p>
                                @endif
                                @if($nricFin || $passport)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>NRIC/FIN/Passport No:</strong> 
                                        @if($nricFin)
                                            @php
                                                $nric = $nricFin;
                                                if (strlen($nric) > 4) {
                                                    $first = substr($nric, 0, 1);
                                                    $last = substr($nric, -3);
                                                    echo $first . str_repeat('*', strlen($nric) - 4) . $last;
                                                } else {
                                                    echo $nric;
                                                }
                                            @endphp
                                        @elseif($passport)
                                            @php
                                                $passportVal = $passport;
                                                if (strlen($passportVal) > 4) {
                                                    $first = substr($passportVal, 0, 1);
                                                    $last = substr($passportVal, -3);
                                                    echo $first . str_repeat('*', strlen($passportVal) - 4) . $last;
                                                } else {
                                                    echo $passportVal;
                                                }
                                            @endphp
                                        @endif
                                    </p>
                                @endif
                                @if($gender)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Gender:</strong> {{ $gender }}
                                    </p>
                                @endif
                                @if($location)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Location:</strong> {{ $location }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Note -->
                    <div class="border-t pt-4">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>Note:</strong> Rescheduling an appointment must be done at least 48 hours in advance of the appointment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reschedule Modal -->
    @if($showRescheduleModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
            wire:click="closeRescheduleModal">
            <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto" 
                wire:click.stop>
                <!-- Header -->
                <div class="flex justify-between items-center bg-blue-800 text-white px-6 py-4 rounded-t-md mb-4">
                    <h2 class="text-xl font-bold">Reschedule</h2>
                    <button wire:click="closeRescheduleModal" 
                        class="text-white hover:text-gray-200 text-2xl font-bold">
                        ×
                    </button>
                </div>

                <form wire:submit.prevent="rescheduleAppointment" class="space-y-6 px-6 pb-6">
                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Location
                        </label>
                        <div class="relative">
                            <select wire:model.live="rescheduleLocationId" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Please Choose</option>
                                @foreach($rescheduleLocations as $location)
                                    <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('rescheduleLocationId') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Location Details Card -->
                    @if($rescheduleLocationId && $rescheduleSelectedLocation)
                        <div class="border-2 border-red-500 rounded-lg p-6 bg-white dark:bg-gray-800">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                                <!-- Left Section: Clinic Details -->
                                <div class="space-y-4">
                                    <div class="flex items-start">
                                        <svg class="w-6 h-6 text-gray-700 dark:text-gray-300 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ $rescheduleSelectedLocation->location_name }}
                                        </h3>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                            @if($rescheduleSelectedLocation->address)
                                                @php
                                                    $addressParts = preg_split('/[,\n]/', $rescheduleSelectedLocation->address);
                                                @endphp
                                                @foreach($addressParts as $part)
                                                    @if(trim($part))
                                                        {{ trim($part) }}<br>
                                                    @endif
                                                @endforeach
                                            @endif
                                            @if($rescheduleSelectedLocation->city || $rescheduleSelectedLocation->state || $rescheduleSelectedLocation->zip)
                                                @if($rescheduleSelectedLocation->city){{ $rescheduleSelectedLocation->city }}@endif
                                                @if($rescheduleSelectedLocation->state){{ $rescheduleSelectedLocation->city ? ', ' : '' }}{{ $rescheduleSelectedLocation->state }}@endif
                                                @if($rescheduleSelectedLocation->zip){{ ($rescheduleSelectedLocation->city || $rescheduleSelectedLocation->state) ? ' ' : '' }}{{ $rescheduleSelectedLocation->zip }}@endif
                                            @endif
                                        </p>
                                    </div>

                                    @if($rescheduleLocationPhone)
                                        <div>
                                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                                <span class="font-semibold">Tel :</span> {{ $rescheduleLocationPhone }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($rescheduleLocationBusinessHours && is_array($rescheduleLocationBusinessHours))
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
                                                        foreach($rescheduleLocationBusinessHours as $day) {
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
                                @if($rescheduleSelectedLocation && ($rescheduleSelectedLocation->longitude && $rescheduleSelectedLocation->latitude))
                                    <div class="w-full">
                                        <div class="w-full rounded-lg overflow-hidden border border-gray-300" style="height: 250px;">
                                            @php
                                                $mapUrl = "https://www.google.com/maps?q={$rescheduleSelectedLocation->latitude},{$rescheduleSelectedLocation->longitude}&hl=en&z=15&output=embed";
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
                                @elseif($rescheduleSelectedLocation && $rescheduleSelectedLocation->map_link)
                                    <div class="w-full">
                                        <div class="w-full rounded-lg overflow-hidden border border-gray-300" style="height: 250px;">
                                            <iframe 
                                                src="{{ $rescheduleSelectedLocation->map_link }}" 
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
                    @if($rescheduleLocationId)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Date & Time
                            </label>
                            
                            <!-- Date Picker -->
                            <div class="mb-4">
                                <input type="date" 
                                    wire:model.live="rescheduleAppointmentDate" 
                                    onclick="this.showPicker()"
                                    min="{{ date('Y-m-d') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('rescheduleAppointmentDate') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                @enderror
                            </div>

                            <!-- Time Slots -->
                            @if($rescheduleAppointmentDate)
                                @if(count($rescheduleAvailableTimeSlots) > 0)
                                    <div class="mb-2">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Available time slots:</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($rescheduleAvailableTimeSlots as $slot)
                                                <button type="button" 
                                                    wire:click="selectRescheduleTimeSlot('{{ $slot }}')"
                                                    class="px-4 py-2 rounded-lg border-2 transition {{ $rescheduleAppointmentTime === $slot ? 'bg-green-500 text-white border-green-600' : 'bg-green-100 hover:bg-green-200 border-green-300 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600' }}">
                                                    {{ $slot }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('rescheduleAppointmentTime') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No available time slots for this date.</p>
                                @endif
                            @endif
                        </div>
                    @endif

                    <!-- Additional Comments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Additional Comments
                        </label>
                        <textarea 
                            wire:model="rescheduleAdditionalComments"
                            rows="3"
                            placeholder="Comments"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none"></textarea>
                    </div>

                    <!-- Booking Availability Legend -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Booking availability
                        </label>
                        <div class="flex gap-4">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Available</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-gray-300 rounded mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Fully booked</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button 
                            type="button"
                            wire:click="closeRescheduleModal"
                            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition">
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                            Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    function setupCancelAppointmentListener() {
        Livewire.on('show-cancel-confirmation', (data) => {
            console.log('Cancel confirmation event received:', data);
            
            // Extract bookingId from event data
            // Livewire 3 passes data as first parameter, which could be array or object
            let bookingId = null;
            if (Array.isArray(data)) {
                bookingId = data[0]?.bookingId || data[0];
            } else if (data && typeof data === 'object') {
                bookingId = data.bookingId;
            } else {
                bookingId = data;
            }
            
            console.log('Extracted bookingId:', bookingId);
            
            if (!bookingId) {
                console.error('Booking ID not found in event:', data);
                return;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to cancel this appointment? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, cancel it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('User confirmed cancellation for bookingId:', bookingId);
                        // Dispatch event to confirm cancellation
                        console.log('Dispatching confirmed-cancel-appointment event with bookingId:', bookingId);
                        Livewire.dispatch('confirmed-cancel-appointment', { bookingId: bookingId });
                    }
                });
            } else {
                // Fallback to browser confirm if SweetAlert is not loaded
                if (confirm('Are you sure you want to cancel this appointment? This action cannot be undone.')) {
                    Livewire.dispatch('confirmed-cancel-appointment', { bookingId: bookingId });
                }
            }
        });
    }

    // Setup listener when Livewire initializes
    document.addEventListener('livewire:init', () => {
        setupCancelAppointmentListener();
    });

    // Also setup if Livewire is already initialized (for cases where script loads after Livewire)
    if (window.Livewire && document.readyState === 'complete') {
        setupCancelAppointmentListener();
    }

    // Setup listener for reschedule success
    function setupRescheduleSuccessListener() {
        Livewire.on('appointment-rescheduled', (data) => {
            const message = Array.isArray(data) 
                ? (data[0]?.message || data[0] || 'Appointment has been rescheduled successfully.')
                : (data?.message || 'Appointment has been rescheduled successfully.');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Success!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
            }
        });

        // Setup listener for reschedule error
        Livewire.on('appointment-reschedule-error', (data) => {
            const message = Array.isArray(data) 
                ? (data[0]?.message || data[0] || 'Failed to reschedule appointment. Please try again.')
                : (data?.message || 'Failed to reschedule appointment. Please try again.');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                });
            }
        });
    }

    // Setup listener when Livewire initializes
    document.addEventListener('livewire:init', () => {
        setupRescheduleSuccessListener();
    });

    // Also setup if Livewire is already initialized
    if (window.Livewire && document.readyState === 'complete') {
        setupRescheduleSuccessListener();
    }
</script>

