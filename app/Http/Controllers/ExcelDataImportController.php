<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExcelImportService;
use Illuminate\Support\Facades\Log;

class ExcelDataImportController extends Controller
{
    protected $excelImportService;

    public function __construct(ExcelImportService $excelImportService)
    {
        $this->excelImportService = $excelImportService;
    }

    public function index()
    {
        return view('tenant.import.index');
    }

    public function importCategories(Request $request)
    {
        $request->validate([
            'category_file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $this->excelImportService->importCategories($request->file('category_file'));
            return redirect()->back()->with('success', 'Categories imported successfully successfully.');
        } catch (\Exception $e) {
            Log::error('Category Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error importing categories: ' . $e->getMessage());
        }
    }

    public function importLocations(Request $request)
    {
        $request->validate([
            'location_file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $this->excelImportService->importLocations($request->file('location_file'));
            return redirect()->back()->with('success', 'Locations imported successfully.');
        } catch (\Exception $e) {
            Log::error('Location Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error importing locations: ' . $e->getMessage());
        }
    }
}
