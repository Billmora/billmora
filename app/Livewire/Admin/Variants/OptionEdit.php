<?php

namespace App\Livewire\Admin\Variants;

use App\Models\Currency;
use App\Models\Variant;
use App\Models\VariantOption;
use Livewire\Component;

class OptionEdit extends Component
{
    public Variant $variant;
    public VariantOption $option;
    public array $pricings = [];

    /**
     * Initialize the component for the given variant and option.
     * Loads the option's existing prices ordered by ID, maps each price into
     * an editable structure with per-currency rate entries, merging any missing
     * currencies with default fallback values. Restores previously submitted
     * pricing entries from old input on a failed form submission.
     * Appends a blank pricing entry if the resolved list is empty.
     *
     * @param  \App\Models\Variant        $variant
     * @param  \App\Models\VariantOption  $option
     * @return void
     */
    public function mount(Variant $variant, VariantOption $option)
    {
        $this->variant = $variant;
        $this->option = $option;

        $currenciesList = \Illuminate\Support\Facades\View::shared('currencies');

        $option->load(['prices' => fn($q) => $q->orderBy('id')]);

        $pricingsFromDb = [];
        foreach ($option->prices as $price) {
            $ratesDb = is_string($price->rates) ? json_decode($price->rates, true) : ($price->rates ?? []);
            $rates = [];

            foreach ($currenciesList as $currency) {
                $rates[$currency->code] = [
                    'currency' => $currency->code,
                    'price' => $ratesDb[$currency->code]['price'] ?? '',
                    'setup_fee' => $ratesDb[$currency->code]['setup_fee'] ?? '0',
                    'enabled' => $ratesDb[$currency->code]['enabled'] ?? $currency->is_default,
                ];
            }

            $pricingsFromDb[] = [
                'uid' => 'uid_' . $price->id,
                'id' => $price->id,
                'name' => $price->name,
                'type' => $price->type,
                'time_interval' => $price->time_interval,
                'billing_period' => $price->billing_period,
                'rates' => $rates,
            ];
        }

        $oldPricings = old('pricings');

        if (!empty($oldPricings)) {
            $this->pricings = [];
            foreach ($oldPricings as $pricing) {
                $pricing['uid'] = $pricing['uid'] ?? uniqid('uid_');
                $this->pricings[] = $pricing;
            }
        } else {
            $this->pricings = $pricingsFromDb;
        }

        if (empty($this->pricings)) {
            $this->addPrice();
        }
    }

    /**
     * Build and return a blank pricing structure pre-populated with a rate entry
     * for every available currency. Only the default currency is enabled by default.
     * Used as the template when adding a new pricing row.
     *
     * @return array{
     *     name: string,
     *     type: string,
     *     time_interval: string,
     *     billing_period: string,
     *     rates: array<string, array{currency: string, price: string, setup_fee: string, enabled: bool}>
     * }
     */
    private function getEmptyPricingStructure()
    {
        $currenciesList = \Illuminate\Support\Facades\View::shared('currencies');
        $rates = [];

        foreach ($currenciesList as $currency) {
            $rates[$currency->code] = [
                'currency' => $currency->code,
                'price' => '',
                'setup_fee' => '0',
                'enabled' => $currency->is_default,
            ];
        }

        return [
            'uid' => uniqid('uid_'),
            'name' => '',
            'type' => 'free',
            'time_interval' => '',
            'billing_period' => 'monthly',
            'rates' => $rates,
        ];
    }

    /**
     * Append a new blank pricing entry to the pricings list
     * using the default empty pricing structure.
     *
     * @return void
     */
    public function addPrice()
    {
        $this->pricings[] = $this->getEmptyPricingStructure();
    }

    /**
     * Remove a pricing entry at the given index and re-index the array.
     * Automatically appends a blank pricing entry if the list becomes empty
     * to ensure at least one pricing row is always present.
     *
     * @param  string  $uid  The unique identifier of the pricing entry to remove
     * @return void
     */
    public function removePrice($uid)
    {
        $this->pricings = collect($this->pricings)
            ->filter(function ($pricing) use ($uid) {
                return ($pricing['uid'] ?? '') !== $uid;
            })
            ->values()
            ->toArray();

        if (empty($this->pricings)) {
            $this->addPrice();
        }
    }

    /**
     * Render the variant option edit Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin::livewire.variants.option-edit');
    }
}
