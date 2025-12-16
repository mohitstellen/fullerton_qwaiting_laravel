<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Booking,
    Member,
    Category,
    Location,
    AccountSetting,
    SiteDetail,
    Order
};
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

#[Layout('components.layouts.patient-layout')]
#[Title('My Appointments')]
class PatientMyAppointments extends Component
{
    public $teamId;
    public $member;
    public $appointments = [];
    public $selectedBooking = null;
    public $selectedBookingOrders = [];
    public $showOrderDetails = false;
    
    // Reschedule modal properties
    public $showRescheduleModal = false;
    public $rescheduleBookingId = null;
    public $rescheduleLocationId = null;
    public $rescheduleAppointmentDate = null;
    public $rescheduleAppointmentTime = null;
    public $rescheduleAdditionalComments = '';
    public $rescheduleLocations = [];
    public $rescheduleSelectedLocation = null;
    public $rescheduleAvailableTimeSlots = [];
    public $rescheduleLocationBusinessHours = null;
    public $rescheduleLocationPhone = null;

    public function mount()
    {
        // Check if patient is logged in
        if (!Session::has('patient_member_id')) {
            return redirect()->route('tenant.patient.login');
        }

        $this->teamId = tenant('id');
        $memberId = Session::get('patient_member_id');
        
        $this->member = Member::where('team_id', $this->teamId)
            ->where('id', $memberId)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->first();

        if (!$this->member) {
            Session::forget(['patient_member_id', 'patient_member', 'patient_customer_type']);
            return redirect()->route('tenant.patient.login')->with('error', 'Session expired. Please login again.');
        }

        $this->loadAppointments();
        
        // Set timezone
        $siteDetail = SiteDetail::where('team_id', $this->teamId)->first();
        if ($siteDetail && $siteDetail->select_timezone) {
            Config::set('app.timezone', $siteDetail->select_timezone);
            date_default_timezone_set($siteDetail->select_timezone);
        }
    }

    public function loadAppointments()
    {
        // Get all orders for this member
        $orders = Order::where('team_id', $this->teamId)
            ->where('member_id', $this->member->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $this->appointments = [];
        
        foreach ($orders as $order) {
            // Get all bookings linked to this order via pivot table
            $bookingIds = DB::table('booking_order')
                ->where('order_id', $order->id)
                ->pluck('booking_id')
                ->toArray();
            
            $bookings = Booking::whereIn('id', $bookingIds)
                ->with(['categories', 'sub_category', 'location'])
                ->orderBy('booking_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
            
            // Create a separate row for EACH booking
            foreach ($bookings as $booking) {
                // Build service name from booking categories
                $serviceName = '';
                if ($booking->categories) {
                    $serviceName = $booking->categories->name ?? '';
                    if ($booking->sub_category) {
                        $serviceName .= ' - ' . $booking->sub_category->name;
                    }
                    if ($booking->location) {
                        $serviceName .= ' - ' . $booking->location->location_name;
                    }
                }
                
                // Format booking date/time
                $appointmentDateTime = '';
                if ($booking->booking_date) {
                    $appointmentDateTime = Carbon::parse($booking->booking_date)->format('d/m/Y');
                    if ($booking->booking_time) {
                        try {
                            $timeParts = explode('-', $booking->booking_time);
                            $startTime = trim($timeParts[0] ?? '');
                            $appointmentDateTime .= ' ' . Carbon::createFromFormat('H:i', $startTime)->format('h:iA');
                        } catch (\Exception $e) {
                            // If parsing fails, try to format as is
                            $appointmentDateTime .= ' ' . $booking->booking_time;
                        }
                    }
                }
                
                // Create a row for each booking
                $this->appointments[] = [
                    'id' => $booking->id, // Use booking ID for actions
                    'order_id' => $order->id, // Keep order ID for viewing invoice
                    'booking_id' => $booking->id,
                    'order_number' => $order->order_number,
                    'refID' => $order->refID ?? $order->order_number,
                    'appointment_for' => $booking->name ?? $order->name ?? '',
                    'appointment_date_time' => $appointmentDateTime,
                    'appointment_status' => $booking->status ?? 'Reserved',
                    'service' => $serviceName,
                    'appointment_type' => $booking->categories->name ?? '',
                    'package' => $booking->sub_category->name ?? '',
                    'location' => $booking->location ? $booking->location->location_name : '',
                    'booking_date' => $booking->booking_date ?? '',
                    'booking_time' => $booking->booking_time ?? '',
                    'additional_comments' => $booking->additional_comments ?? '',
                    'order' => $order,
                    'booking' => $booking,
                ];
            }
        }
        
        // Sort appointments by booking date and time
        usort($this->appointments, function($a, $b) {
            $dateA = $a['booking_date'] ?? '';
            $dateB = $b['booking_date'] ?? '';
            if ($dateA === $dateB) {
                $timeA = $a['booking_time'] ?? '';
                $timeB = $b['booking_time'] ?? '';
                return strcmp($timeA, $timeB);
            }
            return strcmp($dateA, $dateB);
        });
    }

    public function viewOrderDetails($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('member_id', $this->member->id)
            ->first();
            
        if ($order) {
            // Get ALL bookings linked to this order via pivot table
            $bookingIds = DB::table('booking_order')
                ->where('order_id', $order->id)
                ->pluck('booking_id')
                ->toArray();
            
            $bookings = Booking::whereIn('id', $bookingIds)
                ->with(['categories', 'sub_category', 'location'])
                ->orderBy('booking_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
            
            // Get patient name from first booking
            $firstBooking = $bookings->first();
            $patientName = $firstBooking->name ?? '';
            
            $this->selectedBooking = (object) [
                'order' => $order,
                'order_number' => $order->order_number,
                'patient_name' => $patientName,
            ];
            $this->selectedBookingOrders = $bookings; // Show all bookings for this order
            $this->showOrderDetails = true;
        }
    }

    public function closeOrderDetails()
    {
        $this->showOrderDetails = false;
        $this->selectedBooking = null;
        $this->selectedBookingOrders = [];
    }

    public function openRescheduleModal($bookingId)
    {
        // Find the booking
        $booking = Booking::where('team_id', $this->teamId)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            session()->flash('error', 'Appointment not found or you do not have permission to reschedule it.');
            return;
        }

        // Check if already cancelled
        if ($booking->status === Booking::STATUS_CANCELLED) {
            session()->flash('error', 'Cannot reschedule a cancelled appointment.');
            return;
        }

        $this->rescheduleBookingId = $bookingId;
        $this->rescheduleLocationId = $booking->location_id ?? null;
        $this->rescheduleAppointmentDate = $booking->booking_date ?? null;
        $this->rescheduleAppointmentTime = $booking->booking_time ?? null;
        $this->rescheduleAdditionalComments = $booking->additional_comments ?? '';
        
        // Load locations
        $this->rescheduleLocations = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->get();
        
        // Load selected location details
        if ($this->rescheduleLocationId) {
            $this->rescheduleSelectedLocation = Location::find($this->rescheduleLocationId);
            $this->loadRescheduleLocationDetails($this->rescheduleLocationId);
        }
        
        // Load time slots for the current date
        if ($this->rescheduleAppointmentDate) {
            $this->loadRescheduleTimeSlots();
        }
        
        $this->showRescheduleModal = true;
    }

    public function closeRescheduleModal()
    {
        $this->showRescheduleModal = false;
        $this->rescheduleBookingId = null;
        $this->rescheduleLocationId = null;
        $this->rescheduleAppointmentDate = null;
        $this->rescheduleAppointmentTime = null;
        $this->rescheduleAdditionalComments = '';
        $this->rescheduleSelectedLocation = null;
        $this->rescheduleAvailableTimeSlots = [];
        $this->rescheduleLocationBusinessHours = null;
        $this->rescheduleLocationPhone = null;
    }

    public function updatedRescheduleLocationId($value)
    {
        if ($value && !empty($value)) {
            $this->rescheduleSelectedLocation = Location::find($value);
            $this->rescheduleAppointmentTime = null;
            $this->rescheduleAvailableTimeSlots = [];
            
            if ($this->rescheduleSelectedLocation) {
                $this->loadRescheduleLocationDetails($value);
            }
            
            if ($this->rescheduleAppointmentDate) {
                $this->loadRescheduleTimeSlots();
            }
        } else {
            $this->rescheduleSelectedLocation = null;
            $this->rescheduleLocationBusinessHours = null;
            $this->rescheduleLocationPhone = null;
            $this->rescheduleAppointmentDate = null;
            $this->rescheduleAppointmentTime = null;
            $this->rescheduleAvailableTimeSlots = [];
        }
    }

    public function updatedRescheduleAppointmentDate($value)
    {
        if ($value && $this->rescheduleLocationId) {
            $this->rescheduleAppointmentTime = null;
            $this->loadRescheduleTimeSlots();
        }
    }

    public function loadRescheduleLocationDetails($locationId)
    {
        $locationSlot = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $locationId)
            ->where('slot_type', AccountSetting::LOCATION_SLOT)
            ->first();
        
        if ($locationSlot && $locationSlot->business_hours) {
            $this->rescheduleLocationBusinessHours = is_array($locationSlot->business_hours) 
                ? $locationSlot->business_hours 
                : json_decode($locationSlot->business_hours, true);
        }
        
        if ($this->rescheduleSelectedLocation && $this->rescheduleSelectedLocation->user) {
            $this->rescheduleLocationPhone = $this->rescheduleSelectedLocation->user->phone ?? null;
        }
    }

    public function loadRescheduleTimeSlots()
    {
        $this->rescheduleAvailableTimeSlots = [];
        
        if (!$this->rescheduleLocationId || !$this->rescheduleAppointmentDate) {
            return;
        }
        
        try {
            $carbonDate = Carbon::parse($this->rescheduleAppointmentDate);
            $dayOfWeek = $carbonDate->format('l');
            
            $locationSlot = AccountSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->rescheduleLocationId)
                ->where('slot_type', AccountSetting::LOCATION_SLOT)
                ->first();
            
            if (!$locationSlot) {
                return;
            }
            
            $getAdvanceBookingDates = AccountSetting::datesGet($locationSlot->allow_req_before ?? 30);
            
            $customSlot = \App\Models\CustomSlot::whereDate('selected_date', $carbonDate)
                ->where('slots_type', AccountSetting::LOCATION_SLOT)
                ->where('team_id', $this->teamId)
                ->where('location_id', $this->rescheduleLocationId)
                ->first();
            
            $businessHoursArray = null;
            if ($customSlot) {
                $businessHoursArray = json_decode($customSlot->business_hours, true);
            } else {
                $businessHoursArray = json_decode($locationSlot->business_hours, true);
            }
            
            if (!$businessHoursArray || !is_array($businessHoursArray)) {
                return;
            }
            
            $indexedBusinessHours = array_column($businessHoursArray, null, 'day');
            
            if (!isset($indexedBusinessHours[$dayOfWeek]) || 
                $indexedBusinessHours[$dayOfWeek]['is_closed'] !== \App\Models\ServiceSetting::SERVICE_OPEN) {
                return;
            }
            
            $dateFormatted = date('d-m-Y', strtotime($carbonDate));
            if (!in_array($dateFormatted, $getAdvanceBookingDates)) {
                return;
            }
            
            $breakHours = [];
            $availableSlots = AccountSetting::getAvailableSlots(
                $carbonDate,
                $indexedBusinessHours[$dayOfWeek],
                $breakHours,
                $locationSlot,
                null,
                'location',
                $locationSlot
            );
            
            if ($availableSlots instanceof \Illuminate\Support\Collection) {
                $this->rescheduleAvailableTimeSlots = $availableSlots->toArray();
            } elseif (is_array($availableSlots)) {
                $this->rescheduleAvailableTimeSlots = $availableSlots;
            } else {
                $this->rescheduleAvailableTimeSlots = [];
            }
            
            // Filter out past time slots if the selected date is today
            if ($carbonDate->isToday()) {
                $now = Carbon::now();
                $this->rescheduleAvailableTimeSlots = array_filter($this->rescheduleAvailableTimeSlots, function ($slot) use ($now) {
                    [$startTime] = explode('-', $slot);
                    try {
                        $slotStart = Carbon::createFromFormat('h:i A', trim($startTime))
                            ->setDate($now->year, $now->month, $now->day);
                        return $slotStart->greaterThanOrEqualTo($now);
                    } catch (\Exception $e) {
                        return false;
                    }
                });
                $this->rescheduleAvailableTimeSlots = array_values($this->rescheduleAvailableTimeSlots);
            }
            
        } catch (\Exception $e) {
            Log::error('Error loading reschedule time slots: ' . $e->getMessage());
            $this->rescheduleAvailableTimeSlots = [];
        }
    }

    public function selectRescheduleTimeSlot($timeSlot)
    {
        $this->rescheduleAppointmentTime = $timeSlot;
    }

    public function rescheduleAppointment()
    {
        $this->validate([
            'rescheduleLocationId' => 'required|exists:locations,id',
            'rescheduleAppointmentDate' => 'required|date|after_or_equal:today',
            'rescheduleAppointmentTime' => 'required',
        ], [
            'rescheduleLocationId.required' => 'Please select a location.',
            'rescheduleAppointmentDate.required' => 'Please select a date.',
            'rescheduleAppointmentDate.after_or_equal' => 'Please select a valid date.',
            'rescheduleAppointmentTime.required' => 'Please select a time slot.',
        ]);

        try {
            DB::beginTransaction();
            
            // Find the booking
            $booking = Booking::where('team_id', $this->teamId)
                ->where('id', $this->rescheduleBookingId)
                ->first();

            if (!$booking) {
                session()->flash('error', 'Appointment not found.');
                DB::rollBack();
                return;
            }

            // Parse time slot and convert to 24-hour format
            $timeParts = explode('-', $this->rescheduleAppointmentTime);
            $startTime12h = trim($timeParts[0] ?? '');
            $endTime12h = trim($timeParts[1] ?? $startTime12h);
            
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
            
            $bookingTime24h = $startTime . ($endTime !== $startTime ? '-' . $endTime : '');
            
            // Update the booking
            $booking->update([
                'location_id' => $this->rescheduleLocationId,
                'booking_date' => $this->rescheduleAppointmentDate,
                'booking_time' => $bookingTime24h,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'additional_comments' => $this->rescheduleAdditionalComments,
                'is_rescheduled' => 1,
            ]);
            
            // Order doesn't store appointment data anymore - all data is in bookings table
            
            DB::commit();
            
            // Reload appointments
            $this->loadAppointments();
            
            // Close modal
            $this->closeRescheduleModal();
            
            session()->flash('success', 'Appointment has been rescheduled successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rescheduling appointment: ' . $e->getMessage());
            session()->flash('error', 'Failed to reschedule appointment. Please try again.');
        }
    }

    public function cancelAppointment($bookingId)
    {
        // Find the booking
        $booking = Booking::where('team_id', $this->teamId)
            ->where('id', $bookingId)
            ->first();

        if (!$booking) {
            session()->flash('error', 'Appointment not found or you do not have permission to cancel it.');
            return;
        }

        // Check if already cancelled
        if ($booking->status === Booking::STATUS_CANCELLED) {
            session()->flash('error', 'This appointment is already cancelled.');
            $this->loadAppointments();
            return;
        }

        // Update booking status
        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancel_reason' => 'Cancelled by patient',
            'cancel_remark' => 'Cancelled by patient through My Appointments page',
        ]);
        
        // Also update the order if it exists (for display purposes)
        $orderIds = DB::table('booking_order')
            ->where('booking_id', $booking->id)
            ->pluck('order_id')
            ->toArray();
        
        if (!empty($orderIds)) {
            $order = Order::find($orderIds[0]);
            if ($order) {
                // Check if all bookings in this order are cancelled
                $allBookingIds = DB::table('booking_order')
                    ->where('order_id', $order->id)
                    ->pluck('booking_id')
                    ->toArray();
                
                $allCancelled = Booking::whereIn('id', $allBookingIds)
                    ->where('status', '!=', Booking::STATUS_CANCELLED)
                    ->count() === 0;
                
                if ($allCancelled) {
                    // If all bookings are cancelled, cancel the order
                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                    ]);
                }
                // If other bookings exist, keep order status as is
            }
        }

        // Reload appointments
        $this->loadAppointments();
        
        session()->flash('success', 'Appointment has been cancelled successfully.');
    }

    public function render()
    {
        return view('livewire.patient-my-appointments');
    }
}

