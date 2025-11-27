<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAppointmentType extends Model
{
    protected $fillable = [
        'company_id',
        'appointment_type_id',
        'valid_from',
        'valid_to',
        'applicable_for',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    /**
     * Get the company that owns the appointment type.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the appointment type (category).
     */
    public function appointmentType()
    {
        return $this->belongsTo(Category::class, 'appointment_type_id');
    }
}
