<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'voucher_name',
        'voucher_code',
        'valid_from',
        'valid_to',
        'discount_percentage',
        'no_of_redemption',
        'category_id',
        'card_types',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'discount_percentage' => 'decimal:2',
        'no_of_redemption' => 'integer',
        'card_types' => 'array',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
