<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip license check for login and authentication routes
        if ($request->routeIs('tenant.login', 'tenant.loginstore', 'tenant.authenticate', 'upload-license', 'upload-license.store')) {
            return $next($request);
        }

        $currentTenantId = tenant('id');
        
        // If tenant context is not available yet, try to get it from authenticated user
        if (!$currentTenantId && auth()->check() && auth()->user()->team_id) {
            $currentTenantId = auth()->user()->team_id;
        }

        \Illuminate\Support\Facades\Log::info('CheckLicense middleware executing for path: ' . $request->path(), [
            'tenant_id' => $currentTenantId,
            'user_id' => auth()->check() ? auth()->id() : null
        ]);

        $licenseService = app(\App\Services\LicenseService::class);
        
        // Clear cache if tenant context changed
        if ($currentTenantId) {
            $cachedTenantId = $licenseService->tenantId();
            if ($cachedTenantId && $cachedTenantId !== $currentTenantId) {
                $licenseService->clearCache();
                \Illuminate\Support\Facades\Log::info('License cache cleared due to tenant change', [
                    'old_tenant' => $cachedTenantId,
                    'new_tenant' => $currentTenantId
                ]);
            }
        }

        // If we still don't have tenant context and user is authenticated, 
        // allow the request to proceed (tenant context will be ready on next request)
        if (!$currentTenantId && auth()->check()) {
            \Illuminate\Support\Facades\Log::warning('CheckLicense: Tenant context not available yet, allowing request to proceed');
            return $next($request);
        }

        if (!$licenseService->isValid()) {
            \Illuminate\Support\Facades\Log::warning('License check failed in middleware', [
                'path' => $request->path(),
                'tenant_id' => $currentTenantId,
                'auth_check' => auth()->check(),
                'license_tenant_id' => $licenseService->tenantId()
            ]);

            if (auth()->check()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('tenant.login')->withErrors(['login' => 'Your license has expired. You have been logged out. Please contact the vendor.']);
            }

            // Non-authenticated user handling
            if ($request->expectsJson()) {
                return response()->json(['message' => 'License expired'], 403);
            }

            return response()->view('errors.license-expired', [], 403);
        }

        return $next($request);
    }
}
