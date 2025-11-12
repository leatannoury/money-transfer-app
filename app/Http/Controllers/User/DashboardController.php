<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
public function index()
{
    $user = Auth::user();
    $transactions = \App\Models\Transaction::where('sender_id', $user->id)
        ->orWhere('receiver_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Get user's review if exists
    $userReview = \App\Models\Review::where('user_id', $user->id)->first();
    
    // Get all reviews for display (latest first)
    $reviews = \App\Models\Review::with('user')
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    
    // Get average rating and total reviews
    $averageRating = \App\Models\Review::averageRating();
    $totalReviews = \App\Models\Review::totalReviews();

    return view('user.dashboard', compact('user', 'transactions', 'userReview', 'reviews', 'averageRating', 'totalReviews'));
}

}
