<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'location_id',
        'identification_type',
        'nric_fin',
        'passport',
        'salutation',
        'full_name',
        'date_of_birth',
        'gender',
        'mobile_country_code',
        'mobile_number',
        'email',
        'status',
        'nationality',
        'company_id',
        'customer_type',
        'relationship',
        'primary_id',
        'password',
        'is_temporary_password',
        'created_by',
        'approved_by',
        'approved_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'approved_at' => 'datetime',
        'is_active' => 'boolean',
        'is_temporary_password' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'team_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Mutator for password hashing
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where(function($q) {
            $q->where('is_active', 0)
              ->orWhere('status', 'inactive');
        });
    }

    // Helper methods
    public function getFullMobileAttribute()
    {
        return '+' . $this->mobile_country_code . $this->mobile_number;
    }

    public function getDisplayNricFinAttribute()
    {
        if (!$this->nric_fin) {
            return null;
        }
        
        // Mask the middle characters (e.g., T1234567A -> T******7A)
        $nric = $this->nric_fin;
        if (strlen($nric) > 4) {
            $first = substr($nric, 0, 1);
            $last = substr($nric, -3);
            return $first . str_repeat('*', strlen($nric) - 4) . $last;
        }
        
        return $nric;
    }
}




