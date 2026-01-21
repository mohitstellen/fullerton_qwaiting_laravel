<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PlatoAPIService
{
    protected $baseUrl;
    protected $token;
    protected $clinic;

    public function __construct()
    {
        $this->baseUrl = env('PLATO_API_BASE_URL', 'https://clinic.platomedical.com/api');
        $this->token = config('services.plato_api.token');
        $this->clinic = env('PLATO_API_CLINIC', 'drfullerton_trial2');
    }

    /**
     * Search for a patient by NRIC
     *
     * @param string $nric
     * @return \Illuminate\Http\Client\Response
     */
    public function searchPatient($nric)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ])->get($this->baseUrl . '/' . $this->clinic . '/search/patient', [
            'nric' => $nric
        ]);

        return $response;
    }

    /**
     * Create a new patient
     *
     * @param array $patientData
     * @return \Illuminate\Http\Client\Response
     */
    public function createPatient($patientData)
    {
        // Prepare the patient data with only basic fields
        $data = [
            'name' => $patientData['name'] ?? '',
            'nric' => $patientData['nric'] ?? '',
            'dob' => $patientData['dob'] ?? '',
            'marital_status' => $patientData['marital_status'] ?? '',
            'sex' => $patientData['sex'] ?? '',
            'nationality' => $patientData['nationality'] ?? '',
            // Optional fields - send empty if not provided
            'given_id' => $patientData['given_id'] ?? '',
            'nric_type' => $patientData['nric_type'] ?? '',
            'allergies_select' => $patientData['allergies_select'] ?? 'No',
            'allergies' => $patientData['allergies'] ?? '',
            'food_allergies_select' => $patientData['food_allergies_select'] ?? 'No',
            'food_allergies' => $patientData['food_allergies'] ?? '',
            'g6pd' => $patientData['g6pd'] ?? 'No',
            'telephone' => $patientData['telephone'] ?? '',
            'alerts' => $patientData['alerts'] ?? '',
            'address' => $patientData['address'] ?? '',
            'postal' => $patientData['postal'] ?? '',
            'unit_no' => $patientData['unit_no'] ?? '',
            'email' => $patientData['email'] ?? '',
            'telephone2' => $patientData['telephone2'] ?? '',
            'telephone3' => $patientData['telephone3'] ?? '',
            'title' => $patientData['title'] ?? '',
            'dnd' => $patientData['dnd'] ?? '0',
            'occupation' => $patientData['occupation'] ?? '',
            'doctor' => $patientData['doctor'] ?? [],
            'notes' => $patientData['notes'] ?? '',
            'custom' => $patientData['custom'] ?? (object)[],
            'referred_by' => $patientData['referred_by'] ?? '',
            'nok' => $patientData['nok'] ?? [],
            'tag' => $patientData['tag'] ?? [],
            'corporate' => $patientData['corporate'] ?? [],
            'other' => $patientData['other'] ?? (object)[],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/' . $this->clinic . '/patient', $data);

        return $response;
    }
}
