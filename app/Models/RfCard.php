<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfCard extends Model
{
    use HasFactory;

    protected $table = 'rf_cards';

    protected $fillable = [
        'team_id',
        'rfcard',
        'username',
        'name',
        'contact',
        'email'
    ];

    public function team()
 {
        return $this->belongsTo(Tenant::class );
    }



}
