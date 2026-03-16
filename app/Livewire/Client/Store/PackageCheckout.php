<?php

namespace App\Livewire\Client\Store;

use App\Models\Package;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use App\Facades\Currency;

class PackageCheckout extends Component
{
    public Package $package;
    public array $pricesPayload = [];
    public array $variantsPayload = [];
    public array $checkoutSchema = [];
    public string $currencyCode;

    public $selectedBillingId;
    public array $variantSelections = [];
    public array $sliderIndexes = [];

    public array $pricingSummary = [];

    /**
     * Initialize the checkout component with package data, pricing, variants, and schema.
     * Sets the default billing cycle, initializes checkbox variant selections,
     * then runs validation and total calculation on first load.
     *
     * @param  \App\Models\Package  $package
     * @param  array  $pricesPayload      Flattened billing cycle data (id, name, price, setup_fee)
     * @param  array  $variantsPayload    Flattened variant & option data with prices_by_name map
     * @param  array  $checkoutSchema     Dynamic form field schema for the order form
     * @return void
     */
    public function mount(Package $package, array $pricesPayload, array $variantsPayload, array $checkoutSchema)
    {
        $this->package = $package;
        $this->pricesPayload = $pricesPayload;
        $this->variantsPayload = $variantsPayload;
        $this->checkoutSchema = $checkoutSchema;
        $this->currencyCode = Session::get('currency');

        if (!empty($this->pricesPayload)) {
            $this->selectedBillingId = $this->pricesPayload[0]['id'];
        }

        foreach ($this->variantsPayload as $variant) {
            if ($variant['type'] === 'checkbox') {
                $this->variantSelections[$variant['id']] = [];
            }
        }

        $this->validateSelections();
        $this->calculateTotals();
    }

    /**
     * React to billing cycle change by re-validating variant availability
     * and recalculating totals for the newly selected cycle.
     *
     * @return void
     */
    public function updatedSelectedBillingId()
    {
        $this->validateSelections();
        $this->calculateTotals();
    }

    /**
     * React to any variant selection change and recalculate the pricing summary.
     *
     * @return void
     */
    public function updatedVariantSelections()
    {
        $this->calculateTotals();
    }

    /**
     * React to slider index change for a specific variant, resolve the corresponding
     * option from the available options list, update the variant selection, and recalculate totals.
     *
     * @param  int|string  $value      The new slider index position
     * @param  int|string  $variantId  The ID of the slider variant being updated
     * @return void
     */
    public function updatedSliderIndexes($value, $variantId)
    {
        $variant = collect($this->variantsPayload)->firstWhere('id', $variantId);
        if ($variant) {
            $options = array_values($this->getAvailableOptions($variant));
            if (isset($options[$value])) {
                $this->variantSelections[$variantId] = $options[$value]['id'];
                $this->calculateTotals();
            }
        }
    }

    /**
     * Validate and normalize current variant selections against available options
     * for the selected billing cycle. Resets out-of-scope selections to the first
     * available option and syncs slider indexes accordingly.
     *
     * @return void
     */
    private function validateSelections()
    {
        foreach ($this->variantsPayload as $variant) {
            $availableOptions = array_values($this->getAvailableOptions($variant));
            $availableIds = array_column($availableOptions, 'id');
            
            if ($variant['type'] === 'checkbox') {
                $this->variantSelections[$variant['id']] = array_intersect(
                    $this->variantSelections[$variant['id']] ?? [], 
                    $availableIds
                );
            } else {
                if (!in_array($this->variantSelections[$variant['id']] ?? null, $availableIds)) {
                    $this->variantSelections[$variant['id']] = $availableIds[0] ?? null;
                    if ($variant['type'] === 'slider') {
                        $this->sliderIndexes[$variant['id']] = 0;
                    }
                } else {
                    if ($variant['type'] === 'slider') {
                        $index = array_search($this->variantSelections[$variant['id']], $availableIds);
                        $this->sliderIndexes[$variant['id']] = $index !== false ? $index : 0;
                    }
                }
            }
        }
    }

    /**
     * Filter a variant's options to only those that have a price defined
     * under the currently selected billing cycle name.
     *
     * @param  array  $variant  A single variant entry from $variantsPayload
     * @return array            Filtered options keyed by their original index
     */
    public function getAvailableOptions($variant)
    {
        if (!$this->selectedBillingId) return [];
        
        $cycleName = collect($this->pricesPayload)->firstWhere('id', $this->selectedBillingId)['name'] ?? '';

        return array_filter($variant['options'], function ($option) use ($cycleName) {
            return isset($option['prices_by_name'][$cycleName]);
        });
    }

    /**
     * Calculate the full pricing summary from in-memory payload data without any
     * database queries. Resolves base price, per-variant line items, setup fees,
     * subtotal, and grand total, then stores the result in $pricingSummary.
     *
     * @return void
     */
    public function calculateTotals()
    {
        if (!$this->selectedBillingId) return;

        $cycleName = '';
        $basePrice = 0;
        $setupFeePackage = 0;

        foreach ($this->pricesPayload as $p) {
            if ($p['id'] == $this->selectedBillingId) {
                $cycleName = $p['name'];
                $basePrice = $p['price'];
                $setupFeePackage = $p['setup_fee'];
                break;
            }
        }

        $variantTotal = 0;
        $variantItems = [];
        $setupFeeVariants = [];

        foreach ($this->variantsPayload as $variant) {
            $vId = $variant['id'];
            $selectedOptionIds = $variant['type'] === 'checkbox' 
                ? ($this->variantSelections[$vId] ?? []) 
                : [$this->variantSelections[$vId] ?? null];

            foreach ($selectedOptionIds as $optionId) {
                if (!$optionId) continue;
                $option = collect($variant['options'])->firstWhere('id', $optionId);
                
                if ($option && isset($option['prices_by_name'][$cycleName])) {
                    $p = $option['prices_by_name'][$cycleName];
                    if ($p['price'] > 0) {
                        $variantTotal += $p['price'];
                        $variantItems[] = [
                            'description' => $variant['name'] . ': ' . $option['name'],
                            'price' => $p['price'],
                        ];
                    }
                    if ($p['setup_fee'] > 0) {
                        $setupFeeVariants[] = [
                            'description' => $variant['name'] . ': ' . $option['name'],
                            'amount' => $p['setup_fee'],
                        ];
                    }
                }
            }
        }

        $recurringTotal = $basePrice + $variantTotal;
        $setupFeeTotal = $setupFeePackage + array_sum(array_column($setupFeeVariants, 'amount'));

        $this->pricingSummary = [
            'base_price' => $basePrice,
            'variant_items' => $variantItems,
            'subtotal' => $recurringTotal,
            'setup_fee_total' => $setupFeeTotal,
            'total' => $recurringTotal + $setupFeeTotal,
        ];
    }

    /**
     * Format a display label for a variant option's price under the current billing cycle.
     * Returns 'Free' when both price and setup fee are zero, otherwise formats the
     * recurring price and appends the setup fee suffix when applicable.
     *
     * @param  array   $option  A single option entry from a variant's options list
     * @return string           Human-readable price string, or empty string if unavailable
     */
    public function formatOptionPrice($option)
    {
        if (!$this->selectedBillingId) return '';
        $cycleName = collect($this->pricesPayload)->firstWhere('id', $this->selectedBillingId)['name'] ?? '';
        
        $priceData = $option['prices_by_name'][$cycleName] ?? null;
        if (!$priceData) return '';

        $price = (float) $priceData['price'];
        $setupFee = (float) $priceData['setup_fee'];

        if ($price == 0 && $setupFee == 0) return 'Free';
        
        $priceStr = $price == 0 ? 'Free' : Currency::format($price);
        
        if ($setupFee > 0) {
            return $priceStr . ' + ' . Currency::format($setupFee) . ' ' . __('client/store.package.setup_fee');
        }

        return $priceStr;
    }

    /**
     * Render the Livewire component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('client::livewire.store.package-checkout');
    }
}