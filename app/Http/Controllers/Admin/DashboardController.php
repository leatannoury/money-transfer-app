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
        $totalUsers = User::role('user')->count();
        $totalAgents = User::role('agent')->count();
        $totalTransactions = Transaction::count();
        $adminBalance = auth()->user()->balance ?? 0;

        $transactions = Transaction::with(['sender', 'receiver'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalAgents', 'totalTransactions', 'transactions', 'adminBalance'
        ));
    }

  public function getHourlyFees()
{
    $admin = auth()->user();
    $feePercentage = $admin->commission ?? 25; // fallback to 25% if not set

    $now = \Carbon\Carbon::now();
    $start = $now->copy()->subHours(23)->startOfHour(); // 24 hours ago
    $end = $now->copy()->endOfHour();

    // Initialize array for all 24 hours
    $hours = [];
    for ($i = 0; $i < 24; $i++) {
        $hourLabel = $start->copy()->addHours($i)->format('H:00');
        $hours[$hourLabel] = 0;
    }

    // Fetch transaction fees
    $fees = \App\Models\Transaction::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw("SUM(amount * $feePercentage / 100) as total_fee")
            )
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

    // Map fees into hours array
    foreach ($fees as $fee) {
        $hourLabel = $start->copy()->addHours($fee->hour - $start->hour)->format('H:00');
        if (isset($hours[$hourLabel])) {
            $hours[$hourLabel] = round($fee->total_fee, 2);
        }
    }

    // Format response
    $data = [];
    foreach ($hours as $label => $total) {
        $data[] = ['hour' => $label, 'total_fee' => $total];
    }

    return response()->json($data);
}

}
