<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PublicUserList extends Component
{
    use WithPagination;

    #[Title('Patient Search')]

    public $teamId;
    public $locationId;
    public $activeTab = 'active'; // 'active' or 'inactive'
    
    // Search filters
    public $searchNric = '';
    public $searchMobile = '';
    public $searchName = '';
    public $searchEmail = '';
    public $searchCompany = '';

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function search()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->searchNric = '';
        $this->searchMobile = '';
        $this->searchName = '';
        $this->searchEmail = '';
        $this->searchCompany = '';
        $this->resetPage();
    }

    public function sendEmail($memberId)
    {
        $member = Member::findOrFail($memberId);
        
        if (!$member->email) {
            session()->flash('error', 'Member does not have an email address.');
            return;
        }

        // Dispatch event to send email - we'll handle this in the form component
        $this->dispatch('send-member-email', memberId: $memberId);
        
        session()->flash('message', 'Email sent successfully.');
    }

    public function render()
    {
        $query = Member::where('team_id', $this->teamId);
        
        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }

        // Apply tab filter
        if ($this->activeTab === 'active') {
            $query->where('is_active', 1);
        } else {
            $query->where('is_active', 0);
        }

        // Apply search filters
        if (!empty($this->searchNric)) {
            $query->where('nric_fin', 'like', '%' . $this->searchNric . '%');
        }

        if (!empty($this->searchMobile)) {
            $query->where('mobile_number', 'like', '%' . $this->searchMobile . '%');
        }

        if (!empty($this->searchName)) {
            $query->where('full_name', 'like', '%' . $this->searchName . '%');
        }

        if (!empty($this->searchEmail)) {
            $query->where('email', 'like', '%' . $this->searchEmail . '%');
        }

        if (!empty($this->searchCompany)) {
            $query->whereHas('company', function($q) {
                $q->where('company_name', 'like', '%' . $this->searchCompany . '%');
            });
        }

        $members = $query->with('company')->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.public-user-list', [
            'members' => $members,
        ]);
    }
}

