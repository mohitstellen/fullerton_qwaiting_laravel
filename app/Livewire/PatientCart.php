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
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\InvalidRequestException;

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
    
    public function removeFromCart($itemId)
    {
        $cart = Session::get('patient_cart', []);
        $cart = array_filter($cart, function($item) use ($itemId) {
            return $item['id'] !== $itemId;
        });
        $cart = array_values($cart); // Re-index array
        Session::put('patient_cart', $cart);
        
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
        
        // If this method is called, it means the button condition passed, so payment should be required
        // Just verify the essential conditions are still met
        if ($this->paymentSetting && 
            $this->paymentSetting->enable_payment == 1 && 
            $this->grandTotal > 0 &&
            !empty($this->paymentSetting->api_key) && 
            !empty($this->paymentSetting->api_secret) &&
            $this->paymentSetting->stripe_enable == 1) {
            
            // Show payment step - this is what we want!
            $this->paymentStep = 1;
            $this->isFree = 1;
            
            // Set Stripe config to ensure it's current
            Config::set([
                'services.stripe.key' => $this->paymentSetting->api_key,
                'services.stripe.secret' => $this->paymentSetting->api_secret,
            ]);
            
            // Dispatch event to initialize Stripe card element
            $this->dispatch('cardElement');
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
                'amount' => $paymentIntent->amount,
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
             
            $bookings = [];
            
            // Create INDIVIDUAL bookings (one per cart item) - all linked to the SAME order
            foreach ($this->cartItems as $item) {
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
            }
            
            DB::commit();
            
            // Clear checkout flag
            Session::forget('checkout_in_progress');
            
            // Clear cart
            Session::forget('patient_cart');
            $this->cartItems = [];
            
            session()->flash('checkout_success', 'All appointments have been booked successfully! Order Number: ' . $order->order_number);
            
            // Redirect to appointments page
            $this->redirect(route('tenant.patient.appointments'), navigate: true);
            
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
            return false;
        }
        
        if ($this->paymentSetting->enable_payment != 1) {
            return false;
        }
        
        if ($this->grandTotal <= 0) {
            return false;
        }
        
        // $paymentApplicableTo = $this->paymentSetting->payment_applicable_to ?? 'walkin';
        // if ($paymentApplicableTo != 'patient' && $paymentApplicableTo != 'both') {
        //     return false;
        // }

        
        if (empty($this->paymentSetting->api_key) || empty($this->paymentSetting->api_secret)) {
            return false;
        }
        
        if ($this->paymentSetting->stripe_enable != 1) {
            return false;
        }
        
        return true;
    }
    
    public function render()
    {
        return view('livewire.patient-cart');
    }
}

