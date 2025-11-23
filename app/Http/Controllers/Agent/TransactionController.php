<?php

namespace App\Http\Controllers\Agent;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use App\Services\CurrencyService;
use App\Services\NotificationService;

class TransactionController extends Controller
{
    /**
     * Show all transactions assigned to this agent
     */
    public function index()
    {
        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        // Mark notifications read
        $agent->agentNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 1) User ↔ User transfers via agent (including pending)
        $userTransfers = Transaction::with(['sender', 'receiver'])
            ->where(function ($q) use ($agent) {
                $q->where('status', 'pending_agent')      // not yet accepted
                  ->orWhere('agent_id', $agent->id);      // already assigned to this agent
            })
            ->where(function ($q) {
                $q->whereNull('service_type')
                  ->orWhereIn('service_type', ['via_agent', 'transfer_via_agent']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'transfers_page');

        // 2) Cash Pickup payouts
        $cashPickups = Transaction::with(['sender', 'receiver'])
            ->where(function ($q) use ($agent) {
                $q->where('status', 'pending_agent')
                  ->orWhere('agent_id', $agent->id);
            })
            ->where('service_type', 'cash_pickup')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'cash_pickup_page');

        // 3) Cash-In operations (only ones done by this agent)
        $cashIns = Transaction::with(['sender', 'receiver'])
            ->where('agent_id', $agent->id)
            ->where('service_type', 'cash_in')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'cash_in_page');

        // 4) Cash-Out operations (only ones done by this agent)
        $cashOuts = Transaction::with(['sender', 'receiver'])
            ->where('agent_id', $agent->id)
            ->where('service_type', 'cash_out')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'cash_out_page');

        return view('agent.transactions', compact(
            'agent',
            'userTransfers',
            'cashPickups',
            'cashIns',
            'cashOuts'
        ));
    }

    /**
     * Accept a pending transaction (assign to current agent)
     */
    public function accept($id)
    {
        /** @var \App\Models\User $agent */
        $agent = Auth::user();
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending_agent') {
            return back()->with('error', 'This transaction is no longer available.');
        }

        $transaction->agent_id = $agent->id;
        $transaction->status   = 'in_progress';
        $transaction->save();

        Log::info("Transaction #{$transaction->id} accepted by agent #{$agent->id}");

        return back()->with('success', 'Transaction accepted successfully.');
    }

    /**
     * Mark a transaction as completed
     *
     *  - For via_agent transfers → balances already handled in user flow. We only change status.
     *  - For cash_pickup → add commission to agent when he actually gives cash +
     *    store recipient phone.
     */
    public function complete(Request $request, $id)
{
    /** @var \App\Models\User $agent */
    $agent = Auth::user();
    $transaction = Transaction::findOrFail($id);

    // Safety: only assigned agent can complete
    if ($transaction->agent_id !== $agent->id) {
        abort(403);
    }

    // Only in_progress transactions can be completed
    if ($transaction->status !== 'in_progress') {
        return back()->with('error', 'This transaction is not in progress.');
    }

    // 🔹 SPECIAL LOGIC FOR LOCAL CASH PICKUP
    if ($transaction->service_type === 'cash_pickup') {
        // 1) Agent must enter phone
        $data = $request->validate([
            'recipient_phone' => ['required', 'string', 'regex:/^\d{8}$/'],
        ]);

        $enteredPhone = trim($data['recipient_phone'] ?? '');

        // 2) If transaction already has a stored phone from user,
        //    agent MUST enter the SAME phone to complete
        if (!empty($transaction->recipient_phone)) {
            if (trim($transaction->recipient_phone) !== $enteredPhone) {
                return back()->with('error', 'The phone number does not match the original recipient phone.');
            }
        } else {
            // Fallback: if somehow no phone was stored, save the one entered now
            $transaction->recipient_phone = $enteredPhone;
        }

        // 3) Deduct money NOW from sender (ONLY for local cash pickup)
        /** @var \App\Models\User|null $sender */
        $sender = User::find($transaction->sender_id);

        if (!$sender) {
            return back()->with('error', 'Sender not found. Cannot complete this transaction.');
        }

        if ($transaction->payment_method === 'wallet') {
            $senderBalance   = (float) ($sender->balance ?? 0);
            $amountToDeduct  = (float) ($transaction->amount_usd ?? $transaction->amount);

            if ($senderBalance < $amountToDeduct) {
                return back()->with('error', 'Sender no longer has enough balance to fund this cash pickup.');
            }

            $sender->balance = $senderBalance - $amountToDeduct;
            $sender->save();
        }

        // 4) Pay commission to agent
        if ($transaction->fee_amount_usd > 0) {
            $agent->balance = (float) ($agent->balance ?? 0) + (float) $transaction->fee_amount_usd;
            $agent->save();
        }
    }

    // ✅ Mark as completed
    $transaction->status = 'completed';
    $transaction->save();

    // 5) Notify Sender (User)
    /** @var \App\Models\User|null $sender */
    $sender = $transaction->sender;
    if ($sender) {
        NotificationService::sendUserNotification(
            $sender,
            'transfer_completed',
            'Transfer Completed',
            "Your transfer of " . CurrencyService::format($transaction->amount, $transaction->currency) . " has been completed by the agent.",
            $transaction
        );
    }

    // 6) Notify Agent (Current User)
    NotificationService::sendAgentNotification(
        $agent,
        'Transfer Completed',
        "You have successfully completed the transfer #" . $transaction->id . ".",
        $transaction
    );

    return back()->with('success', 'Transaction completed successfully.');
}
   


    public function reject($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === 'in_progress') {
            $transaction->status = 'failed';
            $transaction->save();

            NotificationService::transferFailed($transaction, 'An agent rejected the transfer request.');
        }

        return redirect()->back()->with('success', 'Transaction rejected successfully.');
    }

    /* -----------------------------------------------------------------
     |  CASH-IN / CASH-OUT OPERATIONS
     |------------------------------------------------------------------*/

    public function cashForm()
    {
        $agent = Auth::user();
        return view('agent.cash', compact('agent'));
    }

    /**
     * Agent CASH-IN:
     * User gives cash, agent loads money into user wallet.
     */
    public function cashIn(Request $request)
    {
        $request->validate([
            'search_type'    => 'required|in:email,phone',
            'email_or_phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->search_type === 'phone' && !preg_match('/^\d{8}$/', $value)) {
                        $fail('The phone number must be exactly 8 digits.');
                    }
                },
            ],
            'amount'         => 'required|numeric|min:1',
        ]);

        $user = $request->search_type === 'email'
            ? User::where('email', $request->email_or_phone)->first()
            : User::where('phone', $request->email_or_phone)->first();

        if (!$user) {
            return back()->withErrors([
                'email_or_phone' => "No user found using this {$request->search_type}. Please double-check and try again.",
            ])->withInput();
        }

        /** @var \App\Models\User $agent */
        $agent      = auth()->user();
        $amount     = $request->amount;
        $commission = $amount * 0.005; // 0.5%

        // Balances
        $user->balance  = $user->balance - $commission + $amount;
        $agent->balance = $agent->balance + $commission;

        $user->save();
        $agent->save();

        $transaction = Transaction::create([
            'sender_id'      => $agent->id,
            'receiver_id'    => $user->id,
            'amount'         => $amount,
            'amount_usd'     => $amount,
            'currency'       => 'USD',
            'service_type'   => 'cash_in',
            'agent_id'       => $agent->id,
            'status'         => 'completed',
            'fee_percent'    => 0.5,
            'fee_amount_usd' => $commission,
        ]);

        NotificationService::sendAgentNotification(
            $agent,
            'Cash-In Completed',
            "You loaded " . CurrencyService::format($amount, 'USD') . " for {$user->name}.",
            $transaction
        );

        NotificationService::sendUserNotification(
            $user,
            'cash_in',
            'Cash-In Completed',
            "Agent {$agent->name} added " . CurrencyService::format($amount, 'USD') . " to your wallet (fee " . CurrencyService::format($commission, 'USD') . ").",
            $transaction
        );

        return back()->with('success', "Cash-In completed successfully.");
    }

    /**
     * Agent CASH-OUT:
     * User withdraws from wallet, agent gives physical cash.
     */
    public function cashOut(Request $request)
    {
        $request->validate([
            'search_type'    => 'required|in:email,phone',
            'email_or_phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->search_type === 'phone' && !preg_match('/^\d{8}$/', $value)) {
                        $fail('The phone number must be exactly 8 digits.');
                    }
                },
            ],
            'amount'         => 'required|numeric|min:1',
        ]);

        $user = $request->search_type === 'email'
            ? User::where('email', $request->email_or_phone)->first()
            : User::where('phone', $request->email_or_phone)->first();

        if (!$user) {
            return back()->withErrors([
                'email_or_phone' => "No user found using this {$request->search_type}. Please enter a valid email or phone.",
            ])->withInput();
        }

        /** @var \App\Models\User $agent */
        $agent      = auth()->user();
        $amount     = $request->amount;
        $commission = $amount * 0.005; // 0.5%
        $total      = $amount + $commission;

        if ($user->balance < $total) {
            return back()->withErrors([
                'amount' => "User doesn't have enough balance to cash out $amount USD.",
            ])->withInput();
        }

        $user->balance  -= $total;
        $agent->balance += $commission;

        $user->save();
        $agent->save();

        $transaction = Transaction::create([
            'sender_id'      => $user->id,
            'receiver_id'    => $agent->id,
            'amount'         => $amount,
            'amount_usd'     => $amount,
            'currency'       => 'USD',
            'service_type'   => 'cash_out',
            'agent_id'       => $agent->id,
            'status'         => 'completed',
            'fee_percent'    => 0.5,
            'fee_amount_usd' => $commission,
        ]);

        NotificationService::sendAgentNotification(
            $agent,
            'Cash-Out Completed',
            "You handed " . CurrencyService::format($amount, 'USD') . " to {$user->name}.",
            $transaction
        );

        NotificationService::sendUserNotification(
            $user,
            'cash_out',
            'Cash-Out Completed',
            "Agent {$agent->name} processed your cash-out of " . CurrencyService::format($amount, 'USD') . " (fee " . CurrencyService::format($commission, 'USD') . ").",
            $transaction
        );

        return back()->with('success', "Cash-Out completed successfully.");
    }

    public function cashMenu()
    {
        $agent = Auth::user();
        return view('agent.cash-menu', compact('agent'));
    }

    public function cashInForm()
    {
        $agent = Auth::user();
        return view('agent.cash-in', compact('agent'));
    }

    public function cashOutForm()
    {
        $agent = Auth::user();
        return view('agent.cash-out', compact('agent'));
    }
}
