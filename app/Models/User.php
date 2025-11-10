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

    // Transactions the user received
    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
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
        if (!$this->is_available || !$this->work_start_time || !$this->work_end_time) {
            return false;
        }

        $currentTime = now()->format('H:i:s');
        return $currentTime >= $this->work_start_time && $currentTime <= $this->work_end_time;
    }
}
