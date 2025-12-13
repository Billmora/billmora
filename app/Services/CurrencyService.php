<?php

namespace App\Services;

use App\Models\Currency;

class CurrencyService
{
    /**
     * The default currency model instance.
     *
     * @var \App\Models\Currency
     */
    protected Currency $default;

    /**
     * Initialize the service and fetch the default currency.
     *
     * @return void
     */
    public function __construct()
    {
        $this->default = Currency::where('is_default', true)->first();
    }

    /**
     * Format an amount into the specified or default currency format.
     *
     * @param  float|int|string  $amount   The numeric amount to format
     * @param  string|null       $currencyCode Optional currency code to override default
     * @return string            The formatted currency string
     */
    public function format(float|int|string $amount, ?string $currencyCode = null): string
    {
        $currency = $currencyCode
            ? Currency::where('code', $currencyCode)->first()
            : $this->default;

        $currency ??= $this->default;

        $number = $this->formatNumber($amount, $currency->format);

        return trim("{$currency->prefix}{$number} {$currency->suffix}");
    }

    /**
     * Format a number based on the given number format.
     *
     * @param  float|int|string  $amount  The raw amount to be formatted
     * @param  string            $format  The number formatting style
     * @return string             The formatted number
     */
    protected function formatNumber(float|int|string $amount, string $format): string
    {
        return match($format) {
            '1,234.56' => number_format($amount, 2, '.', ','),
            '1.234,56' => number_format($amount, 2, ',', '.'),
            '1,234' => number_format($amount, 0, '.', ','),
            default => number_format($amount, 2, '.', ''),
        };
    }

    /**
     * Get the prefix of the specified currency or default currency.
     *
     * @param  string|null  $currencyCode  Currency code, optional
     * @return string|null  The prefix string
     */
    public function getPrefix(?string $currencyCode = null): ?string
    {
        if ($currencyCode) {
            return Currency::where('code', $currencyCode)->value('prefix')
                ?? $this->default->prefix;
        }

        return $this->default->prefix;
    }

    /**
     * Get the suffix of the specified currency or default currency.
     *
     * @param  string|null  $currencyCode  Currency code, optional
     * @return string|null  The suffix string
     */
    public function getSuffix(?string $currencyCode = null): ?string
    {
        if ($currencyCode) {
            return Currency::where('code', $currencyCode)->value('suffix')
                ?? $this->default->suffix;
        }

        return $this->default->suffix;
    }

    /**
     * Get the default currency model.
     *
     * @return \App\Models\Currency
     */
    public function default(): Currency
    {
        return $this->default;
    }
}