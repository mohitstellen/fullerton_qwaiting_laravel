<?php

namespace App\Livewire\Company;

use App\Models\Company;
use Livewire\Component;

class EditCompany extends Component
{
    public Company $companyModel;

    public array $company = [];

    protected function rules(): array
    {
        return [
            'company.company_name' => ['required', 'string', 'max:255'],
            'company.ehs_appointments_per_year' => ['required', 'integer', 'min:1'],
            'company.address' => ['required', 'string'],
            'company.billing_address' => ['required', 'string'],
            'company.contact_person1_name' => ['required', 'string', 'max:255'],
            'company.contact_person1_phone' => ['required', 'string', 'max:30'],
            'company.contact_person1_email' => ['required', 'email', 'max:255'],
        ];
    }

    public function mount(Company $companyRecord): void
    {
        $this->companyModel = $companyRecord;

        $this->company = $companyRecord->only([
            'company_name',
            'address',
            'billing_address',
            'is_billing_same_as_company',
            'remarks',
            'status',
            'ehs_appointments_per_year',
            'contact_person1_name',
            'contact_person1_phone',
            'contact_person1_email',
            'contact_person2_name',
            'contact_person2_phone',
            'contact_person2_email',
        ]);
    }

    public function update(): void
    {
        $this->validate();

        $this->companyModel->update($this->company);

        session()->flash('message', 'Company updated successfully.');

        $this->redirectRoute('tenant.companies.index');
    }

    public function render()
    {
        return view('livewire.company.edit-company');
    }
}
