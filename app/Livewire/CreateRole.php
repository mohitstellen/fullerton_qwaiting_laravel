<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class CreateRole extends Component
{
    #[Title('Role Create')]

    public $name;
    public $teamId;
    public $locationId;
    public $permissions = [];
    public $allPermissions = [];
    public $selectAll = false;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('team_id', $this->teamId)->where( 'location_id',$this->locationId,);
                })
            ],
            'permissions' => 'array',
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Role Add')) {
            abort(403);
        }

        $this->teamId = tenant('id'); // Ensure `tenant('id')` returns a valid team ID.
        $this->locationId = Session::get('selectedLocation');
       
        $this->allPermissions = Permission::whereNotNull('team_id')->pluck('name', 'id')->toArray();
    }

    public function toggleSelectAll()
{
    if ($this->selectAll) {
        $this->permissions = [];
        $this->selectAll = false;
    } else {
        $this->permissions = array_keys($this->allPermissions);
        $this->selectAll = true;
    }
}

public function updatedPermissions()
{
    // Automatically check/uncheck "selectAll" based on selection count
    $this->selectAll = count($this->permissions) === count($this->allPermissions);
}

    // public function updatedSelectAll($value)
    // {
    //     // If selected, assign all permissions; otherwise, clear selection
    //     $this->permissions = $value ? array_keys($this->allPermissions) : [];
    // }

    public function save()
    {
        $this->validate();

        $role = Role::firstOrCreate([
            'name' => strtolower($this->name),
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            // 'guard_name' => 'web',
        ]);

        $role->permissions()->sync($this->permissions);

        session()->flash('success', 'Role created successfully.');
        return redirect()->route('tenant.roles');
    }

    private function formatPermissionName($name, $category)
    {
        // Format permission names for display in Master Menu
        if ($category === 'Master Menu') {
            // Replace "Location" with "Clinics" (case-insensitive)
            $name = preg_replace('/\bLocation\b/i', 'Clinics', $name);
            
            // Replace "Service" or "Services" with "Appointment Type" (case-insensitive)
            $name = preg_replace('/\bServices?\b/i', 'Appointment Type', $name);
        }
        
        return $name;
    }

    public function getGroupedPermissions()
    {
        $allPermissions = Permission::whereNotNull('team_id')->get();
        
        $grouped = [
            'Master Menu' => [],
            'Operation Module' => [],
            'Reports' => [],
            'Settings' => [],
            'Other' => []
        ];

        foreach ($allPermissions as $permission) {
            $name = $permission->name;
            $category = 'Other';

            // Master Menu: Corporate eVoucher, Import Member Details, Voucher, Company, Appointment Type, Clinic, Location, Service, Country, Package
            if (stripos($name, 'Corporate') !== false && stripos($name, 'eVoucher') !== false ||
                stripos($name, 'Corporate eVoucher') !== false ||
                stripos($name, 'Import Member') !== false ||
                stripos($name, 'Import') !== false && stripos($name, 'Member') !== false ||
                stripos($name, 'Voucher') !== false ||
                stripos($name, 'Company') !== false ||
                stripos($name, 'Appointment Type') !== false ||
                stripos($name, 'Clinic') !== false ||
                stripos($name, 'Location') !== false ||
                stripos($name, 'Service') !== false ||
                stripos($name, 'Country') !== false ||
                stripos($name, 'Package') !== false) {
                $category = 'Master Menu';
            } 
            // Operation Module: Schedule Settings, Book/View Appointment, Patient Search
            elseif (stripos($name, 'Schedule') !== false ||
                      stripos($name, 'Book') !== false ||
                      stripos($name, 'View Appointment') !== false ||
                      stripos($name, 'Patient') !== false && stripos($name, 'Search') !== false ||
                      stripos($name, 'Appointment') !== false ||
                      stripos($name, 'Call') !== false ||
                      stripos($name, 'Counter') !== false) {
                $category = 'Operation Module';
            } 
            // Reports: Booking Reports, Booking List, Analytics, and other reports
            elseif (stripos($name, 'Report') !== false || 
                      stripos($name, 'Analytics') !== false ||
                      stripos($name, 'Booking Report') !== false ||
                      stripos($name, 'Booking List') !== false) {
                $category = 'Reports';
            } 
            // Settings: All settings-related permissions (excluding Staff which goes to Others)
            elseif ((stripos($name, 'Setting') !== false || 
                      stripos($name, 'Template') !== false ||
                      stripos($name, 'Logo') !== false ||
                      stripos($name, 'Color') !== false ||
                      stripos($name, 'Display') !== false ||
                      stripos($name, 'Form') !== false ||
                      stripos($name, 'Term') !== false ||
                      stripos($name, 'Role') !== false) &&
                      stripos($name, 'Staff') === false) {
                $category = 'Settings';
            }
            // Staff goes to Others (not Settings)
            elseif (stripos($name, 'Staff') !== false) {
                $category = 'Other';
            }

            // Format the display name based on category
            $displayName = $this->formatPermissionName($name, $category);

            $grouped[$category][] = [
                'id' => $permission->id,
                'name' => $displayName,
                'original_name' => $permission->name // Keep original for permission checks
            ];
        }

        return $grouped;
    }

    public function render()
    {
        return view('livewire.create-role', [
            'allPermissions' => Permission::whereNotNull('team_id')->pluck('name', 'id'),
            'groupedPermissions' => $this->getGroupedPermissions(),
        ]);
    }
}
