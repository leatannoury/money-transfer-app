<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    // Redirect to provider
    public function redirect(Request $request, $provider)
    {
        $mode = $request->query('mode', 'login');
        session(['social_auth_mode' => $mode]);

        return Socialite::driver($provider)->stateless()->redirect();
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

        Auth::login($user, true);
        request()->session()->regenerate();

        if ($user->hasRole('Admin')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('Agent')) return redirect()->route('agent.dashboard');
        return redirect()->route('user.dashboard');

    } catch (\Exception $e) {
    \Log::error('Social auth error: ' . $e->getMessage());
    \Log::error('Full stack trace: ' . $e->getTraceAsString());
    return redirect()->route('login')->with('error', 'Login failed. Please try again.');
}

}

}
