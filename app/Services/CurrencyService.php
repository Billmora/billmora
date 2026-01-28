<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\App;

class CurrencyService
{
    protected array $cachedCurrencies = [];

    /**
     * Format an amount using the active currency or provided currency.
     *
     * @param float|int|string|null $amount
     * @param \App\Models\Currency|string|null $currency
     * @return string
     */
    public function format(float|int|string|null $amount, Currency|string|null $currency = null): string
    {
        if ($amount === null) {
            return __('client/store.unavailable_currency');
        }

        if (is_string($currency)) {
            $currency = $this->getCurrency($currency);
        }

        $currency = $currency ?? $this->currency();
        $number = $this->formatNumber($amount, $currency->format);

        return trim("{$currency->prefix}{$number} {$currency->suffix}");
    }

    /**
     * Get currency from cache or database.
     *
     * @param string $code
     * @return \App\Models\Currency|null
     */
    protected function getCurrency(string $code): ?Currency
    {
        if (!isset($this->cachedCurrencies[$code])) {
            $this->cachedCurrencies[$code] = Currency::where('code', $code)->first();
        }

        return $this->cachedCurrencies[$code];
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
        $amount = (float) $amount;
        
        return match ($format) {
            '1,234.56' => number_format($amount, 2, '.', ','),
            '1.234,56' => number_format($amount, 2, ',', '.'),
            '1,234' => number_format($amount, 0, '.', ','),
            default => number_format($amount, 2, '.', ''),
        };
    }
}