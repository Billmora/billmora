<?php

namespace App\Livewire\Admin\Variants;

use App\Models\Currency;
use App\Models\Variant;
use Livewire\Component;

class OptionCreate extends Component
{
    public Variant $variant;
    public array $pricings = [];

    /**
     * Initialize the component for the given variant, restoring previously
     * submitted pricing entries from old input on a failed form submission.
     * Appends one empty pricing structure when no prior input exists.
     *
     * @param  \App\Models\Variant  $variant
     * @return void
     */
    public function mount(Variant $variant)
    {
        $this->variant = $variant;

        $oldPricings = old('pricings', []);

        if (!empty($oldPricings)) {
            $this->pricings = [];
            foreach ($oldPricings as $pricing) {
                $pricing['uid'] = $pricing['uid'] ?? uniqid('uid_');
                $this->pricings[] = $pricing;
            }
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
     * @param  int  $index  The zero-based index of the pricing entry to remove
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
     * Render the variant option creation Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin::livewire.variants.option-create');
    }
}