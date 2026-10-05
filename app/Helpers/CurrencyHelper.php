<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CurrencyHelper
{
    public static function rates()
    {
        return Cache::remember('usd_rates', 86400, function () {

            try {
                $response = Http::timeout(3)->get(
                    'https://api.frankfurter.app/latest?from=USD'
                );

                return $response->successful()
                    ? $response->json('rates', [])
                    : [];

            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public static function toUsd($amount, $currency)
    {
        $currency = strtoupper(trim((string) $currency));
        $amount = (float) $amount;

        if ($currency === 'USD') {
            return $amount;
        }

        $rates = self::rates();

        return isset($rates[$currency]) && $rates[$currency] > 0
            ? $amount / $rates[$currency]
            : 0;
    }
}