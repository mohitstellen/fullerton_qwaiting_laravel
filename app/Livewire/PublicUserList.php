<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Traits\SendsEmails;

class PublicUserList extends Component
{
    use WithPagination, SendsEmails;

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
    public int $perPage = 25; // Number of records per page

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Patient Search')) {
            abort(403);
        }
        
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

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sendMemberEmail($memberId)
    {
        try {
            $member = Member::where('team_id', $this->teamId)
                ->findOrFail($memberId);

            if (!$member->email) {
                session()->flash('error', 'Member does not have an email address.');
                return;
            }

            // Generate new password
            $newPassword = $this->generateRandomPassword();

            // Update member password
            $member->password = $newPassword;
            $member->save();

            // Get company information
            $company = Company::find($member->company_id);

            // Prepare email data
            $emailData = [
                'to_mail' => $member->email,
                'member_name' => $member->full_name,
                'member_email' => $member->email,
                'member_mobile' => $member->full_mobile,
                'login_id' => $member->full_mobile,
                'password' => $newPassword,
                'company_name' => $company ? $company->company_name : 'N/A',
            ];

            // Send email using the trait
            $this->sendEmail($emailData, 'Your Account Credentials', 'member-info', $this->teamId);

            session()->flash('message', 'New credentials sent successfully to ' . $member->email);
        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to send email to member ' . $memberId . ': ' . $e->getMessage());
            session()->flash('error', 'Failed to send email. Please try again.');
        }
    }

    /**
     * Generate a random password
     * 
     * @return string
     */
    protected function generateRandomPassword($length = 8)
    {
        // Generate a secure random password with uppercase, lowercase, numbers, and special characters
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%&*';

        // Ensure at least one character from each set
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill the rest randomly
        $allCharacters = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        // Shuffle the password to randomize character positions
        return str_shuffle($password);
    }

    public function render()
    {
        $query = Member::where('team_id', $this->teamId);

        // if ($this->locationId) {
        //     $query->where('location_id', $this->locationId);
        // }

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
            $query->whereHas('company', function ($q) {
                $q->where('company_name', 'like', '%' . $this->searchCompany . '%');
            });
        }

        $members = $query->with('company')->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.public-user-list', [
            'members' => $members,
        ]);
    }
}
