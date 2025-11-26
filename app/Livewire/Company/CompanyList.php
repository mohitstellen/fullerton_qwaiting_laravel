<?php

namespace App\Livewire\Company;

use App\Models\Company;
use Livewire\Attributes\On;
use Livewire\Component;

class CompanyList extends Component
{
    public function confirmDelete(int $companyId): void
    {
        $this->dispatch('confirm-company-delete', companyId: $companyId);
    }

    #[On('delete-company-confirmed')]
    public function deleteCompany(int $companyId): void
    {
        Company::whereKey($companyId)->delete();
        session()->flash('message', 'Company deleted successfully.');
    }

    public function render()
    {
        $companies = Company::orderBy('company_name')->get();

        return view('livewire.company.company-list', compact('companies'));
    }
}
