<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    protected $fillable = [
        'user_id',
        'beneficiary_user_id',
        'full_name',
        'payout_method',
        'account_number',
        'phone_number',
        'address',
        'notes',
    ];

    // Owner relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

