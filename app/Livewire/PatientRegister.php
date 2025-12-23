<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\Company;
use App\Models\Country;
use App\Models\SiteDetail;
use App\Models\SmtpDetails;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

#[Layout('components.layouts.custom-booking-layout')]
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
    public $company_id = null;
    public $company_search = '';
    public $showCompanyDropdown = false;
    public $allCompanies = [];
    
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
        'email.unique' => 'This email already exists.',
        'confirm_email.required' => 'Please confirm your email address.',
        'confirm_email.same' => 'Email addresses do not match.',
        'nationality.required' => 'Nationality is required.',
        'company_id.required' => 'Company is required. Please select from the dropdown list.',
        'consent_data_collection.required' => 'You must consent to data collection.',
        'nric_fin.unique' => 'This NRIC / FIN already exists.',
        'passport.unique' => 'This passport number already exists.',
        'mobile_number.unique' => 'This mobile number already exists.',
    ];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
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
            'email' => 'required|email|unique:members,email,NULL,id,team_id,' . $this->teamId,
            'confirm_email' => 'required|email|same:email',
            'nationality' => 'required|string',
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

    public function register()
    {
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
                'nationality' => $this->nationality,
                'company_id' => $this->company_id,
                'password' => $password,
                'status' => 'active', // New registrations are inactive until approved
                'is_active' => 1, // Requires admin approval
            ];

            $member = Member::create($data);

            // Send registration success email with password
            $this->sendRegistrationEmail($data, $password);

            session()->flash('success', 'Registration successful! Your account is pending approval. An email with your login credentials has been sent to your email address.');

            // Reset form
            $this->reset([
                'identification_type', 'nric_fin', 'passport', 'salutation', 
                'full_name', 'date_of_birth', 'gender', 'mobile_number', 
                'email', 'confirm_email', 'nationality', 'company_id', 
                'company_search', 'consent_data_collection', 'consent_marketing'
            ]);
            $this->identification_type = 'NRIC / FIN';
            $this->salutation = 'Mr';
            $this->gender = 'Male';
            $this->mobile_country_code = '65';
            $this->nationality = 'Singaporean';

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

        // Get logo
        $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId ?? null, $this->locationId ?? null);

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
