<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Domain extends Model
{


    protected $fillable = ['domain', 'team_id','hold_queue_feature','enable_location_page','expired','stripe_id','stripe_email','pm_type','pm_last_four','trial_ends_at'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'team_id');
    }

    public function isExpiringSoon(): bool
    {
        if (!$this->expired) {
            return false;
        }

        $expiry = Carbon::parse($this->expired);
        return now()->diffInDays($expiry, false) <= 7 && $expiry->isFuture();
    }
}
