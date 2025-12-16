<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LicenseService
{
    protected $licensePath = 'license.json';
    protected $publicKeyPath = 'public.pem';
    protected $cachedData = null;

    public function isValid(): bool
    {
        try {
            $data = $this->getLicenseData();
            if (!$data) {
                return false;
            }

            // Verify tenant ID matches current tenant
            $currentTenantId = tenant('id');
            if ($currentTenantId && isset($data['tenant_id'])) {
                if ((int)$data['tenant_id'] !== (int)$currentTenantId) {
                    return false;
                }
            } elseif (isset($data['tenant_id'])) {
                // If license has tenant_id but no current tenant, invalid
                return false;
            }

            // Verify expiration
            $expiresAt = Carbon::parse($data['expires_at'], 'UTC');
            if ($expiresAt->isPast()) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function daysLeft(): int
    {
        try {
            $data = $this->getLicenseData();
            if (!$data) {
                return 0;
            }

            $expiresAt = Carbon::parse($data['expires_at'], 'UTC');
            return (int) Carbon::now()->diffInDays($expiresAt, false);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function client(): string
    {
        $data = $this->getLicenseData();
        return $data['client'] ?? 'Unknown';
    }

    public function tenantId(): ?int
    {
        $data = $this->getLicenseData();
        return isset($data['tenant_id']) ? (int)$data['tenant_id'] : null;
    }

    public function expiresAt(): string
    {
        $data = $this->getLicenseData();
        return $data['expires_at'] ?? 'Unknown';
    }

    protected function getLicenseData()
    {
        if ($this->cachedData) {
            return $this->cachedData;
        }

        $path = base_path('storage/app/' . $this->licensePath);

        if (!file_exists($path)) {
            return null;
        }

        $content = json_decode(file_get_contents($path), true);
        if (!$content || !isset($content['data']) || !isset($content['signature'])) {
            return null;
        }

        $publicKey = file_get_contents(base_path($this->publicKeyPath));
        if (!$publicKey) {
            return null;
        }

        $result = openssl_verify(
            $content['data'],
            base64_decode($content['signature']),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($result !== 1) {
            return null;
        }

        $this->cachedData = json_decode(base64_decode($content['data']), true);
        return $this->cachedData;
    }
}
