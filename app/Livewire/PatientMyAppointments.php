<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Booking,
    Member,
    Category,
    Location
};
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

#[Layout('components.layouts.patient-layout')]
#[Title('My Appointments')]
class PatientMyAppointments extends Component
{
    public $teamId;
    public $member;
    public $appointments = [];
    public $selectedBooking = null;
    public $showOrderDetails = false;

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
    }

    public function loadAppointments()
    {
        // Get all bookings for this member
        $this->appointments = Booking::where('team_id', $this->teamId)
            ->where('email', $this->member->email)
            ->with(['categories', 'sub_category', 'location'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'refID' => $booking->refID,
                    'appointment_for' => $booking->name,
                    'appointment_date_time' => Carbon::parse($booking->booking_date)->format('d/m/Y') . ' ' . 
                        ($booking->start_time ? Carbon::parse($booking->start_time)->format('g:iA') : ''),
                    'appointment_status' => $booking->status,
                    'service' => ($booking->categories ? $booking->categories->name : '') . 
                        ($booking->sub_category ? ' - ' . $booking->sub_category->name : ''),
                    'location' => $booking->location ? $booking->location->location_name : '',
                    'booking' => $booking,
                ];
            })
            ->toArray();
    }

    public function viewOrderDetails($bookingId)
    {
        $booking = Booking::with(['categories', 'sub_category', 'location'])
            ->find($bookingId);
            
        if ($booking) {
            $this->selectedBooking = $booking;
            $this->showOrderDetails = true;
        }
    }

    public function closeOrderDetails()
    {
        $this->showOrderDetails = false;
        $this->selectedBooking = null;
    }

    public function render()
    {
        return view('livewire.patient-my-appointments');
    }
}

