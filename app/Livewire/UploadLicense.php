<?php

namespace App\Livewire;

use Livewire\Component;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class UploadLicense extends Component
{
    use WithFileUploads;

    public $licenseFile;

    protected $rules = [
        'licenseFile' => 'required|file|mimes:json',
    ];

    public function upload()
    {
        $this->validate();

        try {
            // Get the temporary file path
            $tempPath = $this->licenseFile->getRealPath();

            // Read content directly from temp file
            $content = file_get_contents($tempPath);
            $json = json_decode($content, true);

            // Validate JSON structure
            if (!$json || !isset($json['data']) || !isset($json['signature'])) {
                $this->addError('licenseFile', 'Invalid license file format.');
                return;
            }

            // Decode and validate license data
            $licenseData = json_decode(base64_decode($json['data']), true);

            if (!$licenseData || !isset($licenseData['expires_at'])) {
                $this->addError('licenseFile', 'The key is invalid.');
                return;
            }

            // Validate tenant ID matches current tenant
            $currentTenantId = tenant('id');
            if ($currentTenantId && isset($licenseData['tenant_id'])) {
                if ((int)$licenseData['tenant_id'] !== (int)$currentTenantId) {
                    $this->addError('licenseFile', 'This license key is not valid for your tenant.');
                    return;
                }
            } elseif (isset($licenseData['tenant_id'])) {
                // License has tenant_id but no current tenant context
                $this->addError('licenseFile', 'The key is invalid.');
                return;
            }

            // Check if the license is expired
            $expiresAt = \Carbon\Carbon::parse($licenseData['expires_at'], 'UTC');
            if ($expiresAt->isPast()) {
                $this->addError('licenseFile', 'The key is invalid.');
                return;
            }

            $licensePath = base_path('storage/app/license.json');

            // Delete old license file if exists
            if (file_exists($licensePath)) {
                unlink($licensePath);
            }

            // Write new license file directly to storage/app/license.json
            file_put_contents($licensePath, $content);

            // Clean up the temporary file immediately
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            // Clean up old livewire temp files (older than 1 hour)
            $this->cleanupLivewireTempFiles();

            // Reset the file property
            $this->reset('licenseFile');

            session()->flash('success', 'License key is activated successfully.');

            return redirect()->route('tenant.login');

        } catch (\Exception $e) {
            $this->addError('licenseFile', 'Failed to upload license: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old temporary files from livewire-tmp directory
     */
    private function cleanupLivewireTempFiles()
    {
        $tempDir = storage_path('app/private/livewire-tmp');

        if (!is_dir($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                // Delete files older than 1 hour
                if ($now - filemtime($file) >= 3600) {
                    @unlink($file);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.upload-license');
    }
}
