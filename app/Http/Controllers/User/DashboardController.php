<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
public function index(Request $request)
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

    // Handle currency conversion
    $selectedCurrency = $request->get('currency', session('user_currency', 'USD'));
    session(['user_currency' => $selectedCurrency]);
    
    // Convert balance to selected currency (assuming balance is stored in USD)
    $convertedBalance = CurrencyService::convert($user->balance, $selectedCurrency, 'USD');
    
    // Get supported currencies
    $currencies = CurrencyService::getSupportedCurrencies();

    return view('user.dashboard', compact(
        'user', 
        'transactions', 
        'userReview', 
        'reviews', 
        'averageRating', 
        'totalReviews',
        'selectedCurrency',
        'convertedBalance',
        'currencies'
    ));
}

}
