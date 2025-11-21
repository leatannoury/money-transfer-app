<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderSeeder extends Seeder
{
    public function run()
    {
        $providers = [
            // --------------------
            // Turkey (TR)
            // --------------------
            ['name' => 'TurkeyExpress', 'country_code' => 'TR', 'is_active' => true],
            ['name' => 'Anatolia Money', 'country_code' => 'TR', 'is_active' => true],
            ['name' => 'Istanbul Transfer', 'country_code' => 'TR', 'is_active' => true],

            // --------------------
            // Jordan (JO)
            // --------------------
            ['name' => 'JordanCash', 'country_code' => 'JO', 'is_active' => true],
            ['name' => 'Petra Payouts', 'country_code' => 'JO', 'is_active' => true],
            ['name' => 'Amman Express', 'country_code' => 'JO', 'is_active' => true],

            // --------------------
            // Egypt (EG)
            // --------------------
            ['name' => 'Nile Transfers', 'country_code' => 'EG', 'is_active' => true],
            ['name' => 'Cairo Cash', 'country_code' => 'EG', 'is_active' => true],
            ['name' => 'Pyramid Payments', 'country_code' => 'EG', 'is_active' => true],

            // --------------------
            // United Arab Emirates (AE)
            // --------------------
            ['name' => 'Dubai Exchange', 'country_code' => 'AE', 'is_active' => true],
            ['name' => 'EmiratesMoney', 'country_code' => 'AE', 'is_active' => true],
            ['name' => 'Abu Dhabi Remit', 'country_code' => 'AE', 'is_active' => true],

            // --------------------
            // United States of America (US)
            // --------------------
            ['name' => 'US QuickTransfer', 'country_code' => 'US', 'is_active' => true],
            ['name' => 'American Remit', 'country_code' => 'US', 'is_active' => true],
            ['name' => 'Liberty Money', 'country_code' => 'US', 'is_active' => true],
        ];

        foreach ($providers as $provider) {
            DB::table('providers')->updateOrInsert(
                ['name' => $provider['name'], 'country_code' => $provider['country_code']],
                array_merge($provider, [
                    'created_at' => now(), 
                    'updated_at' => now()
                ])
            );
        }
    }
}