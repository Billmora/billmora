<?php

namespace App\Livewire\Admin\Tlds;

use App\Models\Currency;
use App\Models\Plugin;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TldCreate extends Component
{
    public array $prices = [];

    /**
     * Initialize the component with a pricing entry for each active currency.
     * Default currency is always enabled, non-default currencies start disabled.
     * Restores previously submitted data from old input on a failed form submission.
     *
     * @return void
     */
    public function mount()
    {
        $oldPrices = old('prices');

        if (!empty($oldPrices)) {
            $this->prices = $oldPrices;
        } else {
            $this->prices = $this->buildEmptyPrices();
        }
    }

    /**
     * Build a fresh currency-keyed pricing array with one entry per currency.
     * Default currency is always enabled.
     *
     * @return array
     */
    private function buildEmptyPrices(): array
    {
        $prices = [];
        $currencies = \Illuminate\Support\Facades\View::shared('currencies');

        foreach ($currencies as $currency) {
            $prices[$currency->code] = [
                'currency' => $currency->code,
                'register_price' => '',
                'transfer_price' => '',
                'renew_price' => '',
                'enabled' => $currency->is_default,
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
        return \Illuminate\Support\Facades\View::shared('currencies');
    }

    /**
     * Render the TLD creation Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('admin::livewire.tlds.tld-create');
    }
}
