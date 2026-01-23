<?php

namespace App\Livewire\Company;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AddCompany extends Component
{
    public array $company = [
        'status' => 'active',
        'ehs_appointments_per_year' => 1,
    ];

    // Account Manager Search
    public string $accountManagerSearch = '';
    public bool $showAccountManagerDropdown = false;
    public array $accountManagers = [];

    protected array $messages = [
        'company.company_name.required' => 'The company name field is required.',
        'company.address.required' => 'The address field is required.',
        'company.billing_code.required' => 'The billing code field is required.',
        'company.ehs_appointments_per_year.required' => 'The EHS appointments per year field is required.',
        'company.ehs_appointments_per_year.min' => 'The EHS appointments per year must be at least 1.',
        'company.contact_person1_name.required' => 'The primary contact name field is required.',
    ];

    protected function rules(): array
    {
        return [
            'company.company_name' => ['required', 'string', 'max:255'],
            'company.address' => ['required', 'string'],
            'company.billing_code' => ['required', 'string', 'max:100'],
            'company.ehs_appointments_per_year' => ['required', 'integer', 'min:1'],
            'company.contact_person1_name' => ['required', 'string', 'max:255'],
            'company.contact_person1_phone' => ['nullable', 'string', 'max:30'],
            'company.contact_person1_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function mount(): void
    {
        $this->loadAccountManagers();
    }

    public function clear(): void
    {
        $this->company = [
            'status' => 'active',
            'ehs_appointments_per_year' => 1,
        ];
        $this->accountManagerSearch = '';
        $this->company['account_manager_id'] = null;
    }

    public function selectAccountManager(int $userId, string $userName): void
    {
        $this->company['account_manager_id'] = $userId;
        $this->accountManagerSearch = $userName;
        $this->showAccountManagerDropdown = false;
    }

    public function updatedAccountManagerSearch(): void
    {
        $this->showAccountManagerDropdown = !empty($this->accountManagerSearch);

        // If search matches exactly with a selected manager, keep it selected
        if (!empty($this->company['account_manager_id'])) {
            $selectedManager = collect($this->accountManagers)->firstWhere('id', (int) $this->company['account_manager_id']);
            if ($selectedManager && strtolower($selectedManager['name']) === strtolower($this->accountManagerSearch)) {
                return;
            }
        }

        // Clear selection if search doesn't match
        $this->company['account_manager_id'] = null;
    }



    public function getFilteredAccountManagersProperty(): array
    {
        if (empty($this->accountManagerSearch)) {
            return $this->accountManagers;
        }

        $search = strtolower($this->accountManagerSearch);
        return array_values(array_filter($this->accountManagers, function ($manager) use ($search) {
            return str_contains(strtolower($manager['name']), $search) ||
                str_contains(strtolower($manager['email'] ?? ''), $search);
        }));
    }

    protected function loadAccountManagers(): void
    {
        $teamId = tenant('id') ?? (Auth::user()->team_id ?? null);

        $this->accountManagers = User::query()
            ->when($teamId, function ($query) use ($teamId) {
                $query->where('team_id', $teamId);
            })
            ->where(function ($query) {
                $query->where('is_admin', 1)
                    ->orWhereHas('roles', function ($q) {
                        $q->where('name', User::ROLE_ADMIN);
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'team_id'])
            ->map(fn($user) => [
                'id' => (int) $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->toArray();
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
            'billing_code' => $this->company['billing_code'] ?? null,
            'remarks' => $this->company['remarks'] ?? null,
            'account_manager_id' => $this->company['account_manager_id'] ?? null,
            'status' => $this->company['status'] ?? 'active',
            'ehs_appointments_per_year' => $this->company['ehs_appointments_per_year'] ?? 1,
            'contact_person1_name' => $this->company['contact_person1_name'] ?? null,
            'contact_person1_phone' => $this->company['contact_person1_phone'] ?? null,
            'contact_person1_email' => $this->company['contact_person1_email'] ?? null,
        ]);

        session()->flash('message', 'Company created successfully.');

        $this->redirectRoute('tenant.companies.index');
    }

    public function render()
    {
        return view('livewire.company.add-company');
    }
}
