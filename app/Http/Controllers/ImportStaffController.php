<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ImportStaffController extends Controller
{
   

    public function index()
    {
        // Verify authentication and tenant context
      

        $teamId = tenant('id') ?? Auth::user()->team_id;
        
        if (!$teamId) {
            Log::error('Import staff page accessed without team context', [
                'user_id' => Auth::id(),
                'tenant_id' => tenant('id'),
                'user_team_id' => Auth::user()->team_id ?? 'null'
            ]);
        }

        return view('import-staff', ['teamId' => $teamId]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Import staff request received');
            
            $request->validate([
                'file' => 'required|mimes:csv,xlsx,xls|max:10240',
            ]);

            Log::info('File validation passed', ['filename' => $request->file('file')->getClientOriginalName()]);

            // Get team_id with multiple fallback methods
            $teamId = null;
            
            // Try tenant helper first
            if (function_exists('tenant') && tenant('id')) {
                $teamId = tenant('id');
                Log::info('Team ID from tenant helper', ['team_id' => $teamId]);
            }
            
            // Try authenticated user
            if (!$teamId && Auth::check() && Auth::user()->team_id) {
                $teamId = Auth::user()->team_id;
                Log::info('Team ID from auth user', ['team_id' => $teamId]);
            }
            
            // Try session
            if (!$teamId && session('tenant_id')) {
                $teamId = session('tenant_id');
                Log::info('Team ID from session', ['team_id' => $teamId]);
            }

            Log::info('Team ID determined', [
                'team_id' => $teamId,
                'tenant_id' => tenant('id'),
                'auth_user_id' => Auth::id(),
                'auth_user_team_id' => Auth::check() ? Auth::user()->team_id : null,
                'session_tenant_id' => session('tenant_id')
            ]);

            if (!$teamId) {
                Log::error('Team ID not found');
                return back()->with('error', 'Unable to determine team ID. Please ensure you are logged in and have proper tenant context.');
            }

            Log::info('Starting import process');
            $import = new UsersImport($teamId);
            Excel::import($import, $request->file('file'));

            Log::info('Import completed', [
                'imported' => $import->importedCount,
                'failed' => $import->failedCount,
                'failures' => $import->failures
            ]);

            $message = "Import processing complete. Imported: {$import->importedCount}, Failed: {$import->failedCount}.";

            if ($import->failedCount > 0) {
                return back()->with('success', $message)
                             ->with('import_failures', $import->failures);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('File validation error', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            Log::error('Excel validation error during import: ' . implode(' | ', $errorMessages));
            return back()->with('error', 'Validation errors occurred during import. Please check your file format.')
                         ->with('validation_errors', $errorMessages);

        } catch (\Exception $e) {
            Log::error('Error during import', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }
}
