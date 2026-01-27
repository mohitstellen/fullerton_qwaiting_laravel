<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Team;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // List of permissions with their parent_type categories
        $permissions = [
            // Master Menu permissions
            ['name' => 'Corporate eVoucher', 'parent_type' => 'Master Menu'],
            ['name' => 'Corporate eVoucher Add', 'parent_type' => 'Master Menu'],
            ['name' => 'Corporate eVoucher Edit', 'parent_type' => 'Master Menu'],
            ['name' => 'Corporate eVoucher Delete', 'parent_type' => 'Master Menu'],
            ['name' => 'Corporate eVoucher Read', 'parent_type' => 'Master Menu'],
            ['name' => 'Import Member Details', 'parent_type' => 'Master Menu'],
            ['name' => 'Import Member Details Add', 'parent_type' => 'Master Menu'],
            ['name' => 'Import Member Details Edit', 'parent_type' => 'Master Menu'],
            ['name' => 'Import Member Details Delete', 'parent_type' => 'Master Menu'],
            ['name' => 'Import Member Details Read', 'parent_type' => 'Master Menu'],
            ['name' => 'Voucher', 'parent_type' => 'Master Menu'],
            ['name' => 'Voucher Add', 'parent_type' => 'Master Menu'],
            ['name' => 'Voucher Edit', 'parent_type' => 'Master Menu'],
            ['name' => 'Voucher Delete', 'parent_type' => 'Master Menu'],
            ['name' => 'Voucher Read', 'parent_type' => 'Master Menu'],
            ['name' => 'Company', 'parent_type' => 'Master Menu'],
            ['name' => 'Company Add', 'parent_type' => 'Master Menu'],
            ['name' => 'Company Edit', 'parent_type' => 'Master Menu'],
            ['name' => 'Company Delete', 'parent_type' => 'Master Menu'],
            ['name' => 'Company Read', 'parent_type' => 'Master Menu'],
            ['name' => 'Company View', 'parent_type' => 'Master Menu'],
            
            // Operation Module permissions
            ['name' => 'Schedule Settings', 'parent_type' => 'Operation Module'],
            ['name' => 'Schedule Settings Add', 'parent_type' => 'Operation Module'],
            ['name' => 'Schedule Settings Edit', 'parent_type' => 'Operation Module'],
            ['name' => 'Schedule Settings Delete', 'parent_type' => 'Operation Module'],
            ['name' => 'Schedule Settings Read', 'parent_type' => 'Operation Module'],
            ['name' => 'Book / View Appointment', 'parent_type' => 'Operation Module'],
            ['name' => 'Book Appointment', 'parent_type' => 'Operation Module'],
            ['name' => 'View Appointment', 'parent_type' => 'Operation Module'],
            ['name' => 'Patient Search', 'parent_type' => 'Operation Module'],
            ['name' => 'Patient Search Add', 'parent_type' => 'Operation Module'],
            ['name' => 'Patient Search Edit', 'parent_type' => 'Operation Module'],
            ['name' => 'Patient Search Delete', 'parent_type' => 'Operation Module'],
            ['name' => 'Patient Search Read', 'parent_type' => 'Operation Module'],
            
            // Reports permissions
            ['name' => 'Booking Reports', 'parent_type' => 'Reports'],
            ['name' => 'Booking Reports Add', 'parent_type' => 'Reports'],
            ['name' => 'Booking Reports Edit', 'parent_type' => 'Reports'],
            ['name' => 'Booking Reports Delete', 'parent_type' => 'Reports'],
            ['name' => 'Booking Reports Read', 'parent_type' => 'Reports'],
            ['name' => 'Booking List', 'parent_type' => 'Reports'], // Keep for backward compatibility
        ];

        // Get all teams to create permissions for each team
        $teams = Team::all();
        
        foreach ($teams as $team) {
            foreach ($permissions as $permissionData) {
                Permission::updateOrCreate(
                    [
                        'name' => $permissionData['name'],
                        'team_id' => $team->id,
                        'guard_name' => 'web',
                    ],
                    [
                        'parent_type' => $permissionData['parent_type'],
                        'guard_name' => 'web',
                    ]
                );
            }
        }
        
        // Also create permissions without team_id for global permissions (if needed)
        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                [
                    'name' => $permissionData['name'],
                    'team_id' => null,
                    'guard_name' => 'web',
                ],
                [
                    'parent_type' => $permissionData['parent_type'],
                    'guard_name' => 'web',
                ]
            );
        }
        
        $this->command->info('Permissions seeded successfully!');
    }
}
