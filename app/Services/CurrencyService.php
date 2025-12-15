<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\App;

class CurrencyService
{

    /**
     * Format an amount using the active currency.
     *
     * @param float|int|string|null $amount
     * @return string
     */
    public function format(float|int|string|null $amount): string
    {
        if ($amount === null) {
            return __('client/store.unavailable_currency');
        }

        $currency = $this->currency();

        $number = $this->formatNumber($amount, $currency->format);

        return trim("{$currency->prefix}{$number} {$currency->suffix}");
    }

    /**
     * Resolve the active currency instance.
     *
     * @return \App\Models\Currency
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function currency(): Currency
    {
        if (App::bound(Currency::class)) {
            return App::make(Currency::class);
        }

        return Currency::where('is_default', true)->firstOrFail();
    }

    /**
     * Format a numeric amount based on the given currency format.
     *
     * @param float|int|string $amount
     * @param string $format
     * @return string
     */
    protected function formatNumber(float|int|string $amount, string $format): string
    {
        return match ($format) {
            '1,234.56' => number_format($amount, 2, '.', ','),
            '1.234,56' => number_format($amount, 2, ',', '.'),
            '1,234' => number_format($amount, 0, '.', ','),
            default => number_format($amount, 2, '.', ''),
        };
    }
}