<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LicenseUploadController extends Controller
{
    public function show()
    {
        return view('license-upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'licenseFile' => 'required|file|mimes:json',
        ]);

        try {
            $file = $request->file('licenseFile');

            // Read content directly
            $content = file_get_contents($file->getRealPath());
            $json = json_decode($content, true);

            // Validate JSON structure
            if (!$json || !isset($json['data']) || !isset($json['signature'])) {
                return redirect()->back()->withErrors(['licenseFile' => 'Invalid license file format.']);
            }

            // Decode and validate license expiration
            $licenseData = json_decode(base64_decode($json['data']), true);

            if (!$licenseData || !isset($licenseData['expires_at'])) {
                return redirect()->back()->withErrors(['licenseFile' => 'The key is invalid.']);
            }

            // Validate tenant ID matches current tenant
            $currentTenantId = tenant('id');
            if ($currentTenantId && isset($licenseData['tenant_id'])) {
                if ((int)$licenseData['tenant_id'] !== (int)$currentTenantId) {
                    return redirect()->back()->withErrors(['licenseFile' => 'This license key is not valid for your tenant.']);
                }
            } elseif (isset($licenseData['tenant_id'])) {
                // License has tenant_id but no current tenant context
                return redirect()->back()->withErrors(['licenseFile' => 'The key is invalid.']);
            }

            // Check if the license is expired
            $expiresAt = \Carbon\Carbon::parse($licenseData['expires_at'], 'UTC');
            if ($expiresAt->isPast()) {
                return redirect()->back()->withErrors(['licenseFile' => 'The key is invalid.']);
            }

            $licensePath = base_path('storage/app/license.json');

            // Delete old license file if exists
            if (file_exists($licensePath)) {
                unlink($licensePath);
            }

            // Write new license file directly to storage/app/license.json
            file_put_contents($licensePath, $content);

            return redirect()->route('tenant.login')->with('success', 'License key is activated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['licenseFile' => 'Failed to upload license: ' . $e->getMessage()]);
        }
    }
}
