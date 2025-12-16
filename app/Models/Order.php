<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'team_id',
        'member_id',
        'order_number',
        'status',
        'total_amount',
        'gst_amount',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'team_id', 'id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_order', 'order_id', 'booking_id')
            ->withTimestamps();
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }
}
