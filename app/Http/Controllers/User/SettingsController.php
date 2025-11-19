<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Transaction;
use App\Services\NotificationService;


class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('user.settings.index', compact('user'));
    }

    /**
     * Request to become an agent
     */
    public function requestAgentStatus(Request $request)
    {
        $user = Auth::user();

        // Check if user already has a role other than User
        if ($user->hasAnyRole(['Admin', 'Agent'])) {
            return back()->with('error', 'You are already an agent or admin.');
        }

        // Check if there's already a pending request
        if ($user->agent_request_status === 'pending') {
            return back()->with('error', 'You already have a pending request.');
        }

        $hasPendingAgentTransfer = Transaction::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->whereIn('status', ['pending_agent', 'in_progress'])
            ->where(function ($query) {
                $query->where('service_type', 'transfer_via_agent')
                      ->orWhereNotNull('agent_id');
            })
            ->exists();

        if ($hasPendingAgentTransfer) {
            return back()->with('error', 'You cannot request agent status while you have transfers that still require an agent.');
        }

        // Allow users to request again even after rejection (no 30-day wait)
        // If request was previously rejected, allow them to request again
        if ($user->agent_request_status === 'rejected') {
            // Reset to null so they can make a new request
            $user->agent_request_status = null;
        }

        // Validate required fields for agent
        $request->validate([
            'city' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^\d{8}$/',
        ], [
            'phone.regex' => 'Enter exactly 8 digits.',
            'city.required' => 'City is required to become an agent.',
        ]);

        // Update user with agent request information
        $user->agent_request_status = 'pending';
        $user->city = $request->city;
        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }
        $user->save();

        NotificationService::notifyAdmins(
            'New Agent Request',
            "{$user->name} has requested to become an agent."
        );

        return back()->with('success', 'Your request to become an agent has been submitted. The admin will review it soon.');
    }

    /**
     * Cancel pending agent request
     */
    public function cancelAgentRequest()
    {
        $user = Auth::user();

        if ($user->agent_request_status !== 'pending') {
            return back()->with('error', 'No pending request to cancel.');
        }

        $user->agent_request_status = null;
        $user->save();

        return back()->with('success', 'Your agent request has been cancelled.');
    }
}
