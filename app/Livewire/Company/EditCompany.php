<?php

namespace App\Livewire\Company;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyAppointmentType;
use App\Models\CompanyPackage;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class EditCompany extends Component
{
    use WithPagination;

    public Company $companyModel;

    public array $company = [];

    public array $appointmentTypes = [];

    public array $appointmentTypesForValidity = [];

    public array $packageOptions = [];

    public array $locations = [];

    public array $locationLookup = [];

    public array $mappingForm = [];

    public bool $showForm = false;

    public array $defaultMoiOptions = [
        'Fullerton Ecard',
        'Hard or soft copy medical chit',
        'NRIC',
        'Staff pass',
        'Name List #',
    ];

    // Appointment Type Validity properties
    public array $appointmentTypeForm = [];

    public bool $showAppointmentTypeForm = false;

    public array $applicableForOptions = ['Both', 'Self'];

    public string $appointmentTypeSearch = '';

    public bool $showAppointmentTypeDropdown = false;

    // Company Package Appointment Type Search
    public string $packageAppointmentTypeSearch = '';

    public bool $showPackageAppointmentTypeDropdown = false;

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

    protected $listeners = [
        'companyPackagesClinicsUpdated' => 'syncClinicSelection',
        'companyPackagesMoiUpdated' => 'syncMoiSelection',
        'companyPackagesPackageUpdated' => 'syncPackageSelection',
    ];

    protected function rules(): array
    {
        return [
            'company.company_name' => ['required', 'string', 'max:255'],
            'company.ehs_appointments_per_year' => ['required', 'integer', 'min:1'],
            'company.address' => ['required', 'string'],
            'company.billing_code' => ['required', 'string', 'max:100'],
            'company.contact_person1_name' => ['required', 'string', 'max:255'],
            'company.contact_person1_phone' => ['nullable', 'string', 'max:30'],
            'company.contact_person1_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function mount(Company $companyRecord): void
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Company')) {
            abort(403);
        }
        
        $this->companyModel = $companyRecord;

        $this->company = $companyRecord->only([
            'company_name',
            'address',
            'billing_code',
            'remarks',
            'status',
            'ehs_appointments_per_year',
            'account_manager_id',
            'contact_person1_name',
            'contact_person1_phone',
            'contact_person1_email',
        ]);

        // Load account manager name if exists
        if (!empty($this->company['account_manager_id'])) {
            $accountManager = User::find($this->company['account_manager_id']);
            if ($accountManager) {
                $this->accountManagerSearch = $accountManager->name;
            }
        }

        $this->mappingForm = $this->emptyMappingForm();
        $this->appointmentTypeForm = $this->emptyAppointmentTypeForm();

        $this->loadOptions();
        $this->loadAccountManagers();
    }

    public function updatedMappingFormAppointmentTypeIds($value): void
    {
        // Load all packages since we removed appointment type dependency
        $this->loadPackageOptions();
        $this->dispatchSelectInitializers();
    }

    public function createMapping(): void
    {
        $this->mappingForm = $this->emptyMappingForm();
        $this->loadPackageOptions(); // Load all packages
        $this->packageAppointmentTypeSearch = '';
        $this->showPackageAppointmentTypeDropdown = false;
        $this->showForm = true;
        $this->dispatchSelectInitializers();
    }

    public function editMapping(int $mappingId): void
    {
        $mapping = CompanyPackage::where('company_id', $this->companyModel->id)->findOrFail($mappingId);

        // Get all mappings with the same package to find all associated appointment types
        $relatedMappings = CompanyPackage::where('company_id', $this->companyModel->id)
            ->where('package_id', $mapping->package_id)
            ->pluck('appointment_type_id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        $this->mappingForm = [
            'id' => $mapping->id,
            'appointment_type_ids' => $relatedMappings, // Multiple appointment types
            'package_ids' => [(string) $mapping->package_id],
            'modes_of_identification' => $mapping->modes_of_identification ?? [],
            'clinic_ids' => $mapping->clinic_ids ?? [],
            'remarks' => $mapping->remarks,
        ];

        $this->packageAppointmentTypeSearch = '';
        $this->showPackageAppointmentTypeDropdown = false;
        $this->loadPackageOptions(); // Load all packages
        $this->showForm = true;
        $this->dispatchSelectInitializers();
    }

    public function selectPackageAppointmentType(int $appointmentTypeId, string $appointmentTypeName): void
    {
        // Toggle appointment type selection
        $currentIds = $this->mappingForm['appointment_type_ids'] ?? [];
        $appointmentTypeIdStr = (string) $appointmentTypeId;

        if (in_array($appointmentTypeIdStr, $currentIds)) {
            // Remove if already selected
            $this->mappingForm['appointment_type_ids'] = array_values(array_filter(
                $currentIds,
                fn($id) => $id !== $appointmentTypeIdStr
            ));
        } else {
            // Add if not selected
            $this->mappingForm['appointment_type_ids'][] = $appointmentTypeIdStr;
        }

        $this->dispatchSelectInitializers();
    }

    public function updatedPackageAppointmentTypeSearch(): void
    {
        $this->showPackageAppointmentTypeDropdown = !empty($this->packageAppointmentTypeSearch);
    }

    public function getFilteredPackageAppointmentTypesProperty(): array
    {
        $search = trim($this->packageAppointmentTypeSearch ?? '');

        if (empty($search)) {
            return $this->appointmentTypes;
        }

        $searchLower = strtolower($search);
        $filtered = array_filter($this->appointmentTypes, function ($type) use ($searchLower) {
            if (empty($type['name'])) {
                return false;
            }
            $name = strtolower(trim($type['name']));
            // Check if search term is contained in the name
            return str_contains($name, $searchLower);
        });

        return array_values($filtered);
    }

    public function saveMapping(): void
    {
        $this->validate($this->mappingRules(), [
            'mappingForm.appointment_type_ids.required' => 'Please select at least one appointment type.',
            'mappingForm.package_ids.required' => 'Please select at least one package.',
            'mappingForm.package_ids.min' => 'Please select at least one package.',
            'mappingForm.package_ids.*.required' => 'Invalid package selected.',
            'mappingForm.package_ids.*.exists' => 'The selected package is invalid.',
        ]);

        $packageIds = $this->mappingForm['package_ids'] ?? [];
        $appointmentTypeIds = array_map('intval', $this->mappingForm['appointment_type_ids'] ?? []);

        if (! empty($this->mappingForm['id'])) {
            // Editing existing: delete all old mappings for this package and recreate
            $oldMapping = CompanyPackage::where('company_id', $this->companyModel->id)
                ->findOrFail($this->mappingForm['id']);

            $oldPackageId = $oldMapping->package_id;

            // Delete all mappings for this package
            CompanyPackage::where('company_id', $this->companyModel->id)
                ->where('package_id', $oldPackageId)
                ->delete();
        }

        // Create new records for each combination of appointment type and package
        foreach ($appointmentTypeIds as $appointmentTypeId) {
            foreach ($packageIds as $packageId) {
                $packageIdInt = (int) $packageId;

                $payload = [
                    'company_id' => $this->companyModel->id,
                    'appointment_type_id' => $appointmentTypeId,
                    'package_id' => $packageIdInt,
                    'modes_of_identification' => $this->sanitizeMoiValues(),
                    'clinic_ids' => $this->sanitizeClinicValues(),
                    'remarks' => $this->mappingForm['remarks'] ?? null,
                ];

                // Check if this combination already exists
                $exists = CompanyPackage::where([
                    'company_id' => $this->companyModel->id,
                    'appointment_type_id' => $appointmentTypeId,
                    'package_id' => $packageIdInt,
                ])->exists();

                if (!$exists) {
                    CompanyPackage::create($payload);
                }
            }
        }

        session()->flash('companyPackagesMessage', 'Company package(s) saved successfully.');

        $this->loadAppointmentTypesForValidity();
        $this->closeForm();
        $this->resetPage('packagesPage');
    }

    public function deleteMapping(int $mappingId): void
    {
        CompanyPackage::where('company_id', $this->companyModel->id)
            ->findOrFail($mappingId)
            ->delete();

        if (($this->mappingForm['id'] ?? null) === $mappingId) {
            $this->closeForm();
        }

        $this->loadAppointmentTypesForValidity();
        session()->flash('companyPackagesMessage', 'Company package removed.');
        $this->resetPage('packagesPage');
    }

    public function closeForm(): void
    {
        $this->mappingForm = $this->emptyMappingForm();
        $this->packageOptions = [];
        $this->packageAppointmentTypeSearch = '';
        $this->showPackageAppointmentTypeDropdown = false;
        $this->showForm = false;
    }



    public function clear(): void
    {
        // Reset to original company values
        $this->company = $this->companyModel->only([
            'company_name',
            'address',
            'billing_code',
            'remarks',
            'status',
            'ehs_appointments_per_year',
            'account_manager_id',
            'contact_person1_name',
            'contact_person1_phone',
            'contact_person1_email',
        ]);

        // Reset account manager search
        if (!empty($this->company['account_manager_id'])) {
            $accountManager = User::find($this->company['account_manager_id']);
            if ($accountManager) {
                $this->accountManagerSearch = $accountManager->name;
            }
        } else {
            $this->accountManagerSearch = '';
        }
        $this->showAccountManagerDropdown = false;
    }

    public function update(): void
    {
        $this->validate();

        $this->companyModel->update($this->company);

        session()->flash('message', 'Company updated successfully.');

        $this->redirectRoute('tenant.companies.index');
    }

    public function syncClinicSelection($clinicIds): void
    {
        $this->mappingForm['clinic_ids'] = $this->sanitizeClinicValues($clinicIds);
    }

    public function syncMoiSelection($moiValues): void
    {
        $values = is_array($moiValues) ? $moiValues : [];
        $this->mappingForm['modes_of_identification'] = $this->sanitizeMoiValues($values);
    }

    public function syncPackageSelection($packageIds): void
    {
        $this->mappingForm['package_ids'] = is_array($packageIds) ? $packageIds : [];
    }

    public function toggleSelectAllPackages(): void
    {
        $packageIds = array_column($this->packageOptions, 'id');

        // Convert to strings to match wire:model behavior (checkboxes send string values)
        $packageIds = array_map(fn($id) => (string) $id, $packageIds);

        // Get current selected values and normalize to strings
        $currentSelected = $this->mappingForm['package_ids'] ?? [];
        $currentSelected = array_map(fn($id) => (string) $id, $currentSelected);

        // Check if all packages are selected
        $allSelected = count($currentSelected) === count($packageIds) &&
            count($packageIds) > 0 &&
            empty(array_diff($packageIds, $currentSelected));

        // Toggle: if all selected, deselect all; otherwise, select all
        $this->mappingForm['package_ids'] = $allSelected ? [] : $packageIds;
    }

    public function areAllPackagesSelected(): bool
    {
        if (empty($this->packageOptions)) {
            return false;
        }

        $packageIds = array_map(fn($pkg) => (string) $pkg['id'], $this->packageOptions);
        $selectedIds = array_map(fn($id) => (string) $id, $this->mappingForm['package_ids'] ?? []);

        return count($selectedIds) === count($packageIds) &&
            count($packageIds) > 0 &&
            empty(array_diff($packageIds, $selectedIds));
    }

    public function render()
    {
        return view('livewire.company.edit-company', [
            'companyAppointmentTypes' => $this->getCompanyAppointmentTypesProperty(),
            'companyPackages' => $this->getCompanyPackagesProperty(),
        ]);
    }

    protected function mappingRules(): array
    {
        $teamId = $this->companyModel->team_id;

        return [
            'mappingForm.appointment_type_ids' => ['required', 'array', 'min:1'],
            'mappingForm.appointment_type_ids.*' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn($query) => $query->where('team_id', $teamId)),
            ],
            'mappingForm.package_ids' => ['required', 'array', 'min:1'],
            'mappingForm.package_ids.*' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn($query) => $query->where('team_id', $teamId)),
            ],
            'mappingForm.modes_of_identification' => ['nullable', 'array'],
            'mappingForm.modes_of_identification.*' => ['nullable', 'string', 'max:255'],
            'mappingForm.clinic_ids' => ['nullable', 'array'],
            'mappingForm.clinic_ids.*' => [
                'integer',
                Rule::exists('locations', 'id')->where(fn($query) => $query->where('team_id', $teamId)),
            ],
            'mappingForm.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function getCompanyPackagesProperty()
    {
        return $this->companyModel->companyPackages()
            ->with(['appointmentType:id,name', 'package:id,name,amount'])
            ->orderByDesc('updated_at')
            ->paginate(5, ['*'], 'packagesPage');
    }

    protected function dispatchSelectInitializers(): void
    {
        $this->dispatch('company-packages:init-clinic-select', selected: $this->mappingForm['clinic_ids'] ?? []);
        $this->dispatch('company-packages:init-moi-select', selected: $this->mappingForm['modes_of_identification'] ?? []);
        $this->dispatch('company-packages:init-packages-select', selected: $this->mappingForm['package_ids'] ?? []);
    }

    protected function loadOptions(): void
    {
        $teamId = $this->companyModel->team_id;

        $this->appointmentTypes = Category::query()
            ->where('team_id', $teamId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
            ])
            ->toArray();

        // Load appointment types that are assigned in company packages
        $this->loadAppointmentTypesForValidity();

        $locations = Location::query()
            ->where('team_id', $teamId)
            ->where('status', 1)
            ->orderBy('location_name')
            ->get(['id', 'location_name']);

        $this->locations = $locations->map(fn($location) => [
            'id' => (int) $location->id,
            'name' => $location->location_name,
        ])->toArray();

        $this->locationLookup = $locations->pluck('location_name', 'id')->toArray();
    }

    protected function loadAppointmentTypesForValidity(): void
    {
        // Get unique appointment_type_ids from company packages
        $appointmentTypeIds = CompanyPackage::where('company_id', $this->companyModel->id)
            ->distinct()
            ->pluck('appointment_type_id')
            ->filter()
            ->toArray();

        if (empty($appointmentTypeIds)) {
            $this->appointmentTypesForValidity = [];
            return;
        }

        // Filter appointment types to only include those in company packages
        $this->appointmentTypesForValidity = array_values(array_filter(
            $this->appointmentTypes,
            fn($type) => in_array($type['id'], $appointmentTypeIds)
        ));
    }

    protected function loadPackageOptions(?int $appointmentTypeId = null): void
    {
        // Load ALL packages regardless of appointment type
        $this->packageOptions = Category::query()
            ->where('team_id', $this->companyModel->team_id)
            ->whereNotNull('parent_id') // Only get packages (children), not appointment types
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
            ])
            ->toArray();
    }

    protected function emptyMappingForm(): array
    {
        return [
            'id' => null,
            'appointment_type_ids' => [], // Changed to support multiple appointment types
            'package_ids' => [],
            'modes_of_identification' => [],
            'clinic_ids' => [],
            'remarks' => '',
        ];
    }

    protected function sanitizeMoiValues(?array $values = null): array
    {
        $values = $values ?? ($this->mappingForm['modes_of_identification'] ?? []);
        $values = array_map(static fn($value) => trim((string) $value), $values);

        return array_values(array_filter($values, static fn($value) => $value !== ''));
    }

    protected function sanitizeClinicValues(?array $values = null): array
    {
        $values = $values ?? ($this->mappingForm['clinic_ids'] ?? []);
        $values = array_map(static fn($value) => (int) $value, $values);

        return array_values(array_filter($values, static fn($value) => $value > 0));
    }

    // ==================== Appointment Type Validity Methods ====================

    public function createAppointmentType(): void
    {
        $this->appointmentTypeForm = $this->emptyAppointmentTypeForm();
        $this->appointmentTypeSearch = '';
        $this->showAppointmentTypeDropdown = false;
        $this->showAppointmentTypeForm = true;
    }

    public function editAppointmentType(int $appointmentTypeId): void
    {
        $appointmentType = CompanyAppointmentType::where('company_id', $this->companyModel->id)
            ->findOrFail($appointmentTypeId);

        $selectedType = collect($this->appointmentTypesForValidity)->firstWhere('id', $appointmentType->appointment_type_id);

        $this->appointmentTypeForm = [
            'id' => $appointmentType->id,
            'appointment_type_id' => (string) $appointmentType->appointment_type_id,
            'valid_from' => $appointmentType->valid_from->format('Y-m-d'),
            'valid_to' => $appointmentType->valid_to->format('Y-m-d'),
            'applicable_for' => $appointmentType->applicable_for,
        ];

        $this->appointmentTypeSearch = $selectedType['name'] ?? '';
        $this->showAppointmentTypeDropdown = false;
        $this->showAppointmentTypeForm = true;
    }

    public function selectAppointmentType(int $appointmentTypeId, string $appointmentTypeName): void
    {
        $this->appointmentTypeForm['appointment_type_id'] = (string) $appointmentTypeId;
        $this->appointmentTypeSearch = $appointmentTypeName;
        $this->showAppointmentTypeDropdown = false;
    }

    public function updatedAppointmentTypeSearch(): void
    {
        $this->showAppointmentTypeDropdown = !empty($this->appointmentTypeSearch);

        // If search matches exactly with a selected type, keep it selected
        if (!empty($this->appointmentTypeForm['appointment_type_id'])) {
            $selectedType = collect($this->appointmentTypesForValidity)->firstWhere('id', (int) $this->appointmentTypeForm['appointment_type_id']);
            if ($selectedType && strtolower($selectedType['name']) === strtolower($this->appointmentTypeSearch)) {
                return;
            }
        }

        // Clear selection if search doesn't match
        $this->appointmentTypeForm['appointment_type_id'] = '';
    }

    public function getFilteredAppointmentTypesProperty(): array
    {
        if (empty($this->appointmentTypeSearch)) {
            return $this->appointmentTypes;
        }

        $search = strtolower($this->appointmentTypeSearch);
        return array_values(array_filter($this->appointmentTypes, function ($type) use ($search) {
            return str_contains(strtolower($type['name']), $search);
        }));
    }

    public function saveAppointmentType(): void
    {
        $this->validate($this->appointmentTypeRules(), [
            'appointmentTypeForm.appointment_type_id.required' => 'Please select an appointment type.',
            'appointmentTypeForm.valid_from.required' => 'Valid from date is required.',
            'appointmentTypeForm.valid_to.required' => 'Valid to date is required.',
            'appointmentTypeForm.valid_to.after_or_equal' => 'Valid to date must be after or equal to valid from date.',
            'appointmentTypeForm.applicable_for.required' => 'Please select applicable for option.',
        ]);

        $payload = [
            'company_id' => $this->companyModel->id,
            'appointment_type_id' => (int) $this->appointmentTypeForm['appointment_type_id'],
            'valid_from' => $this->appointmentTypeForm['valid_from'],
            'valid_to' => $this->appointmentTypeForm['valid_to'],
            'applicable_for' => $this->appointmentTypeForm['applicable_for'],
        ];

        if (!empty($this->appointmentTypeForm['id'])) {
            // Update existing
            CompanyAppointmentType::where('company_id', $this->companyModel->id)
                ->findOrFail($this->appointmentTypeForm['id'])
                ->update($payload);

            session()->flash('appointmentTypesMessage', 'Appointment type validity updated successfully.');
        } else {
            // Create new
            CompanyAppointmentType::create($payload);
            session()->flash('appointmentTypesMessage', 'Appointment type validity added successfully.');
        }

        $this->closeAppointmentTypeForm();
        $this->resetPage('appointmentTypesPage');
    }

    public function deleteAppointmentType(int $appointmentTypeId): void
    {
        CompanyAppointmentType::where('company_id', $this->companyModel->id)
            ->findOrFail($appointmentTypeId)
            ->delete();

        if (($this->appointmentTypeForm['id'] ?? null) === $appointmentTypeId) {
            $this->closeAppointmentTypeForm();
        }

        session()->flash('appointmentTypesMessage', 'Appointment type validity removed.');
        $this->resetPage('appointmentTypesPage');
    }

    public function closeAppointmentTypeForm(): void
    {
        $this->appointmentTypeForm = $this->emptyAppointmentTypeForm();
        $this->appointmentTypeSearch = '';
        $this->showAppointmentTypeDropdown = false;
        $this->showAppointmentTypeForm = false;
    }

    protected function appointmentTypeRules(): array
    {
        $teamId = $this->companyModel->team_id;

        return [
            'appointmentTypeForm.appointment_type_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn($query) => $query->where('team_id', $teamId)),
            ],
            'appointmentTypeForm.valid_from' => ['required', 'date'],
            'appointmentTypeForm.valid_to' => ['required', 'date', 'after_or_equal:appointmentTypeForm.valid_from'],
            'appointmentTypeForm.applicable_for' => ['required', 'in:Both,Self'],
        ];
    }

    protected function getCompanyAppointmentTypesProperty()
    {
        return $this->companyModel->companyAppointmentTypes()
            ->with(['appointmentType:id,name'])
            ->orderByDesc('updated_at')
            ->paginate(5, ['*'], 'appointmentTypesPage');
    }

    protected function emptyAppointmentTypeForm(): array
    {
        return [
            'id' => null,
            'appointment_type_id' => '',
            'valid_from' => '',
            'valid_to' => '',
            'applicable_for' => 'Both',
        ];
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
        $teamId = $this->companyModel->team_id;

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
}
