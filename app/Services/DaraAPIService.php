<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DaraAPIService
{
    protected $apiEndpoint;
    protected $accessToken;
    protected $organization;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->apiEndpoint = env('DARA_API_ENDPOINT');
        $this->accessToken = env('DARA_API_ACCESS_TOKEN');
        $this->organization = env('DARA_API_ORGANIZATION');
        $this->username = env('DARA_API_USERNAME');
        $this->password = env('DARA_API_PASSWORD');
    }

    public function getTokenPair()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->post($this->apiEndpoint . '/api/v1/token', [
            'organization' => $this->organization,
            'username' => $this->username,
            'password' => $this->password,
        ]);
        return $response;
    }

    public function refreshToken($refreshToken)
    {
        $response = Http::asForm()
            ->acceptJson()
            ->post($this->apiEndpoint . '/auth/v1/refreshtoken', [
                'refresh_token' => $refreshToken,
            ]);

        return $response;
    }

    public function createAppointment($apiAccessToken, $appointmentData)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiAccessToken,
            'Accept' => 'application/json'
        ])->post($this->apiEndpoint . '/api/v2/appointment', $appointmentData);
        return $response;
    }
}
