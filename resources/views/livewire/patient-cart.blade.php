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

        <!-- Payment Step -->
        @if($this->paymentStep == 1)
        <div class="paymentStep mt-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <!-- Payment Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                        <h2 class="text-2xl font-bold text-slate-950">{{ __('text.Payment') }}</h2>
                        <p class="text-slate-950 text-sm mt-1">Complete your payment securely</p>
                    </div>

                    <!-- Payment Form -->
                    <div class="p-6 space-y-6" wire:ignore>
                        <!-- Email Field -->
                        <div>
                            <label for="payment-email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="payment-email"
                                wire:model.defer="email"
                                placeholder="your.email@example.com"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <p class="text-xs text-gray-500 mt-1">We'll send your receipt to this email</p>
                        </div>

                        <!-- Card Details Field -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Card Details <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div id="card-element" class="px-4 py-3 border border-gray-300 rounded-lg bg-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all">
                                    <!-- Stripe Elements will mount here -->
                                </div>
                                <!-- Error display area for Stripe -->
                                <div id="card-errors" role="alert" class="mt-2 text-sm text-red-600 hidden"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Your card information is secure and encrypted</p>
                        </div>

                        <!-- Security Badge -->
                        <div class="flex items-center justify-center pt-2 pb-4 border-t border-gray-200">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span class="text-xs text-gray-600">Secured by Stripe</span>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <div class="px-6 pb-6">
                        <button
                            id="pay-btn"
                            data-pay-text="{{ __('text.Pay') }}"
                            data-pay-amount="{{ number_format($this->grandTotal, 2) }}"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="button-text">{{ __('text.Pay') }} <span id="pay-amount">${{ number_format($this->grandTotal, 2) }}</span></span>
                            <svg
                                id="pay-loader"
                                class="ml-2 h-5 w-5 text-white animate-spin hidden"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-6 flex gap-4">
            <a href="{{ route('tenant.patient.book-appointment') }}" 
                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-6 rounded-lg transition text-center">
                Continue Shopping
            </a>
            @if($this->paymentStep == 1)
            {{-- Payment step is active, show payment UI instead of button --}}
            @elseif($this->paymentRequired)
            <button 
                wire:click="showPaymentPage"
                wire:loading.attr="disabled"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition disabled:opacity-50">
                <span wire:loading.remove wire:target="showPaymentPage">Checkout</span>
                <span wire:loading wire:target="showPaymentPage">Processing...</span>
            </button>
            @else
            <button 
                wire:click="checkout"
                wire:loading.attr="disabled"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition disabled:opacity-50">
                <span wire:loading.remove wire:target="checkout">Checkout</span>
                <span wire:loading wire:target="checkout">Processing...</span>
            </button>
            @endif
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

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    let stripe;
    let card;

    // Wait for Stripe to be available
    function waitForStripe(callback, maxAttempts = 50) {
        let attempts = 0;
        const checkStripe = setInterval(() => {
            attempts++;
            if (typeof Stripe !== 'undefined') {
                clearInterval(checkStripe);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkStripe);
                console.error('Stripe library failed to load.');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Payment Error',
                        text: 'Stripe payment library failed to load. Please refresh the page.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        }, 100);
    }

    Livewire.on('cardElement', () => {
        waitForStripe(() => {
            setTimeout(() => {
                const cardContainer = document.getElementById('card-element');

                if (!cardContainer) {
                    console.error('Stripe card element not found.');
                    return;
                }

                // Check if paymentSettingKey is available
                const paymentKey = "{{ $this->paymentSettingKey ?? '' }}";
                if (!paymentKey) {
                    console.error('Stripe payment key is not configured.');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Payment Error',
                            text: 'Payment gateway is not configured.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                    return;
                }

                try {
                    // Initialize Stripe
                    stripe = Stripe(paymentKey);
                    const elements = stripe.elements({
                        appearance: {
                            theme: 'stripe',
                            variables: {
                                colorPrimary: '#4f46e5',
                                colorBackground: '#ffffff',
                                colorText: '#1f2937',
                                colorDanger: '#ef4444',
                                fontFamily: 'system-ui, sans-serif',
                                spacingUnit: '4px',
                                borderRadius: '8px',
                            },
                        },
                    });
                    
                    // Create card element with custom styling
                    card = elements.create('card', {
                        style: {
                            base: {
                                fontSize: '16px',
                                color: '#1f2937',
                                '::placeholder': {
                                    color: '#9ca3af',
                                },
                            },
                            invalid: {
                                color: '#ef4444',
                                iconColor: '#ef4444',
                            },
                        },
                    });

                    // Mount card
                    card.mount('#card-element');

                    // Handle real-time validation errors from the card Element
                    card.on('change', function(event) {
                        const displayError = document.getElementById('card-errors');
                        if (event.error) {
                            displayError.textContent = event.error.message;
                            displayError.classList.remove('hidden');
                        } else {
                            displayError.classList.add('hidden');
                        }
                    });

                    console.log('Card mounted.');

                    // ✅ Attach Pay button listener here
                    const payBtn = document.getElementById('pay-btn');
                    const loader = document.getElementById('pay-loader');
                    const buttonText = payBtn ? payBtn.querySelector('.button-text') : null;
                    
                    if (payBtn && buttonText) {
                        // Remove any existing listeners to prevent duplicates
                        const newPayBtn = payBtn.cloneNode(true);
                        payBtn.parentNode.replaceChild(newPayBtn, payBtn);
                        
                        newPayBtn.addEventListener('click', async () => {
                            if (!stripe || !card) {
                                console.log('Card is not ready yet.');
                                return;
                            }

                            // Disable button and show loader
                            newPayBtn.disabled = true;
                            const btnText = newPayBtn.querySelector('.button-text');
                            const btnLoader = document.getElementById('pay-loader');
                            
                            if (btnText) btnText.textContent = 'Processing...';
                            if (btnLoader) btnLoader.classList.remove('hidden');

                            try {
                                const {
                                    paymentMethod,
                                    error
                                } = await stripe.createPaymentMethod({
                                    type: 'card',
                                    card: card
                                });

                                if (error) {
                                    console.error('Stripe error:', error.message);
                                    
                                    // Show error in card-errors div
                                    const displayError = document.getElementById('card-errors');
                                    if (displayError) {
                                        displayError.textContent = error.message || 'Please check your card details.';
                                        displayError.classList.remove('hidden');
                                    }
                                    
                                    // Re-enable button
                                    newPayBtn.disabled = false;
                                    if (btnText) {
                                        const payText = newPayBtn.getAttribute('data-pay-text') || 'Pay';
                                        const payAmount = newPayBtn.getAttribute('data-pay-amount') || '0.00';
                                        const payAmountEl = document.getElementById('pay-amount');
                                        if (payAmountEl) {
                                            btnText.innerHTML = payText + ' <span id="pay-amount">$' + payAmount + '</span>';
                                        } else {
                                            btnText.textContent = payText + ' $' + payAmount;
                                        }
                                    }
                                    if (btnLoader) btnLoader.classList.add('hidden');
                                    
                                    // Scroll to error
                                    if (displayError) {
                                        displayError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                    }
                                } else {
                                    // Clear any previous errors
                                    const displayError = document.getElementById('card-errors');
                                    if (displayError) {
                                        displayError.classList.add('hidden');
                                    }
                                    Livewire.dispatch('stripe-payment-method', {
                                        paymentMethodId: paymentMethod.id
                                    });

                                }
                            } catch (err) {
                                console.error('Payment processing error:', err);
                                newPayBtn.disabled = false;
                                if (btnText) {
                                    const payText = newPayBtn.getAttribute('data-pay-text') || 'Pay';
                                    const payAmount = newPayBtn.getAttribute('data-pay-amount') || '0.00';
                                    btnText.textContent = payText + ' $' + payAmount;
                                }
                                if (btnLoader) btnLoader.classList.add('hidden');
                                
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: 'Payment Error',
                                        text: 'An error occurred while processing payment. Please try again.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            }
                        });
                    } else {
                        console.warn('Pay button or button text not found.');
                    }
                } catch (err) {
                    console.error('Error initializing Stripe:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Payment Error',
                            text: 'Failed to initialize payment gateway. Please refresh the page.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            }, 300); // Delay to ensure DOM is ready
        });
    });

    // Setup listener for payment success
    function setupPaymentSuccessListener() {
        Livewire.on('payment-success', (data) => {
            // Extract message from event data
            const message = Array.isArray(data) 
                ? (data[0]?.message || data[0] || 'Payment successful! Your appointments have been booked successfully!')
                : (data?.message || 'Payment successful! Your appointments have been booked successfully!');
            
            if (typeof Swal !== 'undefined') {
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
            } else {
                // Fallback: redirect directly if SweetAlert is not available
                window.location.href = '{{ route("tenant.patient.appointments") }}';
            }
        });
    }

    // Setup listener when Livewire initializes
    document.addEventListener('livewire:init', () => {
        setupPaymentSuccessListener();
    });

    // Also setup if Livewire is already initialized
    if (window.Livewire && document.readyState === 'complete') {
        setupPaymentSuccessListener();
    }
</script>
<script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>
@endpush

