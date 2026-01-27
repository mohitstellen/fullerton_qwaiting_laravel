<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Country;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Livewire\Attributes\On;

class CountryManager extends Component
{
    use WithPagination;

    public $teamId, $location;
    public $allcountries;
    public $select_countryId;
    public $countryId;
    public $countryName;
    public $countryCode;
    public $mobileLength;
    public $showcountryModel = false;
    public $showLogsModal = false;
    public $activityLogs = [];
    public $selectedCountryForLogs = null;
    public $perPage = 25; // number of records per page
    public $userAuth;
    public $searchCode = '';
    public $searchCountryName = '';

    protected $paginationTheme = 'tailwind'; // works well with Tailwind

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Country')) {
            abort(403);
        }
        
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->allcountries = Country::orderBy('name')->get();
        $this->userAuth = Auth::user();

        // code for add all country code
        // if($this->teamId == 268){
        //      $this->addedallcode();
        // }

    }

    // public function addedallcode(){
    //     foreach($this->allcountries as $country){
    //          AllowedCountry::updateOrCreate(
    //         ['country_id' => $country->id,'team_id'=> $this->teamId,'location_id'=> $this->location],
    //         [
    //             'name'       => $country->name,
    //             'iso_code'   => strtoupper($country->code),
    //             'phone_code' => $country->phonecode,
    //         ]);
    //     }
    // }

    public function updatingSearchCode()
    {
        $this->resetPage();
    }

    public function updatingSearchCountryName()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage(); // Reset pagination when per page changes
    }

    private function countriesQuery()
    {
        return Country::whereNotNull('name')
            ->when($this->searchCode, function ($query) {
                $query->where(function ($q) {
                    $q->where('phonecode', 'like', '%' . $this->searchCode . '%');
                });
            })
            ->when($this->searchCountryName, function ($query) {
                $query->where('name', 'like', '%' . $this->searchCountryName . '%');
            })
            ->orderBy('name', 'asc');
    }

    public function openAddModal(): void
    {
        $this->reset(['countryId', 'select_countryId', 'countryName', 'countryCode', 'mobileLength']);
        $this->showcountryModel = true;
    }

    public function openEditModal(int $id): void
    {
        $country = Country::findOrFail($id);
        $this->countryId = $country->id;
        $this->select_countryId = $country->id;
        $this->countryName = $country->name;
        $this->countryCode = $country->country_code;
        $this->mobileLength = $country->mobile_length;
        $this->showcountryModel = true;
    }

    public function save(): void
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Country')) {
            abort(403);
        }
        
        $this->validate([
            'countryName' => 'required|string|max:255',
            'countryCode' => 'required|string|max:10',
            'mobileLength' => 'required|integer|min:1|max:20',
        ], [
            'countryName.required' => 'Country name is required.',
            'countryName.string' => 'Country name must be a valid text.',
            'countryCode.required' => 'Country code is required.',
            'mobileLength.required' => 'Mobile length is required.',
            'mobileLength.integer' => 'Mobile length must be a number.',
            'mobileLength.min' => 'Mobile length must be at least 1.',
            'mobileLength.max' => 'Mobile length cannot exceed 20.',
        ]);

        $isUpdate = !empty($this->countryId);
        
        if ($isUpdate) {
            // For update, use the existing country
            $country = Country::findOrFail($this->countryId);
            $countryName = $country->name;
            
            // Check if country name is being changed and if it conflicts with another country
            if ($country->name !== $this->countryName) {
                $nameExists = Country::where('name', $this->countryName)
                    ->where('id', '!=', $country->id)
                    ->exists();
                
                if ($nameExists) {
                    $this->dispatch('alert', type: 'error', message: 'This country name already exists!');
                    return;
                }
            }
            
            // Check if country code is being changed and if it conflicts with another country
            if ($country->country_code !== $this->countryCode) {
                $codeExists = Country::where('phonecode', $this->countryCode)
                    ->where('id', '!=', $country->id)
                    ->exists();
                
                if ($codeExists) {
                    $this->dispatch('alert', type: 'error', message: 'This country code is already used!');
                    return;
                }
            }
            
            // Update the country
            $country->update([
                'name' => $this->countryName,
                'phonecode' => $this->countryCode,
                'mobile_length' => $this->mobileLength,
            ]);
        } else {
            // For new entry, check if country name or code already exists
            $nameExists = Country::where('name', $this->countryName)->exists();
            $codeExists = Country::where('phonecode', $this->countryCode)->exists();
            
            if ($nameExists) {
                $this->dispatch('alert', type: 'error', message: 'This country name already exists!');
                return;
            }
            
            if ($codeExists) {
                $this->dispatch('alert', type: 'error', message: 'This country code is already used!');
                return;
            }
            
            // Create new country
            $country = Country::create([
                'name' => $this->countryName,
                'code' => strtoupper(substr($this->countryName, 0, 2)), // Auto-generate code from name
                'phonecode' => '', // Will need to be set separately
                'phonecode' => $this->countryCode,
                'mobile_length' => $this->mobileLength,
            ]);
            $countryName = $this->countryName;
        }

        // Store activity log
        $actionText = $isUpdate ? ActivityLog::EDIT : ActivityLog::ADD;
        $logText = $isUpdate 
            ? "Country Updated"
            : "Country Added";
        
        ActivityLog::storeLog(
            $this->teamId, 
            $this->userAuth->id ?? null, 
            null, 
            null, 
            $logText, 
            $this->location, 
            ActivityLog::SETTINGS, 
            null, 
            $this->userAuth ?? null,
            $country->id
        );

        $this->reset(['showcountryModel', 'select_countryId', 'countryId', 'countryName', 'countryCode', 'mobileLength']);
        $this->dispatch('alert', type: 'success', message: $isUpdate ? 'Country Updated!' : 'Country Added!');
        $this->resetPage(); // reset pagination after save
    }

    public function viewLogs(int $id): void
    {
        $this->selectedCountryForLogs = Country::findOrFail($id);
        $this->activityLogs = ActivityLog::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('country_id', $id)
            ->where('type', ActivityLog::SETTINGS)
            ->with('createdBy')
            ->latest()
            ->get();
        $this->showLogsModal = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->countryId = $id;
        $this->dispatch('confirmDelete');
    }

    #[On('delete')]
    public function delete(): void
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Country')) {
            abort(403);
        }
        
        $country = Country::find($this->countryId);
        
        if ($country) {
            $countryName = $country->name;
            $countryCode = $country->country_code;
            $mobileLength = $country->mobile_length;
            
            // Clear country_code and mobile_length instead of deleting the country
            $country->update([
                'phonecode' => null,
                'mobile_length' => null,
            ]);
            
            // Store activity log
            $logText = "Country Deleted: {$countryName} (Code: {$countryCode}, Mobile Length: {$mobileLength})";
            ActivityLog::storeLog(
                $this->teamId, 
                $this->userAuth->id ?? null, 
                null, 
                null, 
                $logText, 
                $this->location, 
                ActivityLog::SETTINGS, 
                null, 
                $this->userAuth ?? null,
                $this->countryId
            );
        }
        
        $this->reset(['countryId', 'select_countryId', 'countryName', 'countryCode', 'mobileLength']);
        $this->dispatch('alert', type: 'success', message: 'Country Deleted!');
        $this->resetPage(); // reset pagination after delete
    }

    public function exportCSV()
    {
        $query = $this->countriesQuery();
        $countries = $query->get();

        $csvData = [];
        $csvData[] = [
            'S.No',
            'Country (Code)',
            'Country Code',
            'Mobile Length',
            'Created',
        ];

        foreach ($countries as $index => $country) {
            $csvData[] = [
                $index + 1,
                $country->name . ' (+' . $country->phonecode . ')',
                $country->phonecode ?? '-',
                $country->mobile_length ?? '-',
            ];
        }

        $filename = 'country_master_export_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // Add BOM for UTF-8
        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return Response::streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        $countries = $this->countriesQuery()->paginate($this->perPage);

        return view('livewire.country-manager', [
            'countries' => $countries,
        ]);
    }
}
