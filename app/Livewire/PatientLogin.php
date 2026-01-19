<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\SiteDetail;
use App\Models\Location;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Facades\Mail;
use App\Mail\PatientLoginOtp;

#[Layout('components.layouts.custom-patient')]
#[Title('Patient Login')]
class PatientLogin extends Component
{
    public $teamId;
    public $customer_type = 'Corporate'; // Default to Corporate as shown in screenshot
    public $loginMethod = 'phone';
    public $email = '';
    public $mobile_number = '';
    public $otpMethod = 'whatsapp'; // Default
    public $otp = '';
    public $otpSent = false;
    public $generatedOtp = null; // Store locally for now or session

    protected $messages = [
        'customer_type.required' => 'Please select customer type.',
        'mobile_number.required' => 'Phone Number is required.',
        'email.required' => 'Email Address is required.',
        'email.email' => 'Please enter a valid email address.',
    ];

    public function mount()
    {
        $this->teamId = tenant('id');

        // If already logged in as patient, redirect to dashboard
        if (Session::has('patient_member_id')) {
            return redirect()->route('tenant.patient.dashboard');
        }
    }

    protected function rules()
    {
        $rules = [
            'loginMethod' => 'required|in:phone,email',
        ];

        if ($this->loginMethod === 'phone') {
            $rules['mobile_number'] = 'required|string';
            $rules['otpMethod'] = 'required|in:whatsapp,sms';
        } else {
            $rules['email'] = 'required|email';
        }

        return $rules;
    }

    public function sendOtp()
    {
        $this->validate();

        try {
            // Find existing member
            $query = Member::where('team_id', $this->teamId)
                ->where('is_active', 1)
                ->where('status', 'active');

            $member = null;

            if ($this->loginMethod === 'phone') {
                $cleanMobileNumber = preg_replace('/[^0-9]/', '', $this->mobile_number);
                // Try full match first
                $member = (clone $query)->where(function ($q) use ($cleanMobileNumber) {
                    $q->whereRaw("CONCAT(mobile_country_code, mobile_number) = ?", [$cleanMobileNumber]);
                })->first();

                if (!$member) {
                    $member = (clone $query)->where('mobile_number', $cleanMobileNumber)->first();
                }
            } else {
                $member = $query->where('email', $this->email)->first();
            }

            if (!$member) {
                // For security, maybe don't reveal if user exists, but for UX usually we do or we just send valid OTP
                // Creating a new user? The screenshots imply "Login", showing "Sign Up" link elsewhere.
                session()->flash('error', 'User not found.');
                return;
            }

            // Generate OTP
            $this->generatedOtp = rand(100000, 999999);

            // Store likely in session or cache for verification
            Session::put('login_otp', $this->generatedOtp);
            Session::put('login_member_id', $member->id);
            Session::put('login_otp_expires', now()->addMinutes(5));

            // LOG OTP for testing
            LogFacade::info("OTP Generated for Member {$member->id}: {$this->generatedOtp}");

            if ($this->loginMethod === 'email') {
                // Fetch logo based on team ID
                $siteDetail = SiteDetail::where('team_id', $this->teamId)
                    ->whereNotNull('business_logo')
                    ->where('business_logo', '!=', '')
                    ->first();
                
                if ($siteDetail) {
                     $logoUrl = asset('storage/' . $siteDetail->business_logo);
                } else {
                     // Fallback to searching without team_id or default logic if needed, but for now stick to team
                     // Or check for null location specifically if multiple locations exist but one has global settings
                     $logoPath = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId);
                     $logoUrl = asset($logoPath);
                }

                Mail::to($member->email)->send(new PatientLoginOtp($this->generatedOtp, $member->name, $logoUrl));
            } else {
                // Phone OTP logic (WhatsApp/SMS) - skipped as per user request for now
                // if ($this->otpMethod === 'whatsapp') ...
            }
            
            $this->otpSent = true;
            session()->flash('message', 'OTP sent successfully!');

        } catch (\Exception $e) {
            LogFacade::error('OTP Send error: ' . $e->getMessage());
            session()->flash('error', 'Failed to send OTP. Please try again.');
        }
    }

    public function verifyOtp()
    {
        $this->validate([
            'otp' => 'required|numeric'
        ]);

        if (Session::get('login_otp') != $this->otp) {
            session()->flash('error', 'Invalid OTP.');
            return;
        }

        if (now()->greaterThan(Session::get('login_otp_expires'))) {
            session()->flash('error', 'OTP Expired.');
            return;
        }

        $memberId = Session::get('login_member_id');
        $member = Member::find($memberId);

        if (!$member) {
            session()->flash('error', 'User not found.');
            return;
        }

        // Login User
        Session::put('patient_member_id', $member->id);
        Session::put('patient_member', $member->toArray());
        Session::put('patient_customer_type', $this->customer_type); // Still keeping this if needed

        Session::forget(['login_otp', 'login_member_id', 'login_otp_expires']);
        Session::regenerate();

        return redirect()->route('tenant.patient.dashboard');
    }

    // Keep reset method to go back
    public function resetLogin()
    {
        $this->otpSent = false;
        $this->otp = '';
    }

    public function render()
    {
        // Get first location for the team
        $firstLocation = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->orderBy('id')
            ->first();

        $locationId = $firstLocation ? $firstLocation->id : null;

        // Get logo based on team id and first location
        $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId ?? null, $locationId);

        return view('livewire.patient-login', [
            'logo' => $logo,
        ]);
    }
}
