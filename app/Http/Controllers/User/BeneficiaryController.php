<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Beneficiary;
use App\Models\Transaction;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    // Show all beneficiaries for the logged-in user
    public function index()
    {
        $beneficiaries = Beneficiary::where('user_id', auth()->id())->get();
        return view('user.beneficiary.index', compact('beneficiaries'));
    }

    // Show create form
    public function create()
    {
        return view('user.beneficiary.create');
    }

    // Save new beneficiary
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255', // must exist as a user
            'payout_method' => 'required|string|max:50',
            'account_number' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        // Check if the beneficiary user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'The user with this email does not exist.');
        }

        // Create beneficiary for the logged-in user
        Beneficiary::create([
            'user_id' => auth()->id(),
            'beneficiary_user_id' => $user->id,
            'full_name' => $request->full_name,
            'payout_method' => $request->payout_method,
            'account_number' => $request->account_number,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
        ]);

        return redirect()->route('user.beneficiary.index')->with('success', 'Beneficiary added successfully.');
    }

    // Show details of a beneficiary (only if belongs to user)
    public function show($id)
    {
        $beneficiary = Beneficiary::where('user_id', auth()->id())->findOrFail($id);
        return view('user.beneficiary.show', compact('beneficiary'));
    }

    // Show edit form (only if belongs to user)
    public function edit($id)
    {
        $beneficiary = Beneficiary::where('user_id', auth()->id())->findOrFail($id);
        return view('user.beneficiary.edit', compact('beneficiary'));
    }

    // Update beneficiary (only if belongs to user)
    public function update(Request $request, $id)
    {
        $beneficiary = Beneficiary::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'payout_method' => 'required|string|max:50',
            'account_number' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $beneficiary->update([
            'full_name' => $request->full_name,
            'payout_method' => $request->payout_method,
            'account_number' => $request->account_number,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
        ]);

        return redirect()->route('user.beneficiary.index')->with('success', 'Beneficiary updated successfully.');
    }

    // Delete beneficiary (only if belongs to user)
    public function destroy($id)
    {
        $beneficiary = Beneficiary::where('user_id', auth()->id())->findOrFail($id);
        $beneficiary->delete();

        return redirect()->route('user.beneficiary.index')->with('success', 'Beneficiary deleted successfully.');
    }

    // Add beneficiary from transaction
    public function addFromTransaction($transaction)
    {
        $transaction = Transaction::findOrFail($transaction);
        
        // Check if the transaction belongs to the authenticated user and is an outgoing transaction
        if ($transaction->sender_id != auth()->id()) {
            return redirect()->route('user.transactions')->with('error', 'You can only add beneficiaries from your own outgoing transactions.');
        }

        // Check if transaction is completed
        if ($transaction->status !== 'completed') {
            return redirect()->route('user.transactions')->with('error', 'You can only add beneficiaries from completed transactions.');
        }

        // Get the receiver user
        $receiver = $transaction->receiver;
        
        if (!$receiver) {
            return redirect()->route('user.transactions')->with('error', 'Receiver information not found.');
        }

        // Check if beneficiary already exists for this user with the same name or email
        $existingBeneficiary = Beneficiary::where('user_id', auth()->id())
            ->where(function($query) use ($receiver) {
                $query->where('full_name', $receiver->name)
                      ->orWhere('phone_number', $receiver->phone);
            })
            ->first();

        if ($existingBeneficiary) {
            return redirect()->route('user.transactions')->with('error', 'This beneficiary already exists in your list.');
        }

        // Create beneficiary with available information
        Beneficiary::create([
            'user_id' => auth()->id(),
            'full_name' => $receiver->name,
            'payout_method' => 'wallet', // Default value, user can edit later
            'account_number' => null,
            'phone_number' => $receiver->phone ?? null,
            'address' => $receiver->city ?? null,
        ]);

        return redirect()->route('user.transactions')->with('success', 'Beneficiary added successfully!');
    }
}
