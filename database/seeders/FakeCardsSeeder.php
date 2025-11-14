<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FakeCardsSeeder extends Seeder
{
    /**
     * NOTE: DEV ONLY
     * This seeder seeds test PANs and CVVs. Do NOT use this in production.
     */
    public function run(): void
    {
        // If you prefer, read from config('fake_cards.valid_cards'), but duplicating here
        // ensures seed is stable for everyone who pulls the repository.
        $cards = [
            [
                'card_number' => '4111111111111111',
                'provider' => 'Visa',
                'cardholder_name' => 'Lea Tannoury',
                'expiry' => '12/25',
                'cvv' => '123',
                'balance' => 10000.00,
            ],
            [
                'card_number' => '5500000000000004',
                'provider' => 'MasterCard',
                'cardholder_name' => 'Sara Harika',
                'expiry' => '11/26',
                'cvv' => '456',
                'balance' => 5000.00,
            ],
            [
                'card_number' => '340000000000009',
                'provider' => 'Amex',
                'cardholder_name' => 'Elio Mallo',
                'expiry' => '08/24',
                'cvv' => '7890',
                'balance' => 2000.00,
            ],
            [
                'card_number' => '6011000000000004',
                'provider' => 'Discover',
                'cardholder_name' => 'Elio Sarkis',
                'expiry' => '05/27',
                'cvv' => '321',
                'balance' => 7500.00,
            ],
            [
                'card_number' => '4111222233334444',
                'provider' => 'Visa',
                'cardholder_name' => 'Toni Zouki',
                'expiry' => '10/25',
                'cvv' => '111',
                'balance' => 1200.00,
            ],
            [
                'card_number' => '5105105105105100',
                'provider' => 'MasterCard',
                'cardholder_name' => 'Cristina Maalouf',
                'expiry' => '07/26',
                'cvv' => '222',
                'balance' => 3000.00,
            ],
        ];

        foreach ($cards as $c) {
            DB::table('fake_cards')->updateOrInsert(
                ['card_number' => $c['card_number']],
                [
                    'provider' => $c['provider'],
                    'cardholder_name' => $c['cardholder_name'],
                    'expiry' => $c['expiry'],
                    'cvv' => $c['cvv'],
                    'balance' => $c['balance'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
