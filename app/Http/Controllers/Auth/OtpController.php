<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpController extends Controller
{
    // Show OTP form
    public function showForm()
    {
        if (!session()->has('otp_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.otp');
    }

    // Verify OTP
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user_id = $request->session()->get('otp_user_id');
        $user = User::find($user_id);

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired.']);
        }

        $otp = Otp::where('user_id', $user->id)
                  ->where('code', $request->otp)
                  ->first();

        if (!$otp) return back()->withErrors(['otp' => 'Invalid OTP.']);
        if ($otp->isExpired()) {
            $otp->delete();
            return back()->withErrors(['otp' => 'OTP expired.']);
        }

        $otp->delete(); // single-use
        Auth::login($user);
        $request->session()->forget('otp_user_id');

        // Redirect based on role
        if ($user->hasRole('Admin')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('Agent')) return redirect()->route('agent.dashboard');
        return redirect()->route('user.dashboard');
    }

    // Resend OTP
    public function resend(Request $request)
    {
        $user_id = $request->session()->get('otp_user_id');
        $user = User::find($user_id);
        if (!$user) return redirect()->route('login');

        // $code = rand(100000, 999999);
        $code = 111111;
        $expires = Carbon::now()->addMinutes(5);

        Otp::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code, 'expires_at' => $expires]
        );

        // Mail::raw("Your new OTP code is: $code", function($message) use ($user) {
        //     $message->to($user->email)
        //             ->subject("Your OTP Code");
        // });

        return back()->with('success', 'OTP resent successfully.');
    }
}
