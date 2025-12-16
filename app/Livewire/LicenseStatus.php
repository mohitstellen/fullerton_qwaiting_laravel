<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\LicenseService;

class LicenseStatus extends Component
{
    public function checkLicense(LicenseService $licenseService)
    {
        if (!$licenseService->isValid()) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                \Illuminate\Support\Facades\Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
            }
            $errors = new \Illuminate\Support\MessageBag(['login' => 'Your license has expired. You have been logged out. Please contact the vendor.']);
            session()->flash('errors', (new \Illuminate\Support\ViewErrorBag)->put('default', $errors));

            return redirect()->route('tenant.login');
        }
    }

    public function render(LicenseService $licenseService)
    {
        $expiresAt = \Carbon\Carbon::parse($licenseService->expiresAt(), 'UTC');
        return view('livewire.license-status', [
            'days' => $licenseService->daysLeft(),
            'remainingSeconds' => \Carbon\Carbon::now('UTC')->diffInSeconds($expiresAt, false),
            'expiryTimestamp' => $expiresAt->timestamp,
            'client' => $licenseService->client(),
            'expiresAt' => $licenseService->expiresAt(),
            'isValid' => $licenseService->isValid(),
        ]);
    }
}
