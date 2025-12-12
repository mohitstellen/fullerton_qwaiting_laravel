<div class="px-4 py-6 sm:px-0">
    <div class="max-w-7xl mx-auto">
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
                                <tr class="{{ $index % 2 == 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700' }}">
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
                                            wire:click="viewOrderDetails({{ $appointment['id'] }})"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                            {{ $appointment['refID'] }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 px-3 py-1 bg-blue-100 dark:bg-blue-900 rounded">
                                                Reschedule
                                            </button>
                                            <button class="text-gray-600 hover:text-gray-900 dark:text-gray-400 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                                                Cancel
                                            </button>
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
                            <p class="text-sm text-gray-600 dark:text-gray-400">Name : {{ $selectedBooking->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Order No : <span class="font-semibold">{{ $selectedBooking->refID }}</span></p>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div>
                        <div class="bg-blue-600 text-white px-4 py-2 mb-2">
                            <h5 class="font-semibold">Service</h5>
                        </div>
                        <div class="space-y-2 p-4 bg-gray-50 dark:bg-gray-700 rounded">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $selectedBooking->categories ? $selectedBooking->categories->name : '' }}
                                @if($selectedBooking->sub_category)
                                    - {{ $selectedBooking->sub_category->name }}
                                @endif
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>Booking Date/Time:</strong> 
                                {{ \Carbon\Carbon::parse($selectedBooking->booking_date)->format('d/m/Y') }} 
                                {{ $selectedBooking->start_time ? \Carbon\Carbon::parse($selectedBooking->start_time)->format('g:iA') : '' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>Name:</strong> {{ $selectedBooking->name }}
                            </p>
                            @if($selectedBooking->date_of_birth)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Date of Birth:</strong> {{ \Carbon\Carbon::parse($selectedBooking->date_of_birth)->format('d/m/Y') }}
                                </p>
                            @endif
                            @php
                                $bookingJson = json_decode($selectedBooking->json, true);
                                $nricFin = $bookingJson['nric_fin'] ?? null;
                                $passport = $bookingJson['passport'] ?? null;
                            @endphp
                            @if($nricFin || $passport)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>NRIC/FIN/Passport No:</strong> 
                                    @if($nricFin)
                                        {{ substr($nricFin, 0, 1) }}****
                                    @elseif($passport)
                                        {{ substr($passport, 0, 1) }}****
                                    @endif
                                </p>
                            @endif
                            @if($selectedBooking->gender)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Gender:</strong> {{ $selectedBooking->gender }}
                                </p>
                            @endif
                            @if($selectedBooking->location)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>Location:</strong> {{ $selectedBooking->location->location_name }}
                                    @if($selectedBooking->location->address)
                                        <br>{{ $selectedBooking->location->address }}
                                    @endif
                                </p>
                            @endif
                        </div>
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
</div>

