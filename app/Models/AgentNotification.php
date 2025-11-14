<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentNotification extends Model
{
    protected $fillable = [
        'agent_id',
        'transaction_id',
        'title',
        'message',
        'is_read',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
