<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\Company;
use App\Models\Country;
use App\Models\SiteDetail;
use App\Models\SmtpDetails;
use App\Models\Location;
use App\Models\MessageDetail;
use App\Models\SmsAPI;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

#[Layout('components.layouts.custom-patient')]
#[Title('Sign Up')]
class PatientRegister extends Component
{
    public $teamId;
    public $locationId;

    // Form fields
    public $identification_type = 'NRIC / FIN';
    public $nric_fin = '';
    public $passport = '';
    public $salutation = 'Mr';
    public $full_name = '';
    public $date_of_birth = '';
    public $gender = 'Male';
    public $mobile_country_code = '65';
    public $mobile_number = '';
    public $email = '';
    public $confirm_email = '';
    public $nationality = 'Singaporean';
    public $showNationalityField = false;
    public $country_id = null;
    public $showCountryField = false;
    public $allCountries = [];
    public $company_id = null;
    public $company_search = '';
    public $showCompanyDropdown = false;
    public $allCompanies = [];

    // Email verification
    public $email_verification_code = '';
    public $email_otp_sent = false;
    public $email_otp_verified = false;
    public $email_otp_expires_at = null;
    public $email_otp_countdown = 300; // 5 minutes in seconds

    // Mobile verification
    public $mobile_verification_code = '';
    public $mobile_otp_sent = false;
    public $mobile_otp_verified = false;
    public $mobile_otp_expires_at = null;
    public $mobile_otp_countdown = 300; // 5 minutes in seconds

    // Consent checkboxes
    public $consent_data_collection = false;
    public $consent_marketing = false;

    protected $messages = [
        'identification_type.required' => 'Identification Type is required.',
        'nric_fin.required' => 'NRIC / FIN is required.',
        'passport.required' => 'Passport is required.',
        'salutation.required' => 'Salutation is required.',
        'full_name.required' => 'Full Name is required.',
        'date_of_birth.required' => 'Date Of Birth is required.',
        'gender.required' => 'Gender is required.',
        'mobile_number.required' => 'Mobile Number is required.',
        'email.required' => 'Email Address is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.required' => 'Email Address is required.',
        'confirm_email.email' => 'Please enter a valid email address.',
        'confirm_email.required' => 'Please confirm your email address.',
        'email.unique' => 'This email already exists.',
        'confirm_email.required' => 'Please confirm your email address.',
        'confirm_email.same' => 'Email addresses do not match.',
        'country_id.required' => 'Country is required. Please select a country.',
        'country_id.exists' => 'Please select a valid country.',
        'nationality.required' => 'Nationality is required.',
        'company_id.required' => 'Company is required. Please select from the dropdown list.',
        'email_verification_code.required' => 'Email verification code is required.',
        'email_verification_code.digits' => 'Verification code must be 6 digits.',
        'mobile_verification_code.required' => 'Mobile verification code is required.',
        'mobile_verification_code.digits' => 'Verification code must be 6 digits.',
        'consent_data_collection.required' => 'You must consent to data collection.',
        'consent_data_collection.accepted' => 'You must consent to data collection.',
        'nric_fin.unique' => 'This NRIC / FIN already exists.',
        'passport.unique' => 'This passport number already exists.',
        'mobile_number.unique' => 'This mobile number already exists.',
    ];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        
        // Load all countries
        $this->allCountries = Country::orderBy('name')->get();
        
        // Don't auto-show fields on mount - only show when user changes country code
        $this->showCountryField = false;
        $this->showNationalityField = false;
    }

    protected function rules()
    {
        $rules = [
            'identification_type' => 'required|in:NRIC / FIN,Passport',
            'salutation' => 'required|string|in:Mr,Mrs,Ms,Dr',
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female',
            'mobile_country_code' => 'required|string',
            'mobile_number' => 'required|string|unique:members,mobile_number,NULL,id,team_id,' . $this->teamId,
            'email' => ['required', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/', 'unique:members,email,NULL,id,team_id,' . $this->teamId],
            'confirm_email' => 'required|email|same:email',
            'company_id' => 'nullable|exists:companies,id',
            'consent_data_collection' => 'accepted',
            'consent_marketing' => 'nullable|boolean',
        ];

        // Conditional validation based on identification type
        if ($this->identification_type === 'NRIC / FIN') {
            $rules['nric_fin'] = 'required|string|unique:members,nric_fin,NULL,id,team_id,' . $this->teamId;
        } else {
            $rules['passport'] = 'required|string|unique:members,passport,NULL,id,team_id,' . $this->teamId;
        }

        // Only require country and nationality if fields are shown
        if ($this->showCountryField) {
            $rules['country_id'] = 'required|exists:countries,id';
        }
        
        if ($this->showNationalityField) {
            $rules['nationality'] = 'required|string';
        }

        // Require email verification code if OTP was sent but not verified
        if ($this->email_otp_sent && !$this->email_otp_verified) {
            $rules['email_verification_code'] = 'required|digits:6';
        }

        // Require mobile verification code if OTP was sent but not verified
        if ($this->mobile_otp_sent && !$this->mobile_otp_verified) {
            $rules['mobile_verification_code'] = 'required|digits:6';
        }

        return $rules;
    }

    public function updatedCompanySearch()
    {
        if (strlen($this->company_search) >= 1) {
            $this->allCompanies = Company::where('team_id', $this->teamId)
                ->where('status', 'active')
                ->where('company_name', 'like', '%' . $this->company_search . '%')
                ->select('id', 'company_name')
                ->orderBy('company_name')
                ->limit(20)
                ->get();
            $this->showCompanyDropdown = true;
        } elseif (empty($this->company_search)) {
            $this->allCompanies = [];
            $this->showCompanyDropdown = false;
            if ($this->company_id) {
                // Load selected company name
                $company = Company::find($this->company_id);
                $this->company_search = $company ? $company->company_name : '';
            }
        }
    }

    public function selectCompany($companyId, $companyName)
    {
        $this->company_id = $companyId;
        $this->company_search = $companyName;
        $this->showCompanyDropdown = false;
    }

    public function clearCompany()
    {
        $this->company_id = null;
        $this->company_search = '';
        $this->showCompanyDropdown = false;
        $this->allCompanies = [];
    }

    public function updatedIdentificationType()
    {
        // Clear the other field when switching identification type
        if ($this->identification_type === 'NRIC / FIN') {
            $this->passport = '';
        } else {
            $this->nric_fin = '';
        }
    }

    public function updatedSalutation($value)
    {
        // Automatically change gender based on salutation
        if ($value === 'Mr' || $value === 'Dr') {
            $this->gender = 'Male';
        } elseif ($value === 'Ms' || $value === 'Mrs') {
            $this->gender = 'Female';
        }
    }

    public function updatedMobileNumber($value)
    {
        // Reset mobile verification when mobile number changes
        $this->mobile_otp_sent = false;
        $this->mobile_otp_verified = false;
        $this->mobile_verification_code = '';
        $this->mobile_otp_expires_at = null;
        
        // Clear any existing OTP from session
        Session::forget(['mobile_verification_otp', 'mobile_verification_otp_expires', 'mobile_verification_number']);
        
        // Check if mobile number already exists
        if ($value && $this->mobile_country_code) {
            $existingMember = Member::where('team_id', $this->teamId)
                ->where('mobile_country_code', $this->mobile_country_code)
                ->where('mobile_number', $value)
                ->first();
            
            if ($existingMember) {
                $this->dispatch('swal:mobile-exists');
                $this->addError('mobile_number', 'This mobile number already exists.');
                return;
            }
            
            // If mobile number is valid (at least 8 digits), send OTP
            if (strlen($value) >= 8) {
                $this->sendMobileVerificationCode();
            }
        }
    }

    public function updatedMobileCountryCode($value)
    {
        // Show country field when country code is selected
        $this->showCountryField = true;
        $this->showNationalityField = true;
        
        // Find and auto-select country based on phonecode
        $country = Country::where('phonecode', $value)->first();
        if ($country) {
            $this->country_id = $country->id;
            $this->updateNationalityFromCountry();
        } else {
            // If no exact match, try to find the first country with this phonecode
            $countries = Country::where('phonecode', $value)->get();
            if ($countries->count() > 0) {
                $this->country_id = $countries->first()->id;
                $this->updateNationalityFromCountry();
            } else {
                $this->country_id = null;
            }
        }
    }

    public function updatedCountryId($value)
    {
        if ($value) {
            $this->updateNationalityFromCountry();
        }
    }

    protected function updateNationalityFromCountry()
    {
        if ($this->country_id) {
            $country = Country::find($this->country_id);
            if ($country && $country->name) {
                // Check if the country name exists in nationalities config
                $nationalities = config('nationalities', []);
                if (in_array($country->name, $nationalities)) {
                    $this->nationality = $country->name;
                } elseif (in_array($country->name . 'an', $nationalities)) {
                    // Try with 'an' suffix (e.g., Singapore -> Singaporean)
                    $this->nationality = $country->name . 'an';
                } elseif (in_array($country->name . 'ian', $nationalities)) {
                    // Try with 'ian' suffix
                    $this->nationality = $country->name . 'ian';
                }
            }
        }
    }

    public function updatedEmail($value)
    {
        // Reset email verification when email changes
        $this->email_otp_sent = false;
        $this->email_otp_verified = false;
        $this->email_verification_code = '';
        $this->email_otp_expires_at = null;
        
        // Clear any existing OTP from session
        Session::forget(['email_verification_otp', 'email_verification_otp_expires', 'email_verification_email']);
        
        // Check if email already exists
        if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $existingMember = Member::where('team_id', $this->teamId)
                ->where('email', $value)
                ->first();
            
            if ($existingMember) {
                $this->dispatch('swal:email-exists');
                $this->addError('email', 'This email already exists.');
                return;
            }
        }
        
        // If confirm email is already set and matches, send OTP
        if ($this->confirm_email && $this->confirm_email === $value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->sendEmailVerificationCode();
        }
    }

    public function updatedConfirmEmail($value)
    {
        // Reset email verification when confirm email changes
        $this->email_otp_sent = false;
        $this->email_otp_verified = false;
        $this->email_verification_code = '';
        $this->email_otp_expires_at = null;
        
        // Clear any existing OTP from session
        Session::forget(['email_verification_otp', 'email_verification_otp_expires', 'email_verification_email']);
        
        // Check if email already exists before sending OTP
        if ($value && $this->email && $value === $this->email && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $existingMember = Member::where('team_id', $this->teamId)
                ->where('email', $value)
                ->first();
            
            if ($existingMember) {
                $this->dispatch('swal:email-exists');
                $this->addError('email', 'This email already exists.');
                $this->addError('confirm_email', 'This email already exists.');
                return;
            }
            
            // Only send OTP if email doesn't exist
            $this->sendEmailVerificationCode();
        }
    }

    public function sendEmailVerificationCode()
    {
        if (!$this->email || !$this->confirm_email || $this->email !== $this->confirm_email) {
            $this->addError('confirm_email', 'Email addresses must match.');
            return;
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Please enter a valid email address.');
            $this->addError('confirm_email', 'Please enter a valid email address.');
            return;
        }

        // Check if email already exists - don't send verification code if it exists
        $existingMember = Member::where('team_id', $this->teamId)
            ->where('email', $this->email)
            ->first();
        
        if ($existingMember) {
            $this->dispatch('swal:email-exists');
            $this->addError('email', 'This email already exists.');
            $this->addError('confirm_email', 'This email already exists.');
            return;
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        
        // Store OTP in session with expiration (5 minutes)
        Session::put('email_verification_otp', $otp);
        Session::put('email_verification_otp_expires', now()->addMinutes(5));
        Session::put('email_verification_email', $this->email);
        
        $this->email_otp_sent = true;
        $this->email_otp_verified = false;
        $this->email_otp_expires_at = now()->addMinutes(5);
        $this->email_otp_countdown = 300; // 5 minutes in seconds
        
        // Send email with OTP
        try {
            $smtpDetails = SmtpDetails::where('team_id', $this->teamId)->first();
            
            if ($smtpDetails && !empty($smtpDetails->hostname)) {
                // Configure mail settings
                Config::set('mail.mailers.smtp.transport', 'smtp');
                Config::set('mail.mailers.smtp.host', trim($smtpDetails->hostname));
                Config::set('mail.mailers.smtp.port', trim($smtpDetails->port));
                Config::set('mail.mailers.smtp.encryption', trim($smtpDetails->encryption ?? 'ssl'));
                Config::set('mail.mailers.smtp.username', trim($smtpDetails->username));
                Config::set('mail.mailers.smtp.password', trim($smtpDetails->password));
                Config::set('mail.from.address', trim($smtpDetails->from_email));
                Config::set('mail.from.name', trim($smtpDetails->from_name));
                
                // Send OTP email
                Mail::to($this->email)->send(new \App\Mail\SendOtp($otp, $this->teamId));
                
                session()->flash('message', 'Verification code sent to your email address.');
                // Clear any previous errors
                $this->resetErrorBag('email');
                $this->resetErrorBag('confirm_email');
            } else {
                Log::warning('SMTP details not configured for team: ' . $this->teamId);
                $this->addError('confirm_email', 'Unable to send verification code. Please contact support.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email verification code: ' . $e->getMessage());
            $this->addError('confirm_email', 'Failed to send verification code. Please try again.');
        }
    }

    public function updateCountdown()
    {
        if ($this->email_otp_sent && $this->email_otp_expires_at) {
            $remaining = now()->diffInSeconds($this->email_otp_expires_at);
            if ($remaining > 0) {
                $this->email_otp_countdown = $remaining;
            } else {
                $this->email_otp_countdown = 0;
                $this->email_otp_sent = false;
            }
        }
        
        if ($this->mobile_otp_sent && $this->mobile_otp_expires_at) {
            $remaining = now()->diffInSeconds($this->mobile_otp_expires_at);
            if ($remaining > 0) {
                $this->mobile_otp_countdown = $remaining;
            } else {
                $this->mobile_otp_countdown = 0;
                $this->mobile_otp_sent = false;
            }
        }
    }

    public function verifyEmailCode()
    {
        $this->validate([
            'email_verification_code' => 'required|digits:6',
        ]);

        $storedOtp = Session::get('email_verification_otp');
        $storedEmail = Session::get('email_verification_email');
        $expiresAt = Session::get('email_verification_otp_expires');

        if (!$storedOtp || $storedEmail !== $this->email) {
            $this->addError('email_verification_code', 'Invalid verification code.');
            return;
        }

        if (now()->greaterThan($expiresAt)) {
            $this->addError('email_verification_code', 'Verification code has expired. Please request a new one.');
            $this->email_otp_sent = false;
            return;
        }

        if ($this->email_verification_code == $storedOtp) {
            $this->email_otp_verified = true;
            Session::forget(['email_verification_otp', 'email_verification_otp_expires', 'email_verification_email']);
            session()->flash('message', 'Email verified successfully.');
        } else {
            $this->addError('email_verification_code', 'Invalid verification code.');
        }
    }

    public function resendEmailVerificationCode()
    {
        $this->sendEmailVerificationCode();
    }

    public function sendMobileVerificationCode()
    {
        if (!$this->mobile_number || !$this->mobile_country_code) {
            $this->addError('mobile_number', 'Please enter a valid mobile number.');
            return;
        }

        // Check if mobile number already exists - don't send verification code if it exists
        $existingMember = Member::where('team_id', $this->teamId)
            ->where('mobile_country_code', $this->mobile_country_code)
            ->where('mobile_number', $this->mobile_number)
            ->first();
        
        if ($existingMember) {
            $this->dispatch('swal:mobile-exists');
            $this->addError('mobile_number', 'This mobile number already exists.');
            return;
        }

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        
        // Store OTP in session with expiration (5 minutes)
        Session::put('mobile_verification_otp', $otp);
        Session::put('mobile_verification_otp_expires', now()->addMinutes(5));
        Session::put('mobile_verification_number', $this->mobile_number);
        
        $this->mobile_otp_sent = true;
        $this->mobile_otp_verified = false;
        $this->mobile_otp_expires_at = now()->addMinutes(5);
        $this->mobile_otp_countdown = 300; // 5 minutes in seconds
        
        // Send SMS with OTP
        try {
            $smsService = new SmsAPI();
            $fullMobileNumber = $this->mobile_country_code . $this->mobile_number;
            $message = "Your verification code is: {$otp}. Valid for 5 minutes.";

            $data = [
                'phone_code' => $this->mobile_country_code,
                'phone' => $this->mobile_number,
                'message' => $message,
            ];

            $logData = [
                'team_id' => $this->teamId,
                'contact' => $fullMobileNumber,
                'type' => MessageDetail::TRIGGERED_TYPE,
                'event_name' => 'OTP Verification',
                'message' => $message,
            ];


            $result = SmsAPI::sendSms($this->teamId, $data, null, null, $logData, $message);
            
            if ($result) {
                session()->flash('message', 'Verification code sent to your mobile number.');
                // Clear any previous errors
                $this->resetErrorBag('mobile_number');
            } else {
                Log::warning('Failed to send SMS OTP for team: ' . $this->teamId);
                $this->addError('mobile_number', 'Unable to send verification code. Please check SMS configuration.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send mobile verification code: ' . $e->getMessage());
            $this->addError('mobile_number', 'Failed to send verification code. Please try again.');
        }
    }

    public function verifyMobileCode()
    {
        $this->validate([
            'mobile_verification_code' => 'required|digits:6',
        ]);

        $storedOtp = Session::get('mobile_verification_otp');
        $storedNumber = Session::get('mobile_verification_number');
        $expiresAt = Session::get('mobile_verification_otp_expires');

        if (!$storedOtp || $storedNumber !== $this->mobile_number) {
            $this->addError('mobile_verification_code', 'Invalid verification code.');
            return;
        }

        if (now()->greaterThan($expiresAt)) {
            $this->addError('mobile_verification_code', 'Verification code has expired. Please request a new one.');
            $this->mobile_otp_sent = false;
            return;
        }

        if ($this->mobile_verification_code == $storedOtp) {
            $this->mobile_otp_verified = true;
            Session::forget(['mobile_verification_otp', 'mobile_verification_otp_expires', 'mobile_verification_number']);
            session()->flash('message', 'Mobile number verified successfully.');
        } else {
            $this->addError('mobile_verification_code', 'Invalid verification code.');
        }
    }

    public function resendMobileVerificationCode()
    {
        $this->sendMobileVerificationCode();
    }

    public function register()
    {
        // If country field is not shown yet, show it and auto-select country based on phone code
        if (!$this->showCountryField && $this->mobile_country_code) {
            $this->showCountryField = true;
            $this->showNationalityField = true;
            
            // Auto-select country based on phonecode
            $country = Country::where('phonecode', $this->mobile_country_code)->first();
            if ($country) {
                $this->country_id = $country->id;
                $this->updateNationalityFromCountry();
            }
        }

        // Check if email OTP was sent but not verified yet
        if ($this->email_otp_sent && !$this->email_otp_verified) {
            $this->addError('email_verification_code', 'Please verify your email address before submitting.');
            $this->validate(); // This will show all errors including the OTP error
            return;
        }

        // Check if mobile OTP was sent but not verified yet
        if ($this->mobile_otp_sent && !$this->mobile_otp_verified) {
            $this->addError('mobile_verification_code', 'Please verify your mobile number before submitting.');
            $this->validate(); // This will show all errors including the OTP error
            return;
        }

        // Validate all fields
        $this->validate();

        try {
            // Generate a random password for the patient
            $password = $this->generateRandomPassword();

            $data = [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'identification_type' => $this->identification_type === 'NRIC / FIN' ? 'NRIC' : 'Passport',
                'nric_fin' => $this->identification_type === 'NRIC / FIN' ? $this->nric_fin : null,
                'passport' => $this->identification_type === 'Passport' ? $this->passport : null,
                'salutation' => $this->salutation,
                'full_name' => $this->full_name,
                'date_of_birth' => $this->date_of_birth,
                'gender' => $this->gender,
                'mobile_country_code' => $this->mobile_country_code,
                'mobile_number' => $this->mobile_number,
                'email' => $this->email,
                'country_id' => $this->country_id,
                'nationality' => $this->nationality,
                'company_id' => $this->company_id,
                'password' => $password,
                'status' => 'active', // New registrations are inactive until approved
                'is_active' => 1, // Requires admin approval
                'consent_data_collection' => $this->consent_data_collection,
                'consent_marketing' => $this->consent_marketing,
            ];

            $member = Member::create($data);

            // Send registration success email with password
            $this->sendRegistrationEmail($data, $password);

            // Redirect to login page with success message
            return redirect()->route('tenant.patient.login')->with('success', 'Registration successful! Your account is pending approval. An email with your login credentials has been sent to your email address.');
        } catch (\Exception $e) {
            Log::error('Patient registration error: ' . $e->getMessage());
            session()->flash('error', 'Registration failed. Please try again or contact support.');
        }
    }

    /**
     * Generate a random password
     * 
     * @return string
     */
    protected function generateRandomPassword($length = 8)
    {
        // Generate a secure random password with uppercase, lowercase, numbers, and special characters
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%&*';

        // Ensure at least one character from each set
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill the rest randomly
        $allCharacters = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        // Shuffle the password to randomize character positions
        return str_shuffle($password);
    }

    /**
     * Send registration success email with password
     * 
     * @param array $data
     * @param string $plainPassword
     * @return void
     */
    protected function sendRegistrationEmail($data, $plainPassword)
    {
        try {
            // Get SMTP details for the team
            $smtpDetails = SmtpDetails::where('team_id', $this->teamId)->first();

            if (!$smtpDetails || empty($smtpDetails->hostname)) {
                Log::warning('SMTP details not configured for team: ' . $this->teamId);
                return;
            }

            // Configure mail settings
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($smtpDetails->hostname));
            Config::set('mail.mailers.smtp.port', trim($smtpDetails->port));
            Config::set('mail.mailers.smtp.encryption', trim($smtpDetails->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($smtpDetails->username));
            Config::set('mail.mailers.smtp.password', trim($smtpDetails->password));
            Config::set('mail.from.address', trim($smtpDetails->from_email));
            Config::set('mail.from.name', trim($smtpDetails->from_name));

            // Get company name if exists
            $companyName = null;
            if (!empty($data['company_id'])) {
                $company = Company::find($data['company_id']);
                $companyName = $company ? $company->company_name : null;
            }

            // Prepare email data
            $emailData = [
                'salutation' => $data['salutation'],
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'mobile_country_code' => $data['mobile_country_code'],
                'mobile_number' => $data['mobile_number'],
                'password' => $plainPassword,
                'company_name' => $companyName,
            ];

            // Render email template
            $templateContent = view('emails.patient-registration-success', ['data' => $emailData])->render();
            $subject = 'Registration Successful - Your Account Credentials';

            // Send email
            Mail::html($templateContent, function ($message) use ($data, $subject, $smtpDetails) {
                $message->from($smtpDetails->from_email, $smtpDetails->from_name);
                $message->to($data['email'])->subject($subject);
            });

            Log::info('Registration email sent successfully to: ' . $data['email']);
        } catch (\Exception $e) {
            Log::error('Failed to send registration email: ' . $e->getMessage());
            // Don't throw exception - registration should still succeed even if email fails
        }
    }

    public function close()
    {
        return redirect()->route('tenant.patient.login');
    }

    public function render()
    {
        // Get phone codes from countries table
        $phoneCodeCountries = Country::select('name', 'phonecode')
            ->whereNotNull('phonecode')
            ->where('phonecode', '!=', '')
            ->orderBy('name')
            ->get();

        // Get identification types
        $identificationTypes = ['NRIC / FIN', 'Passport'];

        // Get salutation options
        $salutations = ['Mr', 'Mrs', 'Ms', 'Dr'];

        // Get gender options
        $genders = ['Male', 'Female'];

        // Get nationalities from config file
        $nationalities = config('nationalities');

        // Get first location for the team
        $firstLocation = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->orderBy('id')
            ->first();

        $locationId = $firstLocation ? $firstLocation->id : null;

        // Get logo based on team id and first location
        $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId ?? null, $locationId);

        return view('livewire.patient-register', [
            'phoneCodeCountries' => $phoneCodeCountries,
            'identificationTypes' => $identificationTypes,
            'salutations' => $salutations,
            'genders' => $genders,
            'nationalities' => $nationalities,
            'logo' => $logo,
        ]);
    }
}
