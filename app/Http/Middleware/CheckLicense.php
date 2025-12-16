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
        \Illuminate\Support\Facades\Log::info('CheckLicense middleware executing for path: ' . $request->path());
        $licenseService = app(\App\Services\LicenseService::class);

        if (!$licenseService->isValid()) {
            \Illuminate\Support\Facades\Log::info('License check failed in middleware. Auth check: ' . (auth()->check() ? 'true' : 'false'));

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
