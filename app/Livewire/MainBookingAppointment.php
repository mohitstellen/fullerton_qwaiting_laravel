<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Category,
    Booking,
    Location,
    AccountSetting,
    SiteDetail,
    SmtpDetails,
    GenerateQrCode,
    FormField,
    ColorSetting,
    SmsAPI,
    Country,
    ServiceSetting,
    PaymentSetting,
    Customer,
    CustomerActivityLog,
    StripeResponse,
    JuspayOrder,
    User,
    CustomSlot,
    SuspensionLog,
    Queue,
    PreferBooking,
    LanguageSetting,
    MetaAdsAndCampaignsLink,
    MessageDetail,
    ActivityLog,
    QueueFreeSlotCount,
    AllowedCountry,
    // ApiLog,
};
use App\Traits\SendsEmails;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use DB;
use Str;
use DateTime;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\Title;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentConfirmation;
use Illuminate\Support\Collection;
use Carbon\CarbonPeriod;
use App\Services\MicrosoftGraphService;
use App\Mail\SuspensionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;


// #[Layout('components.layouts.custom-layout')]
class MainBookingAppointment extends Component
{
    use SendsEmails;

    #[Title('Online Booking')]
    // protected $middleware = ['auth'];

    public $selectedCategoryId;
    public $teamId;
    public $user;
    public $locationId;
    public $location;
    public $locationName;
    public $accountSetting;
    public $parentCategory;
    public $colorSetting;
    public $siteSetting;
    public $firstChildren;
    public $secondChildren, $thirdChildren, $secondChildId, $thirdChildId;
    public $name = '';
    public $phone = '';
    public $email;
    public $showFormQueue;
    public $locationslots;

    public $locationStep = true;
    public $firstpage = false;
    public $secondpage = false;
    public $thirdpage = false;
    public $calendarpage = false;
    public $formfieldSection = false;
    public $paymentStep = false;


    public $slots;
    public $selectedYear;
    public $years = [];
    public $appointment_date;
    public $appointment_time;
    public $disabledDate = [];
    public $allCategories = [];
    public $allLocations = [];
    public $fontSize = 'text-3xl';
    public $fontFamily = 'font-sans';
    public $borderWidth = 'border-4';

    public $dynamicForm = [];
    public $dynamicProperties = [];
    public $totalLevelCount = Category::STEP_1;
    public $phone_code = null;
    public $selectedCountryCode;
    public $countryCode = [];
    public $start_time;
    public $end_time;
    public $mindate = 0;
    public $maxdate = 30;
    public $weekStart = "Sunday";

    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';

    public $isFree = true;
    public $amount;
    public $stripeCategory;
    public $paymentMethodId;
    public $successMessage;
    public $errorMessage;
    public $stripeResponeID;
    public $paymentSetting;
    public $timezone;

    // Juspay properties
    public $juspayOrderId;
    public $juspayPaymentUrl;
    public $juspayTransactionId;
    public $selectedPaymentGateway = 'stripe'; // 'stripe' or 'juspay'

    public $note;
    public $enable_service;
    public $enable_service_time;
    public $serviceSetting;

    //staff type and get staff id to assign booking
    public $assignedStaffId;

    //video link variable
    public string $organizerEmail = 'rajendra@stelleninfotech.in'; // Office 365 email of RE
    public string $meetingLink = '';

    //customer login
    public $isCustomerLogin = false;
    public $mobile;
    public $otp;
    public $showOtpField = false;
    public $verificationId;
    public $customer_phone_code;

    public $isPreferTimeModel = false;
    public $preferTimeBooking = false;
    public $preferStartTime;
    public $showPreferButton = false;
    public $utm_source;
    public $utm_medium;
    public $utm_campaign;
    public $userAuth;


    public $allowed_Countries = [];
    public $country_phone_mode = 1;


    public function mount(Request $request, $location_id = null)
    {

        $this->utm_source = $request->query('utm_source');
        $this->utm_medium = $request->query('utm_medium');
        $this->utm_campaign = $request->query('utm_campaign');

        Queue::timezoneSet();

        $this->timezone = Session::get('timezone_set') ?? 'UTC';
        $this->showFormQueue = false;
        $this->user =  Auth::user();
        $this->teamId =  tenant('id');
        $this->userAuth = Auth::user();
        //    dd(App::getLocale());
        //  $videoLink = 'https://teams.microsoft.com/l/meetup-join/' . Str::uuid();
        //  dd($videoLink);
        // $this->locationId = Session::get('selectedLocation');

        // Check for route parameter
        if (!Session::has('selectedLocation') && $location_id !== null) {
            $this->locationId = base64_decode($location_id, true);
            Session::put('selectedLocation', $this->locationId);
        } else {
            $this->locationId = Session::get('selectedLocation');
        }

        $this->stripeResponeID = '';
        $this->totalLevelCount = Category::STEP_1;

        if (!empty($this->locationId)) {
            $this->updatedLocation($this->locationId);
        } else {
            $this->locationId = '';
            $this->location = '';
            $this->allLocations = Location::select('id', 'location_name', 'address', 'location_image')->where('team_id', $this->teamId)->where('status', 1)->get();
            $this->locationStep = true;
            $this->firstpage = false;
        }

        $setting = LanguageSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();


        if ($setting && $setting->enabled_language_settings && !empty($setting->default_language)) {
            App::setLocale($setting->default_language);
            Session::put('app_locale', $setting->default_language);

            if (!Session::has('language_applied_once') && $setting->default_language !== 'en') {
                Session::put('language_applied_once', true);

                // Dispatch JavaScript to reload the page once
                $this->dispatch('reload');
            }
        }
        //  $this->createVideoCall();
        //  dd($this->meetingLink );

    }

    public function updatedLocation($value)
    {

        $this->locationId = $value;
        Session::forget('selectedLocation');
        Session::put('selectedLocation', $this->locationId);

        $this->locationName = Location::locationName($this->locationId);
        $this->locationStep = false;
        $this->firstpage = true;
        $currentYear = date('Y');
        $this->years = range($currentYear, $currentYear + 1);
        $this->selectedYear = $currentYear;

        $this->siteSetting = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        if (!$this->siteSetting) {
            abort(402);
        }
        $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)->first();

        $this->paymentSetting = PaymentSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        if ($this->paymentSetting) {
            config([
                'services.stripe.key' => $this->paymentSetting->api_key,
                'services.stripe.secret' => $this->paymentSetting->api_secret,
            ]);
            
            // Configure Juspay
            if (!empty($this->paymentSetting->juspay_merchant_id) && !empty($this->paymentSetting->juspay_api_key)) {
                config([
                    'services.juspay.merchant_id' => $this->paymentSetting->juspay_merchant_id,
                    'services.juspay.api_key' => $this->paymentSetting->juspay_api_key,
                    'services.juspay.env' => 'sandbox', // Change to 'production' when ready
                ]);
            }
        }

        if (empty($this->accountSetting) || $this->accountSetting->booking_system == 0) {
            abort(403);
        }

        // Check if user is returning from Juspay payment
        if (session()->has('juspay_pending_booking')) {
            $this->handleJuspayReturn();
        }
        if (isset($this->accountSetting)) {
            $this->mindate = empty($this->accountSetting->allow_req_min_before) ? 0 : $this->accountSetting->allow_req_min_before;
            $this->maxdate = empty($this->accountSetting->allow_req_before) ? 30 : $this->accountSetting->allow_req_before;
            $this->weekStart = empty($this->accountSetting->week_start) ? "Sunday" : $this->accountSetting->week_start;
        }

        $this->resetDynamic();

        if (!empty($this->siteSetting)) {
            $this->fontSize = $this->siteSetting->category_text_font_size ?? $this->fontSize;
            $this->borderWidth = $this->siteSetting->category_border_size ?? $this->borderWidth;
            $this->fontFamily = $this->siteSetting->ticket_font_family ?? $this->fontFamily;
        }
        //get location detail
        $this->location = Location::find($this->locationId);

        // get Account detail of current location
        $locationSlotsDetail =  AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', AccountSetting::LOCATION_SLOT)
            ->select('id', 'business_hours')
            ->first();

        $this->locationslots =  json_decode($locationSlotsDetail['business_hours'], true);

        //fetch parent Category
        $this->parentCategory =  Category::getFirstCategorybooking($this->teamId, $this->locationId);

        $this->colorSetting = ColorSetting::where('team_id', $this->teamId)->first();
        $this->totalLevelCount = Category::STEP_1;
        //default today select
        $this->appointment_date = Carbon::today($this->timezone);
        $this->appointment_time = '';
        $this->countryCode = Country::query()->pluck('phonecode');

        $this->isCustomerLogin = $this->siteSetting->is_customer_login == 1 ? true : false;
        $this->showPreferButton = $this->siteSetting->is_prefer_time_slot == 1 ? true : false;
        $this->selectedCountryCode = !empty($this->siteSetting->country_code) ?  $this->siteSetting->country_code : null;
        $this->phone_code = !empty($this->selectedCountryCode) ? $this->selectedCountryCode : '91';

        if (Session::has('login_customer_detail')) {

            $this->isCustomerLogin = false;
        }
        // $this->firstpage = true;

        $timezone = $this->siteSetting->select_timezone ?? 'UTC';
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

         $this->country_phone_mode = $this->siteSetting->country_options ?? 1;

        $this->allowed_Countries = AllowedCountry::where('team_id',  $this->teamId)
                ->where('location_id', $this->locationId)->select('id','name','phone_code')->get();
        if( $this->country_phone_mode != 1 && !empty($this->allowed_Countries)){
            $this->phone_code = $this->allowed_Countries[0]->phone_code;
        }


        $this->dispatch('header-show');
    }

    public function goBackFn($page)
    {

        $this->totalLevelDecFn();

        switch ($page) {

            case Category::STEP_2:
                $this->secondChildId =  $this->thirdChildId  = null;
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_1;
                $this->firstpage  = true;
                break;
            case Category::STEP_3:
                $this->resetallpages();
                $this->thirdChildId = null;
                $this->totalLevelCount = Category::STEP_2;
                $this->secondpage = true;
                break;
            case Category::STEP_4:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_3;
                $this->thirdpage = true;
                break;
            case Category::STEP_5:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_4;
                $this->calendarpage = true;
                break;
            case Category::STEP_6:
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_5;
                $this->formfieldSection = true;
                break;
            default:
                $this->secondChildId = $this->selectedCategoryId =    $this->thirdChildId  = null;
                $this->resetallpages();
                $this->totalLevelCount = Category::STEP_1;
                $this->firstpage = true;
        }
    }

    public function modelPreferTimeSlot()
    {
        $this->isPreferTimeModel = true;
        $this->dispatch('open-modal', id: 'preferTimeModel');
    }

    public function addPreferTime()
    {
        if (empty($this->preferStartTime)) {
            $this->dispatch('close-modal', id: 'preferTimeModel');
            $this->dispatch('swal:time-required');
            return;
        }
        $this->preferTimeBooking = true;
        $this->preferStartTime = Carbon::createFromFormat('H:i', $this->preferStartTime)->format('h:i A');
        $this->appointment_time = '';
        $timeslotsExlplode = $this->preferStartTime;
        if (empty($timeslotsExlplode)) {
            $this->start_time = null;
            $this->end_time = null;
        } else {
            $interval = (int)$this->accountSetting?->slot_period ?? 10;
            $this->start_time = $timeslotsExlplode;
            $this->end_time = null;
        }

        $this->getAmount();

        if (!empty($this->preferStartTime)) {

            $this->locations = false;
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->paymentStep = false;
            $this->formfieldSection = true;
        }



        $this->resetDynamic();
        $this->dispatch('close-modal', id: 'preferTimeModel');
    }

    public function totalLevelIncFn()
    {
        $this->totalLevelCount++;
    }


    public function totalLevelDecFn()
    {
        if ($this->totalLevelCount > 0)
            $this->totalLevelCount--;
    }

    public function resetallpages()
    {
        $this->locationStep = false;
        $this->firstpage = false;
        $this->secondpage = false;
        $this->thirdpage = false;
        $this->calendarpage = false;
        $this->paymentStep = false;
        $this->formfieldSection = false;
    }

    public function showFirstChild($categoryId)
    {

        $this->selectedCategoryId = $categoryId;

        $this->firstChildren = Category::getchildDetailBooking($categoryId, $this->locationId);
        $this->totalLevelIncFn();
        if (count($this->firstChildren) > 0) {
            $this->firstpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;
            $this->secondpage = true;
        } else {

            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;
            $this->calendarpage = true;
            $this->timeSlots();
            $category = Category::find($this->selectedCategoryId);

            $this->note = $category?->note ?? '';
            $this->enable_service = $category?->is_service_template ?? '';
            $this->enable_service_time = $category?->service_time ?? 'N/A';
            $this->dispatch('update-calendar', [
                'year' => now()->year,  // Get current year dynamically
                'month' => now()->month - 1,
                'disabledDate' => $this->disabledDate,
            ]);
        }
    }



    public function showSecondChild($categoryId)
    {

        $this->secondChildId = $categoryId;

        $this->secondChildren = Category::getchildDetailBooking($categoryId, $this->locationId);
        $this->totalLevelIncFn();

        if (count($this->secondChildren) > 0) {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->calendarpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;
            $this->thirdpage = true;
        } else {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->formfieldSection = false;
            $this->paymentStep = false;

            $category = Category::find($this->secondChildId);

            $this->note = $category?->note ?? '';
            $this->enable_service = $category?->is_service_template ?? '';
            $this->enable_service_time = $category?->service_time ?? 0;

            $this->calendarpage = true;
            $this->timeSlots();

            $this->dispatch('update-calendar', [
                'year' => now()->year,  // Get current year dynamically
                'month' => now()->month - 1,
                'disabledDate' => $this->disabledDate,
            ]);
        }
    }
    public function showThirdChild($categoryId)
    {
        $this->thirdChildId = $categoryId;
        $this->thirdChildren = Category::getchildDetailBooking($categoryId, $this->locationId);
        if (count($this->thirdChildren) == 0) {
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->paymentStep = false;
            $this->formfieldSection = false;
            $category = Category::find($this->thirdChildId);
            $this->note = $category?->note ?? '';
            $this->enable_service = $category?->is_service_template ?? '';
            $this->enable_service_time = $category?->service_time ?? 0;
            $this->calendarpage = true;
            $this->timeSlots();
            $this->dispatch('update-calendar', [
                'year' => now()->year,  // Get current year dynamically
                'month' => now()->month - 1,
                'disabledDate' => $this->disabledDate,
            ]);
        }
    }

    public function updatedAppointmentTime($value)
    {
        $this->appointment_time = $value;
        $current = $value;

        $this->preferTimeBooking = false;
        $this->preferStartTime = '';

        $timeslotsExlplode = explode('-', $current);
        if ($this->start_time == $timeslotsExlplode[0]) {
            $this->start_time = null;
            $this->end_time = null;
        } else {
            $interval = (int)$this->accountSetting?->slot_period ?? 10;
            $this->start_time = $timeslotsExlplode[0];
            $this->end_time = $timeslotsExlplode[1];
        }

        $this->getAmount();

        if (!empty($value)) {

            $this->locations = false;
            $this->firstpage = false;
            $this->secondpage = false;
            $this->thirdpage = false;
            $this->calendarpage = false;
            $this->paymentStep = false;
            $this->formfieldSection = true;
        }

        $this->resetDynamic();
    }

    // get time slots
    public function timeSlots()
    {

        if ($this->siteSetting->category_slot_level == 1 && $this->selectedCategoryId) {
            $categoryId =  $this->selectedCategoryId;
        } elseif ($this->siteSetting->category_slot_level == 2 &&  $this->secondChildId) {
            $categoryId = $this->secondChildId;
        } elseif ($this->siteSetting->category_slot_level == 3 &&  $this->thirdChildId) {
            $categoryId = $this->thirdChildId;
        } else {
            $categoryId =  $this->selectedCategoryId;
        }
        if ($this->siteSetting->category_level_est == "parent" && $this->selectedCategoryId) {
            $estimatecategoryId =  $this->selectedCategoryId;
        } elseif ($this->siteSetting->category_level_est == "child" &&  $this->secondChildId) {
            $estimatecategoryId = $this->secondChildId;
        } elseif ($this->siteSetting->category_level_est == "automatic" &&  $this->thirdChildId) {
            $estimatecategoryId = $this->thirdChildId;
        } else {
            $estimatecategoryId =  $this->selectedCategoryId;
        }

        if ($this->siteSetting->choose_time_slot != 'staff') {

            $this->slots = AccountSetting::checktimeslot($this->teamId, $this->locationId, $this->appointment_date, $categoryId, $this->siteSetting);
        } else {
            // Remove null values from category array
            $selectedCategories = array_filter([
                $this->selectedCategoryId ?? null,
                $this->secondChildId ?? null,
                $this->thirdChildId ?? null
            ], fn($val) => !is_null($val));

            $staffIds = User::whereHas('categories', function ($query) use ($selectedCategories) {
                $query->whereIn('categories.id', $selectedCategories);
            })->pluck('id')->toArray();
            if (!empty($staffIds)) {
                $this->slots = AccountSetting::checkStafftimeslot($this->teamId, $this->locationId, $this->appointment_date, $estimatecategoryId, $this->siteSetting, $staffIds);
            }
        }
        $this->disabledDate = $this->slots['disabled_date'] ?? [];
    }


    protected $rules = [
        'mobile' => 'required|digits:10',
        'otp' => 'required|digits:6'
    ];

    public function sendOtp()
    {
        $this->validate(['mobile' => 'required|digits:10']);

        $phone_code = isset($this->customer_phone_code) ? ltrim($this->customer_phone_code, '+') : '91';
        //    $phone_code ='91';
        $contactWithCode = $phone_code . $this->mobile;

        // Send OTP (implementation depends on your SMS gateway)
        $this->verificationId = random_int(100000, 999999);
        // $this->verificationId = 123456;
        $status = SmsAPI::currentQueueSms($contactWithCode, $this->verificationId, $this->teamId, 'Send customer login otp');

        $this->showOtpField = true;
        session()->flash('message', 'OTP sent to your mobile number');
    }

    public function verifyOtp()
    {
        $this->validate(['otp' => 'required|digits:6']);

        if ($this->verificationId == $this->otp) {
            // OTP verified - log in the customer
            // auth()->loginUsingId($this->findCustomerByMobile($this->mobile));
            // return redirect()->route('dashboard'); // Redirect to dashboard
            $customer = $this->findCustomerByMobile($this->mobile);
            Session::put('login_customer_detail', $customer);
            $this->isCustomerLogin = false;
            $this->firstpage = true;
            $this->reset('verificationId', 'otp');
        } else {
            Session::forget('login_customer_detail');
            $this->addError('otp', 'Invalid OTP. Please try again.');
        }
    }

    protected function findCustomerByMobile($mobile)
    {
        // Implement your logic to find customer by mobile
        return Customer::where('phone', $mobile)->first();
    }



    public function checkstaffId()
    {
        if ($this->siteSetting->choose_time_slot == 'staff' || $this->siteSetting->assigned_staff_id == 1) {
            $selectedCategories = array_filter([
                $this->selectedCategoryId ?? null,
                $this->secondChildId ?? null,
                $this->thirdChildId ?? null
            ], fn($val) => !is_null($val));

            $staffIds = User::whereHas('categories', function ($query) use ($selectedCategories) {
                $query->whereIn('categories.id', $selectedCategories);
            })->pluck('id')->toArray();

            if (!empty($staffIds)) {
                $staffAvailability = [];

                foreach ($staffIds as $staffId) {
                    // if ($this->CheckstaffAvailabilty($staffId)) {
                    //     $staffAvailability[] = $staffId;
                    // }
                    $staffAvailability[] = $staffId;
                }
            }


            if (count($staffAvailability) > 0) {
                $capacityPerSlot = (int)$this->accountSetting->req_per_slot ?? 1;

                // 6. Get already booked staff for this date and time
                $bookedStaffs = Booking::where('booking_date', $this->appointment_date)
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $this->locationId)
                    ->where('start_time', $this->start_time)
                    ->where('end_time', $this->end_time)
                    ->whereIn('staff_id', $staffAvailability)
                    ->pluck('staff_id')
                    ->toArray();

                // 7. If already reached capacity, reject
                if (count($bookedStaffs) >= count($staffAvailability) * $capacityPerSlot) {
                    $this->assignedStaffId = '';
                    throw new \Exception('All staff are fully booked for this time slot (checkstaffId -first error).');
                }

                // 8. Find last assigned staff
                $lastBooking = Booking::where('booking_date', $this->appointment_date)
                    ->where('start_time', $this->start_time)
                    ->where('end_time', $this->end_time)
                    ->whereIn('staff_id', $staffAvailability)
                    ->latest('id')
                    ->first();

                // 9. Find next staff (round-robin style)
                if ($lastBooking && in_array($lastBooking->staff_id, $staffAvailability)) {
                    $lastStaffIndex = array_search($lastBooking->staff_id, $staffAvailability);

                    $nextIndex = ($lastStaffIndex + 1) % count($staffAvailability);
                    $this->assignedStaffId = $staffAvailability[$nextIndex];
                } else {
                    // If no previous booking, assign the first available staff
                    $this->assignedStaffId = $staffAvailability[0];
                }
            }
        } else {
            $this->assignedStaffId = '';
        }
    }



    public function CheckstaffAvailabilty($staffId)
    {

        $availableSlots = [];
        $date = $this->appointment_date;
        $periodOfSlot = $this->accountSetting->slot_period ?: '10';
        $type = "staff";
        // Check for custom slots
        $customSlotQuery = CustomSlot::whereDate('selected_date', $this->appointment_date)
            ->where('slots_type', $type)->where('team_id', $this->teamId)->where('location_id', $this->locationId);

        // Apply additional filtering based on $type
        if ($type == "staff") {
            $customSlotQuery->where('user_id', $staffId);
        }

        $customSlot = $customSlotQuery->first();

        $dayOfWeek = Carbon::parse($this->appointment_date)->format('l');

        // Use business hours from custom slots if available
        if (isset($customSlot)) {
            $businessHours_get = json_decode($customSlot->business_hours, true);
            $businessHours = $businessHours_get[0];
        } else {

            // Retrieve all account settings for the staff
            $staffAccount = AccountSetting::where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->where('user_id', $staffId)
                ->where('slot_type', AccountSetting::STAFF_SLOT)
                ->first();

            $businessHours = json_decode($staffAccount->business_hours, true);
            $indexedBusinessHours = collect($businessHours)->keyBy('day');
            $businessHours = $indexedBusinessHours[$dayOfWeek];
        }

        if (isset($businessHours) && $businessHours['is_closed'] == ServiceSetting::SERVICE_OPEN) {
            $availableSlots = new Collection();
            $mainSlots = AccountSetting::generateSlots($businessHours['start_time'], $businessHours['end_time'], $periodOfSlot);
            $availableSlots = $availableSlots->concat($mainSlots);

            if (!empty($businessHours['day_interval'])) {
                foreach ($businessHours['day_interval'] as $interval) {
                    $intervalSlots = AccountSetting::generateSlots($interval['start_time'], $interval['end_time'], $periodOfSlot);
                    $availableSlots = $availableSlots->concat($intervalSlots);
                }
            }

            // Now check if the selected slot is fully within available slots
            $selectedStart = Carbon::parse($this->start_time)->format('H:i');
            $selectedEnd = Carbon::parse($this->end_time)->format('H:i');
            $slotRange = AccountSetting::generateSlots($selectedStart, $selectedEnd, $periodOfSlot);

            $allAvailable = true;
            foreach ($slotRange as $slot) {
                if (!$availableSlots->contains($slot)) {
                    $allAvailable = false;
                    break;
                }
            }

            return $allAvailable;
        }

        return false;
    }


    public function changemonthandyear($month, $year)
    {
        $current = Carbon::now($this->timezone);
        $selectedDate = Carbon::createFromDate($year, $month, 1);

        // Set the appointment date based on whether it's current or not
        if ($selectedDate->isSameMonth($current)) {
            // $this->appointment_date = $current->format('Y-m-d');
            $this->appointment_date = Carbon::today($this->timezone);
        } else {
            // $this->appointment_date = $selectedDate->format('Y-m-d');
            $this->appointment_date = Carbon::parse($selectedDate);
        }

        // If selected month/year is *before* current, skip timeSlots
        if ($selectedDate->lessThan($current->copy()->startOfMonth())) {
            $this->appointment_time = '';
            $this->start_time = null;
            $this->end_time = null;
            $this->slots['start_at'] = [];
            return;
        }

        $this->serviceSetting = ServiceSetting::getDetails(
            $this->teamId,
            $this->locationId,
            $this->selectedCategoryId
        );

        $this->appointment_time = '';
        $this->start_time = null;
        $this->end_time = null;
        $this->slots['start_at'] = [];

        // $this->appointment_date = Carbon::parse($this->appointment_date);
        $this->timeSlots();
        $this->dispatch('update-calendar', [
            'year' => $year,  // Get current year dynamically
            'month' => $month - 1,
            'disabledDate' => $this->disabledDate,
        ]);
    }

    #[On('selected-date')]
    public function SelectedDate($date)
    {
        $this->appointment_date = Carbon::parse($date);
        $this->serviceSetting = ServiceSetting::getDetails($this->teamId, $this->locationId, $this->selectedCategoryId);
        $this->appointment_time = '';
        $this->start_time = null;
        $this->end_time = null;
        $this->timeSlots();
    }


    public function resetDynamic()
        {
            $this->allCategories = [
                'thirdChildId' => $this->thirdChildId ?? '',
                'secondChildId' => $this->secondChildId ?? '',
                'selectedCategoryId' => $this->selectedCategoryId,
            ];
            $this->dynamicForm = FormField::getFieldsbooking($this->teamId,true,$this->locationId,$this->allCategories);


            foreach ($this->dynamicForm as $field) {
                $propertyName = $field['title'] . '_' . $field['id'];
                $this->dynamicProperties[$propertyName] = '';
            }
            // dd($this->dynamicProperties);
        }

    public function rules()
    {

        try {
            $rules = [];
            if (!empty($this->dynamicProperties)) {
                foreach ($this->dynamicProperties as $fieldName => $value) {
                    $fieldId = explode('_', $fieldName)[1];

                    $field = FormField::findDynamicFormField($this->dynamicForm, $fieldId);

                    if ($field) {
                        FormField::addDynamicFieldRules($rules, $fieldName, $field, $this->allCategories);
                    }
                }
            }

            return $rules;
        } catch (\Throwable $ex) {
            $this->dispatch('swal:ticket-generate', [
                'title' => 'Oops...',
                'text' => 'Unable to generate ticket due to invalid rules. Please contact to the admin',
                'icon' => 'error'
            ]);

            return $rules = [];
        }
    }

    public function messages()
    {
        $messages = [];

        foreach ($this->dynamicProperties as $fieldName => $value) {
            $fieldId = explode('_', $fieldName)[1];

            $field = FormField::findDynamicFormField($this->dynamicForm, $fieldId);
            if ($field) {
                $fieldTitle = $field['title'];
                $messages["dynamicProperties.$fieldName.required"] = "The {$fieldTitle} field is required.";
                if (str_contains(strtolower($fieldTitle), 'email')) {
                    $messages["dynamicProperties.$fieldName.email"] = "Invalid email address for {$fieldTitle}.";
                }
                $messages["dynamicProperties.$fieldName.regex"] = "The {$fieldTitle} field is invalid.";
                $messages["dynamicProperties.$fieldName.min"] = "The {$fieldTitle} field must be at least :min characters.";
                $messages["dynamicProperties.$fieldName.max"] = "The {$fieldTitle} field must be at most :max characters.";
            }
        }
        return $messages;
    }


    public function saveAppointmentForm()
    {


        if (!empty($this->dynamicProperties)) {
            $this->validate();
        }

        $this->dispatch('swal:saving-booking', [
            'title' => 'Saving',
            'icon' => 'success',
        ]);

        $formattedFields = [];
        foreach ($this->dynamicProperties as $key => $value) {
            $fieldName = preg_replace('/_\d+/', '', $key);
            $fieldName = strtolower($fieldName);
            $formattedFields[$fieldName] = $value;
        }

        $this->name = $formattedFields['name'] ?? null;
            $possiblePhoneKeys = [
                'phone',
                'phone number',
                'phonenumber',
                'phone_no',
                'phoneno',
                'mobile',
                'mobile number',
                'mobileno',
                'cell',
                'cellphone',
                'telephone',
                'tel',
                'contact',
                'contact number',
                'whatsapp',
            ];

            $this->phone = null;

            foreach ($possiblePhoneKeys as $key) {
                if (isset($formattedFields[$key]) && !empty($formattedFields[$key])) {
                    $this->phone = $formattedFields[$key];
                    // $formattedFields[$key] = $this->phone_code.$formattedFields[$key];
                    break;
                }
            }
        $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['email address']) ? $formattedFields['email address'] : null);

        $jsonDynamicData = json_encode($formattedFields);

        try {
            DB::beginTransaction();
             $capacityPerSlot = (int)$this->accountSetting->req_per_slot ?? 1;

            if (($this->siteSetting->choose_time_slot == 'staff') || ($this->siteSetting->assigned_staff_id == 1)) {

                $this->checkstaffId();

                if (empty($this->assignedStaffId)) {
                    // Log the exception with stack trace and context
                    \Log::error('Booking save failed', [
                        'message' => "No staff Available",
                        'team_id' => $this->teamId,
                        'user_id' => auth()->id(),
                        'category_id' => $this->selectedCategoryId,
                        'appointment_date' => $this->appointment_date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                    ]);

                    $this->dispatch('swal:exist-booking', [
                        'title' => "No staff Available",
                        'icon' => 'error',
                    ]);
                    return;
                }
            }



            $last_category = $this->selectedCategoryId;
             if(!empty($this->secondChildId)){
                 $last_category = $this->secondChildId;
             }

             if(!empty($this->thirdChildId)){
                 $last_category = $this->thirdChildId;
             }



            $limitData = [
                        'team_id'=>$this->teamId,
                        'location_id'=>$this->locationId,
                        'category_id' => $this->selectedCategoryId,
                        'last_category' =>  $last_category,
                        'appointment_date' => $this->appointment_date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                        'staff_id' => $this->assignedStaffId ?? null,
                        'capacity_per_slot' => $capacityPerSlot,
            ];

            $count = 1;
            $freeslotId = '';
        //     $checkcount = Booking::checkBookingSlotsLimit($limitData);
		// if($checkcount['status'] == true){
        //     $count = $checkcount['count'];
        // }else{


        // }

            $status = Booking::STATUS_PENDING;
            if ($this->accountSetting?->req_accept_mode == Booking::AUTO_CONFIRM && $this->preferTimeBooking == false) {
                $status = Booking::STATUS_CONFIRMED;
            }

            if ($this->accountSetting?->custom_booking_id == 'default') {
                $refID = time();
            } elseif ($this->accountSetting?->custom_booking_id == 'email') {
                if (isset($this->email) && $this->email != '') {
                    $refID = $this->email;
                } else {
                    $refID = time();
                }
            } elseif ($this->accountSetting?->custom_booking_id == 'phone') {
                if (isset($this->phone) && $this->phone != '') {
                    $refID = $this->phone;
                } else {
                    $refID = time();
                }
            } else {
                $refID = time();
            }


            $userAuth = '';
            if (Auth::check()) {
                $userAuth = Auth::id();
            }

            if (!empty($this->utm_source) && !empty($this->utm_medium) && !empty($this->utm_campaign)) {
                $getCampaign = MetaAdsAndCampaignsLink::where('source', $this->utm_source)->where('medium', $this->utm_medium)->where('campaign', $this->utm_campaign)->first();

                $campaignId = $getCampaign->id;
            }


            if ($this->preferTimeBooking == false) {
                $booking = Booking::create([
                    'team_id' => $this->teamId,
                    'booking_date' => $this->appointment_date,
                    'booking_time' => $this->start_time . '-' . $this->end_time,
                    'name' => $this->name ?? '',
                    'phone' => $this->phone ?? '',
                    'phone_code' => $this->phone_code ?? '91',
                    'email' => $this->email ?? '',
                    'category_id' => $this->selectedCategoryId ?? null,
                    'sub_category_id' => !empty($this->secondChildId) ? $this->secondChildId : null,
                    'child_category_id' => !empty($this->thirdChildId) ? $this->thirdChildId : null,
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                    'location_id' => $this->locationId,
                    'json' => $jsonDynamicData,
                    'status' => $status,
                    // 'created_by' => $userAuth ?? '',
                    'refID' => $refID ?? time(),
                    'staff_id' => $this->assignedStaffId ?? '',
                    'campaign_id' => isset($campaignId) ? $campaignId : null,
                    'last_category' => $last_category ?? null,
                    'count' => $count ?? null,
                ]);
            } else {
                $booking = PreferBooking::create([
                    'team_id' => $this->teamId,
                    'booking_date' => $this->appointment_date,
                    'name' => $this->name ?? '',
                    'phone' => $this->phone ?? '',
                    'phone_code' => $this->phone_code ?? '91',
                    'email' => $this->email ?? '',
                    'category_id' => $this->selectedCategoryId ?? null,
                    'sub_category_id' => !empty($this->secondChildId) ? $this->secondChildId : null,
                    'child_category_id' => !empty($this->thirdChildId) ? $this->thirdChildId : null,
                    'start_time' => $this->start_time,
                    'location_id' => $this->locationId,
                    'json' => $jsonDynamicData,
                    'status' => $status,
                    // 'created_by' => $userAuth ?? '',
                    'refID' => $refID ?? time(),
                    'staff_id' => $this->assignedStaffId ?? '',
                    'campaign_id' => isset($campaignId) ? $campaignId : '',
                    'last_category' => $last_category ?? null,
                    'count' => $count ?? null,
                ]);
            }


            if (!empty($this->thirdChildId))
                $this->thirdCategoryName = Category::viewCategoryName($this->thirdChildId);
            if (!empty($this->secondChildId))
                $this->secondCategoryName = Category::viewCategoryName($this->secondChildId);
            if (!empty($this->selectedCategoryId))
                $this->categoryName =  Category::viewCategoryName($this->selectedCategoryId);

            $url = url('booking-confirmed', ['id' => base64_encode($booking->id)]);
            // $cleanedUrl = str_replace('/', '', $url);
            $cleanedUrl =  $url;

            //store customer data and activity log
            if (!empty($this->phone)) {
                $existingCustomer = Customer::where('phone', $this->phone)
                    ->where('team_id', $this->teamId)
                    ->where('location_id', $this->locationId)
                    ->first();

                // Create customer if not exists
                if (!$existingCustomer) {
                    $existingCustomer = Customer::create([
                        'team_id' => $this->teamId,
                        'location_id' => $this->locationId,
                        'name' => $this->name ?? null,
                        'phone' => $this->phone,
                        'json_data' => $jsonDynamicData, // casted automatically to JSON
                    ]);
                }

                // Log customer activity with type 'queue'
                CustomerActivityLog::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->locationId,
                    'queue_id' => null,
                    'booking_id' => $booking->id ?? null,
                    'type' => 'booking',
                    'customer_id' => $existingCustomer->id,
                    'note' => 'Customer joined the booking.',
                ]);
                $booking->update([
                    'created_by' => $existingCustomer->id,
                ]);
            }
            $data = [
                'booking_id' => $booking->id,
                'name' => $booking->name ?? '',
                'phone' => $booking->phone ?? '',
                'phone_code' => $this->phone_code ?? '91',
                'booking_date' => \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y'),
                'booking_time' => $booking?->booking_time ?? $booking->start_time,
                'booked_by' => $userAuth,
                'category_name' => $this->categoryName,
                'thirdC_name' => $this->thirdCategoryName,
                'secondC_name' => $this->secondCategoryName,
                'location' => $booking->location?->location_name,
                'status' => $booking->status,
                'json' => $booking->json,
                'refID' => $booking->refID,
                'view_booking' => $cleanedUrl,
                'locations_id' => $this->locationId,
                'team_id' => $this->teamId,
            ];

            if (!empty($this->stripeResponeID)) {
                StripeResponse::where('id', $this->stripeResponeID)->update([
                    'booking_id' => $booking->id,
                ]);

                $this->stripeResponeID = '';
            }


            $data = array_merge($data, ['to_mail' => $booking->email, 'service_time' => $this->enable_service_time, 'service_note' => $this->note]);

            $message = 'Appointment request has been successfully sent.But Email is not sent';
            // Send email
            if ($this->preferTimeBooking == false) {

                $logData = [
                    'team_id' => $this->teamId,
                    'location_id' => $this->locationId,
                    'customer_id' => $booking->created_by,
                    'booking_id' => $booking->id,
                    'email' => $booking->email,
                    'contact' => $booking->phone,
                    'type' => MessageDetail::TRIGGERED_TYPE,
                    'event_name' => 'Booking Confirmed',
                ];
                  \Log::info('step 2');


                if ($status == Booking::STATUS_CONFIRMED) {
                    $message = 'Appointment Booked Successfully';
                      \Log::info('step 4');
                    // $this->sendEmail( $data, 'Appointment Booked Successfully', 'booking-confirmation', $this->teamId );
                    $this->sendNotification($data, 'booking confirmed', $message, $logData);
                } else {
                    // $this->sendEmail( $data, 'Appointment Request', 'admin_booking_approval', $this->teamId );
                    $message = 'Appointment request has been successfully sent. Please wait for confirmation';
                    $this->sendNotification($data, 'booking confirmed', $message, $logData);
                }
            }


            DB::commit();
            
            // Send booking data to external API (only for specific team/location)
            // if($this->teamId == 3 && $this->locationId == 80){
            //     try {
            //         $this->SendToCrelioAPI($booking, $data);
            //     } catch (\Exception $e) {
            //         \Log::error('Failed to send booking to Crelio API: ' . $e->getMessage());
            //     }
            // }
            
            $this->preferTimeBooking = false;
            $this->preferStartTime = '';
            $this->resetForm();

             //delete freeslot data
            //  if($checkcount['status'] == true && !empty($checkcount['freeslotId'])){
            //        QueueFreeSlotCount::where('id',$checkcount['freeslotId'])->delete();
            //  }

            if ($status == Booking::STATUS_CONFIRMED  && $this->accountSetting->booking_confirmation_page == 1) {
                return  $this->redirect($cleanedUrl);
            } else {
                $this->dispatch('swal:saved-booking', [
                    'title' => $message,
                    'icon' => 'success',
                ]);
            }
        } catch (\Throwable $ex) {
            DB::rollBack();

            // Log the exception with stack trace and context
            \Log::error('Booking save failed', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
                'team_id' => $this->teamId,
                'user_id' => auth()->id(),
                'category_id' => $this->selectedCategoryId,
                'appointment_date' => $this->appointment_date,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
            ]);

            $this->dispatch('swal:exist-booking', [
                'title' => $ex->getMessage(),
                'icon' => 'error',
            ]);

            // ActivityLog::storeLog($this->teamId, null, null, null, 'Booking', $this->locationId, ActivityLog::TYPE_BOOKING, null, null);

            return;
        }
    }


    // public function saveAppointmentForm()
    // {
    //     if(!empty($this->dynamicProperties)){
    //         $this->validate();
    //         }

    //     $this->dispatch('swal:saving-booking', [
    //         'title' => 'Saving',
    //         'icon' => 'success',
    //     ]);

    //     $formattedFields = [];
    //     foreach ($this->dynamicProperties as $key => $value) {
    //         $fieldName = preg_replace('/_\d+/', '', $key);
    //         $fieldName = strtolower($fieldName);
    //         $formattedFields[$fieldName] = $value;
    //     }
    //     $this->name = $formattedFields['name'] ?? null;
    //     $this->phone = $formattedFields['phone'] ?? null;
    //     $this->email = isset($formattedFields['email']) ? $formattedFields['email'] : (isset($formattedFields['email address']) ? $formattedFields['email address'] : null);

    //     $jsonDynamicData = json_encode($formattedFields);

    //     try {
    //         DB::beginTransaction();

    //         $status = Booking::STATUS_PENDING;
    //         if ($this->accountSetting?->req_accept_mode == Booking::AUTO_CONFIRM) {
    //             $status = Booking::STATUS_CONFIRMED;
    //         }

    //         if ($this->accountSetting?->custom_booking_id == 'default') {
    //             $refID = time();
    //         } elseif($this->accountSetting?->custom_booking_id == 'email') {
    //             if (isset($this->email) && $this->email != '') {
    //                 $refID = $this->email;
    //             } else {
    //                 $refID = time();
    //             }
    //         }elseif($this->accountSetting?->custom_booking_id == 'phone') {
    //              if (isset($this->phone) && $this->phone != '') {
    //                 $refID = $this->phone;
    //             } else {
    //                 $refID = time();
    //             }
    //         }else{
    //             $refID = time();
    //         }


    //         $userAuth = '';
    //        if(Auth::check()){
    //            $userAuth = Auth::id();
    //        }


    //         $booking = Booking::create([
    //             'team_id' => $this->teamId,
    //             'booking_date' => $this->appointment_date,
    //             'booking_time' => $this->start_time .'-'. $this->end_time,
    //             'name' => $this->name ?? '',
    //             'phone' => $this->phone ?? '',
    //             'email' => $this->email ?? '',
    //             'category_id' => $this->selectedCategoryId ?? null,
    //             'sub_category_id' => !empty($this->secondChildId) ? $this->secondChildId : null,
    //             'child_category_id' => !empty($this->thirdChildId) ? $this->thirdChildId : null,
    //             'start_time' => $this->start_time,
    //             'end_time' => $this->end_time,
    //             'location_id' => $this->locationId,
    //             'json' => $jsonDynamicData,
    //             'status' => $status,
    //             'created_by' => $userAuth ?? '',
    //             'refID' => $refID ?? time()
    //         ]);

    //          if (!empty($this->thirdChildId))
    //         $this->thirdCategoryName = Category::viewCategoryName($this->thirdChildId);
    //     if (!empty($this->secondChildId))
    //         $this->secondCategoryName = Category::viewCategoryName($this->secondChildId);
    //     if (!empty($this->selectedCategoryId))
    //         $this->categoryName =  Category::viewCategoryName($this->selectedCategoryId);

    //         $url = url('booking-confirmed', ['id' => base64_encode($booking->id)]);
    //         // $cleanedUrl = str_replace('/', '', $url);
    //         $cleanedUrl =  $url;

    //           //store customer data and activity log
    //     if (!empty($this->phone)) {
    //         $existingCustomer = Customer::where('phone', $this->phone)
    //             ->where('team_id', $this->teamId)
    //             ->where('location_id', $this->locationId)
    //             ->first();

    //         // Create customer if not exists
    //         if (!$existingCustomer) {
    //             $existingCustomer = Customer::create([
    //                 'team_id' => $this->teamId,
    //                 'location_id' => $this->locationId,
    //                 'name' => $this->name ?? null,
    //                 'phone' => $this->phone,
    //                 'json_data' => $jsonDynamicData, // casted automatically to JSON
    //             ]);
    //         }

    //         // Log customer activity with type 'queue'
    //         CustomerActivityLog::create([
    //             'team_id' => $this->teamId,
    //             'location_id' => $this->locationId,
    //             'queue_id' => null,
    //             'booking_id' => $booking->id ?? null,
    //             'type' => 'booking',
    //             'customer_id' => $existingCustomer->id,
    //             'note' => 'Customer joined the booking.',
    //         ]);
    //     }

    //         $data = [
    //             'booking_id' => $booking->id,
    //             'name' => $booking->name ?? '',
    //             'phone' => $booking->phone ?? '',
    //             'phone_code' => $booking->phone_code ?? '91',
    //             'booking_date' => \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y'),
    //             'booking_time' => $booking->booking_time,
    //             'booked_by' => $userAuth,
    //             'category_name' => $this->categoryName,
    //             'thirdC_name' => $this->thirdCategoryName,
    //             'secondC_name' => $this->secondCategoryName,
    //             'location' => $booking->location?->location_name,
    //             'status' => $booking->status,
    //             'json' => $booking->json,
    //             'refID' => $booking->refID,
    //             'view_booking' => $cleanedUrl,
    //             'locations_id' => $this->locationId,
    //             'team_id' => $this->teamId,
    //         ];

    //          if(!empty($this->stripeResponeID)){
    //           StripeResponse::where('id',$this->stripeResponeID)->update([
    //             'booking_id'=>$booking->id,
    //           ]);

    //           $this->stripeResponeID = '';
    //     }


    //         $data = array_merge($data, ['to_mail' => $booking->email]);


    //            // Send email
    //             if ($this->enable_service_time && !empty($this->email)) {


    //                 Mail::to($this->email)->send(new AppointmentConfirmation($this->name, $this->enable_service_time, $this->note));
    //             }else{
    //             if ($status == Booking::STATUS_CONFIRMED) {
    //                                 $message = 'Appointment Booked Successfully';
    //                                 // $this->sendEmail( $data, 'Appointment Booked Successfully', 'booking-confirmation', $this->teamId );
    //                                 $this->sendNotification($data, 'booking confirmed', $message);
    //                             } else {
    //                                 // $this->sendEmail( $data, 'Appointment Request', 'admin_booking_approval', $this->teamId );
    //                                 $message = 'Appointment request has been successfully sent. Please wait for confirmation';
    //                                 $this->sendNotification($data, 'booking confirmed', $message);
    //                             }
    //             }


    //         DB::commit();
    //         $this->resetForm();


    //         if ($status == Booking::STATUS_CONFIRMED  && $this->accountSetting->booking_confirmation_page == 1) {
    //             return  $this->redirect($cleanedUrl);
    //         } else {
    //             $this->dispatch('swal:saved-booking', [
    //                 'title' => $message,
    //                 'icon' => 'success',
    //             ]);
    //         }
    //     } catch (\Throwable $ex) {
    //         DB::rollBack();

    //            // Log the exception with stack trace and context
    //         \Log::error('Booking save failed', [
    //             'message' => $ex->getMessage(),
    //             'trace' => $ex->getTraceAsString(),
    //             'team_id' => $this->teamId,
    //             'user_id' => auth()->id(),
    //             'category_id' => $this->selectedCategoryId,
    //             'appointment_date' => $this->appointment_date,
    //             'start_time' => $this->start_time,
    //             'end_time' => $this->end_time,
    //         ]);

    //         $this->dispatch('swal:exist-booking', [
    //             'title' => $ex->getMessage(),
    //             'icon' => 'error',
    //         ]);
    //         return;
    //     }
    // }

    public function resetForm()
    {
        $this->name = $this->phone = $this->start_time = $this->end_time = $this->appointment_date = null;
        $this->dynamicProperties = [];
        $this->resetDynamic();
    }

    public function sendNotification($data, $title, $template, $logData = null)
    {
        $data['team_id'] = $this->teamId;
        if (isset($data['to_mail']) && $data['to_mail'] != '') {

            \Log::info('email send', ['message' => 'booking email send']);

            if (!empty($logData)) {
               $logData['channel'] = 'email';
               $logData['status'] = MessageDetail::SENT_STATUS;
               // MessageDetail::storeLog($logData);
           }
            SmtpDetails::sendMail($data, $title, $template, $this->teamId,$logData);

        } else {
            \Log::error('email not send', ['message' => 'no booking email send']);
        }
        \Log::info('sms first', ['message' => 'booking first sms send']);
        $data['location'] = Location::find($this->locationId)->value('location_name');
        if (!empty($data['phone'])) {
              \Log::info('step 6 sms');
            \Log::info('sms send', ['message' => 'booking sms send']);
            $logData['channel'] = 'sms';
            $logData['status'] = MessageDetail::SENT_STATUS;
            SmsAPI::sendSms($this->teamId, $data, $title, $title, $logData);
   \Log::info('sms end', ['message' => 'booking end sms send']);
            // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
        } else {
            \Log::error('sms no send', ['message' => 'no booking sms send']);
        }
    }



    public function showPaymentPage()
    {
        if (!empty($this->dynamicProperties)) {
            $this->validate();
        }

        $this->formfieldSection = false;
        $this->paymentStep = true;

        // Only dispatch cardElement if Stripe is selected
        if ($this->selectedPaymentGateway === 'stripe') {
            $this->dispatch('cardElement');
        }
        //   dd($this->siteDetails->is_paid_categories,$this->siteDetails->paid_category_level);
    }

    // Livewire hook: called when selectedPaymentGateway changes
    public function updatedSelectedPaymentGateway($value)
    {
        if ($value === 'stripe') {
            $this->dispatch('cardElement');
        }
    }

    public function getAmount()
    {
        // Check if paymentSetting exists
        if (!empty($this->paymentSetting)) {
            $stripeEnabled = !empty($this->paymentSetting->api_key) && !empty($this->paymentSetting->api_secret) && ($this->paymentSetting->stripe_enable == 1);
            $juspayEnabled = !empty($this->paymentSetting->juspay_merchant_id) && !empty($this->paymentSetting->juspay_api_key) && ($this->paymentSetting->juspay_enable == 1);
            
            // Check if at least one payment gateway is enabled
            if ($stripeEnabled || $juspayEnabled) {
                // Configure Stripe if enabled
                if ($stripeEnabled) {
                    config([
                        'services.stripe.key' => $this->paymentSetting->api_key,
                        'services.stripe.secret' => $this->paymentSetting->api_secret,
                    ]);
                }
                
                // Configure Juspay if enabled
                if ($juspayEnabled) {
                    config([
                        'services.juspay.merchant_id' => $this->paymentSetting->juspay_merchant_id,
                        'services.juspay.api_key' => $this->paymentSetting->juspay_api_key,
                        'services.juspay.env' => 'sandbox',
                    ]);
                }
                
                // Set default payment gateway
                if ($juspayEnabled && !$stripeEnabled) {
                    $this->selectedPaymentGateway = 'juspay';
                } else {
                    $this->selectedPaymentGateway = 'stripe';
                }
            } else {
                // Show error message if keys are missing
                $this->dispatch('show-toast', type: 'error', message: 'Payment service keys are missing. Please set API Key and Secret in payment settings.');
                $this->isFree = 0;
                $this->paymentStep = false;
                return;
            }
        } else {
            // Show error message if payment setting is completely missing
            $this->dispatch('show-toast', type: 'error', message: 'Payment setting not configured. Please configure payment settings.');
            $this->isFree = 0;
            $this->paymentStep = false;
            return;
        }

        // Determine amount and stripe category based on selected category level
        if (!empty($this->thirdChildId) && $this->paymentSetting?->category_level === 3) {
            $this->amount = Category::where('id', $this->thirdChildId)->value('amount') ?? 0;
            $this->stripeCategory = $this->thirdChildId;
        } elseif (!empty($this->secondChildId) && $this->paymentSetting?->category_level >= 2) {
            $this->amount = Category::where('id', $this->secondChildId)->value('amount') ?? 0;
            $this->stripeCategory = $this->secondChildId;
        } elseif (!empty($this->selectedCategoryId)) {
            $this->amount = Category::where('id', $this->selectedCategoryId)->value('amount') ?? 0;
            $this->stripeCategory = $this->selectedCategoryId;
        } else {
            $this->amount = 0;
            $this->stripeCategory = null;
        }

        // Check if category is paid
        if ($this->paymentSetting->enable_payment == 1 && $this->amount > 0) {
            $this->isFree = Category::where('id', $this->stripeCategory)->value('is_paid') ?? 0;
        } else {
            $this->isFree = 0;
            $this->paymentStep = false;
        }
    }

    #[On('stripe-payment-method')]
    public function setPaymentMethod(string $paymentMethodId)
    {
        $this->getAmount();
        $this->paymentMethodId = $paymentMethodId;
        $this->handleCheckout();
    }


    public function handleCheckout()
    {
        try {

            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($this->amount * 100),
                'currency' => strtolower($this->paymentSetting->currency) ?? 'usd',
                'payment_method' => $this->paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'receipt_email' => $this->email,
                'return_url' => route('payment.success'),
            ]);

            $stripeResponse = StripeResponse::create([
                'team_id' => $this->teamId,
                'location_id' =>  $this->locationId,
                'category_id' => $this->stripeCategory,
                'payment_intent_id' => $paymentIntent->id,
                'customer_email' => $this->email,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
                'full_response' => $paymentIntent->toArray(),
            ]);
            $this->stripeResponeID = $stripeResponse->id;
            \Log::info("Booking payment done " . $this->teamId);
            $this->saveAppointmentForm();

            $this->paymentStep = false;
            $this->isFree = 0;
            $this->amount = 0;
            $this->stripeCategory = '';
            $this->email = '';

            $this->successMessage = 'Payment successful!';
        } catch (\Exception $e) {
            \Log::error('Payment booking failed: teamID' . $this->teamId . '= ' . $e->getMessage());
            $this->errorMessage = 'Payment failed: Something went Wrong';
        }
    }

    public function initiateJuspayPayment()
    {
        \Log::info('Juspay payment initiated - START');
        
        // Validate email first
        if (empty($this->email)) {
            $this->dispatch('show-toast', type: 'error', message: 'Please enter your email address.');
            return;
        }

        try {
            // Get credentials directly from paymentSetting
            $merchantId = $this->paymentSetting->juspay_merchant_id ?? null;
            $apiKey = $this->paymentSetting->juspay_api_key ?? null;
            $env = 'sandbox'; // Change to 'production' when ready

            \Log::info('Juspay Config', [
                'merchant_id' => $merchantId ? 'SET' : 'NOT SET',
                'api_key' => $apiKey ? 'SET' : 'NOT SET',
                'env' => $env,
                'amount' => $this->amount,
                'email' => $this->email,
                'payment_setting_id' => $this->paymentSetting->id ?? 'NULL',
            ]);

            if (empty($apiKey)) {
                $this->errorMessage = 'Juspay API key missing. Please configure Juspay in payment settings.';
                $this->dispatch('show-toast', type: 'error', message: 'Juspay API key missing. Please configure Juspay in payment settings.');
                \Log::error('Juspay API key missing', [
                    'payment_setting' => $this->paymentSetting ? 'EXISTS' : 'NULL',
                    'juspay_enable' => $this->paymentSetting->juspay_enable ?? 'NULL',
                ]);
                return;
            }

            $url = $env === 'production'
                ? 'https://api.juspay.in/orders'
                : 'https://sandbox.juspay.in/orders';

            $this->juspayOrderId = 'ORDER-' . time() . '-' . Str::random(4);

            $payload = [
                'order_id'       => $this->juspayOrderId,
                'amount'         => $this->amount,
                'currency'       => $this->paymentSetting->currency ?? 'INR',
                'customer_id'    => $this->phone ?? 'GUEST',
                'customer_email' => $this->email,
                'customer_phone' => $this->phone,
                'return_url'     => route('book-appointment', ['location_id' => base64_encode($this->locationId)]),
            ];

            $response = Http::withBasicAuth($apiKey, '')
                ->asForm()
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                $this->juspayTransactionId = $data['id'] ?? $data['order_id'] ?? null;
                $this->juspayPaymentUrl = $data['payment_links']['web'] ?? null;
                $status = strtoupper($data['status'] ?? 'CREATED');

                // Save the order to DB
                $juspayOrder = JuspayOrder::create([
                    'order_id'        => $this->juspayOrderId,
                    'transaction_id'  => $this->juspayTransactionId,
                    'team_id'         => $this->teamId,
                    'location_id'     => $this->locationId,
                    'customer_id'     => $this->phone ?? 'GUEST',
                    'customer_email'  => $this->email,
                    'customer_phone'  => $this->phone,
                    'amount'          => $this->amount,
                    'currency'        => $this->paymentSetting->currency ?? 'INR',
                    'status'          => $status,
                    'payment_url'     => $this->juspayPaymentUrl,
                    'response_json'   => $data,
                ]);

                \Log::info("Juspay payment initiated for team: " . $this->teamId);
                
                // Store booking data in session for later (after payment)
                session([
                    'juspay_pending_booking' => [
                        'order_id' => $this->juspayOrderId,
                        'dynamic_properties' => $this->dynamicProperties,
                        'team_id' => $this->teamId,
                        'location_id' => $this->locationId,
                        'category_id' => $this->selectedCategoryId,
                        'second_child_id' => $this->secondChildId,
                        'third_child_id' => $this->thirdChildId,
                        'appointment_date' => $this->appointment_date,
                        'start_time' => $this->start_time,
                        'end_time' => $this->end_time,
                        'email' => $this->email,
                        'phone' => $this->phone,
                        'phone_code' => $this->phone_code,
                        'amount' => $this->amount,
                    ]
                ]);

                // Redirect to Juspay payment page
                if ($this->juspayPaymentUrl) {
                    \Log::info("Redirecting to Juspay payment URL: " . $this->juspayPaymentUrl);
                    return redirect()->away($this->juspayPaymentUrl);
                } else {
                    $this->dispatch('show-toast', type: 'error', message: 'Payment URL not received from Juspay.');
                }

            } else {
                $body = $response->body();
                \Log::error('Juspay initiation failed', ['status' => $response->status(), 'body' => $body]);
                $this->errorMessage = "Failed to initiate payment (HTTP {$response->status()}).";
                $this->dispatch('show-toast', type: 'error', message: "Failed to initiate payment. Please try again.");
            }

        } catch (\Exception $e) {
            \Log::error('Juspay Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->errorMessage = 'Something went wrong while initiating payment.';
            $this->dispatch('show-toast', type: 'error', message: 'Payment error: ' . $e->getMessage());
        }
    }

    public function handleJuspayReturn()
    {
        try {
            $pendingBooking = session('juspay_pending_booking');
            $orderId = $pendingBooking['order_id'] ?? null;

            if (!$orderId) {
                \Log::error('Juspay return: No order ID in session');
                session()->forget('juspay_pending_booking');
                return;
            }

            // Get the Juspay order from database
            $juspayOrder = JuspayOrder::where('order_id', $orderId)->first();

            if (!$juspayOrder) {
                \Log::error('Juspay return: Order not found', ['order_id' => $orderId]);
                session()->forget('juspay_pending_booking');
                return;
            }

            // TODO: Verify payment status with Juspay API
            // For now, we'll assume payment is successful if user returns
            // In production, you should verify the payment status

            \Log::info('Juspay return: Processing booking', ['order_id' => $orderId]);

            // Restore booking data from session
            $this->dynamicProperties = $pendingBooking['dynamic_properties'] ?? [];
            $this->teamId = $pendingBooking['team_id'];
            $this->locationId = $pendingBooking['location_id'];
            $this->selectedCategoryId = $pendingBooking['category_id'];
            $this->secondChildId = $pendingBooking['second_child_id'];
            $this->thirdChildId = $pendingBooking['third_child_id'];
            $this->appointment_date = $pendingBooking['appointment_date'];
            $this->start_time = $pendingBooking['start_time'];
            $this->end_time = $pendingBooking['end_time'];
            $this->email = $pendingBooking['email'];
            $this->phone = $pendingBooking['phone'];
            $this->phone_code = $pendingBooking['phone_code'];
            $this->amount = $pendingBooking['amount'];

            // Save the booking
            $this->saveAppointmentForm();

            // Update Juspay order with booking ID
            if (session()->has('last_booking_id')) {
                $juspayOrder->update([
                    'booking_id' => session('last_booking_id'),
                    'status' => 'COMPLETED', // Update status
                ]);
            }

            // Clear session
            session()->forget('juspay_pending_booking');

            \Log::info('Juspay payment completed and booking saved', [
                'order_id' => $orderId,
                'booking_id' => session('last_booking_id')
            ]);

            $this->dispatch('show-toast', type: 'success', message: 'Payment successful! Your booking has been confirmed.');

        } catch (\Exception $e) {
            \Log::error('Juspay return error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->forget('juspay_pending_booking');
        }
    }

    public function createVideoCall()
    {
        $graph = new MicrosoftGraphService();
        $meeting = $graph->createTeamsMeeting(
            $this->organizerEmail,
            'Appointment with Customer',
            now()->addMinutes(5)->toIso8601String(),
            now()->addMinutes(35)->toIso8601String()
        );

        $this->meetingLink = $meeting->getJoinWebUrl(); // e.g. https://teams.live.com/meet/...
    }

    //   public function checkAndCancelBookings()
    // {
    //     $now = Carbon::now($this->timezone);
    //     $fiveMinutesAgo = $now->copy()->subMinutes(5);

    //     $bookings = Booking::where('team_id', $this->teamId)
    //         ->whereDate('booking_date', $now->toDateString())
    //         ->where('status', '!=', 'Cancelled')
    //         ->whereNull('convert_datetime')
    //         ->where('is_convert', "No")
    //         ->whereTime('booking_time', '<=', $fiveMinutesAgo->format('H:i:s')) // 5 mins after scheduled time
    //         ->get();

    //            Log::info("run checking".$fiveMinutesAgo->format('H:i:s'));

    //     $details = SmtpDetails::where('team_id', $this->teamId)
    //         ->where('location_id', $this->locationId)
    //         ->first();

    //     if ($details && !empty($details->hostname) && !empty($details->port) &&
    //         !empty($details->username) && !empty($details->password) &&
    //         !empty($details->from_email) && !empty($details->from_name)) {

    //         Config::set('mail.mailers.smtp.transport', 'smtp');
    //         Config::set('mail.mailers.smtp.host', trim($details->hostname));
    //         Config::set('mail.mailers.smtp.port', trim($details->port));
    //         Config::set('mail.mailers.smtp.encryption', trim($details->encryption ?? 'ssl'));
    //         Config::set('mail.mailers.smtp.username', trim($details->username));
    //         Config::set('mail.mailers.smtp.password', trim($details->password));
    //         Config::set('mail.from.address', trim($details->from_email));
    //         Config::set('mail.from.name', trim($details->from_name));
    //     }

    //     foreach ($bookings as $booking) {
    //         $message = 'Your appointment was automatically cancelled because you did not check in within 5 minutes of the scheduled time.';

    //         $suspensionLog = SuspensionLog::create([
    //             'team_id' => $booking->team_id,
    //             'location_id' => $booking->location_id,
    //             'action_type' => 'Appointment',
    //             'notification_type' => 'Email',
    //             'reason' => $message,
    //         ]);

    //         $booking->update([
    //             'suspension_logs_id' => $suspensionLog->id,
    //             'status' => 'Cancelled',
    //             'cancel_remark' => $message,
    //             'cancel_reason' => $message,
    //         ]);

    //         try {
    //             if (!empty($booking->email)) {
    //                 $bookingData = [
    //                     'booking_date' => $booking->booking_date,
    //                     'booking_time' => $booking->booking_time,
    //                     'team_id' => $booking->team_id,
    //                     'location_id' => $booking->location_id,
    //                 ];

    //                 if ($details && !empty($details->hostname)) {
    //                     Mail::to($booking->email)->send(new SuspensionNotification(
    //                         $message,
    //                         'Appointment Auto-Cancelled',
    //                         $bookingData
    //                     ));
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             Log::error("Email failed to send to {$booking->email}: " . $e->getMessage());
    //         }
    //     }
    // }

    /**
     * Send booking data to external Crelio API
     * 
     * @param Booking $booking
     * @param array $bookingData
     * @return array|null
     */
    protected function SendToCrelioAPI($booking, $bookingData)
    {
        // Fetch API settings from AccountSetting
        $accountSetting = \App\Models\AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', \App\Models\AccountSetting::BOOKING_SLOT)
            ->first();
        
        // Get auth key and lab user ID from settings or use defaults
        $authKey = $accountSetting?->crelio_auth_key ?? "62a91266-c52c-11ec-9ced-0af58d7f72b8";
        $labUserId = $accountSetting?->crelio_lab_user_id ?? "4783";
        
        \Log::info('SendToAPI: Starting API integration', [
            'booking_id' => $booking->id,
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'form_request_data' => $bookingData,
            'form_post_data' => $this->dynamicProperties ?? [],
            'auth_key' => $authKey,
            'lab_user_id' => $labUserId,
        ]);
        
        // Step 1: Get organization list
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://crelio.solutions/androidOrganizationListForCC/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'tokenObj' => json_encode(['token' => $authKey])
            ],
            CURLOPT_HTTPHEADER => [
                'Cookie: DEPLOYMENT_MODE=Prod; DEPLOYMENT_ZONE=IN; labUserId=' . $labUserId
            ],
        ]);
        
        $orgResponse = curl_exec($curl);
        $orgError = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        // Log organization API call to database
        ApiLog::logApiCall([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'booking_id' => $booking->id,
            'api_name' => 'Organization List',
            'api_url' => 'https://crelio.solutions/androidOrganizationListForCC/',
            'method' => 'POST',
            'request_data' => json_encode(['tokenObj' => json_encode(['token' => $authKey])]),
            'response_data' => $orgResponse,
            'http_code' => $httpCode,
            'status' => $orgError ? 'error' : 'success',
            'error_message' => $orgError ?: null,
        ]);
        
        \Log::info('SendToAPI: Organization list API response', [
            'booking_id' => $booking->id,
            'http_code' => $httpCode,
            'response' => $orgResponse,
            'error' => $orgError ?: null,
        ]);
        
        if ($orgError) {
            \Log::error("SendToAPI: Error fetching organization list", [
                'booking_id' => $booking->id,
                'error' => $orgError,
            ]);
            throw new \Exception("Error fetching organization list: $orgError");
        }
        
        $orgData = json_decode($orgResponse, true);
        
        // Step 2: Extract organization data from orgList
        if (empty($orgData['orgList'])) {
            \Log::error("SendToAPI: No organization data found", [
                'booking_id' => $booking->id,
                'response' => $orgResponse,
            ]);
            throw new \Exception("No organization data found in response");
        }
        
        // Pick the first organization
        $organization = $orgData['orgList'][0];
        $organizationIdLH = $organization['orgId'];
        $organizationName = $organization['orgFullName'];
        
        \Log::info('SendToAPI: Organization retrieved', [
            'booking_id' => $booking->id,
            'organizationIdLH' => $organizationIdLH,
            'organizationName' => $organizationName,
        ]);
        
        // Step 3: Create appointment with dynamic data from booking
        // Parse booking JSON data
        $jsonData = json_decode($booking->json, true) ?? [];
        
        // Extract fields from JSON with fallbacks
        $age = $jsonData['age'] ?? "38";
        $gender = $jsonData['gender'] ?? "Male";
        $pincode = $jsonData['pincode'] ?? "452001";
        $name = $jsonData['name'] ?? $booking->name ?? "Test User";
        $phone = $jsonData['phone'] ?? $booking->phone ?? "9876543210";
        $email = $jsonData['email'] ?? $booking->email ?? "Test@gmail.com";
        
        // Get slot period from account settings (default to 60 if not set)
        $slotPeriod = (int)($this->accountSetting->slot_period ?? 60);
        
        // Calculate dynamic start and end dates
        $startDate = Carbon::parse($booking->booking_date)->format('Y-m-d') . 'T' . Carbon::parse($booking->start_time)->format('H:i:s') . 'Z';
        $endDate = Carbon::parse($booking->booking_date)->format('Y-m-d') . 'T' . Carbon::parse($booking->start_time)->addMinutes($slotPeriod)->format('H:i:s') . 'Z';
        
        $appointmentData = [
            "mobile" => $phone,
            "email" => $email,
            "designation" => "MR.",
            "fullName" => $name,
            "age" => (string)$age,
            "gender" => (string)$gender,
            "area" => "",
            "city" => "Indore",
            "labPatientId" => (string)$booking->id,
            "pincode" => (string)$pincode,
            "isAppointmentRequest" => 1,
            "startDate" => $startDate,
            "endDate" => $endDate,
            "billDetails" => [
                "emergencyFlag" => "0",
                "billTotalAmount" => "0",
                "advance" => "0",
                "paymentType" => "CREDIT",
                "referralName" => "",
                "otherReferral" => "",
                "orderNumber" => "HIS REF NO",
                "organisationName" => $organizationName,
                "additionalAmount" => "0",
                "organizationIdLH" => $organizationIdLH,
                "comments" => ""
            ]
        ];
        
        \Log::info('SendToAPI: Sending appointment creation request', [
            'booking_id' => $booking->id,
            'appointment_data' => $appointmentData,
        ]);
        
        // Step 4: Create appointment using the organizationIdLH
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://crelio.solutions/integration/appointment/create-new/?authKey=$authKey",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($appointmentData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Cookie: DEPLOYMENT_MODE=Prod; DEPLOYMENT_ZONE=IN; labUserId=4783'
            ],
        ]);
        
        $appointmentResponse = curl_exec($curl);
        $appointmentError = curl_error($curl);
        $appointmentHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        // Log appointment creation API call to database
        ApiLog::logApiCall([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'booking_id' => $booking->id,
            'api_name' => 'Create Appointment',
            'api_url' => "https://crelio.solutions/integration/appointment/create-new/?authKey=$authKey",
            'method' => 'POST',
            'request_data' => json_encode($appointmentData),
            'response_data' => $appointmentResponse,
            'http_code' => $appointmentHttpCode,
            'status' => $appointmentError ? 'error' : 'success',
            'error_message' => $appointmentError ?: null,
        ]);
        
        \Log::info('SendToAPI: Appointment creation API response', [
            'booking_id' => $booking->id,
            'http_code' => $appointmentHttpCode,
            'response' => $appointmentResponse,
            'error' => $appointmentError ?: null,
        ]);
        
        if ($appointmentError) {
            \Log::error("SendToAPI: Error creating appointment", [
                'booking_id' => $booking->id,
                'error' => $appointmentError,
                'http_code' => $appointmentHttpCode,
            ]);
            throw new \Exception("Error creating appointment: $appointmentError");
        }
        
        $responseData = json_decode($appointmentResponse, true);
        
        \Log::info("SendToAPI: ✅ Appointment created successfully", [
            'booking_id' => $booking->id,
            'response_data' => $responseData,
        ]);
        
        return $responseData;
    }


    public function render()
    {
        // $layout = Auth::check() ? 'components.layouts.app' : 'components.layouts.custom-layout';
        $layout = 'components.layouts.custom-booking-layout';

        return view('livewire.main-booking-appointment')->layout($layout);
    }
}
