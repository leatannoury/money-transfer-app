<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use PDF;


class TransactionController extends Controller
{

public function index(Request $request)
{
    $query = Transaction::where(function($q) {
        $q->where('sender_id', Auth::id())
          ->orWhere('receiver_id', Auth::id());
    })->with(['sender', 'receiver']);

    // Filters
    if ($request->filled('type')) {
        if ($request->type === 'sent') {
            $query->where('sender_id', Auth::id());
        } elseif ($request->type === 'received') {
            $query->where('receiver_id', Auth::id());
        }
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    // Sorting
    if ($request->filled('sort')) {
        switch ($request->sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'amount_desc':
                $query->orderBy('amount', 'desc');
                break;
            case 'amount_asc':
                $query->orderBy('amount', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
    } else {
        $query->orderBy('created_at', 'desc');
    }

    // 🧠 Handle export BEFORE pagination
    if ($request->filled('export')) {
        $allTransactions = (clone $query)->get();

        if ($request->export === 'pdf') {
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction History</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Transaction History</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>';
            
            foreach($allTransactions as $txn) {
                $html .= '<tr>
                    <td>'.$txn->id.'</td>
                    <td>'.($txn->sender->name ?? 'Unknown').'</td>
                    <td>'.($txn->receiver->name ?? 'Unknown').'</td>
                    <td>'.number_format($txn->amount, 2).'</td>
                    <td>'.$txn->currency.'</td>
                    <td>'.ucfirst($txn->status).'</td>
                    <td>'.$txn->created_at->format('Y-m-d H:i').'</td>
                </tr>';
            }

            $html .= '</tbody></table></body></html>';

            return PDF::loadHTML($html)->download('transactions.pdf');
        }

        if ($request->export === 'csv') {
            return response()->streamDownload(function() use ($allTransactions) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['ID','Sender','Receiver','Amount','Currency','Status','Date']);
                foreach($allTransactions as $txn){
                    fputcsv($handle, [
                        $txn->id,
                        $txn->sender->name ?? 'Unknown',
                        $txn->receiver->name ?? 'Unknown',
                        number_format($txn->amount, 2),
                        $txn->currency,
                        ucfirst($txn->status),
                        $txn->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                fclose($handle);
            }, 'transactions_' . date('Ymd_His') . '.csv');
        }
    }

    // 🧾 Only paginate if not exporting
    $transactions = $query->paginate(10)->withQueryString();

    $beneficiaryNames = Beneficiary::where('user_id', Auth::id())
        ->pluck('full_name')->toArray();

    $beneficiaryPhones = Beneficiary::where('user_id', Auth::id())
        ->whereNotNull('phone_number')
        ->pluck('phone_number')->toArray();

    return view('user.history', compact(
        'transactions',
        'beneficiaryNames',
        'beneficiaryPhones'
    ));
}




}
