<?php

namespace App\Listeners;

use App\Services\LicenseService;
use Stancl\Tenancy\Events\TenancyInitialized;
use Stancl\Tenancy\Events\TenancyEnded;

class ClearLicenseCache
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $licenseService = app(LicenseService::class);
        $licenseService->clearCache();
    }
}

