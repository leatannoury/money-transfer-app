<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Otp;

class SocialAuthController extends Controller
{
    // Redirect to provider
   public function redirect(Request $request, $provider)
{
    $mode = $request->query('mode', 'login');
    session(['social_auth_mode' => $mode]);

    $driver = Socialite::driver($provider)->stateless();

    
    if ($provider === 'google') {
        $driver = $driver->with(['prompt' => 'select_account']);
    }

    return $driver->redirect();
}
    // Handle callback from provider
   public function callback($provider)
{
    try {
        $driver = Socialite::driver($provider)->stateless();

        if ($provider === 'facebook') {
            $driver->fields(['name', 'first_name', 'last_name', 'email'])
                   ->scopes(['email']);
        } elseif ($provider === 'google') {
            $driver->scopes(['email', 'profile']);
        }

        $socialUser = $driver->user();

        \Log::info('Socialite user data:', (array) $socialUser);

        $email = $socialUser->getEmail() ?? $socialUser->getId() . "@{$provider}.local";
        $mode = session()->pull('social_auth_mode', 'login');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $providerName = ucfirst($provider);
            if ($mode !== 'register') {
                return redirect()->route('login')
                    ->with('error', "Account not found. Please register with {$providerName} first.");
            }

            $user = User::create([
                'name' => $socialUser->getName() ?? 'No Name',
                'email' => $email,
                'status' => 'active',
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        if (!$user->hasAnyRole(['Admin','Agent','User'])) {
            $user->assignRole('User');
        }

        if ($user->status === 'banned') {
            return redirect()->route('login')->with('error', 'Your account has been banned.');
        }
        $code = 111111; // or rand(100000, 999999)
        $expires = now()->addMinutes(5);

        Otp::updateOrCreate(
            ['user_id' => $user->id],
            ['code' => $code, 'expires_at' => $expires]
        );

        // Log out user after generating OTP
        Auth::logout();

        // Save user ID for OTP verification
        session(['otp_user_id' => $user->id]);

        // Redirect to OTP form (same as normal login)
        return redirect()->route('otp.form');

    } catch (\Exception $e) {
    \Log::error('Social auth error: ' . $e->getMessage());
    \Log::error('Full stack trace: ' . $e->getTraceAsString());
    return redirect()->route('login')->with('error', 'Login failed. Please try again.');
}

}

}
