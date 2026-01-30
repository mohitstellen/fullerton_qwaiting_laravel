<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Location;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\StripeResponse;
use App\Models\SmtpDetails;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\InvalidRequestException;
use App\Services\DaraAPIService;
use App\Models\IntegrationToken;
use App\Models\SiteDetail;
use App\Mail\PatientAppointmentConfirmation;
use Illuminate\Support\Facades\Mail;
use App\Services\PlatoAPIService;
use App\Models\Member;

#[Layout('components.layouts.patient-layout')]
#[Title('Shopping Cart')]
class PatientCart extends Component
{
    public $cartItems = [];
    public $member;
    public $teamId;

    // Payment-related properties
    public $paymentStep = 0;
    public $paymentSetting;
    public $paymentSettingKey;
    public $paymentSettingSecret;
    public $paymentMethodId;
    public $email;
    public $stripeResponeID;
    public $isFree = 0;
    public $successMessage;
    public $errorMessage;

    public function mount()
    {
        // Check if patient is logged in
        if (!Session::has('patient_member_id')) {
            $this->redirect(route('tenant.patient.login'), navigate: true);
            return;
        }

        $this->teamId = tenant('id');
        $memberId = Session::get('patient_member_id');

        $this->member = \App\Models\Member::where('team_id', $this->teamId)
            ->where('id', $memberId)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->first();

        if (!$this->member) {
            Session::forget(['patient_member_id', 'patient_member', 'patient_customer_type']);
            $this->redirect(route('tenant.patient.login'), navigate: true);
            return;
        }

        // Load cart items from session
        $this->loadCartItems();

        // Initialize payment settings
        $this->initializePaymentSettings();

        // Check payment requirement
        $this->checkPaymentRequirement();
    }

    public function initializePaymentSettings()
    {
        // Reset payment settings if cart is empty
        if (empty($this->cartItems)) {
            $this->paymentSetting = null;
            $this->paymentSettingKey = null;
            $this->paymentSettingSecret = null;
            $this->email = $this->member->email ?? '';
            $this->stripeResponeID = '';
            return;
        }

        // Get location_id from first cart item
        $locationId = $this->cartItems[0]['location_id'] ?? null;

        if ($locationId) {
            $this->paymentSetting = PaymentSetting::where('team_id', $this->teamId)
                ->where('location_id', $locationId)
                ->first();

            if ($this->paymentSetting) {
                if (!empty($this->paymentSetting->api_key) && !empty($this->paymentSetting->api_secret)) {
                    $this->paymentSettingKey = $this->paymentSetting->api_key;
                    $this->paymentSettingSecret = $this->paymentSetting->api_secret;

                    Config::set([
                        'services.stripe.key' => $this->paymentSetting->api_key,
                        'services.stripe.secret' => $this->paymentSetting->api_secret,
                    ]);
                }
            }
        }

        // Set email from member
        $this->email = $this->member->email ?? '';
        $this->stripeResponeID = '';
    }

    public function loadCartItems()
    {
        $cart = Session::get('patient_cart', []);

        // Check if cart timer has expired
        $this->checkAndClearExpiredCart();

        // Reload cart after expiration check
        $cart = Session::get('patient_cart', []);
        $this->cartItems = $cart;

        // Re-initialize payment settings when cart changes
        if (!empty($this->cartItems)) {
            $this->initializePaymentSettings();
            $this->checkPaymentRequirement();
        } else {
            $this->isFree = 0;
            $this->paymentStep = 0;
        }
    }

    /**
     * Check if cart timer has expired and clear cart if needed
     * Can be called from JavaScript interval or Livewire polling
     */
    public function checkAndClearExpiredCart()
    {
        $cart = Session::get('patient_cart', []);
        $cartTimerStart = Session::get('cart_timer_start');
        $cartTimerDuration = Session::get('cart_timer_duration', 9900); // Default 2 hours 45 minutes

        if ($cartTimerStart && !empty($cart)) {
            $elapsedSeconds = now()->timestamp - $cartTimerStart;

            // If timer has expired, clear the cart
            if ($elapsedSeconds >= $cartTimerDuration) {
                Session::forget('patient_cart');
                Session::forget('cart_timer_start');
                Session::forget('cart_timer_duration');
                session()->flash('cart_error', 'Your cart has expired. Please add items again.');
                return true; // Cart was expired
            }
        }

        return false; // Cart is still valid
    }

    public function removeFromCart($itemId)
    {
        $cart = Session::get('patient_cart', []);
        $cart = array_filter($cart, function ($item) use ($itemId) {
            return $item['id'] !== $itemId;
        });
        $cart = array_values($cart); // Re-index array
        Session::put('patient_cart', $cart);

        // Clear timer if cart is empty
        if (empty($cart)) {
            Session::forget('cart_timer_start');
            Session::forget('cart_timer_duration');
        }

        $this->loadCartItems();

        session()->flash('cart_message', 'Item removed from cart successfully.');
    }

    public function showPaymentPage()
    {
        if (empty($this->cartItems)) {
            session()->flash('cart_error', 'Your cart is empty.');
            return;
        }

        // Ensure payment settings are loaded
        if (empty($this->paymentSetting)) {
            $this->initializePaymentSettings();
        }

        // Re-check payment requirement to ensure settings are up to date
        // Preserve paymentStep during check since we're about to set it
        $this->checkPaymentRequirement(true);

        Log::info('showPaymentPage called', [
            'has_payment_setting' => !empty($this->paymentSetting),
            'enable_payment' => $this->paymentSetting->enable_payment ?? null,
            'grand_total' => $this->grandTotal,
            'has_api_key' => !empty($this->paymentSetting->api_key ?? null),
            'has_api_secret' => !empty($this->paymentSetting->api_secret ?? null),
            'stripe_enable' => $this->paymentSetting->stripe_enable ?? null,
        ]);

        // If this method is called, it means the button condition passed, so payment should be required
        // Just verify the essential conditions are still met
        if (
            $this->paymentSetting &&
            $this->paymentSetting->enable_payment == 1 &&
            $this->grandTotal > 0 &&
            !empty($this->paymentSetting->api_key) &&
            !empty($this->paymentSetting->api_secret) &&
            $this->paymentSetting->stripe_enable == 1
        ) {

            Log::info('Payment conditions met - showing payment modal', [
                'grand_total' => $this->grandTotal,
                'team_id' => $this->teamId,
            ]);

            // Show payment step - this is what we want!
            $this->paymentStep = 1;
            $this->isFree = 1;

            // Set Stripe config to ensure it's current
            Config::set([
                'services.stripe.key' => $this->paymentSetting->api_key,
                'services.stripe.secret' => $this->paymentSetting->api_secret,
            ]);

            // Dispatch event to initialize Stripe card element and open modal
            $this->dispatch('cardElement');
            $this->dispatch('payment-modal-open');

            Log::info('Payment modal events dispatched', [
                'paymentStep' => $this->paymentStep,
            ]);
        } else {
            // Settings changed or invalid - this shouldn't happen if button condition was correct
            // But handle it gracefully
            Log::warning('Payment conditions not met in showPaymentPage', [
                'has_payment_setting' => !empty($this->paymentSetting),
                'enable_payment' => $this->paymentSetting->enable_payment ?? null,
                'grand_total' => $this->grandTotal,
                'has_api_key' => !empty($this->paymentSetting->api_key ?? null),
                'has_api_secret' => !empty($this->paymentSetting->api_secret ?? null),
                'stripe_enable' => $this->paymentSetting->stripe_enable ?? null,
            ]);

            $this->paymentStep = 0;
            if ($this->grandTotal <= 0) {
                // Free order, proceed to checkout
                Log::info('Free order detected, proceeding to checkout directly');
                $this->checkout();
            } else {
                session()->flash('cart_error', 'Payment gateway is not properly configured. Please contact support.');
            }
        }
    }

    public function checkPaymentRequirement($preservePaymentStep = false)
    {
        // Reset to default
        $this->isFree = 0;
        if (!$preservePaymentStep) {
            $this->paymentStep = 0;
        }

        // Check if paymentSetting exists
        if (empty($this->paymentSetting)) {
            return;
        }

        // Check if both API key and secret are set and Stripe is enabled
        if (empty($this->paymentSetting->api_key) || empty($this->paymentSetting->api_secret) || ($this->paymentSetting->stripe_enable != 1)) {
            return;
        }

        // Set Stripe config
        Config::set([
            'services.stripe.key' => $this->paymentSetting->api_key,
            'services.stripe.secret' => $this->paymentSetting->api_secret,
        ]);

        // Check if payment is enabled and grand total > 0
        if ($this->paymentSetting->enable_payment == 1 && $this->grandTotal > 0) {
            // Check payment_applicable_to setting
            $paymentApplicableTo = $this->paymentSetting->payment_applicable_to ?? 'walkin';
            if ($paymentApplicableTo == 'patient' || $paymentApplicableTo == 'both') {
                $this->isFree = 1; // Payment required
            }
        }
    }

    #[On('stripe-payment-method')]
    public function setPaymentMethod(string $paymentMethodId)
    {
        $this->paymentMethodId = $paymentMethodId;
        $this->handleCheckout();
    }

    public function handleCheckout()
    {
        Log::info('handleCheckout called - payment processing started', [
            'team_id' => $this->teamId,
            'grand_total' => $this->grandTotal,
            'has_payment_method_id' => !empty($this->paymentMethodId),
        ]);

        try {
            // Ensure payment settings are loaded
            if (empty($this->paymentSetting)) {
                $this->initializePaymentSettings();
            }

            // Use the secret key directly from payment setting to ensure it matches the publishable key
            if (empty($this->paymentSettingSecret)) {
                throw new \Exception('Payment secret key is not configured.');
            }

            // Set the API key directly from payment setting
            Stripe::setApiKey($this->paymentSettingSecret);

            // Verify the payment method exists and is valid
            if (empty($this->paymentMethodId)) {
                throw new \Exception('Payment method ID is missing.');
            }

            Log::info('Creating payment intent', [
                'team_id' => $this->teamId,
                'amount' => $this->grandTotal,
                'payment_method_id' => substr($this->paymentMethodId, 0, 10) . '...',
                'currency' => $this->paymentSetting->currency ?? 'sgd',
            ]);

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($this->grandTotal * 100),
                'currency' => strtolower($this->paymentSetting->currency) ?? 'sgd',
                'payment_method' => $this->paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'receipt_email' => $this->email,
                'return_url' => route('payment.success'),
            ]);

            $locationId = !empty($this->cartItems) ? ($this->cartItems[0]['location_id'] ?? null) : null;

            $stripeResponse = StripeResponse::create([
                'team_id' => $this->teamId,
                'location_id' => $locationId,
                'category_id' => null, // For cart, we don't have a single category
                'payment_intent_id' => $paymentIntent->id,
                'customer_email' => $this->email,
                'amount' => $this->grandTotal,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'full_response' => $paymentIntent->toArray(),
            ]);
            $this->stripeResponeID = $stripeResponse->id;

            Log::info("Payment done for cart checkout", [
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
            ]);

            // Proceed with checkout after successful payment
            $this->checkout();

            $this->paymentStep = 0;
            $this->isFree = 0;
            $this->email = '';

            $this->successMessage = 'Payment successful!';
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::error('Stripe payment failed: teamID ' . $this->teamId . ' = ' . $e->getMessage(), [
                'stripe_error_type' => $e->getStripeCode(),
                'payment_method_id' => substr($this->paymentMethodId ?? '', 0, 10) . '...',
            ]);
            $this->errorMessage = 'Payment failed: ' . $e->getMessage();
            $this->paymentStep = 0;

            // Show user-friendly error
            session()->flash('cart_error', 'Payment processing failed. Please try again or use a different payment method.');
        } catch (\Exception $e) {
            Log::error('Payment failed: teamID ' . $this->teamId . ' = ' . $e->getMessage());
            $this->errorMessage = 'Payment failed: Something went Wrong';
            $this->paymentStep = 0;

            session()->flash('cart_error', 'Payment processing failed. Please try again.');
        }
    }

    public function checkout()
    {
        Log::info('checkout method called', [
            'team_id' => $this->teamId,
            'cart_items_count' => count($this->cartItems),
            'grand_total' => $this->grandTotal,
            'stripe_response_id' => $this->stripeResponeID ?? 'none',
            'called_from' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3),
        ]);

        if (empty($this->cartItems)) {
            session()->flash('cart_error', 'Your cart is empty.');
            return;
        }

        // Prevent duplicate checkout attempts
        if (Session::has('checkout_in_progress')) {
            session()->flash('cart_error', 'Checkout is already in progress. Please wait.');
            return;
        }

        try {
            // Set checkout flag to prevent duplicates
            Session::put('checkout_in_progress', true);

            DB::beginTransaction();

            // Generate a unique refID for all bookings in this checkout
            $refID = time();

            // Calculate order totals
            $subTotal = $this->subTotal;
            $codeApplied = $this->codeApplied;
            $total = $this->total;
            $gstAmount = $this->gstAmount;
            $grandTotal = $this->grandTotal;

            // Create ONE order for all cart items (created BEFORE the loop)
            $order = Order::create([
                'team_id' => $this->teamId,
                'member_id' => $this->member->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'total_amount' => $total,
                'gst_amount' => $gstAmount,
                'grand_total' => $grandTotal,
                'refID' => $refID,
            ]);

            // Dara API - Get Token (Run once for all items)
            $daraApi = new DaraAPIService();
            $getAccessToken = null;

            // Check for stored token
            $storedToken = IntegrationToken::where('service_name', 'dara')->first();

            if ($storedToken) {
                try {
                    $getAccessTokenRes = $daraApi->refreshToken($storedToken->refresh_token);
                    $refreshTokenResponse = $getAccessTokenRes->json();

                    if (isset($refreshTokenResponse['success']) && $refreshTokenResponse['success'] == 'true') {
                        Log::info('Cart Checkout: Refresh Token API Run Successfully via DB token');
                        $getAccessToken = $refreshTokenResponse['data']['access_token'];

                        // Update token if refresh token is new provided or keep existing
                        $newRefreshToken = $refreshTokenResponse['data']['refresh_token'] ?? $storedToken->refresh_token;

                        $storedToken->update([
                            'refresh_token' => $newRefreshToken,
                            'access_token' => $getAccessToken
                        ]);
                    }
                } catch (\Exception $ex) {
                    Log::error('Cart Checkout: Failed to refresh token using stored token', ['error' => $ex->getMessage()]);
                }
            }

            // If no access token yet, fetch new pair
            if (!$getAccessToken) {
                $generateTokenPair = $daraApi->getTokenPair();
                $tokenPairResponse = $generateTokenPair->json();

                if (isset($tokenPairResponse['success']) && $tokenPairResponse['success'] == 'true') {
                    Log::info('Cart Checkout: Token Pair API Run Successfully');

                    $getAccessToken = $tokenPairResponse['data']['access_token'];
                    $getRefreshToken = $tokenPairResponse['data']['refresh_token'];

                    IntegrationToken::updateOrCreate(
                        ['service_name' => 'dara'],
                        [
                            'refresh_token' => $getRefreshToken,
                            'access_token' => $getAccessToken
                        ]
                    );
                }
            }

            if (!$getAccessToken) {
                Log::warning('Cart Checkout: Dara API createAppointment will not be called - no access token available', [
                    'has_stored_token' => !empty($storedToken),
                    'get_access_token' => $getAccessToken
                ]);
            }

            $bookings = [];

            // Create INDIVIDUAL bookings (one per cart item) - all linked to the SAME order
            foreach ($this->cartItems as $item) {
                // Determine booking person (Self or Dependent)
                $bookingPerson = $this->member; // Default to self
                if (isset($item['booking_for']) && $item['booking_for'] === 'Dependent' && !empty($item['dependent_id'])) {
                    $dependent = Member::find($item['dependent_id']);
                    if ($dependent) {
                        $bookingPerson = $dependent;
                    }
                }

                // Parse time slot and convert to 24-hour format
                $timeParts = explode('-', $item['booking_time']);
                $startTime12h = trim($timeParts[0] ?? '');
                $endTime12h = trim($timeParts[1] ?? $startTime12h);

                // Convert from 12-hour format to 24-hour format
                try {
                    $startTimeCarbon = Carbon::createFromFormat('h:i A', $startTime12h);
                    $startTime = $startTimeCarbon->format('H:i');
                } catch (\Exception $e) {
                    $startTimeCarbon = Carbon::createFromFormat('h:iA', $startTime12h);
                    $startTime = $startTimeCarbon->format('H:i');
                }

                try {
                    $endTimeCarbon = Carbon::createFromFormat('h:i A', $endTime12h);
                    $endTime = $endTimeCarbon->format('H:i');
                } catch (\Exception $e) {
                    $endTimeCarbon = Carbon::createFromFormat('h:iA', $endTime12h);
                    $endTime = $endTimeCarbon->format('H:i');
                }

                // Format booking_time in 24-hour format
                $bookingTime24h = $startTime . ($endTime !== $startTime ? '-' . $endTime : '');

                // Get appointment type and package
                $appointmentType = Category::find($item['appointment_type_id']);
                $package = $item['package_id'] ? Category::find($item['package_id']) : null;
                $location = Location::find($item['location_id']);

                // Dara API Call
                $daraTokenForQuestionnaire = null;
                if ($getAccessToken) {
                    $daraApiBooking = [
                        'sampleid' => $item['nric_fin_passport'] ?? $bookingPerson->nric_fin ?? $bookingPerson->passport ?? '',
                        'email' => $bookingPerson->email ?? $this->member->email,
                        'phone' => $bookingPerson->mobile_number ?? $this->member->mobile_number,
                        'name' => ($bookingPerson->salutation ? $bookingPerson->salutation . ' ' : '') . $bookingPerson->full_name,
                        'appointmentdate' => $item['booking_date'] . 'T' .  $startTime . ':00',
                        'additional_info' => [
                            'key' => 'value'
                        ]
                    ];

                    Log::info('Cart Checkout: Calling Dara API createAppointment', [
                        'access_token' => substr($getAccessToken, 0, 10) . '...',
                        'booking_data' => $daraApiBooking
                    ]);

                    try {
                        $createAppointment = $daraApi->createAppointment($getAccessToken, $daraApiBooking);
                        
                        // Log the raw response
                        Log::info('Cart Checkout: Dara API createAppointment raw response', [
                            'status' => $createAppointment->status(),
                            'successful' => $createAppointment->successful(),
                            'body' => $createAppointment->body(),
                        ]);
                        
                        // Convert response to array if it's an HTTP response object
                        $appointmentResponse = is_array($createAppointment) ? $createAppointment : $createAppointment->json();
                        
                        // Log the parsed response
                        Log::info('Cart Checkout: Dara API createAppointment parsed response', [
                            'response' => $appointmentResponse,
                            'is_null' => is_null($appointmentResponse),
                            'is_array' => is_array($appointmentResponse)
                        ]);

                        if (!empty($appointmentResponse) && isset($appointmentResponse['success']) && $appointmentResponse['success'] == 'true') {
                        Log::info('Cart Checkout: Patient Appointment Booking Successfully via Dara API', [
                            'createAppointment' => $appointmentResponse,
                        ]);
                        $daraTokenForQuestionnaire = $appointmentResponse['data']['token'] ?? null;
                    } else {
                        Log::info('Cart Checkout: Dara API response indicates failure, checking error type', [
                            'success_value' => $appointmentResponse['success'] ?? 'not_set',
                            'success_type' => gettype($appointmentResponse['success'] ?? null),
                            'message' => $appointmentResponse['message'] ?? 'not_set',
                        ]);
                        
                        // Check if response is null or empty
                        if (empty($appointmentResponse)) {
                            Log::error('Cart Checkout: Dara API returned empty response', [
                                'status' => $createAppointment->status(),
                                'body' => $createAppointment->body(),
                                'payload' => $daraApiBooking
                            ]);
                        } else {
                            // Check if the error is "Appointment already exists"
                            $errorMessage = $appointmentResponse['message'] ?? '';
                            $successValue = $appointmentResponse['success'] ?? null;
                            
                            Log::info('Cart Checkout: Checking for "Appointment already exists" error', [
                                'success_value' => $successValue,
                                'success_is_false' => ($successValue === false || $successValue === 'false'),
                                'error_message' => $errorMessage,
                                'message_contains_text' => (stripos($errorMessage, 'Appointment already exists') !== false),
                            ]);
                            
                            if (
                                isset($appointmentResponse['success']) && 
                                ($appointmentResponse['success'] === false || $appointmentResponse['success'] === 'false') &&
                                (stripos($errorMessage, 'Appointment already exists') !== false)
                            ) {
                                Log::info('Cart Checkout: "Appointment already exists" error detected - showing user message');
                                DB::rollBack();
                                Session::forget('checkout_in_progress');
                                session()->flash('checkout_error', 'Booking already exists.');
                                // Dispatch event for SweetAlert
                                $this->dispatch('booking-already-exists');
                                Log::error('Cart Checkout: Appointment already exists', [
                                    'api_response' => $appointmentResponse,
                                    'payload' => $daraApiBooking
                                ]);
                                return;
                            }
                            
                            Log::error('Cart Checkout: Patient Book Appointment failed via Dara API', [
                                'error' => 'API Error',
                                'api_response' => $appointmentResponse ?? null,
                                'payload' => $daraApiBooking
                            ]);
                        }
                    }
                    } catch (\Exception $apiEx) {
                        Log::error('Cart Checkout: Dara API createAppointment exception', [
                            'error' => $apiEx->getMessage(),
                            'trace' => $apiEx->getTraceAsString(),
                            'booking_data' => $daraApiBooking
                        ]);
                    }
                }

                // Create one booking per cart item
                $booking = Booking::create([
                    'team_id' => $this->teamId,
                    'location_id' => $item['location_id'],
                    'booking_date' => $item['booking_date'],
                    'booking_time' => $bookingTime24h,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'category_id' => $item['appointment_type_id'],
                    'sub_category_id' => $item['package_id'],
                    'refID' => $refID,
                    'status' => Booking::STATUS_RESERVED,
                    'is_private_customer' => true,
                    'booking_for' => $item['booking_for'] ?? 'Self',
                    'dependent_id' => ($item['booking_for'] ?? '') === 'Dependent' ? ($item['dependent_id'] ?? null) : null,
                    'name' => $item['name'],
                    'email' => $this->member->email,
                    'phone' => $this->member->mobile_number,
                    'phone_code' => $this->member->mobile_country_code ?? '+65',
                    'date_of_birth' => !empty($item['date_of_birth'])
                        ? Carbon::createFromFormat('d/m/Y', $item['date_of_birth'])->format('Y-m-d')
                        : $this->member->date_of_birth,
                    'gender' => $item['gender'] ?? '',
                    'nationality' => $this->member->nationality,
                    'identification_type' => $this->member->identification_type,
                    'additional_comments' => $item['additional_comments'] ?? '',
                ]);

                $bookings[] = $booking;

                // Link booking to order via pivot table (use insertOrIgnore to prevent duplicates)
                DB::table('booking_order')->insertOrIgnore([
                    'booking_id' => $booking->id,
                    'order_id' => $order->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Send Questionnaire Email if Dara API was successful
                if ($daraTokenForQuestionnaire) {
                    $url = "https://testquestionaire.s3-ap-southeast-1.amazonaws.com/fullerton-questionnaire/index.html?token=" . $daraTokenForQuestionnaire;

                    try {
                        $siteDetail = SiteDetail::where('team_id', $this->teamId)
                            ->where('location_id', $item['location_id'])
                            ->select('business_logo')
                            ->first();

                        $logo = isset($siteDetail) && $siteDetail->business_logo
                            ? url('storage/' . $siteDetail->business_logo)
                            : '';

                        $recipientEmail = $bookingPerson->email ?? $this->member->email;
                        $recipientName = ($bookingPerson->salutation ? $bookingPerson->salutation . ' ' : '') . $bookingPerson->full_name;

                        $appointmentDetails = [
                            'booking_date' => $item['booking_date'],
                            'booking_time' => $startTime12h,
                            'location' => $item['location_name'] ?? '',
                            'service_name' => $item['service_name'] ?? '',
                        ];

                        // Fetch SMTP Details from DB and configure mailer
                        $smtpDetails = SmtpDetails::where('team_id', $this->teamId)
                            ->where('location_id', $item['location_id'])
                            ->first();

                        if (
                            $smtpDetails && !empty($smtpDetails->hostname) && !empty($smtpDetails->port) &&
                            !empty($smtpDetails->username) && !empty($smtpDetails->password) &&
                            !empty($smtpDetails->from_email) && !empty($smtpDetails->from_name)
                        ) {
                            Config::set('mail.mailers.smtp.transport', 'smtp');
                            Config::set('mail.mailers.smtp.host', trim($smtpDetails->hostname));
                            Config::set('mail.mailers.smtp.port', trim($smtpDetails->port));
                            Config::set('mail.mailers.smtp.encryption', trim($smtpDetails->encryption ?? 'ssl'));
                            Config::set('mail.mailers.smtp.username', trim($smtpDetails->username));
                            Config::set('mail.mailers.smtp.password', trim($smtpDetails->password));
                            Config::set('mail.from.address', trim($smtpDetails->from_email));
                            Config::set('mail.from.name', trim($smtpDetails->from_name));
                        }

                        Mail::to($recipientEmail)->send(new PatientAppointmentConfirmation($recipientName, $url, $logo, $appointmentDetails));

                        Log::info('Cart Checkout: Questionnaire email sent successfully', ['email' => $recipientEmail]);
                    } catch (\Exception $e) {
                        Log::error('Cart Checkout: Failed to send questionnaire email', ['error' => $e->getMessage()]);
                    }
                }
            }

            DB::commit();

            // Store cart items before clearing (needed for email sending)
            $cartItemsCopy = $this->cartItems;

            // Send appointment confirmation emails for all customers
            try {
                // Send email for each booking
                foreach ($bookings as $index => $booking) {
                    // Get the original cart item for this booking (use index to match)
                    $cartItem = $cartItemsCopy[$index] ?? null;

                    if ($cartItem && $booking) {
                        // Parse time slot for display
                        $timeParts = explode('-', $cartItem['booking_time']);
                        $startTime12h = trim($timeParts[0] ?? '');

                        // Get appointment type
                        $appointmentType = Category::find($booking->category_id);

                        if ($appointmentType) {
                            // Prepare email data
                            $location = $booking->location;
                            $emailData = [
                                'to_mail' => $this->member->email,
                                'name' => $cartItem['name'] ?? ($this->member->salutation ? $this->member->salutation . ' ' : '') . $this->member->full_name,
                                'booking_id' => $refID,
                                'booking_date' => Carbon::parse($booking->booking_date)->format('d/m/Y'),
                                'booking_time' => $startTime12h,
                                'refID' => $refID,
                                'category_name' => $appointmentType->name ?? '',
                                'service_name' => $appointmentType->name ?? '',
                                'locations_id' => $booking->location_id,
                                'location' => $location?->location_name ?? '',
                                'clinic' => $location?->location_name ?? '',
                                'created_by' => $booking->createdBy?->name ?? '',
                            ];

                            // Send email
                            SmtpDetails::sendAppointmentConfirmationEmail(
                                $emailData,
                                $this->teamId,
                                $booking->location_id,
                                $booking->category_id
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail the checkout
                Log::error('Failed to send appointment confirmation emails in cart checkout: ' . $e->getMessage());
            }

            // Clear checkout flag
            Session::forget('checkout_in_progress');

            // Clear cart and timer
            Session::forget('patient_cart');
            Session::forget('cart_timer_start');
            Session::forget('cart_timer_duration');
            $this->cartItems = [];

            // Plato API Integration - Search and Create Patient
            try {
                $platoApi = new PlatoAPIService();

                // Get NRIC for search - use the main member
                $nric = $this->member->nric_fin ?? $this->member->passport ?? '';

                if (!empty($nric)) {
                    // Search for patient in Plato
                    $searchResponse = $platoApi->searchPatient($nric);
                    $searchData = $searchResponse->json();

                    Log::info('Plato API - Search Patient Response', [
                        'nric' => $nric,
                        'status' => $searchResponse->status(),
                        'response' => $searchData
                    ]);

                    // Check if patient exists
                    // Patient not found: API returns 200 OK with empty array []
                    // Patient found: API returns 200 OK with array containing patient data
                    $patientExists = $searchResponse->successful() &&
                        is_array($searchData) &&
                        count($searchData) > 0;

                    if (!$patientExists) {
                        // Patient not found - create new patient
                        Log::info('Patient not found in Plato, creating new patient', ['nric' => $nric]);

                        // Prepare patient data with ALL fields from Plato API
                        // Basic information fields are populated, rest are empty
                        $patientData = [
                            // Basic Information (populated)
                            'name' => $this->member->full_name,
                            'nric' => $nric,
                            'dob' => $this->member->date_of_birth ? Carbon::parse($this->member->date_of_birth)->format('Y-m-d') : '',
                            'marital_status' => $this->member->marital_status ?? '',
                            'sex' => $this->member->gender ?? '',
                            'nationality' => $this->member->nationality ?? '',
                            'email' => $this->member->email ?? '',
                            'telephone' => $this->member->mobile_number ?? '',
                            'nric_type' => $this->member->identification_type ?? 'NRIC',
                            'title' => $this->member->salutation ?? '',

                            // Additional fields (empty)
                            'given_id' => '',
                            'allergies_select' => 'No',
                            'allergies' => '',
                            'food_allergies_select' => 'No',
                            'food_allergies' => '',
                            'g6pd' => 'No',
                            'alerts' => '',
                            'address' => '',
                            'postal' => '',
                            'unit_no' => '',
                            'telephone2' => '',
                            'telephone3' => '',
                            'dnd' => '0',
                            'occupation' => '',
                            'doctor' => [],
                            'notes' => '',
                            'custom' => (object)[],
                            'referred_by' => '',
                            'nok' => [],
                            'tag' => [],
                            'corporate' => [],
                            'other' => (object)[],
                        ];

                        // Create patient in Plato
                        $createResponse = $platoApi->createPatient($patientData);

                        Log::info('Plato API - Create Patient Response', [
                            'patient_data' => $patientData,
                            'status' => $createResponse->status(),
                            'response' => $createResponse->json()
                        ]);

                        if ($createResponse->successful()) {
                            Log::info('Patient created successfully in Plato', [
                                'nric' => $nric,
                                'patient_id' => $createResponse->json()['_id'] ?? 'N/A'
                            ]);
                        } else {
                            Log::error('Failed to create patient in Plato', [
                                'nric' => $nric,
                                'status' => $createResponse->status(),
                                'error' => $createResponse->body()
                            ]);
                        }
                    } else {
                        // Patient found - log the existing patient info
                        $existingPatient = $searchData[0] ?? [];
                        Log::info('Patient already exists in Plato', [
                            'nric' => $nric,
                            'patient_id' => $existingPatient['_id'] ?? 'N/A',
                            'patient_name' => $existingPatient['name'] ?? 'N/A'
                        ]);
                    }
                } else {
                    Log::warning('No NRIC/Passport found for Plato API integration', [
                        'member_id' => $this->member->id
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the booking
                Log::error('Plato API integration failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            session()->flash('checkout_success', 'All appointments have been booked successfully! Order Number: ' . $order->order_number);

            // Dispatch event to show SweetAlert - JavaScript will handle redirect
            $this->dispatch('payment-success', [
                'message' => 'Payment successful! All appointments have been booked successfully! Order Number: ' . $order->order_number
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Clear checkout flag on error
            Session::forget('checkout_in_progress');

            Log::error('Checkout error: ' . $e->getMessage());
            session()->flash('checkout_error', 'Failed to checkout. Please try again.');
        }
    }

    public function getSubTotalProperty()
    {
        $subTotal = 0;
        foreach ($this->cartItems as $item) {
            $subTotal += (float) ($item['package_amount'] ?? 0);
        }
        return $subTotal;
    }

    public function getCodeAppliedProperty()
    {
        // Placeholder for discount codes - can be implemented later
        return 0.00;
    }

    public function getTotalProperty()
    {
        return $this->subTotal - $this->codeApplied;
    }

    public function getGstAmountProperty()
    {
        // GST is 9% in Singapore
        return $this->total * 0.09;
    }

    public function getGrandTotalProperty()
    {
        return $this->total + $this->gstAmount;
    }

    public function getPaymentRequiredProperty()
    {
        // Check if payment is required
        if (empty($this->paymentSetting)) {
            Log::info('Payment not required: paymentSetting is empty');
            return false;
        }

        if ($this->paymentSetting->enable_payment != 1) {
            Log::info('Payment not required: enable_payment is not 1', [
                'enable_payment' => $this->paymentSetting->enable_payment,
            ]);
            return false;
        }

        if ($this->grandTotal <= 0) {
            Log::info('Payment not required: grandTotal is 0 or less', [
                'grand_total' => $this->grandTotal,
            ]);
            return false;
        }

        // $paymentApplicableTo = $this->paymentSetting->payment_applicable_to ?? 'walkin';
        // if ($paymentApplicableTo != 'patient' && $paymentApplicableTo != 'both') {
        //     return false;
        // }


        if (empty($this->paymentSetting->api_key) || empty($this->paymentSetting->api_secret)) {
            Log::info('Payment not required: API keys missing', [
                'has_api_key' => !empty($this->paymentSetting->api_key),
                'has_api_secret' => !empty($this->paymentSetting->api_secret),
            ]);
            return false;
        }

        if ($this->paymentSetting->stripe_enable != 1) {
            Log::info('Payment not required: stripe_enable is not 1', [
                'stripe_enable' => $this->paymentSetting->stripe_enable,
            ]);
            return false;
        }

        Log::info('Payment IS required - all conditions met', [
            'grand_total' => $this->grandTotal,
            'team_id' => $this->teamId,
        ]);

        return true;
    }

    public function render()
    {
        return view('livewire.patient-cart');
    }
}
