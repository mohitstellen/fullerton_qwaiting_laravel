<?php

require __DIR__ . '/vendor/autoload.php';

use Carbon\Carbon;

if ($argc < 5) {
    echo "Usage: php generate_license.php \"[ClientName]\" [TenantID] [Duration] [Unit: days/seconds]\n";
    exit(1);
}

$clientName = $argv[1];
$tenantId = (int) $argv[2];
$duration = (int) $argv[3];
$unit = strtolower($argv[4]);

if (!file_exists('private.pem')) {
    echo "Error: private.pem not found.\n";
    exit(1);
}

$privateKey = file_get_contents('private.pem');

$issuedAt = Carbon::now('UTC');
$expiresAt = null;

if ($unit === 'days') {
    $expiresAt = $issuedAt->copy()->addDays($duration)->endOfDay();
} elseif ($unit === 'seconds') {
    $expiresAt = $issuedAt->copy()->addSeconds($duration);
} else {
    echo "Invalid unit. Please use 'days' or 'seconds'.\n";
    exit(1);
}

$payload = [
    'client' => $clientName,
    'tenant_id' => $tenantId,
    'product' => 'Fullerton QWaiting',
    'issued_at' => $issuedAt->toDateTimeString(),
    'expires_at' => $expiresAt->toDateTimeString(),
    'machine_id' => gethostname()
];

$jsonPayload = json_encode($payload);
$base64Payload = base64_encode($jsonPayload);

$signature = '';
openssl_sign($base64Payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
$base64Signature = base64_encode($signature);

$licenseData = [
    'data' => $base64Payload,
    'signature' => $base64Signature
];

$licenseDir = __DIR__ . '/storage/app';
if (!is_dir($licenseDir)) {
    mkdir($licenseDir, 0755, true);
}

file_put_contents($licenseDir . '/license.json', json_encode($licenseData, JSON_PRETTY_PRINT));

echo "License generated successfully for {$clientName} (Tenant ID: {$tenantId}). Expires at {$expiresAt->toDateTimeString()}.\n";
echo "Saved to storage/app/license.json\n";
