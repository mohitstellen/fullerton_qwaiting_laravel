<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\Title;

class AppointmentCalendar extends Component
{
    #[Title('Booking Calendar View')]

    public $teamId;
    public $location;
    public $bookings;

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->location = Session::get( 'selectedLocation');
   // FIXED: Added validation filters to prevent Carbon formatting errors
        $this->bookings = Booking::where('team_id', $this->teamId)
           ->where('location_id', $this->location)
           ->whereNotNull('booking_date')  // FIXED: Filter out null booking dates
           ->whereNotNull('start_time')    // FIXED: Filter out null start times
           ->whereNotNull('end_time')      // FIXED: Filter out null end times
           ->get()
           ->map(function ($booking) {
            try {
                $booking_date = $booking->booking_date;
                $start_time = $booking->start_time;
                $end_time = $booking->end_time;
                
                // Validate that we have all required data
                if (empty($booking_date) || empty($start_time) || empty($end_time)) {
                    return null; // Skip this booking
                }
                
                // FIXED: Try to create Carbon instances with error handling and multiple format support
                $start_datetime = null;
                $end_datetime = null;
                
                // FIXED: Try different date/time formats to handle various database formats
                $formats = [
                    'Y-m-d h:i A',
                    'Y-m-d H:i:s',
                    'Y-m-d H:i',
                    'd/m/Y h:i A',
                    'd-m-Y h:i A'
                ];
                
                foreach ($formats as $format) {
                    try {
                        $start_datetime = Carbon::createFromFormat($format, $booking_date . ' ' . $start_time);
                        $end_datetime = Carbon::createFromFormat($format, $booking_date . ' ' . $end_time);
                        if ($start_datetime && $end_datetime) {
                            break; // Success, exit the loop
                        }
                    } catch (\Exception $e) {
                        continue; // Try next format
                    }
                }
                
                // FIXED: If all formats failed, try parsing individually as fallback
                if (!$start_datetime || !$end_datetime) {
                    try {
                        $date_obj = Carbon::parse($booking_date);
                        $start_time_obj = Carbon::parse($start_time);
                        $end_time_obj = Carbon::parse($end_time);
                        
                        $start_datetime = $date_obj->copy()->setTime($start_time_obj->hour, $start_time_obj->minute);
                        $end_datetime = $date_obj->copy()->setTime($end_time_obj->hour, $end_time_obj->minute);
                    } catch (\Exception $e) {
                        // Still failed, will be handled below
                    }
                }
                
                // Verify the Carbon instances were created successfully
                if (!$start_datetime || !$end_datetime) {
                    return null; // Skip this booking if date parsing failed
                }
                
                return [
                    'id' => $booking->id,
                    'title' => ($booking->name ?? 'Booking') . ' - ' . ($booking->refID ?? 'N/A'),
                    'start' => $start_datetime->toIso8601String(),
                    'end' => $end_datetime->toIso8601String(),
                ];
            } catch (\Exception $e) {
                // FIXED: Log the error and skip this booking to prevent crashes
                Log::warning('Failed to process booking for calendar', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                    'booking_date' => $booking->booking_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time
                ]);
                return null;
            }
        })->filter(); // FIXED: Remove null entries from failed date parsing
    }

    public function render()
    {
        return view('livewire.appointment-calendar');
    }
}
