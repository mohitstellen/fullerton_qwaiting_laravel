<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Country;
use App\Models\AllowedCountry;
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
    public $countryCode;
    public $mobileLength;
    public $showcountryModel = false;
    public $showLogsModal = false;
    public $activityLogs = [];
    public $selectedCountryForLogs = null;
    public $perPage = 10; // number of records per page
    public $userAuth;
    public $searchCode = '';
    public $searchCountryName = '';

    protected $paginationTheme = 'tailwind'; // works well with Tailwind

    public function mount()
    {
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

    private function countriesQuery()
    {
        return AllowedCountry::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->when($this->searchCode, function ($query) {
                $query->where(function ($q) {
                    $q->where('country_code', 'like', '%' . $this->searchCode . '%')
                      ->orWhere('phone_code', 'like', '%' . $this->searchCode . '%');
                });
            })
            ->when($this->searchCountryName, function ($query) {
                $query->where('name', 'like', '%' . $this->searchCountryName . '%');
            })
            ->latest();
    }

    public function openAddModal(): void
    {
        $this->reset(['countryId', 'select_countryId', 'countryCode', 'mobileLength']);
        $this->showcountryModel = true;
    }

    public function openEditModal(int $id): void
    {
        $country = AllowedCountry::findOrFail($id);
        $this->countryId = $country->id;
        $this->select_countryId = $country->country_id;
        $this->countryCode = $country->country_code;
        $this->mobileLength = $country->mobile_length;
        $this->showcountryModel = true;
    }

    public function save(): void
    {
        $this->validate([
            'select_countryId' => 'required',
            'countryCode' => 'required|string|max:10',
            'mobileLength' => 'required|integer|min:1|max:20',
        ], [
            'select_countryId.required' => 'Please select a country.',
            'countryCode.required' => 'Country code is required.',
            'mobileLength.required' => 'Mobile length is required.',
            'mobileLength.integer' => 'Mobile length must be a number.',
            'mobileLength.min' => 'Mobile length must be at least 1.',
            'mobileLength.max' => 'Mobile length cannot exceed 20.',
        ]);

        $exists = $this->countriesQuery()
            ->where('country_id', $this->select_countryId)
            ->when($this->countryId, fn($q) => $q->where('id', '!=', $this->countryId))
            ->exists();

        if ($exists) {
            $this->dispatch('alert', type: 'error', message: 'This country is already added!');
            return;
        }

        $data = Country::findOrFail($this->select_countryId);

        $isUpdate = !empty($this->countryId);
        $countryName = $data->name;

        $allowedCountry = AllowedCountry::updateOrCreate(
            ['id' => $this->countryId,'team_id'=> $this->teamId,'location_id'=> $this->location],
            [
                'country_id' => $data->id,
                'name'       => $data->name,
                'iso_code'   => strtoupper($data->code),
                'phone_code' => $data->phonecode,
                'country_code' => $this->countryCode,
                'mobile_length' => $this->mobileLength,
            ]
        );

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
            $allowedCountry->id
        );

        $this->reset(['showcountryModel', 'select_countryId', 'countryId', 'countryCode', 'mobileLength']);
        $this->dispatch('alert', type: 'success', message: $isUpdate ? 'Country Updated!' : 'Country Added!');
        $this->resetPage(); // reset pagination after save
    }

    public function viewLogs(int $id): void
    {
        $this->selectedCountryForLogs = AllowedCountry::findOrFail($id);
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
        $country = AllowedCountry::find($this->countryId);
        
        if ($country) {
            $countryName = $country->name;
            $countryCode = $country->country_code;
            $mobileLength = $country->mobile_length;
            
            $country->delete();
            
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
        
        $this->reset(['countryId', 'select_countryId', 'countryCode', 'mobileLength']);
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
                $country->name . ' (+' . $country->phone_code . ')',
                $country->country_code ?? '-',
                $country->mobile_length ?? '-',
                $country->created_at->format('Y-m-d H:i:s'),
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
