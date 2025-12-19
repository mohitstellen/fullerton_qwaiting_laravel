<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'company_name',
        'address',
        'billing_address',
        'is_billing_same_as_company',
        'remarks',
        'account_manager_id',
        'status',
        'ehs_appointments_per_year',
        'contact_person1_name',
        'contact_person1_phone',
        'contact_person1_email',
        'contact_person2_name',
        'contact_person2_phone',
        'contact_person2_email',
    ];

    public function companyPackages(): HasMany
    {
        return $this->hasMany(CompanyPackage::class);
    }

    public function companyAppointmentTypes(): HasMany
    {
        return $this->hasMany(CompanyAppointmentType::class);
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }
}
