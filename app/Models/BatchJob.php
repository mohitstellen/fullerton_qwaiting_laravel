<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchJob extends Model
{
    protected $fillable = [
        'job_name',
        'total_added',
        'total_updated',
        'total_records',
        'run_time',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'run_time' => 'decimal:2',
    ];
}
