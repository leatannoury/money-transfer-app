<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;


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
        if (Role::count() === 0) {
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
    }
    if (!User::where('email', 'admin123@gmail.com')->exists()) {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin123@gmail.com',
            'password' =>'admin123',
            'status' => 'active',
        ]);

        // Assign role (make sure roles are already seeded)
        $admin->assignRole('Admin');
    }
    

    }

}