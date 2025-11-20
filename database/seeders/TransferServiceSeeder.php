<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransferServiceSeeder extends Seeder
{

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

private $destinationTypes = [
    'wallet' => 'Wallet Transfer',
    'card' => 'Card Transfer',
    'bank' => 'Bank Transfer',
    'cash_pickup' => 'Cash Pickup',
];

private function getBaseFallbackRate(string $currency): float
{
    // Fix LBP, JOD, and TRY rates to be more realistic (as of late 2023/2024)
    $fallbackRates = [
        'USD' => 1.0,
        // ... (other currencies)
        'AED' => 3.67,
        'SAR' => 3.75,
        // CRITICAL FIX: LBP rate should be the official pegged rate for money transfer 
        // (If 1 USD = 89,500 LBP is the desired rate for the app)
        'LBP' => 89500.0, 
        // JOD is pegged near 0.71, so 1 USD buys ~0.71 JOD. 
        // Rate is 1/0.71 = ~1.4. The rate in the array is USD/X, so X/USD is what we need.
        // Wait, the function returns a value X for the rate 1 USD = X Currency
        // JOD is 1 JOD = 1.41 USD. So 1 USD = 0.71 JOD.
        'JOD' => 0.71, 
        // TRY is around 30.5 at the time of writing
        'TRY' => 30.50, 
        'EGP' => 31.0,    
    ];

    // If the currency is not found, it defaults to 1.0 (USD)
    return $fallbackRates[strtoupper($currency)] ?? 1.0;
}

    public function run(): void
    {
        $countries = [
            'Lebanon',
            'Egypt',
            'Jordan',
            'UAE',
            'Turkey',
            'USA',
        ];

        $services = [];
        $id = 1;

        foreach ($countries as $country) {

            $destinationCurrency = $this->getCurrencyForCountry($country);
            $baseRate = $this->getBaseFallbackRate($destinationCurrency);

            if ($country === 'Lebanon') {
                // Lebanon only has a 'wallet' destination type
                $destinationType = 'wallet';
                
                $services[] = [
                    'id' => $id++,
                    'name' => 'Local Wallet Transfer', // Unique name for this service
                    // Source Type is no longer relevant for the unique service, set to null or a placeholder
                    'source_type' => null, 
                    'destination_type' => $destinationType,
                    'destination_country' => 'Lebanon',
                    'speed' => 'instant',
                    'fee' => 0.50,
                    'exchange_rate' => 89500.0, 
                    'promotion_active' => true,
                    'promotion_text' => 'First transfer free for local wallet.',
                    'destination_currency' => $destinationCurrency,
                ];

            } else {
                // All other countries have Card, Bank, and Cash Pickup destinations
                foreach ($this->destinationTypes as $destinationType => $nameSuffix) {
                    
                    // Skip 'wallet' for non-Lebanon destinations
                    if ($destinationType === 'wallet') {
                        continue;
                    }

                    $variance = mt_rand(-50, 50) / 10000;
                    $rate = round($baseRate * (1 + $variance), 4);
                    
                    // The service name is now just based on the destination method
                    $serviceName = $nameSuffix; 

                    $services[] = [
                        'id' => $id++,
                        'name' => $serviceName,
                        'source_type' => null, // Source Type is no longer relevant for the unique service
                        'destination_type' => $destinationType,
                        'destination_country' => $country,
                        'speed' => 'instant',
                        'fee' => mt_rand(500, 2000) / 100,
                        'exchange_rate' => $rate,
                        'promotion_active' => (bool) rand(0, 1),
                        'promotion_text' => rand(0, 1) ? '1st Transfer Free' : 'Best Rate Guarantee',
                        'destination_currency' => $destinationCurrency,
                    ];
                }
            }
        }

        // Insert/Update into database
        foreach ($services as $s) {
            DB::table('transfer_services')->updateOrInsert(
                [
                    // Use a combination of name, destination type, and country as the unique key
                    'name' => $s['name'],
                    'destination_type' => $s['destination_type'],
                    'destination_country' => $s['destination_country'],
                    // source_type is no longer needed in the unique key
                ],
                [
                    'source_type' => $s['source_type'], // Keep null/placeholder in the DB record
                    'speed' => $s['speed'],
                    'fee' => $s['fee'],
                    'exchange_rate' => $s['exchange_rate'],
                    'promotion_active' => $s['promotion_active'],
                    'promotion_text' => $s['promotion_text'],
                    'destination_currency' => $s['destination_currency'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }


    private function getCurrencyForCountry(string $countryName): string
    {
        
        return $this->fallbackCurrencyMap[$countryName] ?? 'USD';
    }
}