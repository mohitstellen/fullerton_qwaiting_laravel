<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Member;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

#[Layout('components.layouts.patient-layout')]
#[Title('Dependents')]
class PatientDependents extends Component
{
    public $teamId;
    public $member;
    public $dependents = [];
    
    // Tab state
    public $activeTab = 'new'; // 'new' or 'list'
    
    // Form fields for new/edit dependent
    public $dependentId = null;
    public $identificationType = '';
    public $nricFin = '';
    public $passport = '';
    public $salutation = '';
    public $fullName = '';
    public $dateOfBirth = '';
    public $gender = '';
    public $relationship = '';
    
    // Form validation messages
    public $successMessage = '';
    public $errorMessage = '';
    
    // Relationship options
    public $relationshipOptions = [
        'Father',
        'Mother',
        'Spouse',
        'Son',
        'Daughter',
        'Brother',
        'Sister',
        'Other'
    ];
    
    // Salutation options
    public $salutationOptions = [
        'Mr',
        'Mrs',
        'Ms',
        'Miss',
        'Dr',
        'Prof'
    ];

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

        $this->loadDependents();
    }

    public function loadDependents()
    {
        // Load all dependents for this member (where primary_id = member id)
        $this->dependents = Member::where('team_id', $this->teamId)
            ->where('primary_id', $this->member->id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($dependent) {
                return [
                    'id' => $dependent->id,
                    'identification_type' => $dependent->identification_type,
                    'nric_fin' => $dependent->nric_fin,
                    'passport' => $dependent->passport,
                    'salutation' => $dependent->salutation,
                    'full_name' => $dependent->full_name,
                    'date_of_birth' => $dependent->date_of_birth ? Carbon::parse($dependent->date_of_birth)->format('d/m/Y') : '',
                    'gender' => $dependent->gender,
                    'relationship' => $dependent->relationship,
                    'created_at' => $dependent->created_at,
                ];
            })
            ->toArray();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->dependentId = null;
        $this->identificationType = '';
        $this->nricFin = '';
        $this->passport = '';
        $this->salutation = '';
        $this->fullName = '';
        $this->dateOfBirth = '';
        $this->gender = '';
        $this->relationship = '';
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function editDependent($id)
    {
        $dependent = Member::where('team_id', $this->teamId)
            ->where('id', $id)
            ->where('primary_id', $this->member->id)
            ->first();

        if ($dependent) {
            $this->dependentId = $dependent->id;
            $this->identificationType = $dependent->identification_type ?? '';
            $this->nricFin = $dependent->nric_fin ?? '';
            $this->passport = $dependent->passport ?? '';
            $this->salutation = $dependent->salutation ?? '';
            $this->fullName = $dependent->full_name ?? '';
            $this->dateOfBirth = $dependent->date_of_birth ? Carbon::parse($dependent->date_of_birth)->format('d/m/Y') : '';
            $this->gender = $dependent->gender ?? '';
            $this->relationship = $dependent->relationship ?? '';
            
            $this->activeTab = 'new';
            $this->successMessage = '';
            $this->errorMessage = '';
        }
    }

    public function deleteDependent($id)
    {
        $dependent = Member::where('team_id', $this->teamId)
            ->where('id', $id)
            ->where('primary_id', $this->member->id)
            ->first();

        if ($dependent) {
            $dependent->delete();
            $this->loadDependents();
            $this->successMessage = 'Dependent deleted successfully.';
            $this->errorMessage = '';
        }
    }

    public function saveDependent()
    {
        // Validation rules
        $rules = [
            'identificationType' => 'required|in:NRIC,Passport',
            'salutation' => 'required|string|max:10',
            'fullName' => 'required|string|max:255',
            'dateOfBirth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'relationship' => 'required|string|max:50',
        ];

        // Conditional validation for NRIC/FIN or Passport
        if ($this->identificationType === 'Passport') {
            $rules['passport'] = 'required|string|max:50';
        } else {
            $rules['nricFin'] = 'required|string|max:50';
        }

        // Custom validation messages
        $messages = [
            'identificationType.required' => 'Please select an identification type.',
            'identificationType.in' => 'Invalid identification type selected.',
            'nricFin.required' => 'NRIC / FIN is required.',
            'passport.required' => 'Passport number is required.',
            'salutation.required' => 'Please select a salutation.',
            'fullName.required' => 'Full name is required.',
            'dateOfBirth.required' => 'Date of birth is required.',
            'dateOfBirth.date' => 'Please enter a valid date.',
            'gender.required' => 'Please select a gender.',
            'gender.in' => 'Invalid gender selected.',
            'relationship.required' => 'Please select a relationship.',
        ];

        $this->validate($rules, $messages);

        try {
            // Parse date of birth
            $dateOfBirth = Carbon::createFromFormat('d/m/Y', $this->dateOfBirth)->format('Y-m-d');

            // Check if updating or creating
            if ($this->dependentId) {
                // Update existing dependent
                $dependent = Member::where('team_id', $this->teamId)
                    ->where('id', $this->dependentId)
                    ->where('primary_id', $this->member->id)
                    ->first();

                if (!$dependent) {
                    $this->errorMessage = 'Dependent not found.';
                    return;
                }

                $dependent->update([
                    'identification_type' => $this->identificationType,
                    'nric_fin' => $this->identificationType !== 'Passport' ? $this->nricFin : null,
                    'passport' => $this->identificationType === 'Passport' ? $this->passport : null,
                    'salutation' => $this->salutation,
                    'full_name' => $this->fullName,
                    'date_of_birth' => $dateOfBirth,
                    'gender' => $this->gender,
                    'relationship' => $this->relationship,
                    'customer_type' => $this->member->customer_type, // Inherit from primary member
                ]);

                $this->successMessage = 'Dependent updated successfully.';
            } else {
                // Create new dependent
                // Check if NRIC/FIN or Passport already exists for this team
                $existingMember = null;
                if ($this->identificationType === 'Passport') {
                    $existingMember = Member::where('team_id', $this->teamId)
                        ->where('passport', $this->passport)
                        ->whereNull('deleted_at')
                        ->first();
                } else {
                    $existingMember = Member::where('team_id', $this->teamId)
                        ->where('nric_fin', $this->nricFin)
                        ->whereNull('deleted_at')
                        ->first();
                }

                if ($existingMember) {
                    $this->errorMessage = 'A member with this ' . ($this->identificationType === 'Passport' ? 'passport' : 'NRIC/FIN') . ' already exists.';
                    return;
                }

                Member::create([
                    'team_id' => $this->teamId,
                    'location_id' => $this->member->location_id,
                    'identification_type' => $this->identificationType,
                    'nric_fin' => $this->identificationType !== 'Passport' ? $this->nricFin : null,
                    'passport' => $this->identificationType === 'Passport' ? $this->passport : null,
                    'salutation' => $this->salutation,
                    'full_name' => $this->fullName,
                    'date_of_birth' => $dateOfBirth,
                    'gender' => $this->gender,
                    'relationship' => $this->relationship,
                    'primary_id' => $this->member->id,
                    'customer_type' => $this->member->customer_type, // Inherit from primary member
                    'email' => $this->member->email, // Use primary member's email
                    'mobile_number' => $this->member->mobile_number, // Use primary member's mobile
                    'mobile_country_code' => $this->member->mobile_country_code,
                    'status' => 'active',
                    'is_active' => 1,
                ]);

                $this->successMessage = 'Dependent added successfully.';
            }

            $this->loadDependents();
            $this->resetForm();
            $this->activeTab = 'list';

        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred. Please try again.';
            Log::error('Error saving dependent: ' . $e->getMessage());
        }
    }

    public function clearForm()
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.patient-dependents');
    }
}

