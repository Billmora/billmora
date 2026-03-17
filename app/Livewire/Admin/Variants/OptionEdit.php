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

        $currenciesList = Currency::select(['code', 'is_default'])->get();

        $option->load(['prices' => fn($q) => $q->orderBy('id')]);

        $pricingsFromDb = $option->prices->map(function ($price) use ($currenciesList) {
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

            return [
                'id' => $price->id,
                'name' => $price->name,
                'type' => $price->type,
                'time_interval' => $price->time_interval,
                'billing_period' => $price->billing_period,
                'rates' => $rates,
            ];
        })->toArray();

        $this->pricings = old('pricings', $pricingsFromDb);

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
        $currenciesList = Currency::select(['code', 'is_default'])->get();
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
     * @param  int  $index  The zero-based index of the pricing entry to remove
     * @return void
     */
    public function removePrice($index)
    {
        unset($this->pricings[$index]);
        $this->pricings = array_values($this->pricings);

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