<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // <-- Add this

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
    }
}
