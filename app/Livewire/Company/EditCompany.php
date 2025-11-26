<?php

namespace App\Livewire\Company;

use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyPackage;
use App\Models\Location;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditCompany extends Component
{
    public Company $companyModel;

    public array $company = [];

    public array $companyPackages = [];

    public array $appointmentTypes = [];

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

    protected array $messages = [
        'company.company_name.required' => 'The company name field is required.',
        'company.address.required' => 'The address field is required.',
        'company.billing_address.required' => 'The billing address field is required.',
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
            'company.billing_address' => ['required', 'string'],
            'company.contact_person1_name' => ['required', 'string', 'max:255'],
            'company.contact_person1_phone' => ['nullable', 'string', 'max:30'],
            'company.contact_person1_email' => ['nullable', 'email', 'max:255'],
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

        $this->mappingForm = $this->emptyMappingForm();

        $this->loadOptions();
        $this->loadCompanyPackages();
    }

    public function updatedMappingFormAppointmentTypeId($value): void
    {
        // Cast to int to ensure proper type matching
        $appointmentTypeId = !empty($value) ? (int) $value : null;
        
        Log::info('Appointment Type Selected', [
            'raw_value' => $value,
            'appointment_type_id' => $appointmentTypeId,
        ]);
        
        $this->loadPackageOptions($appointmentTypeId);
        
        Log::info('Package Options Loaded', [
            'count' => count($this->packageOptions),
            'options' => $this->packageOptions,
        ]);

        $packageIds = array_column($this->packageOptions, 'id');
        if (! empty($this->mappingForm['package_ids']) && is_array($this->mappingForm['package_ids'])) {
            // Convert package IDs to strings for consistency with wire:model checkboxes
            $packageIdsStrings = array_map(fn($id) => (string) $id, $packageIds);
            
            // Filter to keep only valid package IDs, preserving string format
            $this->mappingForm['package_ids'] = array_values(array_filter(
                $this->mappingForm['package_ids'],
                fn($id) => in_array((string) $id, $packageIdsStrings, true)
            ));
        } else {
            $this->mappingForm['package_ids'] = [];
        }

        $this->dispatchSelectInitializers();
    }

    public function createMapping(): void
    {
        $this->mappingForm = $this->emptyMappingForm();
        $this->packageOptions = [];
        $this->showForm = true;
        $this->dispatchSelectInitializers();
    }

    public function editMapping(int $mappingId): void
    {
        $mapping = CompanyPackage::where('company_id', $this->companyModel->id)->findOrFail($mappingId);

        $this->mappingForm = [
            'id' => $mapping->id,
            'appointment_type_id' => (string) $mapping->appointment_type_id,
            'package_ids' => [(string) $mapping->package_id],
            'modes_of_identification' => $mapping->modes_of_identification ?? [],
            'clinic_ids' => $mapping->clinic_ids ?? [],
            'remarks' => $mapping->remarks,
        ];

        $this->loadPackageOptions($mapping->appointment_type_id);
        $this->showForm = true;
        $this->dispatchSelectInitializers();
    }

    public function saveMapping(): void
    {
        $this->validate($this->mappingRules(), [
            'mappingForm.appointment_type_id.required' => 'Please select an appointment type.',
            'mappingForm.package_ids.required' => 'Please select at least one package.',
            'mappingForm.package_ids.min' => 'Please select at least one package.',
            'mappingForm.package_ids.*.required' => 'Invalid package selected.',
            'mappingForm.package_ids.*.exists' => 'The selected package is invalid.',
        ]);

        $packageIds = $this->mappingForm['package_ids'] ?? [];
        $appointmentTypeId = (int) $this->mappingForm['appointment_type_id'];
        
        $basePayload = [
            'company_id' => $this->companyModel->id,
            'appointment_type_id' => $appointmentTypeId,
            'modes_of_identification' => $this->sanitizeMoiValues(),
            'clinic_ids' => $this->sanitizeClinicValues(),
            'remarks' => $this->mappingForm['remarks'] ?? null,
        ];

        if (! empty($this->mappingForm['id'])) {
            // Editing existing: delete the old record and create new ones for each selected package
            $oldMapping = CompanyPackage::where('company_id', $this->companyModel->id)
                ->findOrFail($this->mappingForm['id']);
            
            $oldPackageId = $oldMapping->package_id;
            
            // Delete the old mapping
            $oldMapping->delete();
            
            // Create new records for each selected package
            foreach ($packageIds as $packageId) {
                $packageIdInt = (int) $packageId;
                
                // Skip if this is the same package as the old one (to avoid duplicate)
                // But actually, we deleted it, so we should create it again
                $payload = array_merge($basePayload, [
                    'package_id' => $packageIdInt,
                ]);
                
                // Check if this combination already exists (from other mappings)
                $exists = CompanyPackage::where([
                    'company_id' => $this->companyModel->id,
                    'appointment_type_id' => $appointmentTypeId,
                    'package_id' => $packageIdInt,
                ])->exists();
                
                if (!$exists) {
                    CompanyPackage::create($payload);
                }
            }
        } else {
            // Creating new: create one record per selected package
            foreach ($packageIds as $packageId) {
                $payload = array_merge($basePayload, [
                    'package_id' => (int) $packageId,
                ]);
                
                // Check if this combination already exists
                $exists = CompanyPackage::where([
                    'company_id' => $this->companyModel->id,
                    'appointment_type_id' => $appointmentTypeId,
                    'package_id' => (int) $packageId,
                ])->exists();
                
                if (!$exists) {
                    CompanyPackage::create($payload);
                }
            }
        }

        session()->flash('companyPackagesMessage', 'Company package(s) saved successfully.');

        $this->loadCompanyPackages();
        $this->closeForm();
    }

    public function deleteMapping(int $mappingId): void
    {
        CompanyPackage::where('company_id', $this->companyModel->id)
            ->findOrFail($mappingId)
            ->delete();

        if (($this->mappingForm['id'] ?? null) === $mappingId) {
            $this->closeForm();
        }

        $this->loadCompanyPackages();
        session()->flash('companyPackagesMessage', 'Company package removed.');
    }

    public function closeForm(): void
    {
        $this->mappingForm = $this->emptyMappingForm();
        $this->packageOptions = [];
        $this->showForm = false;
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
        return view('livewire.company.edit-company');
    }

    protected function mappingRules(): array
    {
        $teamId = $this->companyModel->team_id;

        return [
            'mappingForm.appointment_type_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('team_id', $teamId)),
            ],
            'mappingForm.package_ids' => ['required', 'array', 'min:1'],
            'mappingForm.package_ids.*' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('team_id', $teamId)),
            ],
            'mappingForm.modes_of_identification' => ['nullable', 'array'],
            'mappingForm.modes_of_identification.*' => ['nullable', 'string', 'max:255'],
            'mappingForm.clinic_ids' => ['nullable', 'array'],
            'mappingForm.clinic_ids.*' => [
                'integer',
                Rule::exists('locations', 'id')->where(fn ($query) => $query->where('team_id', $teamId)),
            ],
            'mappingForm.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function loadCompanyPackages(): void
    {
        $this->companyPackages = $this->companyModel->companyPackages()
            ->with(['appointmentType:id,name', 'package:id,name,amount'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (CompanyPackage $mapping) {
                return [
                    'id' => $mapping->id,
                    'appointment_type_id' => $mapping->appointment_type_id,
                    'appointment_type_name' => $mapping->appointmentType->name ?? 'N/A',
                    'package_id' => $mapping->package_id,
                    'package_name' => $mapping->package->name ?? 'N/A',
                    'package_amount' => $mapping->package->amount ?? 0.00,
                    'modes_of_identification' => $mapping->modes_of_identification ?? [],
                    'clinic_ids' => $mapping->clinic_ids ?? [],
                    'remarks' => $mapping->remarks,
                    'updated_at' => optional($mapping->updated_at)->diffForHumans(),
                ];
            })
            ->toArray();
    }

    protected function dispatchSelectInitializers(): void
    {
        $this->dispatch('company-packages:init-clinic-select', selected: $this->mappingForm['clinic_ids'] ?? []);
        $this->dispatch('company-packages:init-moi-select', selected: $this->mappingForm['modes_of_identification'] ?? []);
    }

    protected function loadOptions(): void
    {
        $teamId = $this->companyModel->team_id;

        $this->appointmentTypes = Category::query()
            ->where('team_id', $teamId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
            ])
            ->toArray();

        $locations = Location::query()
            ->where('team_id', $teamId)
            ->where('status', 1)
            ->orderBy('location_name')
            ->get(['id', 'location_name']);

        $this->locations = $locations->map(fn ($location) => [
            'id' => (int) $location->id,
            'name' => $location->location_name,
        ])->toArray();

        $this->locationLookup = $locations->pluck('location_name', 'id')->toArray();
    }

    protected function loadPackageOptions(?int $appointmentTypeId): void
    {
        if (empty($appointmentTypeId)) {
            $this->packageOptions = [];
            return;
        }

        $this->packageOptions = Category::query()
            ->where('team_id', $this->companyModel->team_id)
            ->where('parent_id', $appointmentTypeId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category) => [
                'id' => (int) $category->id,
                'name' => $category->name,
            ])
            ->toArray();
    }

    protected function emptyMappingForm(): array
    {
        return [
            'id' => null,
            'appointment_type_id' => '',
            'package_ids' => [],
            'modes_of_identification' => [],
            'clinic_ids' => [],
            'remarks' => '',
        ];
    }

    protected function sanitizeMoiValues(?array $values = null): array
    {
        $values = $values ?? ($this->mappingForm['modes_of_identification'] ?? []);
        $values = array_map(static fn ($value) => trim((string) $value), $values);

        return array_values(array_filter($values, static fn ($value) => $value !== ''));
    }

    protected function sanitizeClinicValues(?array $values = null): array
    {
        $values = $values ?? ($this->mappingForm['clinic_ids'] ?? []);
        $values = array_map(static fn ($value) => (int) $value, $values);

        return array_values(array_filter($values, static fn ($value) => $value > 0));
    }
}
