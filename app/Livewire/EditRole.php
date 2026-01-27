<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class EditRole extends Component
{
    #[Title('Edit Role')]

    public $roleId;
    public $teamId;
    public $locationId;
    public $name;
    public $permissions = [];
    public $allPermissions = [];
    public $selectAll = false;

    public function mount($id)
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Role Edit')) {
            abort(403);
        }

        $role = Role::findOrFail($id);
        $this->teamId = tenant('id');
        $this->locationId = $role->location_id ?? Session::get('selectedLocation');
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('id')->toArray();
        $this->allPermissions = Permission::whereNotNull('team_id')->pluck('name', 'id')->toArray();
        $this->selectAll = count($this->permissions) === count($this->allPermissions);
    }

    public function updatedSelectAll($value)
    {
        $this->permissions = $value ? array_keys($this->allPermissions) : [];
    }

    public function updatedPermissions()
    {
        $this->selectAll = count($this->permissions) === count($this->allPermissions);
    }

    protected function rules()
   {
    return [
        'name' => [
            'required',
            'string',
            'max:50',
            Rule::unique('roles')
                ->where(function ($query) {
                    return $query->where('team_id', tenant('id'))->where('location_id',$this->locationId);
                })
                ->ignore($this->roleId), // Ignore current role during update
        ],
        'permissions' => 'array',
    ];
}

    public function update()
    {
        $this->validate();

        $role = Role::findOrFail($this->roleId);
        $role->update([
            'name' => strtolower($this->name),
        ]);

        $role->permissions()->sync($this->permissions);

        session()->flash('success', 'Role updated successfully.');
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
        return view('livewire.edit-role', [
            'groupedPermissions' => $this->getGroupedPermissions(),
        ]);
    }
}
