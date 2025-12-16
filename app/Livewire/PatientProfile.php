<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Member;
use App\Models\Company;
use Carbon\Carbon;

#[Layout('components.layouts.patient-layout')]
#[Title('Profile')]
class PatientProfile extends Component
{
    public $member;
    public $teamId;
    
    // Form fields
    public $identification_type;
    public $nric_fin;
    public $passport;
    public $salutation;
    public $full_name;
    public $date_of_birth;
    public $gender;
    public $mobile_country_code;
    public $mobile_number;
    public $email;
    public $nationality;
    public $company_id;
    public $company_search = '';
    public $showCompanyDropdown = false;
    public $allCompanies = [];
    public $isCorporateCustomer = false;
    
    // Options
    public $identificationTypes = ['NRIC / FIN', 'Passport'];
    public $salutations = ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof'];
    public $genders = ['Male', 'Female', 'Other'];
    public $nationalities = [];
    public $companies = [];
    public $countryCodes = [];
    
    public function mount()
    {
        // Check if patient is logged in
        if (!Session::has('patient_member_id')) {
            $this->redirect(route('tenant.patient.login'), navigate: true);
            return;
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
            $this->redirect(route('tenant.patient.login'), navigate: true);
            return;
        }
        
        // Determine if member is corporate customer
        // Check session first, then member's customer_type, then company_id
        $sessionCustomerType = Session::get('patient_customer_type');
        $this->isCorporateCustomer = $sessionCustomerType === 'Corporate' 
            || $this->member->customer_type === 'Corporate' 
            || !empty($this->member->company_id);
        
        // Update session if not set or inconsistent
        if (!$sessionCustomerType || $sessionCustomerType !== ($this->isCorporateCustomer ? 'Corporate' : 'Private')) {
            Session::put('patient_customer_type', $this->isCorporateCustomer ? 'Corporate' : 'Private');
        }
        
        // Load nationalities from config file
        $this->nationalities = config('nationalities', []);
        
        // Load companies for dropdown (always load, but only show for corporate)
        $this->companies = Company::where('team_id', $this->teamId)
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get();
        
        // Load country codes (common ones)
        $this->countryCodes = [
            '+65' => 'Singapore (+65)',
            '+60' => 'Malaysia (+60)',
            '+91' => 'India (+91)',
            '+86' => 'China (+86)',
            '+63' => 'Philippines (+63)',
            '+62' => 'Indonesia (+62)',
            '+66' => 'Thailand (+66)',
            '+84' => 'Vietnam (+84)',
            '+44' => 'UK (+44)',
            '+1' => 'USA/Canada (+1)',
            '+61' => 'Australia (+61)',
        ];
        
        // Populate form fields
        // Normalize identification_type to match the expected format
        $storedType = trim($this->member->identification_type ?? 'NRIC / FIN');
        if (stripos($storedType, 'nric') !== false || stripos($storedType, 'fin') !== false) {
            $this->identification_type = 'NRIC / FIN';
        } else {
            $this->identification_type = 'Passport';
        }
        $this->nric_fin = $this->member->nric_fin ?? '';
        $this->passport = $this->member->passport ?? '';
        $this->salutation = $this->member->salutation ?? 'Mr';
        $this->full_name = $this->member->full_name ?? '';
        $this->date_of_birth = $this->member->date_of_birth 
            ? Carbon::parse($this->member->date_of_birth)->format('Y-m-d') 
            : '';
        $this->gender = $this->member->gender ?? 'Male';
        $this->mobile_country_code = $this->member->mobile_country_code ?? '+65';
        $this->mobile_number = $this->member->mobile_number ?? '';
        $this->email = $this->member->email ?? '';
        $this->nationality = $this->member->nationality ?? 'Singaporean';
        $this->company_id = $this->member->company_id ?? null;
        
        // Load selected company name for search field
        if ($this->company_id) {
            $company = Company::find($this->company_id);
            $this->company_search = $company ? $company->company_name : '';
        }
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
    
    public function updateProfile()
    {
        // Validate editable fields
        $rules = [
            'salutation' => 'required|in:Mr,Mrs,Ms,Dr,Prof',
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'email' => 'required|email|max:255',
            'nationality' => 'required|string|max:100',
        ];
        
        $messages = [
            'full_name.required' => 'Full name is required.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.date' => 'Please enter a valid date.',
            'gender.required' => 'Gender is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'nationality.required' => 'Nationality is required.',
        ];
        
        // Add company validation for corporate customers
        if ($this->isCorporateCustomer) {
            $rules['company_id'] = 'required|exists:companies,id';
            $messages['company_id.required'] = 'Company selection is required.';
            $messages['company_id.exists'] = 'Please select a valid company from the dropdown.';
        }
        
        $this->validate($rules, $messages);
        
        try {
            // Date is already in Y-m-d format from the date input
            $dateOfBirth = $this->date_of_birth;
            
            // Update member - use existing values for disabled fields
            $updateData = [
                // Keep disabled fields from existing member data
                'identification_type' => $this->member->identification_type,
                'nric_fin' => $this->member->nric_fin,
                'passport' => $this->member->passport,
                'mobile_country_code' => $this->member->mobile_country_code,
                'mobile_number' => $this->member->mobile_number,
                
                // Update editable fields
                'salutation' => $this->salutation,
                'full_name' => $this->full_name,
                'date_of_birth' => $dateOfBirth,
                'gender' => $this->gender,
                'email' => $this->email,
                'nationality' => $this->nationality,
            ];
            
            // Update company_id if it's a corporate customer
            if ($this->isCorporateCustomer) {
                $updateData['company_id'] = $this->company_id;
            }
            
            $this->member->update($updateData);
            
            // Refresh member data
            $this->member->refresh();
            
            session()->flash('profile_success', 'Profile updated successfully!');
            
        } catch (\Exception $e) {
            session()->flash('profile_error', 'Failed to update profile. Please try again.');
            Log::error('Profile update error: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.patient-profile');
    }
}
