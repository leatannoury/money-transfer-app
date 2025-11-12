<?php

return [

    // List of valid test credit card numbers (dev only)
    'valid_cards' => [
        [
            'number' => '4111111111111111',
            'provider' => 'Visa',
            'expiry' => '12/25',
            'cardholder_name' => 'John Doe',
            'cvv' => '123',
        ],
        [
            'number' => '5500000000000004',
            'provider' => 'MasterCard',
            'expiry' => '11/26',
            'cardholder_name' => 'Jane Smith',
            'cvv' => '456',
        ],
        [
            'number' => '340000000000009',
            'provider' => 'Amex',
            'expiry' => '08/24',
            'cardholder_name' => 'Alice Johnson',
            'cvv' => '7890',
        ],
        [
            'number' => '6011000000000004',
            'provider' => 'Discover',
            'expiry' => '05/27',
            'cardholder_name' => 'Bob Brown',
            'cvv' => '321',
        ],
        [
            'number' => '4111222233334444',
            'provider' => 'Visa',
            'expiry' => '10/25',
            'cardholder_name' => 'Charlie Green',
            'cvv' => '111',
        ],
        [
            'number' => '5105105105105100',
            'provider' => 'MasterCard',
            'expiry' => '07/26',
            'cardholder_name' => 'Diana Blue',
            'cvv' => '222',
        ],
    ],

];
