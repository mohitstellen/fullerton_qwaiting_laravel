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
use Carbon\Carbon;

#[Layout('components.layouts.patient-layout')]
#[Title('Shopping Cart')]
class PatientCart extends Component
{
    public $cartItems = [];
    public $member;
    public $teamId;
    
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
    }
    
    public function loadCartItems()
    {
        $cart = Session::get('patient_cart', []);
        $this->cartItems = $cart;
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
    
    public function render()
    {
        return view('livewire.patient-cart');
    }
}

