<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\SiteDetail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.patient-layout')]
#[Title('Change Password')]
class PatientChangePassword extends Component
{
    public $teamId;
    public $current_password = '';
    public $new_password = '';
    public $confirm_password = '';
    public $showCurrentPassword = false;
    public $showNewPassword = false;
    public $showConfirmPassword = false;

    protected $messages = [
        'current_password.required' => 'Current password is required.',
        'new_password.required' => 'New password is required.',
        'new_password.min' => 'New password must be at least 8 characters.',
        'confirm_password.required' => 'Please confirm your new password.',
        'confirm_password.same' => 'New password and confirmation do not match.',
    ];

    public $isTemporaryPassword = false;

    public function mount()
    {
        $this->teamId = tenant('id');
        
        // Check if user is logged in
        if (!Session::has('patient_member_id')) {
            return redirect()->route('tenant.patient.login');
        }

        // Check if user has temporary password
        $memberId = Session::get('patient_member_id');
        $member = Member::where('id', $memberId)
            ->where('team_id', $this->teamId)
            ->first();
        
        if ($member && $member->is_temporary_password) {
            $this->isTemporaryPassword = true;
        }
    }

    protected function rules()
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:new_password',
        ];
    }

    public function changePassword()
    {
        $this->validate();

        try {
            $memberId = Session::get('patient_member_id');
            $member = Member::where('id', $memberId)
                ->where('team_id', $this->teamId)
                ->where('is_active', 1)
                ->where('status', 'active')
                ->first();

            if (!$member) {
                session()->flash('error', 'Member not found. Please login again.');
                return redirect()->route('tenant.patient.login');
            }

            // Verify current password
            if (!Hash::check($this->current_password, $member->password)) {
                session()->flash('error', 'Current password is incorrect.');
                return;
            }

            // Update password and clear temporary flag
            $member->password = $this->new_password;
            $member->is_temporary_password = false;
            $member->save();

            session()->flash('success', 'Password changed successfully!');

            // Reset form
            $this->reset(['current_password', 'new_password', 'confirm_password']);

            // Redirect to dashboard after a short delay
            return redirect()->route('tenant.patient.dashboard');

        } catch (\Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            session()->flash('error', 'Failed to change password. Please try again.');
        }
    }

    public function toggleCurrentPassword()
    {
        $this->showCurrentPassword = !$this->showCurrentPassword;
    }

    public function toggleNewPassword()
    {
        $this->showNewPassword = !$this->showNewPassword;
    }

    public function toggleConfirmPassword()
    {
        $this->showConfirmPassword = !$this->showConfirmPassword;
    }

    public function render()
    {
        // Get logo for layout
        $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId ?? null, null);

        return view('livewire.patient-change-password')->layout('components.layouts.patient-layout', [
            'logo' => $logo,
        ]);
    }
}

