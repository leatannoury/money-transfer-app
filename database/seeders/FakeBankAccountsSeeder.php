<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakeBankAccountsSeeder extends Seeder
{
    /**
     * NOTE: DEV ONLY
     * Seeds test bank accounts. Do NOT use in production.
     */
    public function run(): void
    {
        $accounts = [
            [
                'bank_name' => 'Byblos Bank',
                'account_number' => '000123456789',
                'routing' => '111000025',
                'account_holder' => 'Lea Tannoury',
                'account_type' => 'checking',
                'balance' => 5000.00,
            ],
            [
                'bank_name' => 'Fransabank',
                'account_number' => '000987654321',
                'routing' => '222000111',
                'account_holder' => 'Sara Harika',
                'account_type' => 'savings',
                'balance' => 3000.00,
            ],
            [
                'bank_name' => 'SGBL Bank',
                'account_number' => '000555666777',
                'routing' => '333000444',
                'account_holder' => 'Elio Mallo',
                'account_type' => 'checking',
                'balance' => 2000.00,
            ],
            [
                'bank_name' => 'BLOM Bank',
                'account_number' => '000888999000',
                'routing' => '444000555',
                'account_holder' => 'Elio Sarkis',
                'account_type' => 'savings',
                'balance' => 7500.00,
            ],
            [
                'bank_name' => 'BLC Bank',
                'account_number' => '000111222333',
                'routing' => '555000666',
                'account_holder' => 'Toni Zouki',
                'account_type' => 'checking',
                'balance' => 1200.00,
            ],
            [
                'bank_name' => 'Bank Audi',
                'account_number' => '000444555666',
                'routing' => '666000777',
                'account_holder' => 'Cristina Maalouf',
                'account_type' => 'savings',
                'balance' => 3000.00,
            ],
        ];

        foreach ($accounts as $a) {
            DB::table('fake_bank_accounts')->updateOrInsert(
                ['account_number' => $a['account_number']],
                [
                    'bank_name' => $a['bank_name'],
                    'routing' => $a['routing'],
                    'account_holder' => $a['account_holder'],
                    'account_type' => $a['account_type'],
                    'balance' => $a['balance'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
