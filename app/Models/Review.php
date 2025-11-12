<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'rating', 'comment'];

    /**
     * Get the user that wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get average rating across all reviews
     */
    public static function averageRating(): float
    {
        return static::avg('rating') ?? 0;
    }

    /**
     * Get total number of reviews
     */
    public static function totalReviews(): int
    {
        return static::count();
    }
}
