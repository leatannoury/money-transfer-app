<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'rating',
        'comment',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get average rating across all reviews
     */
    public static function averageRating(): float
    {
        return static::where('is_approved', true)->avg('rating') ?? 0;
    }

    /**
     * Get total number of reviews
     */
    public static function totalReviews(): int
    {
        return static::where('is_approved', true)->count();
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
