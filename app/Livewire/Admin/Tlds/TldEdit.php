<?php

namespace App\Livewire\Admin\Tlds;

use App\Models\Currency;
use App\Models\Plugin;
use App\Models\Tld;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TldEdit extends Component
{
    public Tld $tld;
    public array $prices = [];

    /**
     * Initialize the component with the given TLD. Builds a pricing entry
     * per currency, pre-populating with existing data where available.
     * Default currency is always enabled. Restores old input on failed submission.
     *
     * @param  \App\Models\Tld  $tld
     * @return void
     */
    public function mount(Tld $tld)
    {
        $this->tld = $tld;

        $oldPrices = old('prices');

        if (!empty($oldPrices)) {
            $this->prices = $oldPrices;
        } else {
            $this->prices = $this->buildPricesFromTld();
        }
    }

    /**
     * Build a currency-keyed pricing array, filling in existing TLD prices
     * where they exist and marking those currencies as enabled.
     *
     * @return array
     */
    private function buildPricesFromTld(): array
    {
        $prices = [];
        $currencies = Currency::select(['code', 'is_default'])->get();
        $existingPrices = $this->tld->prices->keyBy('currency');

        foreach ($currencies as $currency) {
            $existing = $existingPrices->get($currency->code);

            $prices[$currency->code] = [
                'currency' => $currency->code,
                'register_price' => $existing->register_price ?? '',
                'transfer_price' => $existing->transfer_price ?? '',
                'renew_price' => $existing->renew_price ?? '',
                'enabled' => $existing ? true : $currency->is_default,
            ];
        }

        return $prices;
    }

    /**
     * Retrieve all active registrar plugins.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    #[Computed(persist: true)]
    public function registrars()
    {
        return Plugin::where('type', 'registrar')->where('is_active', true)->get();
    }

    /**
     * Retrieve all available currencies.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    #[Computed(persist: true)]
    public function currencies()
    {
        return Currency::all();
    }

    /**
     * Render the TLD edit Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin::livewire.tlds.tld-edit');
    }
}
