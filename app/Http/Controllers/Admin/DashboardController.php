<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function index()
    {
        // Total counts
        $totalUsers = User::role('user')->count();
        $totalAgents = User::role('agent')->count();
        $totalTransactions = Transaction::count();

        // Last 10 transactions
        $transactions = Transaction::with(['sender', 'receiver'])
            ->latest()
            ->take(10)
            ->get();

        // Admin balance
        $admin = Auth::user(); // logged-in admin
        $adminBalance = $admin->balance ?? 0;

        // Chart data: total fees earned per day
        $commissionRate = $admin->commission ?? 0; // percentage

        $earnings = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount * ' . $commissionRate . ' / 100) as total_fee')
            )
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $earnings->pluck('date');       // Dates for chart
        $chartData = $earnings->pluck('total_fee');   // Fee amounts

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAgents',
            'totalTransactions',
            'transactions',
            'adminBalance',
            'chartLabels',
            'chartData'
        ));
    }
}
