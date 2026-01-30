<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UsersImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $failedCount = 0;
    public $failures = [];
    protected $teamId;

    public function __construct($teamId = null)
    {
        $this->teamId = $teamId ?? tenant('id');
    }

    public function collection(Collection $rows)
    {
        \Log::info('UsersImport collection method called', [
            'team_id' => $this->teamId,
            'row_count' => $rows->count()
        ]);

        if (!$this->teamId) {
            throw new \Exception('Team ID is required for importing users.');
        }

        foreach ($rows as $index => $row) {
            $rowIndex = $index + 2; // +1 for 0-index, +1 for header row

            \Log::info('Processing row', ['row' => $rowIndex, 'data' => $row->toArray()]);

            try {
                DB::beginTransaction();

                $teamId = $this->teamId;

                // Validate required fields
                if (empty($row['loginname']) || empty($row['email'])) {
                    throw new \Exception('Login name and email are required fields.');
                }

                // Role Handling
                $roleName = $row['rolename'] ?? 'Staff'; // Default role if not provided
                $role = Role::firstOrCreate(
                    [
                        'name'       => $roleName, 
                        'guard_name' => 'web',
                        'team_id'    => $teamId
                    ],
                    [
                        'name'       => $roleName,
                        'guard_name' => 'web',
                        'team_id'    => $teamId
                    ]
                ); 

                // Check if user already exists
                $existingUser = User::where('email', $row['email'])
                    ->where('team_id', $teamId)
                    ->first();

                if ($existingUser) {
                    throw new \Exception("User with email '{$row['email']}' already exists.");
                }

                // Create User
                $user = new User();

                $user->fill([
                    'username' => $row['loginname'],
                    'name'     => $row['firstname'] ?? $row['loginname'],
                    'email'    => $row['email'],
                    'phone'    => $row['mobilenumber'] ?? null,
                    'gender'   => $row['gender'] ?? null,
                    'team_id'  => $teamId, 
                ]);
                $user->password = Hash::make('12345678'); 
                $user->role_id = $role->id;
                $user->save();

                // Assign Role (Spatie)
                $user->assignRole($role);

                DB::commit();
                $this->importedCount++;
                \Log::info('Row imported successfully', ['row' => $rowIndex]);

            } catch (\Exception $e) {
                DB::rollBack();
                $this->failedCount++;
                $this->failures[] = [
                    'row' => $rowIndex,
                    'errors' => [$e->getMessage()]
                ];
                \Log::error('Failed to import row', [
                    'row' => $rowIndex,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        \Log::info('Import collection complete', [
            'imported' => $this->importedCount,
            'failed' => $this->failedCount
        ]);
    }
}
