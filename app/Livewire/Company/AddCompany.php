<?php

namespace App\Livewire\Company;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AddCompany extends Component
{
    public array $company = [
        'status' => 'active',
        'ehs_appointments_per_year' => 1,
    ];

    protected array $messages = [
        'company.company_name.required' => 'The company name field is required.',
        'company.address.required' => 'The address field is required.',
        'company.billing_address.required' => 'The billing address field is required.',
        'company.ehs_appointments_per_year.required' => 'The EHS appointments per year field is required.',
        'company.ehs_appointments_per_year.min' => 'The EHS appointments per year must be at least 1.',
        'company.contact_person1_name.required' => 'The primary contact name field is required.',
    ];

    protected function rules(): array
    {
        return [
            'company.company_name' => ['required', 'string', 'max:255'],
            'company.address' => ['required', 'string'],
            'company.billing_address' => ['required', 'string'],
            'company.ehs_appointments_per_year' => ['required', 'integer', 'min:1'],
            'company.contact_person1_name' => ['required', 'string', 'max:255'],
            'company.contact_person1_phone' => ['nullable', 'string', 'max:30'],
            'company.contact_person1_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function clear(): void
    {
        $this->company = [
            'status' => 'active',
            'ehs_appointments_per_year' => 1,
        ];
    }

    public function save(): void
    {
        $this->validate();

        $teamId = tenant('id') ?? (Auth::user()->team_id ?? null);
        $locationId = Session::get('selectedLocation') ?? (Auth::user()->location_id ?? null);

        Company::create([
            'team_id' => $teamId,
            'location_id' => $locationId,
            'company_name' => $this->company['company_name'] ?? null,
            'address' => $this->company['address'] ?? null,
            'billing_address' => $this->company['billing_address'] ?? null,
            'is_billing_same_as_company' => (bool)($this->company['is_billing_same_as_company'] ?? false),
            'remarks' => $this->company['remarks'] ?? null,
            'account_manager_id' => Auth::id(),
            'status' => $this->company['status'] ?? 'active',
            'ehs_appointments_per_year' => $this->company['ehs_appointments_per_year'] ?? 1,
            'contact_person1_name' => $this->company['contact_person1_name'] ?? null,
            'contact_person1_phone' => $this->company['contact_person1_phone'] ?? null,
            'contact_person1_email' => $this->company['contact_person1_email'] ?? null,
            'contact_person2_name' => $this->company['contact_person2_name'] ?? null,
            'contact_person2_phone' => $this->company['contact_person2_phone'] ?? null,
            'contact_person2_email' => $this->company['contact_person2_email'] ?? null,
        ]);

        session()->flash('message', 'Company created successfully.');

        $this->redirectRoute('tenant.companies.index');
    }

    public function render()
    {
        return view('livewire.company.add-company');
    }
}
