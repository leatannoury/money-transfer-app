<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Services\NotificationService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundRequestController extends Controller
{
    public function index(Request $request)
    {
        $refundRequests = RefundRequest::with(['transaction.sender', 'transaction.receiver', 'user'])
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhereHas('transaction', fn($txn) => $txn->where('id', $search))
                        ->orWhereHas('user', fn($user) => $user->where('email', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.refunds.index', compact('refundRequests'));
    }

    public function decide(Request $request, RefundRequest $refundRequest)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject',
            'note' => 'nullable|string|max:1000',
        ]);

        $admin = Auth::user();

        try {
            if ($data['action'] === 'approve') {
                RefundService::approve($refundRequest, $admin, $data['note'] ?? null);
                $refundRequest->refresh();
                NotificationService::refundRequestApproved($refundRequest);
                $message = 'Refund request approved.';
            } else {
                RefundService::reject($refundRequest, $admin, $data['note'] ?? null);
                $refundRequest->refresh();
                NotificationService::refundRequestRejected($refundRequest);
                $message = 'Refund request rejected.';
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}

