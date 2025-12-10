<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\SiteDetail;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;

#[Title('Patient Dashboard')]
class PatientDashboard extends Component
{
    public $teamId;
    public $member;
    public $logo;

    public function mount()
    {
        // Check if patient is logged in
        if (!Session::has('patient_member_id')) {
            return redirect()->route('tenant.patient.login');
        }

        $this->teamId = tenant('id');
        $memberId = Session::get('patient_member_id');
        
        $this->member = Member::where('team_id', $this->teamId)
            ->where('id', $memberId)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->first();

        if (!$this->member) {
            Session::forget(['patient_member_id', 'patient_member', 'patient_customer_type']);
            return redirect()->route('patient.login')->with('error', 'Session expired. Please login again.');
        }

        // Get logo
        $this->logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId ?? null, $this->member->location_id ?? null);
    }

    public function logout()
    {
        Session::forget(['patient_member_id', 'patient_member', 'patient_customer_type']);
        Session::regenerate();
        return redirect()->route('patient.login');
    }

    public function render()
    {
        return view('livewire.patient-dashboard', [
            'logo' => $this->logo,
        ])->layout('components.layouts.patient-layout', [
            'logo' => $this->logo,
        ]);
    }
}
