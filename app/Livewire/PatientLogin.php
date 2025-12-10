<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\SiteDetail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log as LogFacade;

#[Layout('components.layouts.custom-booking-layout')]
#[Title('Patient Login')]
class PatientLogin extends Component
{
    public $teamId;
    public $customer_type = 'Corporate'; // Default to Corporate as shown in screenshot
    public $mobile_number = '';
    public $password = '';
    public $showPassword = false;

    protected $messages = [
        'customer_type.required' => 'Please select customer type.',
        'mobile_number.required' => 'Country Code & Mobile Number is required.',
        'password.required' => 'Password is required.',
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
        return [
            'customer_type' => 'required|in:Private,Corporate',
            'mobile_number' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function login()
    {
        // Get values from request if properties are empty (fallback for wire:model sync issues)
        $request = request();
        if (empty($this->mobile_number) && $request->has('mobile_number')) {
            $this->mobile_number = $request->input('mobile_number');
        }
        if (empty($this->password) && $request->has('password')) {
            $this->password = $request->input('password');
        }
        if (empty($this->customer_type) && $request->has('customer_type')) {
            $this->customer_type = $request->input('customer_type');
        }

        // Debug: Check what we're receiving
        LogFacade::info('Login attempt', [
            'mobile_number' => $this->mobile_number,
            'password' => $this->password ? '***' : 'empty',
            'customer_type' => $this->customer_type,
            'team_id' => $this->teamId,
            'request_all' => $request->all(),
        ]);

        $this->validate();

        try {
            // Clean mobile number - remove any spaces or special characters except numbers
            $cleanMobileNumber = preg_replace('/[^0-9]/', '', $this->mobile_number);
            
            // Build mobile number query - could be with or without country code
            $mobileQuery = Member::where('team_id', $this->teamId)
                ->where('is_active', 1)
                ->where('status', 'active');

            // Try to find by full mobile number first (with country code)
            $member = $mobileQuery->where(function($query) use ($cleanMobileNumber) {
                $query->whereRaw("CONCAT(mobile_country_code, mobile_number) = ?", [$cleanMobileNumber]);
            })->first();

            // If not found, try without country code
            if (!$member) {
                $member = $mobileQuery->where('mobile_number', $cleanMobileNumber)->first();
            }

            // Check if member exists and password is correct
            if (!$member || !Hash::check($this->password, $member->password)) {
                session()->flash('error', 'Invalid mobile number or password.');
                return;
            }

            // Check if member is approved and active
            if (!$member->is_active || $member->status !== 'active') {
                session()->flash('error', 'Your account is not active. Please contact support.');
                return;
            }

            // Set patient session
            Session::put('patient_member_id', $member->id);
            Session::put('patient_member', $member->toArray());
            Session::put('patient_customer_type', $this->customer_type);

            // Regenerate session for security
            Session::regenerate();

            return redirect()->route('tenant.patient.dashboard');

        } catch (\Exception $e) {
            LogFacade::error('Patient login error: ' . $e->getMessage());
            session()->flash('error', 'Login failed. Please try again.');
        }
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function render()
    {
        // Get logo
        $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId ?? null, null);

        return view('livewire.patient-login', [
            'logo' => $logo,
        ]);
    }
}
