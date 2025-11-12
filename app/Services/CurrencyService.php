<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    /**
     * Supported currencies
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'JPY' => 'Japanese Yen',
            'AUD' => 'Australian Dollar',
            'CAD' => 'Canadian Dollar',
            'CHF' => 'Swiss Franc',
            'CNY' => 'Chinese Yuan',
            'INR' => 'Indian Rupee',
            'AED' => 'UAE Dirham',
            'SAR' => 'Saudi Riyal',
            'LBP' => 'Lebanese Pound',
        ];
    }

    /**
     * Get exchange rate from USD to target currency
     * Uses exchangerate-api.io (free tier: 1500 requests/month)
     */
    public static function getExchangeRate(string $toCurrency, string $fromCurrency = 'USD'): float
    {
        // If same currency, return 1
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        // Cache key for the exchange rate
        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";

        // Try to get from cache first (cache for 1 hour)
        $cachedRate = Cache::get($cacheKey);
        if ($cachedRate !== null) {
            return (float) $cachedRate;
        }

        try {
            // Using exchangerate-api.io free API
            // You can also use: https://api.exchangerate-api.com/v4/latest/USD
            $response = Http::timeout(5)->get("https://api.exchangerate-api.com/v4/latest/{$fromCurrency}");

            if ($response->successful()) {
                $data = $response->json();
                $rate = $data['rates'][$toCurrency] ?? 1.0;

                // Cache the rate for 1 hour
                Cache::put($cacheKey, $rate, now()->addHour());

                return (float) $rate;
            }
        } catch (\Exception $e) {
            // Log error but don't break the app
            \Log::warning("Currency conversion failed: " . $e->getMessage());
        }

        // Fallback: return 1 if API fails (assume same currency)
        return 1.0;
    }

    /**
     * Convert amount from one currency to another
     */
    public static function convert(float $amount, string $toCurrency, string $fromCurrency = 'USD'): float
    {
        $rate = self::getExchangeRate($toCurrency, $fromCurrency);
        return $amount * $rate;
    }

    /**
     * Format currency amount with symbol
     */
    public static function format(float $amount, string $currency): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => 'CHF ',
            'CNY' => '¥',
            'INR' => '₹',
            'AED' => 'AED ',
            'SAR' => 'SAR ',
            'LBP' => 'LBP ',
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        $decimals = in_array($currency, ['JPY', 'LBP']) ? 0 : 2;

        return $symbol . number_format($amount, $decimals);
    }
}

