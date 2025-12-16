<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Shopping Cart
    </h1>

    @if (session()->has('cart_success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('cart_success') }}</span>
        </div>
    @endif

    @if (session()->has('cart_message'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('cart_message') }}</span>
        </div>
    @endif

    @if (session()->has('cart_error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('cart_error') }}</span>
        </div>
    @endif

    @if (count($cartItems) > 0)
        <!-- Cart Header -->
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg mb-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Shopping Cart
                </h2>
            </div>
        </div>

        <!-- Column Headers -->
        <div class="bg-blue-600 text-white px-6 py-3 rounded-t-lg grid grid-cols-2 mb-0">
            <div class="font-semibold">Service</div>
            <div class="font-semibold text-right">Amount</div>
        </div>

        <!-- Cart Items -->
        <div class="space-y-0 border border-gray-200 dark:border-gray-700 rounded-b-lg">
            @foreach($cartItems as $index => $item)
                <div class="border-b border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-gray-800 {{ $loop->last ? 'rounded-b-lg' : '' }}">
                    <!-- Service Name -->
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $item['service_name'] }}
                        </h3>
                    </div>

                    <!-- Details -->
                    <div class="space-y-2 mb-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Booking Date/Time:</span> {{ $item['booking_date_time'] }}
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Name:</span> {{ $item['name'] }}
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Date of Birth:</span> {{ $item['date_of_birth'] }}
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">NRIC/FIN/Passport No:</span>
                            @php
                                $nric = $item['nric_fin_passport'] ?? '';
                                if (strlen($nric) > 4) {
                                    $first = substr($nric, 0, 1);
                                    $last = substr($nric, -3);
                                    echo $first . str_repeat('*', strlen($nric) - 4) . $last;
                                } else {
                                    echo $nric;
                                }
                            @endphp
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Gender:</span> {{ $item['gender'] }}
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Location:</span> {{ $item['location_name'] }}
                        </p>
                    </div>

                    <!-- Amount and Remove Button -->
                    <div class="flex justify-between items-start pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                <span class="font-medium">Amount:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($item['package_amount'] ?? 0, 2) }}</span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                                Note: Payment will be collected at clinic (if applicable).
                            </p>
                        </div>
                        <button 
                            wire:click="removeFromCart('{{ $item['id'] }}')"
                            wire:confirm="Are you sure you want to remove this item from cart?"
                            class="text-red-600 hover:text-red-800 transition-colors p-2 ml-4"
                            title="Remove from cart"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Order Summary -->
        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="max-w-md ml-auto space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Sub Total:</span>
                    <span class="text-gray-900 dark:text-white font-semibold">${{ number_format($this->subTotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Code Applied:</span>
                    <span class="text-gray-900 dark:text-white font-semibold">${{ number_format($this->codeApplied, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total:</span>
                    <span class="text-gray-900 dark:text-white font-semibold">${{ number_format($this->total, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">GST 9%:</span>
                    <span class="text-gray-900 dark:text-white font-semibold">${{ number_format($this->gstAmount, 2) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-gray-900 dark:text-white">Grand Total:</span>
                    <span class="text-gray-900 dark:text-white">${{ number_format($this->grandTotal, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex gap-4">
            <a href="{{ route('tenant.patient.book-appointment') }}" 
                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition text-center">
                Continue Shopping
            </a>
            <button 
                wire:click="checkout"
                wire:loading.attr="disabled"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition disabled:opacity-50">
                <span wire:loading.remove wire:target="checkout">Checkout</span>
                <span wire:loading wire:target="checkout">Processing...</span>
            </button>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="text-center py-12">
            <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Your cart is empty</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Add appointments to your cart to continue.</p>
            <a href="{{ route('tenant.patient.book-appointment') }}" 
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                Book an Appointment
            </a>
        </div>
    @endif
</div>

