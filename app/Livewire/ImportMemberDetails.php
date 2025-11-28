<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\MemberImport;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Import Member Details')]
class ImportMemberDetails extends Component
{
    use WithFileUploads, WithPagination;

    public $teamId;
    public $companySearch = '';
    public $company_id = null;
    public $selectedCompanyName = '';
    public $showCompanyDropdown = false;
    public $allCompanies = [];
    public $enforcePasswordChange = 'Yes';
    public $file;
    public $activeTab = 'import'; // 'import' or 'download'

    protected function rules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'enforcePasswordChange' => 'required|in:Yes,No',
            'file' => 'required|file|mimes:xlsx,csv|max:10240', // 10MB max
        ];
    }

    protected $messages = [
        'company_id.required' => 'Please select a company.',
        'company_id.exists' => 'The selected company is invalid.',
        'enforcePasswordChange.required' => 'Please select enforce password change option.',
        'file.required' => 'Please select a file to upload.',
        'file.mimes' => 'The file must be a xlsx or csv file.',
        'file.max' => 'The file size must not exceed 10MB.',
    ];

    public function mount()
    {
        $this->teamId = tenant('id');
    }

    public function updatedCompanySearch()
    {
        if (strlen($this->companySearch) >= 1) {
            $this->allCompanies = Company::where('team_id', $this->teamId)
                ->where('status', 'active')
                ->where('company_name', 'like', '%' . $this->companySearch . '%')
                ->select('id', 'company_name')
                ->orderBy('company_name')
                ->limit(20)
                ->get();
            $this->showCompanyDropdown = true;
        } elseif (empty($this->companySearch)) {
            $this->allCompanies = [];
            $this->showCompanyDropdown = false;
            if ($this->company_id) {
                // Load selected company name
                $company = Company::find($this->company_id);
                $this->selectedCompanyName = $company ? $company->company_name : '';
            }
        }
    }

    public function selectCompany($companyId, $companyName)
    {
        $this->company_id = $companyId;
        $this->selectedCompanyName = $companyName;
        $this->companySearch = $companyName;
        $this->showCompanyDropdown = false;
    }

    public function clearCompany()
    {
        $this->company_id = null;
        $this->selectedCompanyName = '';
        $this->companySearch = '';
        $this->showCompanyDropdown = false;
        $this->allCompanies = [];
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function import()
    {
        $this->validate();

        try {
            $user = Auth::user();
            $fileName = $this->file->getClientOriginalName();
            
            // Store the file in public storage
            $filePath = $this->file->store('member-imports', 'public');

            // Create import record
            MemberImport::create([
                'team_id' => $this->teamId,
                'company_id' => $this->company_id,
                'file_name' => $fileName,
                'created_by' => $user->name ?? $user->username ?? 'Unknown',
                'created_date_time' => now(),
                'imported_date_time' => now(),
                'status' => MemberImport::STATUS_IN_PROGRESS,
                'enforce_password_change' => $this->enforcePasswordChange === 'Yes',
            ]);

            session()->flash('message', 'File uploaded successfully. Import is in progress.');
            
            // Reset form
            $this->reset(['file', 'company_id', 'companySearch', 'selectedCompanyName', 'enforcePasswordChange']);
            $this->enforcePasswordChange = 'Yes';
            $this->allCompanies = [];
            $this->showCompanyDropdown = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload file: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $imports = MemberImport::where('team_id', $this->teamId)
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.import-member-details', [
            'imports' => $imports,
        ]);
    }
}
