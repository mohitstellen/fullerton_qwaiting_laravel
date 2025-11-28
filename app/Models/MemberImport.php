<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberImport extends Model
{
    protected $fillable = [
        'team_id',
        'company_id',
        'file_name',
        'created_by',
        'created_date_time',
        'imported_date_time',
        'status',
        'enforce_password_change',
    ];

    protected $casts = [
        'created_date_time' => 'datetime',
        'imported_date_time' => 'datetime',
        'enforce_password_change' => 'boolean',
    ];

    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 3;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
