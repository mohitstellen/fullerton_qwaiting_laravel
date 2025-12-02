<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\Company;
use App\Models\Country;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Traits\SendsEmails;

class PublicUserForm extends Component
{
    use SendsEmails;

    public $memberId = null;
    public $teamId;
    public $locationId;

    // Form fields
    public $identification_type = '';
    public $nric_fin = '';
    public $salutation = '';
    public $full_name = '';
    public $date_of_birth = '';
    public $gender = '';
    public $mobile_country_code = '65';
    public $mobile_number = '';
    public $email = '';
    public $status = 'active';
    public $nationality = '';
    public $company_id = '';
    public $company_search = '';
    public $password = '';

    public $editing = false;

    protected $messages = [
        'identification_type.required' => 'Identification Type is required.',
        'nric_fin.required' => 'NRIC / FIN is required.',
        'full_name.required' => 'Full Name is required.',
        'date_of_birth.required' => 'Date Of Birth is required.',
        'gender.required' => 'Gender is required.',
        'mobile_number.required' => 'Mobile Number is required.',
        'email.required' => 'Email Address is required.',
        'nationality.required' => 'Nationality is required.',
        'company_id.required' => 'Company is required.',
        'email.email' => 'Please enter a valid email address.',
        'nric_fin.unique' => 'This NRIC / FIN already exists.',
        'email.unique' => 'This email already exists.',
        'mobile_number.unique' => 'This mobile number already exists.',
    ];

    public function mount($memberId = null)
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        if ($memberId) {
            $this->memberId = $memberId;
            $this->editing = true;
            $this->loadMember();
        }
    }

    public function loadMember()
    {
        $member = Member::where('team_id', $this->teamId)
            ->findOrFail($this->memberId);

        $this->identification_type = $member->identification_type ?? '';
        $this->nric_fin = $member->nric_fin ?? '';
        $this->salutation = $member->salutation ?? '';
        $this->full_name = $member->full_name ?? '';
        $this->date_of_birth = $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : '';
        $this->gender = $member->gender ?? '';
        $this->mobile_country_code = $member->mobile_country_code ?? '65';
        $this->mobile_number = $member->mobile_number ?? '';
        $this->email = $member->email ?? '';
        $this->status = $member->status ?? 'active';
        $this->nationality = $member->nationality ?? '';
        $this->company_id = $member->company_id ?? '';
    }

    protected function rules()
    {
        $rules = [
            'identification_type' => 'required',
            'nric_fin' => 'required|string',
            'salutation' => 'nullable|string|in:Mr,Mrs,Ms,Dr',
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'mobile_country_code' => 'required|string',
            'mobile_number' => 'required|string',
            'email' => 'required|email',
            'status' => 'required|in:active,inactive',
            'nationality' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'password' => 'nullable|string|min:6',
        ];

        // Add unique validation rules
        if ($this->editing) {
            $rules['nric_fin'] .= '|unique:members,nric_fin,' . $this->memberId . ',id,team_id,' . $this->teamId;
            $rules['mobile_number'] .= '|unique:members,mobile_number,' . $this->memberId . ',id,team_id,' . $this->teamId;
            $rules['email'] .= '|unique:members,email,' . $this->memberId . ',id,team_id,' . $this->teamId;
        } else {
            $rules['nric_fin'] .= '|unique:members,nric_fin,NULL,id,team_id,' . $this->teamId;
            $rules['mobile_number'] .= '|unique:members,mobile_number,NULL,id,team_id,' . $this->teamId;
            $rules['email'] .= '|unique:members,email,NULL,id,team_id,' . $this->teamId;
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $isAdmin = $user->is_admin == 1;
        
        // Generate random password for new members
        $generatedPassword = null;
        if (!$this->editing) {
            $generatedPassword = $this->generateRandomPassword();
        }

        $data = [
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'identification_type' => $this->identification_type,
            'nric_fin' => $this->nric_fin,
            'salutation' => $this->salutation,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'mobile_country_code' => $this->mobile_country_code,
            'mobile_number' => $this->mobile_number,
            'email' => $this->email,
            'status' => $this->status,
            'nationality' => $this->nationality,
            'company_id' => $this->company_id,
            'created_by' => $user->id,
        ];

        // Set password - use generated password for new members or provided password for updates
        if (!$this->editing && $generatedPassword) {
            $data['password'] = $generatedPassword;
        } elseif (!empty($this->password)) {
            $data['password'] = $this->password;
        }

        // Admin approval logic
        if ($isAdmin) {
            // Admin adding/editing - active by default
            $data['is_active'] = 1;
            $data['status'] = 'active';
            $data['approved_by'] = $user->id;
            $data['approved_at'] = now();
        } else {
            // Non-admin adding - requires approval
            if (!$this->editing) {
                $data['is_active'] = 0;
                $data['status'] = 'inactive';
            }
        }

        if ($this->editing) {
            // Don't update created_by when editing
            unset($data['created_by']);

            $member = Member::where('team_id', $this->teamId)
                ->findOrFail($this->memberId);

            // Only update approval if admin is editing and status is changing to active
            if ($isAdmin && $this->status === 'active' && !$member->is_active) {
                $data['approved_by'] = $user->id;
                $data['approved_at'] = now();
                $data['is_active'] = 1;
            }

            $member->update($data);
            session()->flash('message', 'Member updated successfully.');
        } else {
            $member = Member::create($data);
            
            // Send welcome email with credentials for new members
            if ($generatedPassword) {
                $this->sendWelcomeEmail($member, $generatedPassword);
            }
            
            session()->flash('message', $isAdmin
                ? 'Member created and activated successfully. Welcome email sent with login credentials.'
                : 'Member created successfully. Welcome email sent. Waiting for admin approval.');
        }

        return $this->redirectRoute('tenant.public-user.index');
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
     * Send welcome email with credentials
     * 
     * @param Member $member
     * @param string $password
     * @return void
     */
    protected function sendWelcomeEmail($member, $password)
    {
        try {
            $company = Company::find($member->company_id);
            
            $emailData = [
                'to_mail' => $member->email,
                'member_name' => $member->full_name,
                'member_email' => $member->email,
                'member_mobile' => $member->full_mobile,
                'login_id' => $member->full_mobile,
                'password' => $password,
                'company_name' => $company ? $company->company_name : 'N/A',
            ];

            // Send email using the trait
            $this->sendEmail($emailData, 'Welcome - Your Account Credentials', 'member-welcome', $this->teamId);
        } catch (\Exception $e) {
            // Log error but don't stop the process
            Log::error('Failed to send welcome email to ' . $member->email . ': ' . $e->getMessage());
        }
    }

    public function sendEmailToMember()
    {
        if (!$this->memberId) {
            session()->flash('error', 'Member ID is required.');
            return;
        }

        $member = Member::findOrFail($this->memberId);

        if (!$member->email) {
            session()->flash('error', 'Member does not have an email address.');
            return;
        }

        // Prepare email data
        $emailData = [
            'to_mail' => $member->email,
            'member_name' => $member->full_name,
            'member_email' => $member->email,
            'member_mobile' => $member->full_mobile,
        ];

        // Send email using the trait
        $this->sendEmail($emailData, 'Member Information', 'member-info', $this->teamId);

        session()->flash('message', 'Email sent successfully to ' . $member->email);
    }

    public function render()
    {
        $companies = Company::where('team_id', $this->teamId)
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get();

        // Get phone codes from countries table
        $phoneCodeCountries = Country::select('name', 'phonecode')
            ->whereNotNull('phonecode')
            ->where('phonecode', '!=', '')
            ->orderBy('name')
            ->get();

        // Get identification types
        $identificationTypes = ['NRIC', 'FIN', 'Passport'];

        // Get salutation options
        $salutations = ['Mr', 'Mrs', 'Ms', 'Dr'];

        // Get gender options
        $genders = ['Male', 'Female'];

        // Get nationalities from config file
        $nationalities = config('nationalities');

        $title = $this->editing ? 'Edit User' : 'Add User';

        return view('livewire.public-user-form', [
            'companies' => $companies,
            'nationalities' => $nationalities,
            'phoneCodeCountries' => $phoneCodeCountries,
            'identificationTypes' => $identificationTypes,
            'salutations' => $salutations,
            'genders' => $genders,
        ])->title($title);
    }
}
