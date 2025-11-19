<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'team_id',
        'location_id',
        'booking_id',
        'api_name',
        'api_url',
        'method',
        'request_headers',
        'request_data',
        'response_data',
        'http_code',
        'status',
        'error_message',
    ];

    /**
     * Create a new API log entry
     */
    public static function logApiCall($data)
    {
        $requestHeaders = $data['request_headers'] ?? null;
        $requestData = $data['request_data'] ?? null;
        $responseData = $data['response_data'] ?? null;
        
        return self::create([
            'team_id' => $data['team_id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'booking_id' => $data['booking_id'] ?? null,
            'api_name' => $data['api_name'] ?? null,
            'api_url' => $data['api_url'] ?? null,
            'method' => $data['method'] ?? 'POST',
            'request_headers' => is_array($requestHeaders) ? json_encode($requestHeaders) : $requestHeaders,
            'request_data' => is_array($requestData) ? json_encode($requestData) : $requestData,
            'response_data' => is_array($responseData) ? json_encode($responseData) : $responseData,
            'http_code' => $data['http_code'] ?? null,
            'status' => $data['status'] ?? 'success',
            'error_message' => $data['error_message'] ?? null,
        ]);
    }

    /**
     * Relationship with booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
