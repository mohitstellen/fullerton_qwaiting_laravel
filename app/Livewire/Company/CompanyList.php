<?php

namespace App\Livewire\Company;

use App\Models\Company;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Response;

class CompanyList extends Component
{
    public string $search = '';

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

    public function updatingSearch()
    {
        // Reset pagination when search changes (if pagination is added later)
    }

    public function exportCSV()
    {
        $query = Company::with('accountManager')
            ->when($this->search !== '', function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('company_name', 'like', $searchTerm)
                        ->orWhere('contact_person1_name', 'like', $searchTerm)
                        ->orWhere('contact_person1_email', 'like', $searchTerm)
                        ->orWhere('contact_person1_phone', 'like', $searchTerm)
                        ->orWhere('contact_person2_name', 'like', $searchTerm)
                        ->orWhere('contact_person2_email', 'like', $searchTerm)
                        ->orWhere('contact_person2_phone', 'like', $searchTerm)
                        ->orWhere('address', 'like', $searchTerm)
                        ->orWhere('remarks', 'like', $searchTerm)
                        ->orWhereHas('accountManager', function ($accountManagerQuery) use ($searchTerm) {
                            $accountManagerQuery->where('name', 'like', $searchTerm)
                                ->orWhere('email', 'like', $searchTerm);
                        });
                });
            });

        $companies = $query->orderBy('company_name')->get();

        $csvData = [];
        
        // Add header row
        $csvData[] = [
            '#',
            'Company Name',
            'Account Manager',
            'Contact Person',
            'Remarks',
            'Status',
        ];

        // Add data rows
        foreach ($companies as $company) {
            $csvData[] = [
                $company->id ?? '',
                $company->company_name ?? '',
                $company->accountManager->name ?? 'N/A',
                $company->contact_person1_name ?? '',
                $company->remarks ?? '',
                $company->status == 'active' ? 'Active' : 'Expired',
            ];
        }

        $filename = 'companies_export_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8 to ensure proper encoding in Excel
        fwrite($handle, "\xEF\xBB\xBF");
        
        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return Response::streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        $companies = Company::with('accountManager')
            ->when($this->search !== '', function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('company_name', 'like', $searchTerm)
                        ->orWhere('contact_person1_name', 'like', $searchTerm)
                        ->orWhere('contact_person1_email', 'like', $searchTerm)
                        ->orWhere('contact_person1_phone', 'like', $searchTerm)
                        ->orWhere('contact_person2_name', 'like', $searchTerm)
                        ->orWhere('contact_person2_email', 'like', $searchTerm)
                        ->orWhere('contact_person2_phone', 'like', $searchTerm)
                        ->orWhere('address', 'like', $searchTerm)
                        ->orWhere('remarks', 'like', $searchTerm)
                        ->orWhereHas('accountManager', function ($accountManagerQuery) use ($searchTerm) {
                            $accountManagerQuery->where('name', 'like', $searchTerm)
                                ->orWhere('email', 'like', $searchTerm);
                        });
                });
            })
            ->orderBy('company_name')
            ->get();

        return view('livewire.company.company-list', compact('companies'));
    }
}
