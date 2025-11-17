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
     * Always uses USD as base currency for reliability
     */
    public static function getExchangeRate(string $toCurrency, string $fromCurrency = 'USD'): float
    {
        // If same currency, return 1
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        // Always use USD as base currency for API calls (most reliable)
        // If fromCurrency is not USD, we need to calculate the inverse rate
        if ($fromCurrency !== 'USD') {
            // Get rate from USD to fromCurrency, then invert
            $usdToFromRate = self::getExchangeRate($fromCurrency, 'USD');
            if ($usdToFromRate == 0 || $usdToFromRate == 1.0) {
                // If rate is invalid, try direct conversion
                return 1.0;
            }
            // Get rate from USD to toCurrency
            $usdToToRate = self::getExchangeRate($toCurrency, 'USD');
            // Calculate: (USD to toCurrency) / (USD to fromCurrency) = fromCurrency to toCurrency
            return $usdToToRate / $usdToFromRate;
        }

        // Cache key for the exchange rate (always USD to target)
        $cacheKey = "exchange_rate_USD_{$toCurrency}";

        // Try to get from cache first (cache for 1 hour)
        $cachedRate = Cache::get($cacheKey);
        if ($cachedRate !== null) {
            // Validate cached rate - if it's 1.0 for a non-USD currency, it's likely invalid
            if ($cachedRate == 1.0 && $toCurrency !== 'USD') {
                \Log::warning("Invalid cached rate detected (1.0) for {$toCurrency}, clearing cache and fetching fresh rate.");
                Cache::forget($cacheKey);
            } else {
                return (float) $cachedRate;
            }
        }

        try {
            // Using exchangerate-api.io free API - always use USD as base
            $response = Http::timeout(5)->get("https://api.exchangerate-api.com/v4/latest/USD");

            if ($response->successful()) {
                $data = $response->json();
                $rate = $data['rates'][$toCurrency] ?? null;

                // If rate not found, try fallback rate
                if ($rate === null || $rate == 0) {
                    \Log::warning("Exchange rate not found in API for currency: {$toCurrency}, using fallback.");
                    $rate = self::getFallbackRate($toCurrency);
                    // Cache the fallback rate for shorter time (15 minutes) since it's approximate
                    Cache::put($cacheKey, $rate, now()->addMinutes(15));
                    return (float) $rate;
                }

                // Cache the rate for 1 hour
                Cache::put($cacheKey, $rate, now()->addHour());

                return (float) $rate;
            }
        } catch (\Exception $e) {
            // Log error but don't break the app
            \Log::warning("Currency conversion failed: " . $e->getMessage());
        }

        // Fallback: use fallback rate if API fails
        \Log::warning("Exchange rate API failed, using fallback rate for {$toCurrency}");
        $fallbackRate = self::getFallbackRate($toCurrency);
        // Cache fallback rate for shorter time (15 minutes)
        Cache::put($cacheKey, $fallbackRate, now()->addMinutes(15));
        return $fallbackRate;
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

    /**
     * Fallback exchange rates (approximate USD base)
     */
    protected static function getFallbackRate(string $currency): float
    {
        $fallbackRates = [
            'USD' => 1.0,
            'EUR' => 0.92,
            'GBP' => 0.78,
            'JPY' => 147.0,
            'AUD' => 1.45,
            'CAD' => 1.35,
            'CHF' => 0.86,
            'CNY' => 7.25,
            'INR' => 83.0,
            'AED' => 3.67,
            'SAR' => 3.75,
            'LBP' => 89500.0,
        ];

        return $fallbackRates[strtoupper($currency)] ?? 1.0;
    }
}
