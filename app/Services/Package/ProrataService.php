<?php

namespace App\Services\Package;

use App\Models\Package;
use App\Models\PackagePrice;
use Carbon\Carbon;

class ProrataService
{
    /**
     * Billing periods that support pro-rata.
     */
    protected const SUPPORTED_PERIODS = ['monthly', 'yearly'];

    /**
     * Calculate pro-rata data for the first invoice of a new subscription.
     *
     * Returns null when:
     * - The package has no prorata_day configured.
     * - The billing_period is daily or weekly.
     * - The billing_type is not recurring.
     * - The purchase date already falls exactly on the prorata_day (no proration needed).
     *
     * When pro-rata applies, the first invoice will contain:
     * 1. A prorated line item from today → prorata_day_date.
     * 2. A full-period line item from prorata_day_date → prorata_day_date + interval.
     *
     * @param  \App\Models\Package      $package
     * @param  \App\Models\PackagePrice $packagePrice
     * @param  float                    $recurringTotal  Total recurring price (base + variants) in the invoice currency.
     * @param  \Carbon\Carbon|null      $purchaseDate    Defaults to today.
     * @return array|null
     */
    public function calculate(
        Package $package,
        PackagePrice $packagePrice,
        float $recurringTotal,
        ?Carbon $purchaseDate = null
    ): ?array {
        $prorataDay = $package->prorata_day;

        if (!$prorataDay || $packagePrice->type !== 'recurring') {
            return null;
        }

        $period = $packagePrice->billing_period;

        if (!in_array($period, self::SUPPORTED_PERIODS, true)) {
            return null;
        }

        $today = ($purchaseDate ?? Carbon::today())->startOfDay();
        $prorataDayDate = $this->nextProrataDayDate($today, $prorataDay);

        // Edge case: purchase date is exactly on the prorata day — no proration needed.
        if ($prorataDayDate->equalTo($today)) {
            return null;
        }

        $proratedDays     = (int) $today->diffInDays($prorataDayDate);
        $daysInCycle      = $this->daysInCycle($prorataDayDate, $period, $packagePrice->time_interval ?? 1);
        $proratedAmount   = round(($recurringTotal / $daysInCycle) * $proratedDays, 2);
        $firstNextDueDate = $this->advanceByInterval($prorataDayDate, $period, $packagePrice->time_interval ?? 1);

        return [
            'prorata_day_date'    => $prorataDayDate,
            'prorated_days'       => $proratedDays,
            'days_in_cycle'       => $daysInCycle,
            'prorated_amount'     => $proratedAmount,
            'full_period_amount'  => $recurringTotal,
            'first_invoice_total' => $proratedAmount + $recurringTotal,
            'first_next_due_date' => $firstNextDueDate,
        ];
    }

    /**
     * Find the next occurrence of the given prorata day after (or on) the given date.
     * The result is always the *same or later* calendar date.
     *
     * @param  \Carbon\Carbon  $from
     * @param  int             $prorataDay  1–28
     * @return \Carbon\Carbon
     */
    public function nextProrataDayDate(Carbon $from, int $prorataDay): Carbon
    {
        $candidate = $from->copy()->startOfDay()->day($prorataDay);

        // If the prorata day this month has already passed (or is today), move to next month.
        if ($candidate->lt($from)) {
            $candidate->addMonthNoOverflow()->day($prorataDay);
        }

        return $candidate;
    }

    /**
     * Calculate the number of days in a billing cycle starting at the given anchor date.
     *
     * For monthly: use the actual days in the month the cycle begins.
     * For yearly:  use 365 days (fixed, for simplicity and consistency).
     *
     * @param  \Carbon\Carbon  $cycleStart
     * @param  string          $period
     * @param  int             $interval
     * @return int
     */
    protected function daysInCycle(Carbon $cycleStart, string $period, int $interval): int
    {
        return match ($period) {
            'monthly' => (int) abs($cycleStart->copy()->addMonthsNoOverflow($interval)->diffInDays($cycleStart)),
            'yearly'  => 365 * $interval,
            default   => 30,
        };
    }

    /**
     * Advance a date by one billing interval.
     *
     * @param  \Carbon\Carbon  $date
     * @param  string          $period
     * @param  int             $interval
     * @return \Carbon\Carbon
     */
    public function advanceByInterval(Carbon $date, string $period, int $interval): Carbon
    {
        return match ($period) {
            'monthly' => $date->copy()->addMonthsNoOverflow($interval),
            'yearly'  => $date->copy()->addYears($interval),
            default   => $date->copy()->addDays($interval),
        };
    }
}
