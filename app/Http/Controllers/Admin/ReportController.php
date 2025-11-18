<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Review;
use PDF; // Make sure barryvdh/laravel-dompdf is installed
use Carbon\Carbon;

class ReportController extends Controller
{
    public function generate()
    {
        // --- Platform Usage (Users) ---
        $totalUsers = User::role('user')->count();
        $newUsers = User::role('user')->whereMonth('created_at', Carbon::now()->month)->count();

        $totalAgents = User::role('agent')->count();
        $newAgents = User::role('agent')->whereMonth('created_at', Carbon::now()->month)->count();

        $totalAdmins = User::role('admin')->count();

        // Active vs Banned Users
        $activeUsers = User::role('user')->where('status', 'active')->count();
        $bannedUsers = User::role('user')->where('status', 'banned')->count();

        // Active vs Banned Agents
        $activeAgents = User::role('agent')->where('status', 'active')->count();
        $bannedAgents = User::role('agent')->where('status', 'banned')->count();


        $totalBalance = User::sum('balance');

        // --- Transactions ---
        $totalTransactions = Transaction::count();
        $completedTransactions = Transaction::where('status', 'completed')->count();
        $failedTransactions = Transaction::where('status', 'failed')->count();
        $inProgressTransactions = Transaction::where('status', 'in_progress')->count();
        $totalAmount = Transaction::sum('amount');

        // --- Reviews / Feedback ---
        $totalReviews = Review::where('is_approved', true)->count();
        $averageRating = Review::where('is_approved', true)->avg('rating');
        $recentReviews = Review::where('is_approved', true)->latest()->take(5)->get();

        // Prepare data for PDF
        $data = compact(
            'totalUsers', 'newUsers',
            'activeUsers','bannedUsers',
            'activeAgents','bannedAgents',
            'totalAgents', 'newAgents',
            'totalAdmins', 'totalBalance',
            'totalTransactions', 'completedTransactions', 'failedTransactions', 'inProgressTransactions', 'totalAmount',
            'totalReviews', 'averageRating', 'recentReviews'
        );

        // Generate PDF
        $pdf = PDF::loadView('admin.reports.pdf', $data);

        return $pdf->download('platform_report_'.date('Y-m-d').'.pdf');
    }
}
