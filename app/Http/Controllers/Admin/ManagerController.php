<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;

class ManagerController extends Controller
{
public function manageUsers()
{
      $totalUsers = User::role('user')->count();
        $totalAgents = User::role('agent')->count();
        $totalTransactions = Transaction::count();

             $transactions = Transaction::with(['sender', 'receiver'])
            ->latest()  
            ->take(10)
            ->get();
    return view('admin.manageUser.manageUser', compact('totalUsers', 'totalAgents', 'totalTransactions','transactions'));
}
public function  manageAgents(){
  return view('admin.manageAgent.manageAgent');
}
}
