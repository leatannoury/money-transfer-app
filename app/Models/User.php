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
        // First check if availability is enabled
        if (!$this->is_available) {
            return false;
        }

        // Check if work hours are set
        if (!$this->work_start_time || !$this->work_end_time) {
            return false;
        }

        // Get current time in H:i:s format
        $currentTime = now()->format('H:i:s');
        
        // Ensure work times are in H:i:s format (they might be stored as H:i)
        $startTime = $this->work_start_time;
        $endTime = $this->work_end_time;
        
        // If work times don't have seconds, add :00
        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }
        if (strlen($endTime) === 5) {
            $endTime .= ':00';
        }
        
        // Compare times as strings (works because format is consistent)
        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    public function paymentMethods()
{
    return $this->hasMany(\App\Models\PaymentMethod::class);
}

public function agentNotifications()
{
    return $this->hasMany(\App\Models\AgentNotification::class, 'agent_id');
}

}
