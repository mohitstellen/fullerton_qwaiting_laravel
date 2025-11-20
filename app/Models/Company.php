<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
