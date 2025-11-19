<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only run if 'roles' table exists
        if (Schema::hasTable('roles') && Role::count() === 0) {
            Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
        }

        // Only run if 'users' table exists
        if (Schema::hasTable('users') && !User::where('email', 'admin123@gmail.com')->exists()) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin123@gmail.com',
                'password' => bcrypt('admin123'), // <-- hash password
                'status' => 'active',
            ]);

            $admin->assignRole('Admin');
        }

        // Only run if 'fake_bank_accounts' table exists
        if (Schema::hasTable('fake_bank_accounts') && DB::table('fake_bank_accounts')->count() === 0) {
            Artisan::call('db:seed', ['--class' => 'FakeBankAccountsSeeder']);
        }

        // Only run if 'fake_cards' table exists
        if (Schema::hasTable('fake_cards') && DB::table('fake_cards')->count() === 0) {
            Artisan::call('db:seed', ['--class' => 'FakeCardsSeeder']);
        }

        if (Schema::hasTable('user_notifications')) {
            View::composer('user.*', function ($view) {
                $notifications = collect();
                $unreadCount = 0;
                $user = Auth::user();

                if ($user && $user->hasRole('User')) {
                    $notifications = $user->userNotifications()
                        ->latest()
                        ->take(10)
                        ->get();

                    $unreadCount = $user->userNotifications()
                        ->where('is_read', false)
                        ->count();
                }

                $view->with('userBellNotifications', $notifications)
                    ->with('userBellUnreadCount', $unreadCount);
            });

            View::composer('admin.*', function ($view) {
                $notifications = collect();
                $unreadCount = 0;
                $admin = Auth::user();

                if ($admin && $admin->hasRole('Admin')) {
                    $notifications = $admin->userNotifications()
                        ->latest()
                        ->take(10)
                        ->get();

                    $unreadCount = $admin->userNotifications()
                        ->where('is_read', false)
                        ->count();
                }

                $view->with('adminBellNotifications', $notifications)
                    ->with('adminBellUnreadCount', $unreadCount);
            });
        }
    }
}
