<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Services\CurrencyService;
use Illuminate\Validation\Rule;
use App\Models\AgentNotification; // ✅ added for agent notifications


class TransferController extends Controller
{
    public function index()
    {
        // Show form to send money
        $users = User::where('id', '!=', Auth::id())->get();
        $beneficiaries = Beneficiary::where('user_id', Auth::id())->get();
        $currencies = CurrencyService::getSupportedCurrencies();
        $selectedCurrency = session('user_currency', 'USD');
        
        // Get available agents (users with 'Agent' role who are currently available)
        $availableAgents = User::role('Agent')
            ->where('is_available', true)
            ->where('status', 'active')
            ->get()
            ->filter(function($agent) {
                return $agent->isCurrentlyAvailable();
            })
            ->values();
        
        return view('user.transfer', compact('users', 'beneficiaries', 'availableAgents', 'currencies', 'selectedCurrency'));
    }
public function send(Request $request)
{
    // Basic validation
        $currencies = CurrencyService::getSupportedCurrencies();

    $request->validate([
        'search_type' => 'required|in:email,phone',
        'amount' => 'required|numeric|min:1',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
        'service_type' => 'required|in:wallet_to_wallet,transfer_via_agent',
        'agent_id' => 'nullable|exists:users,id',
    ]);
    
    // Require agent_id when service_type is transfer_via_agent
    if ($request->service_type === 'transfer_via_agent') {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
        ], [
            'agent_id.required' => 'Please select an agent for this transaction.',
        ]);
        
        // Verify the selected user is actually an agent
        $selectedAgent = User::findOrFail($request->agent_id);
        if (!$selectedAgent->hasRole('Agent')) {
            return back()->withInput()->withErrors(['agent_id' => 'The selected user is not an agent.']);
        }
        
        // Verify agent is available
        if (!$selectedAgent->is_available || $selectedAgent->status !== 'active') {
            return back()->withInput()->withErrors(['agent_id' => 'The selected agent is not available.']);
        }
    }

    $sender = Auth::user();
    $amount = $request->amount;
    $serviceType = $request->service_type;
    $transactionCurrency = $request->currency;
    $amountInUsd = round(CurrencyService::convert($amount, 'USD', $transactionCurrency), 2);

        // Handle receiver
        if ($request->search_type === 'email') {
            $request->validate(['email' => 'required|email|exists:users,email']);
            $receiver = User::where('email', $request->email)->first();
        } else {
            $request->validate(['phone' => 'required|exists:users,phone']);
            $receiver = User::where('phone', $request->phone)->first();
        }

        // Custom validation
        if ($receiver->id === $sender->id) {
            return back()->withInput()->withErrors(['error' => 'You cannot send money to yourself.']);
        }
        if ($amount <= 0) {
            return back()->withInput()->withErrors(['error' => 'Please enter a valid amount greater than 0.']);
        }
        
        // Check balance for all transaction types
        if ($sender->balance < $amountInUsd) {
            $balanceFormatted = number_format($sender->balance, 2);
            return back()->withInput()->withErrors(['amount' => "You don't have enough balance to complete this transfer. Your current balance is \${$balanceFormatted}."]);
        }

        // --- Process transfer ---
        if ($serviceType === 'wallet_to_wallet') {

            // Direct wallet-to-wallet
            $sender->balance -= $amount;
            $receiver->balance += $amount;
            $sender->save();
            $receiver->save();

        Transaction::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'currency' => $transactionCurrency,
            'status' => 'completed', // ✅ completed for wallet transfers
            'agent_id' => null, // Wallet to wallet doesn't need an agent
            'service_type' => $serviceType,
        ]);

        return redirect()->route('user.transactions')->with('success', 'Money sent successfully!');
    } else {
        // Transfer via agent - if agent is pre-selected, set status to in_progress
        // Otherwise, leave it as pending_agent for any agent to accept
        $status = $request->agent_id ? 'in_progress' : 'pending_agent';
        
       $transaction= Transaction::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'status' => $status,
            'agent_id' => $request->agent_id ?? null,
            'service_type' => $serviceType,
        ]);
                    if ($transaction->agent_id) {
                AgentNotification::create([
                    'agent_id'       => $transaction->agent_id,
                    'transaction_id' => $transaction->id,
                    'title'          => 'New money transfer request',
                    'message'        => "You have a new transfer of \${$transaction->amount} from {$sender->name} to {$receiver->name}.",
                ]);
            }

            $message = $request->agent_id 
                ? 'Your transfer request has been sent to the selected agent.' 
                : 'Your transfer request has been sent. An agent will be assigned soon.';
            
            return redirect()->route('user.transactions')->with('success', $message);
        }
    }

}
