<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransferServiceSeeder extends Seeder
{
    /**
     * Map of all countries (full name => ISO Alpha-2 code)
     * Used for Rinvex to fetch currencies.
     */
private $fallbackCurrencyMap = [
    'Afghanistan' => 'AFN',
    'Albania' => 'ALL',
    'Algeria' => 'DZD',
    'Andorra' => 'EUR',
    'Angola' => 'AOA',
    'Antigua and Barbuda' => 'XCD',
    'Argentina' => 'ARS',
    'Armenia' => 'AMD',
    'Australia' => 'AUD',
    'Austria' => 'EUR',
    'Azerbaijan' => 'AZN',
    'Bahamas' => 'BSD',
    'Bahrain' => 'BHD',
    'Bangladesh' => 'BDT',
    'Barbados' => 'BBD',
    'Belarus' => 'BYN',
    'Belgium' => 'EUR',
    'Belize' => 'BZD',
    'Benin' => 'XOF',
    'Bhutan' => 'BTN',
    'Bolivia' => 'BOB',
    'Bosnia and Herzegovina' => 'BAM',
    'Botswana' => 'BWP',
    'Brazil' => 'BRL',
    'Brunei' => 'BND',
    'Bulgaria' => 'BGN',
    'Burkina Faso' => 'XOF',
    'Burundi' => 'BIF',
    'Cabo Verde' => 'CVE',
    'Cambodia' => 'KHR',
    'Cameroon' => 'XAF',
    'Canada' => 'CAD',
    'Central African Republic' => 'XAF',
    'Chad' => 'XAF',
    'Chile' => 'CLP',
    'China' => 'CNY',
    'Colombia' => 'COP',
    'Comoros' => 'KMF',
    'Congo, Democratic Republic of the' => 'CDF',
    'Congo, Republic of the' => 'XAF',
    'Costa Rica' => 'CRC',
    'Croatia' => 'HRK',
    'Cuba' => 'CUP',
    'Cyprus' => 'EUR',
    'Czech Republic' => 'CZK',
    'Denmark' => 'DKK',
    'Djibouti' => 'DJF',
    'Dominica' => 'XCD',
    'Dominican Republic' => 'DOP',
    'Ecuador' => 'USD',
    'Egypt' => 'EGP',
    'El Salvador' => 'USD',
    'Equatorial Guinea' => 'XAF',
    'Eritrea' => 'ERN',
    'Estonia' => 'EUR',
    'Eswatini' => 'SZL',
    'Ethiopia' => 'ETB',
    'Fiji' => 'FJD',
    'Finland' => 'EUR',
    'France' => 'EUR',
    'Gabon' => 'XAF',
    'Gambia' => 'GMD',
    'Georgia' => 'GEL',
    'Germany' => 'EUR',
    'Ghana' => 'GHS',
    'Greece' => 'EUR',
    'Grenada' => 'XCD',
    'Guatemala' => 'GTQ',
    'Guinea' => 'GNF',
    'Guinea-Bissau' => 'XOF',
    'Guyana' => 'GYD',
    'Haiti' => 'HTG',
    'Honduras' => 'HNL',
    'Hungary' => 'HUF',
    'Iceland' => 'ISK',
    'India' => 'INR',
    'Indonesia' => 'IDR',
    'Iran' => 'IRR',
    'Iraq' => 'IQD',
    'Ireland' => 'EUR',
    'Israel' => 'ILS',
    'Italy' => 'EUR',
    'Jamaica' => 'JMD',
    'Japan' => 'JPY',
    'Jordan' => 'JOD',
    'Kazakhstan' => 'KZT',
    'Kenya' => 'KES',
    'Kiribati' => 'AUD',
    'Korea, North' => 'KPW',
    'Korea, South' => 'KRW',
    'Kuwait' => 'KWD',
    'Kyrgyzstan' => 'KGS',
    'Laos' => 'LAK',
    'Latvia' => 'EUR',
    'Lebanon' => 'LBP',
    'Lesotho' => 'LSL',
    'Liberia' => 'LRD',
    'Libya' => 'LYD',
    'Liechtenstein' => 'CHF',
    'Lithuania' => 'EUR',
    'Luxembourg' => 'EUR',
    'Madagascar' => 'MGA',
    'Malawi' => 'MWK',
    'Malaysia' => 'MYR',
    'Maldives' => 'MVR',
    'Mali' => 'XOF',
    'Malta' => 'EUR',
    'Marshall Islands' => 'USD',
    'Mauritania' => 'MRU',
    'Mauritius' => 'MUR',
    'Mexico' => 'MXN',
    'Micronesia' => 'USD',
    'Moldova' => 'MDL',
    'Monaco' => 'EUR',
    'Mongolia' => 'MNT',
    'Montenegro' => 'EUR',
    'Morocco' => 'MAD',
    'Mozambique' => 'MZN',
    'Myanmar' => 'MMK',
    'Namibia' => 'NAD',
    'Nauru' => 'AUD',
    'Nepal' => 'NPR',
    'Netherlands' => 'EUR',
    'New Zealand' => 'NZD',
    'Nicaragua' => 'NIO',
    'Niger' => 'XOF',
    'Nigeria' => 'NGN',
    'North Macedonia' => 'MKD',
    'Norway' => 'NOK',
    'Oman' => 'OMR',
    'Pakistan' => 'PKR',
    'Palau' => 'USD',
    'Panama' => 'PAB',
    'Papua New Guinea' => 'PGK',
    'Paraguay' => 'PYG',
    'Peru' => 'PEN',
    'Philippines' => 'PHP',
    'Poland' => 'PLN',
    'Portugal' => 'EUR',
    'Qatar' => 'QAR',
    'Romania' => 'RON',
    'Russia' => 'RUB',
    'Rwanda' => 'RWF',
    'Saint Kitts and Nevis' => 'XCD',
    'Saint Lucia' => 'XCD',
    'Saint Vincent and the Grenadines' => 'XCD',
    'Samoa' => 'WST',
    'San Marino' => 'EUR',
    'Sao Tome and Principe' => 'STN',
    'Saudi Arabia' => 'SAR',
    'Senegal' => 'XOF',
    'Serbia' => 'RSD',
    'Seychelles' => 'SCR',
    'Sierra Leone' => 'SLL',
    'Singapore' => 'SGD',
    'Slovakia' => 'EUR',
    'Slovenia' => 'EUR',
    'Solomon Islands' => 'SBD',
    'Somalia' => 'SOS',
    'South Africa' => 'ZAR',
    'Spain' => 'EUR',
    'Sri Lanka' => 'LKR',
    'Sudan' => 'SDG',
    'Suriname' => 'SRD',
    'Sweden' => 'SEK',
    'Switzerland' => 'CHF',
    'Syria' => 'SYP',
    'Taiwan' => 'TWD',
    'Tajikistan' => 'TJS',
    'Tanzania' => 'TZS',
    'Thailand' => 'THB',
    'Timor-Leste' => 'USD',
    'Togo' => 'XOF',
    'Tonga' => 'TOP',
    'Trinidad and Tobago' => 'TTD',
    'Tunisia' => 'TND',
    'Turkey' => 'TRY',
    'Turkmenistan' => 'TMT',
    'Tuvalu' => 'AUD',
    'Uganda' => 'UGX',
    'Ukraine' => 'UAH',
    'United Arab Emirates' => 'AED',
    'United Kingdom' => 'GBP',
    'United States' => 'USD',
    'Uruguay' => 'UYU',
    'Uzbekistan' => 'UZS',
    'Vanuatu' => 'VUV',
    'Vatican City' => 'EUR',
    'Venezuela' => 'VES',
    'Vietnam' => 'VND',
    'Yemen' => 'YER',
    'Zambia' => 'ZMW',
    'Zimbabwe' => 'ZWL',
];

// In Database/Seeders/TransferServiceSeeder.php

public function run(): void
{
    // Define your supported countries. Make sure 'Lebanon' is included.
    $countries = [
        'Lebanon',
        'Egypt',
        'Jordan',
        'UAE',
        'Turkey',
        'USA',
        // Add all other non-Lebanon countries here
    ];

    $services = [];
    $id = 1; 

    foreach ($countries as $country) {
        // Fetch the currency for the destination country (using your existing helper method)
        $destinationCurrency = $this->getCurrencyForCountry($country);
        
        if ($country === 'Lebanon') {
            // Rule 1: Lebanon - Only Wallet to Wallet
            $services[] = [
                'id' => $id++,
                'name' => 'Local Wallet Transfer',
                'source_type' => 'wallet',
                'destination_type' => 'wallet',
                'destination_country' => 'Lebanon',
                'speed' => 'instant',
                'fee' => 0.50,
                'exchange_rate' => 1.00, 
                'promotion_active' => true,
                'promotion_text' => 'First transfer free for local wallet.',
                'destination_currency' => $destinationCurrency,
            ];
        } else {
            // Rule 2: Other Countries - All requested combinations
            $combinations = [
                // Wallet to: Card, Bank, Cash Pick Up
                ['source' => 'wallet', 'destination' => 'card', 'name' => 'Wallet to Card'],
                ['source' => 'wallet', 'destination' => 'bank', 'name' => 'Wallet to Bank'],
                ['source' => 'wallet', 'destination' => 'cash_pickup', 'name' => 'Wallet to Cash Pickup'],

                // Card to: Card, Bank, Cash Pick Up
                ['source' => 'card', 'destination' => 'card', 'name' => 'Card to Card'],
                ['source' => 'card', 'destination' => 'bank', 'name' => 'Card to Bank'],
                ['source' => 'card', 'destination' => 'cash_pickup', 'name' => 'Card to Cash Pickup'],

                // Bank to: Bank, Card, Cash Pick Up
                ['source' => 'bank', 'destination' => 'bank', 'name' => 'Bank to Bank'],
                ['source' => 'bank', 'destination' => 'card', 'name' => 'Bank to Card'],
                ['source' => 'bank', 'destination' => 'cash_pickup', 'name' => 'Bank to Cash Pickup'],
            ];

            foreach ($combinations as $combo) {
                $services[] = [
                    'id' => $id++,
                    'name' => $combo['name'] . ' (' . $country . ')',
                    'source_type' => $combo['source'],
                    'destination_type' => $combo['destination'],
                    'destination_country' => $country,
                    // Example values - adjust these rates/fees as needed
                    'speed' => $combo['destination'] === 'cash_pickup' ? 'same_day' : 'instant',
                    'fee' => ($combo['destination'] === 'cash_pickup' ? 5.00 : 2.00),
                    'exchange_rate' => 1.00, 
                    'promotion_active' => false,
                    'promotion_text' => null,
                    'destination_currency' => $destinationCurrency,
                ];
            }
        }
    }

    // --- Existing updateOrInsert loop to seed the database ---
    foreach ($services as $s) {
        DB::table('transfer_services')->updateOrInsert(
            [
                'name' => $s['name'],
                'source_type' => $s['source_type'],
                'destination_type' => $s['destination_type'],
                'destination_country' => $s['destination_country'],
            ],
            [
                'speed' => $s['speed'],
                'fee' => $s['fee'],
                'exchange_rate' => $s['exchange_rate'],
                'promotion_active' => $s['promotion_active'],
                'promotion_text' => $s['promotion_text'] ?? null,
                'destination_currency' => $s['destination_currency'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

private function getCurrencyForCountry(string $countryName): string
{
    $iso = $this->countryMap[$countryName] ?? null;
    if (!$iso) return $this->fallbackCurrencyMap[$countryName] ?? 'USD';

    $country = country($iso);
    if ($country) {
        $currencies = $country->getCurrencies();
        if (!empty($currencies) && isset($currencies[0]['iso_4217_code'])) {
            return $currencies[0]['iso_4217_code'];
        }
    }

    // fallback to predefined currency map
    return $this->fallbackCurrencyMap[$countryName] ?? 'USD';
}


}
