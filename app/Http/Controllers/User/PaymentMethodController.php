<?php
namespace App\Http\Controllers\User;

use App\Models\FakeBankAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentMethod;
use App\Models\FakeCard; 

class PaymentMethodController extends Controller
{
    // List saved methods
    public function index()
    {
        $methods = Auth::user()
    ->paymentMethods()
    ->orderByDesc('is_primary')
    ->latest()
    ->get();

        return view('user.settings.payment-methods.index', compact('methods'));
    }

    // Show add form
    public function create()
    {
        return view('user.settings.payment-methods.create');
    }

    // Store new card (CVV is NOT stored)
public function store(Request $request)
{

        if ($request->input('method_type') === 'bank_account') {

        $request->validate([
            'nickname' => 'nullable|string|max:100',
            'account_holder' => 'required|string|max:150',
            'account_number' => 'required|string',
            'bank_name' => 'required|string|max:150',
            'routing' => 'nullable|string|max:100',
        ]);

        $acctNumber = preg_replace('/\s+/', '', $request->account_number);

        // DEV: verify against fake bank accounts
        $found = FakeBankAccount::where('account_number', $acctNumber)
            ->where('account_holder', $request->account_holder)
            ->first();

        if (!$found) {
            return back()->withErrors(['account_number' => 'Bank account not recognized'])->withInput();
        }

        $last4 = substr($acctNumber, -4);
        $mask = '**** **** ' . $last4;

        PaymentMethod::create([
            'user_id' => Auth::id(),
            'nickname' => $request->nickname,
            'type' => 'bank_account',
            'provider' => $request->bank_name,
            'card_mask' => $mask,
            'last4' => $last4,
            'cardholder_name' => $request->account_holder,
            'expiry' => null,
            'details' => ['routing' => $request->routing, 'dev_note' => 'dev-only fake bank account'],
        ]);

        return redirect()->route('user.payment-methods.index')
                         ->with('success','Bank account added.');
    }

    $request->validate([
        'nickname' => 'nullable|string|max:100',
        'cardholder_name' => 'required|string|max:150',
        'card_number' => 'required|string',
        'expiry' => ['required','regex:/^(0[1-9]|1[0-2])\/\d{2}$/'], // MM/YY
        'cvv' => 'required|digits_between:3,4',
    ]);

    // sanitize card number (digits only)
    $cardNumber = preg_replace('/\D/', '', $request->card_number);

    // Luhn check (optional but recommended)
    if (! $this->luhnCheck($cardNumber)) {
        return back()->withErrors(['card_number' => 'Invalid card number.'])->withInput();
    }



$found = FakeCard::where('card_number', $cardNumber)
    ->where('cardholder_name', $request->cardholder_name)
    ->where('expiry', $request->expiry)
    ->where('cvv', $request->cvv)
    ->first();

if (!$found) {
    return back()->withErrors(['card_number' => 'Card not recognized.'])->withInput();
}


if (!$found) {
    return back()->withErrors(['card_number' => 'Card not recognized.'])->withInput();
}


    // Determine provider (you could also trust $found['provider'])
    $provider = $this->detectProvider($cardNumber);

    // Mask and last4 (DO NOT store full PAN or CVV)
    $last4 = substr($cardNumber, -4);
    $mask = '**** **** **** ' . $last4;

    $method = PaymentMethod::create([
        'user_id' => Auth::id(),
        'nickname' => $request->nickname,
        'type' => 'credit_card',
        'provider' => $provider,
        'card_mask' => $mask,
        'last4' => $last4,
        'cardholder_name' => $request->cardholder_name,
        'expiry' => $request->expiry,
        'details' => ['dev_note' => 'CVV not stored'],
    ]);

    return redirect()->route('user.payment-methods.index')
                     ->with('success','Card added successfully.');
}




    // Optional: delete method
    public function destroy($id)
    {
        $method = PaymentMethod::where('user_id', Auth::id())->findOrFail($id);
        $method->delete();
        return back()->with('success','Payment method removed.');
    }

    // -----------------------
    // Helpers
    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $numDigits = strlen($number);
        $parity = $numDigits % 2;
        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int) $number[$i];
            if ($i % 2 === $parity) $digit *= 2;
            if ($digit > 9) $digit -= 9;
            $sum += $digit;
        }
        return ($sum % 10) === 0;
    }

    private function detectProvider(string $number): ?string
    {
        // very simple detection; extend as needed
        if (preg_match('/^3[47]/', $number)) return 'Amex';
        if (preg_match('/^4/', $number)) return 'Visa';
        if (preg_match('/^5[1-5]|^2(2[2-9]|[3-6]\d|7[01]|720)/', $number)) return 'MasterCard';
        if (preg_match('/^6(?:011|5)/', $number)) return 'Discover';
        return 'Unknown';
    }


    // Edit method
public function edit($id)
{
    $method = PaymentMethod::where('user_id', Auth::id())->findOrFail($id);
    return view('user.settings.payment-methods.edit', compact('method'));
}

// Update method
public function update(Request $request, $id)
{
    $method = PaymentMethod::where('user_id', Auth::id())->findOrFail($id);

    if ($method->type === 'bank_account') {
        $request->validate([
            'nickname' => 'nullable|string|max:100',
            'account_holder' => 'required|string|max:150',
            'account_number' => 'required|string',
            'bank_name' => 'required|string|max:150',
            'routing' => 'nullable|string|max:100',
        ]);

        $acctNumber = preg_replace('/\s+/', '', $request->account_number);

        $found = FakeBankAccount::where('account_number', $acctNumber)
            ->where('account_holder', $request->account_holder)
            ->first();

        if (!$found) {
            return back()->withErrors(['account_number' => 'Bank account not recognized'])->withInput();
        }

        $last4 = substr($acctNumber, -4);
        $mask = '**** **** ' . $last4;

        $method->update([
            'nickname' => $request->nickname,
            'provider' => $request->bank_name,
            'card_mask' => $mask,
            'last4' => $last4,
            'cardholder_name' => $request->account_holder,
            'details' => ['routing' => $request->routing, 'dev_note' => 'dev-only fake bank account'],
        ]);

    } else { // credit card
        $request->validate([
            'nickname' => 'nullable|string|max:100',
            'cardholder_name' => 'required|string|max:150',
            'card_number' => 'required|string',
            'expiry' => ['required','regex:/^(0[1-9]|1[0-2])\/\d{2}$/'],
            'cvv' => 'required|digits_between:3,4',
        ]);

        $cardNumber = preg_replace('/\D/', '', $request->card_number);

        if (! $this->luhnCheck($cardNumber)) {
            return back()->withErrors(['card_number' => 'Invalid card number.'])->withInput();
        }

        $found = FakeCard::where('card_number', $cardNumber)
            ->where('cardholder_name', $request->cardholder_name)
            ->where('expiry', $request->expiry)
            ->where('cvv', $request->cvv)
            ->first();

        if (!$found) {
            return back()->withErrors(['card_number' => 'Card not recognized.'])->withInput();
        }

        $provider = $this->detectProvider($cardNumber);
        $last4 = substr($cardNumber, -4);
        $mask = '**** **** **** ' . $last4;

        $method->update([
            'nickname' => $request->nickname,
            'provider' => $provider,
            'card_mask' => $mask,
            'last4' => $last4,
            'cardholder_name' => $request->cardholder_name,
            'expiry' => $request->expiry,
            'details' => ['dev_note' => 'CVV not stored'],
        ]);
    }

    return redirect()->route('user.payment-methods.index')->with('success', 'Payment method updated.');
}

public function setPrimary(PaymentMethod $method)
{
    // Security check
    if ($method->user_id !== auth()->id()) {
        abort(403);
    }

    // Toggle behavior
    if ($method->is_primary) {
        // Unmark this one
        $method->is_primary = false;
        $method->save();
        return redirect()->back()->with('success', 'Payment method unmarked as primary.');
    } else {
        // Mark this as primary, unmark others of same type
        PaymentMethod::where('user_id', auth()->id())
            ->where('type', $method->type)
            ->update(['is_primary' => false]);

        $method->is_primary = true;
        $method->save();
        return redirect()->back()->with('success', 'Payment method set as primary.');
    }
}


}
