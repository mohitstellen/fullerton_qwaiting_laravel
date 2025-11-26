<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPackage extends Model
{
    protected $table = 'company_packages';

    protected $fillable = [
        'company_id',
        'appointment_type_id',
        'package_id',
        'modes_of_identification',
        'clinic_ids',
        'remarks',
    ];

    protected $casts = [
        'modes_of_identification' => 'array',
        'clinic_ids' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'appointment_type_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'package_id');
    }
}
