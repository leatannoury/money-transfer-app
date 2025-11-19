<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'agent_id',
        'amount',
        'amount_usd',
        'currency',
        'status',
        'service_type',
        'payment_method',
        'fee_percent',
        'fee_amount_usd',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function agent()
    {
    return $this->belongsTo(User::class, 'agent_id');
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }
}
