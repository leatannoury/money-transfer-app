<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewManagementController extends Controller
{
    public function index(Request $request)
    {
        $pendingReviews = Review::with('user')
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedReviews = Review::with(['user', 'approver'])
            ->where('is_approved', true)
            ->orderBy('approved_at', 'desc')
            ->get();

        return view('admin.reviews.manage', compact('pendingReviews', 'approvedReviews'));
    }

    public function approve(Review $review)
    {
        if ($review->is_approved) {
            return back()->with('info', 'Review is already approved.');
        }

        $review->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Review approved successfully.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Review removed successfully.');
    }
}

