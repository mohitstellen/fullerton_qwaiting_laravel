<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Booking;
use App\Models\QueueStorage;
use App\Models\SiteDetail;
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\SmtpDetails;
use App\Models\Location;
use App\Models\Category;
use Google\Client;
use Google\Service\Calendar;
use Spatie\GoogleCalendar\Event;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;

class MainController extends Controller
{
    public function index (){
        // $teams =  Team::with('users')->latest()->get();
        // $domainSlug = request()->route( 'domainSlug' );

    //    return view('main-page',compact('teams','domainSlug'));
    }


    // public function createEvent($id)
    // {
    //     // Decode the provided ID to retrieve the booking record
    //     $decode_id = base64_decode($id);
    //     $booking = Booking::with(['location', 'categories', 'sub_category', 'child_category'])
    //                       ->where(['id' => $decode_id])
    //                       ->first();
    
    //     $siteDetails = SiteDetail::where('team_id',$booking->team_id)->where('location_id',$booking->location_id)->select('select_timezone')->first();
    //       Config::set('app.timezone', $siteData->select_timezone);
    //         date_default_timezone_set($siteData->select_timezone);
    //     // Check if booking exists
    //     if (!$booking) {
    //         return response()->json(['error' => 'Booking not found'], 404);
    //     }
    //     $slug= Domain::where('team_id',$booking->team_id)->select('domain')->first();

    //     $categoryname = Category::viewCategoryName($booking->category_id);
    //     $eventLink = 'https://'.$slug->domain.'./booking-confirmed/' . $id;
    //     $title = "Booking #{$booking->id} - {$categoryname} - " . Carbon::parse($booking->booking_date)->format('Y-m-d');
    //     // Prepare event data based on booking details
    //     $eventData = [
    //         'title' => $title, // You can customize the title based on your needs
    //         'description' => "Booking Confirmation: \n\n" . $eventLink . "\nPhone: " . $booking->phone,
    //         'start_datetime' => $booking->booking_date . ' ' . $booking->start_time, // Format booking start time
    //         'end_datetime' => $booking->booking_date . ' ' . $booking->end_time, // Format booking end time
    //         'location' => $booking->location ? $booking->location->name : 'No Location', // Get location name
    //     ];
    
    //     // Convert booking start and end times to UTC format (Google Calendar format)
    //     $startDateTime = Carbon::parse($eventData['start_datetime'])->utc()->format('Ymd\THis\Z');
    //     $endDateTime = Carbon::parse($eventData['end_datetime'])->utc()->format('Ymd\THis\Z');
    
    //     // Build the Google Calendar URL
    //     $googleCalendarUrl = 'https://calendar.google.com/calendar/u/0/r/eventedit?';
    //     $googleCalendarUrl .= 'text=' . urlencode($eventData['title']);
    //     $googleCalendarUrl .= '&dates=' . $startDateTime . '/' . $endDateTime;
    //     $googleCalendarUrl .= '&details=' . urlencode($eventData['description']);
    //     // $googleCalendarUrl .= '&location=' . urlencode($eventData['location']);
    //     $googleCalendarUrl .= '&sf=true&output=xml';
    
    //     // Redirect the user to Google Calendar with pre-filled event data
    //     return redirect()->to($googleCalendarUrl);
    // }
    
    // public function createOutlookEvent($id)
    // {
    //       // Decode the provided ID to retrieve the booking record
    // $decode_id = base64_decode($id);
    // $booking = Booking::with(['location', 'categories', 'sub_category', 'child_category'])
    //                   ->where(['id' => $decode_id])
    //                   ->first();
    // $siteDetails = SiteDetail::where('team_id',$booking->team_id)->where('location_id',$booking->location_id)->select('select_timezone')->first();
    // if (!$booking) {
    //     return response()->json(['error' => 'Booking not found'], 404);
    // }
    // $slug= Domain::where('team_id',$booking->team_id)->select('domain')->first();

    // $categoryname = Category::viewCategoryName($booking->category_id);
    // $eventLink = 'https://'.$slug->domain.'/booking-confirmed/' . $id;
    // // Define the correct timezone (adjust according to your region)
   
    //  $timezone = $siteDetails->select_timezone ?? 'Asia/Kolkata';

    //  $title = "Booking #{$booking->id} - {$categoryname} - " . Carbon::parse($booking->booking_date)->format('Y-m-d');

    // // Convert booking start and end times with the correct timezone
    // // Convert booking start and end times to UTC (Outlook expects UTC format)
    // $startDateTimeUTC = Carbon::parse($booking->booking_date . ' ' . $booking->start_time, $timezone)
    //                           ->utc()
    //                           ->format('Y-m-d\TH:i:s\Z'); // 'Z' ensures UTC format

    // $endDateTimeUTC = Carbon::parse($booking->booking_date . ' ' . $booking->end_time, $timezone)
    //                         ->utc()
    //                         ->format('Y-m-d\TH:i:s\Z'); // 'Z' ensures UTC format

    // // Build the Outlook Calendar URL
    // $outlookCalendarUrl = 'https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent';
    // $outlookCalendarUrl .= '&startdt=' . urlencode($startDateTimeUTC);
    // $outlookCalendarUrl .= '&enddt=' . urlencode($endDateTimeUTC);
    // $outlookCalendarUrl .= '&subject=' . urlencode($title);
    // $outlookCalendarUrl .= '&body=' . urlencode( "Booking Confirmation: \n\n" . $eventLink . "\nPhone: " . $booking->phone);

    // // Redirect the user to Outlook Calendar with pre-filled event data
    // return redirect()->to($outlookCalendarUrl);
    //     // https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent&startdt=2025-02-04T12%3A00%3A00-05%3A00&enddt=2025-02-04T12%3A30%3A00-05%3A00&subject=Test%One%&body=Test%Body

    //     // https://outlook.live.com/calendar/0/action/compose/?startdt=2025-01-30T16%3A00%3A00-05%3A30&enddt=2025-01-30T17%3A00%3A00-05%3A30&subject=Appointment+with+aksh&body=Booking+ID%3a+1738121927+%7c+Phone%3a+7696396740

   
     
    // }



    public function createEvent($id)
{
    $decode_id = base64_decode($id);
    $booking = Booking::with(['categories', 'sub_category', 'child_category', 'createdBy'])
                      ->find($decode_id);

                      $tenantName = tenant('name');
                      $supportEmail = auth()->user()->email ?? '';
                   
                      $locationdetail = Location::where('id',$booking->location_id)->first();
     
    if (!$booking) {
        return response()->json(['error' => 'Booking not found'], 404);
    }

    $siteDetails = SiteDetail::where('team_id', $booking->team_id)
                              ->where('location_id', $booking->location_id)
                              ->select('select_timezone')
                              ->first();

    $timezone = $siteDetails->select_timezone ?? config('app.timezone');
    Config::set('app.timezone', $timezone);
    date_default_timezone_set($timezone);

    $slug = Domain::where('team_id', $booking->team_id)->value('domain');
    $categoryName = Category::viewCategoryName($booking->category_id);
    $customerName = $booking->name ?? 'Customer';
    $staffName = $booking?->staff->name ?? 'N/A';
    $locationName = $locationdetail?->location_name ?? 'N/A';
    $eventLink = 'https://' . $slug . '/booking-confirmed/' . $id;

    $title = "{$categoryName} – {$customerName} | {$booking->refID}";

    $description = <<<EOT
Dear {$customerName},

This is a confirmation for your upcoming appointment.

📅 **Appointment Details**
• **Service**: {$categoryName}
• **Date**: {$booking->booking_date}
• **Time**: {$booking->start_time} – {$booking->end_time}
• **Location**: {$locationName}
• **Staff**: {$staffName}
• **Booking Reference**: {$booking->refID}

🔗 **Meeting Link** (if applicable):  
{$eventLink}

📌 **Important Notes**:  
– Please arrive 5–10 minutes early.  
– Bring necessary documents (if any).  
– For rescheduling or cancellation, use your booking dashboard or contact support.

Thank you,  
{$tenantName} 
$supportEmail
EOT;

    $startDateTime = Carbon::parse("{$booking->booking_date} {$booking->start_time}", $timezone)->utc()->format('Ymd\THis\Z');
    $endDateTime = Carbon::parse("{$booking->booking_date} {$booking->end_time}", $timezone)->utc()->format('Ymd\THis\Z');

    $googleCalendarUrl = 'https://calendar.google.com/calendar/u/0/r/eventedit?';
    $googleCalendarUrl .= 'text=' . urlencode($title);
    $googleCalendarUrl .= '&dates=' . $startDateTime . '/' . $endDateTime;
    $googleCalendarUrl .= '&details=' . urlencode($description);
    $googleCalendarUrl .= '&location=' . urlencode($locationdetail?->location_name.','.$locationdetail?->address);
    $googleCalendarUrl .= '&sf=true&output=xml';

    return redirect()->to($googleCalendarUrl);
}

public function createOutlookEvent($id)
{
    $decode_id = base64_decode($id);
    $booking = Booking::with(['location', 'categories', 'sub_category', 'child_category', 'createdBy', 'staff'])
                      ->find($decode_id);

    if (!$booking) {
        return response()->json(['error' => 'Booking not found'], 404);
    }

    $tenantName = tenant('name');
    $supportEmail = auth()->user()->email ?? '';
    $locationdetail = Location::where('id', $booking->location_id)->first();

    $siteDetails = SiteDetail::where('team_id', $booking->team_id)
                              ->where('location_id', $booking->location_id)
                              ->select('select_timezone')
                              ->first();

    $timezone = $siteDetails->select_timezone ?? config('app.timezone');
    $slug = Domain::where('team_id', $booking->team_id)->value('domain');
    $categoryName = Category::viewCategoryName($booking->category_id);
    $customerName = $booking->name ?? 'Customer';
    $staffName = $booking?->staff->name ?? 'N/A';
    $locationName = $locationdetail?->location_name ?? 'N/A';
    $eventLink = 'https://' . $slug . '/booking-confirmed/' . $id;

    $title = "{$categoryName} – {$customerName} | {$booking->refID}";

    // Use raw string and encode later
    $rawBody = <<<TEXT
Dear {$customerName},

This is a confirmation for your upcoming appointment.

📅 Appointment Details
• Service: {$categoryName}
• Date: {$booking->booking_date}
• Time: {$booking->start_time} – {$booking->end_time}
• Location: {$locationName}
• Staff: {$staffName}
• Booking Reference: {$booking->refID}

🔗 Meeting Link (if applicable):
{$eventLink}

Important Notes:
– Please arrive 5–10 minutes early.
– Bring necessary documents (if any).
– For rescheduling or cancellation, use your booking dashboard or contact support.

Thank you,
{$tenantName}
{$supportEmail}
TEXT;

    // URL encode body preserving line breaks
    $encodedBody = urlencode($rawBody);

    $startDateTimeUTC = Carbon::parse("{$booking->booking_date} {$booking->start_time}", $timezone)
                              ->utc()
                              ->format('Y-m-d\TH:i:s\Z');

    $endDateTimeUTC = Carbon::parse("{$booking->booking_date} {$booking->end_time}", $timezone)
                            ->utc()
                            ->format('Y-m-d\TH:i:s\Z');

    $outlookCalendarUrl = 'https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent';
    $outlookCalendarUrl .= '&startdt=' . urlencode($startDateTimeUTC);
    $outlookCalendarUrl .= '&enddt=' . urlencode($endDateTimeUTC);
    $outlookCalendarUrl .= '&location=' . urlencode($locationName . ', ' . $locationdetail?->address);
    $outlookCalendarUrl .= '&subject=' . urlencode($title);
    $outlookCalendarUrl .= '&body=' . $encodedBody;

    return redirect()->to($outlookCalendarUrl);
}


    
}
