<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LicenseService
{
    protected $licensePath = 'license.json';
    protected $publicKeyPath = 'public.pem';
    protected $cachedData = null;
    protected $cachedTenantId = null;

    /**
     * Clear the cached license data
     */
    public function clearCache(): void
    {
        $this->cachedData = null;
        $this->cachedTenantId = null;
    }

    public function isValid(): bool
    {
        try {
            $currentTenantId = tenant('id');
            
            // If tenant context is not available yet, try to get it from authenticated user
            // This can happen right after login before tenant is fully initialized
            if (!$currentTenantId && auth()->check() && auth()->user()->team_id) {
                $currentTenantId = auth()->user()->team_id;
            }

            $data = $this->getLicenseData();
            if (!$data) {
                return false;
            }

            // Verify tenant ID matches current tenant
            if ($currentTenantId && isset($data['tenant_id'])) {
                if ((int)$data['tenant_id'] !== (int)$currentTenantId) {
                    // Clear cache if tenant mismatch detected
                    $this->clearCache();
                    \Illuminate\Support\Facades\Log::warning('LicenseService: Tenant ID mismatch', [
                        'license_tenant_id' => $data['tenant_id'],
                        'current_tenant_id' => $currentTenantId
                    ]);
                    return false;
                }
            } elseif (isset($data['tenant_id'])) {
                // If license has tenant_id but no current tenant context available
                // Try to get tenant from authenticated user as fallback
                if (auth()->check() && auth()->user()->team_id) {
                    $userTenantId = auth()->user()->team_id;
                    if ((int)$data['tenant_id'] !== (int)$userTenantId) {
                        return false;
                    }
                } else {
                    // No tenant context and no authenticated user - can't validate tenant
                    // Log warning but don't fail (tenant context might be initializing)
                    \Illuminate\Support\Facades\Log::warning('LicenseService: License has tenant_id but no tenant context available');
                    // Allow to proceed - will be validated on next request when tenant is ready
                }
            }

            // Verify expiration
            $expiresAt = Carbon::parse($data['expires_at'], 'UTC');
            if ($expiresAt->isPast()) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LicenseService validation error: ' . $e->getMessage());
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
        $currentTenantId = tenant('id') ?? (auth()->check() ? auth()->user()->team_id : null);
        
        // If tenant changed, clear cache
        if ($this->cachedData && $this->cachedTenantId !== $currentTenantId) {
            $this->clearCache();
        }

        // Return cached data if available and tenant matches
        if ($this->cachedData && $this->cachedTenantId === $currentTenantId) {
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
        $this->cachedTenantId = $currentTenantId;
        return $this->cachedData;
    }
}
