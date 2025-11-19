<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use App\Models\ApiLog;
use App\Models\AccountSetting;
use Livewire\Attributes\Title;

class ApiLogReport extends Component
{
    use WithPagination;
    
    #[Title('API Logs')]   

    public $teamId;
    public $locationId;
    public $fromSelectedDate;
    public $toSelectedDate;
    public $datetimeFormat;
    public $statusFilter = 'all';
    public $searchTerm = '';

    protected $updatesQueryString = ['fromSelectedDate', 'toSelectedDate', 'statusFilter', 'searchTerm'];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
    
        $this->fromSelectedDate = $this->fromSelectedDate ?? date('Y-m-d');
        $this->toSelectedDate = $this->toSelectedDate ?? date('Y-m-d');
        $this->datetimeFormat = AccountSetting::showDateTimeFormat();
    }

    public function updatingFromSelectedDate()
    {
        $this->resetPage();
    }

    public function updatingToSelectedDate()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function getLogs()
    {
        $query = ApiLog::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->whereDate('created_at', '>=', $this->fromSelectedDate)
            ->whereDate('created_at', '<=', $this->toSelectedDate);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('api_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('api_url', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('booking_id', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('error_message', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.api-log-report', [
            'logs' => $this->getLogs()
        ]);
    }
}
