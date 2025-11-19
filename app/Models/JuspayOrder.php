<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JuspayOrder extends Model
{
    protected $table = 'juspay_orders';

    protected $fillable = [
        'order_id',
        'transaction_id',
        'booking_id',
        'team_id',
        'location_id',
        'customer_id',
        'customer_email',
        'customer_phone',
        'amount',
        'currency',
        'status',
        'payment_url',
        'response_json',
    ];

    protected $casts = [
        'response_json' => 'array',
        'amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
