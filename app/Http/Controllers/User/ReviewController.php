<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display the reviews page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's review if exists
        $userReview = Review::where('user_id', $user->id)->first();
        
        // Get all reviews for display (latest first)
        $reviews = Review::with('user')
            ->approved()
            ->orderBy('approved_at', 'desc')
            ->get();
        
        // Get average rating and total reviews
        $averageRating = Review::averageRating();
        $totalReviews = Review::totalReviews();

        return view('user.reviews', compact('user', 'userReview', 'reviews', 'averageRating', 'totalReviews'));
    }

    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Please select a rating.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
            'comment.max' => 'Comment cannot exceed 1000 characters.',
        ]);

        $user = Auth::user();

        // Check if user already has a review (if unique constraint exists)
        $existingReview = Review::where('user_id', $user->id)->first();

        if ($existingReview) {
            // Update existing review and reset approval status
            $existingReview->fill([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_approved' => false,
                'approved_by' => null,
                'approved_at' => null,
            ]);
            $existingReview->save();

            return back()->with('success', 'Your review update has been submitted and is pending admin approval.');
        }

        // Create new review (pending approval)
        Review::create([
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false,
        ]);

        return back()->with('success', 'Thank you! Your review is pending admin approval.');
    }

    /**
     * Delete user's review
     */
    public function destroy()
    {
        $user = Auth::user();
        $review = Review::where('user_id', $user->id)->first();

        if ($review) {
            $review->delete();
            return back()->with('success', 'Your review has been deleted.');
        }

        return back()->with('error', 'Review not found.');
    }
}
