<?php

namespace App\Livewire\Company;

use App\Models\Company;
use Livewire\Component;

class CompanyList extends Component
{
    public function delete(int $companyId): void
    {
        Company::whereKey($companyId)->delete();
        session()->flash('message', 'Company deleted successfully.');
    }

    public function render()
    {
        $companies = Company::orderBy('company_name')->get();

        return view('livewire.company-list', compact('companies'));
    }
}
