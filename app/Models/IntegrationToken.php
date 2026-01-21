<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'refresh_token',
        'access_token',
        'expires_at',
    ];
}
