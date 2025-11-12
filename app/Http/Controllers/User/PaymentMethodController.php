<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    // List saved methods
    public function index()
    {
        $methods = Auth::user()->paymentMethods()->latest()->get();
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

    // --- DEV-ONLY: verify against predefined cards ---
// --- DEV-ONLY: verify against predefined cards ---
$validCards = config('fake_cards.valid_cards');

$found = collect($validCards)->first(function($c) use ($cardNumber, $request) {
    return $c['number'] === $cardNumber &&
           $c['cardholder_name'] === $request->cardholder_name &&
           $c['expiry'] === $request->expiry &&
           $c['cvv'] === $request->cvv; // <-- added CVV verification
});

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
}
