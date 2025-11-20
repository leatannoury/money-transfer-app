<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'store_name',
        'phone',
        'city',
        'commission',
        'status',
        'latitude',
        'longitude',
        'is_available',
        'work_start_time',
        'work_end_time',
        'timezone',
        'agent_request_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_available' => 'boolean',
        'work_start_time' => 'string',
        'work_end_time' => 'string',
    ];

    /**
     * Relationships
     */

    // Transactions the user sent
    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'sender_id');
    }

public function receivedTransactions()
{
    return $this->hasMany(Transaction::class, 'receiver_id');
}
public function beneficiaries()
{
    return $this->hasMany(Beneficiary::class);
}

public function creditCards() {
    return $this->hasMany(CreditCard::class);
}

public function bankAccounts() {
    return $this->hasMany(BankAccount::class);
}


    // Review written by the user
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Transactions processed by the agent
    public function processedTransactions()
    {
        return $this->hasMany(Transaction::class, 'agent_id');
    }

    /**
     * Check if agent is available now based on current time.
     */
    public function isCurrentlyAvailable(): bool
{
    // Availability toggle must be ON
    if (!$this->is_available) {
        return false;
    }

    // Must have work hours set
    if (!$this->work_start_time || !$this->work_end_time) {
        return false;
    }

    $now   = now()->format('H:i:s');
    $start = $this->work_start_time;
    $end   = $this->work_end_time;

    if ($start < $end) {
        // Normal same-day shift (e.g. 09:00–17:00)
        return $now >= $start && $now <= $end;
    }

    // Overnight shift (e.g. 19:00–00:55)
    // Available if it's after start OR before end
    return $now >= $start || $now <= $end;
}


    public function paymentMethods()
    {
        return $this->hasMany(\App\Models\PaymentMethod::class);
    }

    public function agentNotifications()
    {
        return $this->hasMany(\App\Models\AgentNotification::class, 'agent_id');
    }

    public function userNotifications()
    {
        return $this->hasMany(\App\Models\UserNotification::class);
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }
}
