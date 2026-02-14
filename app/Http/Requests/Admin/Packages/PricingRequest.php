<?php

namespace App\Http\Requests\Admin\Packages;

use App\Models\Currency;
use App\Models\Package;
use App\Models\PackagePrice;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PricingRequest extends FormRequest
{
    protected $package;
    protected $pricing;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $package = $this->getPackage();
        $pricing = $this->getPricing();

        $uniqueRule = Rule::unique('package_prices', 'name')
            ->where('package_id', $package->id);

        if ($pricing) {
            $uniqueRule->ignore($pricing->id);
        }

        return [
            'pricing_name' => [
                'required',
                'string',
                'max:255',
                $uniqueRule,
            ],
            'pricing_type' => ['required', Rule::in(['free', 'onetime', 'recurring'])],
            'pricing_time_interval' => [
                Rule::requiredIf(fn () => $this->pricing_type === 'recurring'),
                'integer',
                'min:1',
                'nullable'
            ],
            'pricing_billing_period' => [
                Rule::requiredIf(fn () => $this->pricing_type === 'recurring'),
                Rule::in(['daily', 'weekly', 'monthly', 'yearly']),
                'nullable',
            ],
            'rates' => [
                Rule::requiredIf(fn () => in_array($this->pricing_type, ['onetime', 'recurring'])),
                'array',
            ],
            'rates.*.currency' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'string',
                Rule::exists('currencies', 'code')
            ],
            'rates.*.price' => ['nullable', 'numeric', 'min:1'],
            'rates.*.setup_fee' => ['nullable', 'numeric', 'min:0'],
            'rates.*.enabled' => ['boolean'],
        ];
    }

    /**
     * Configure the validator instance with custom validation logic.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateEnabledRates($validator);
        });
    }

    /**
     * Validate enabled currency rates and enforce default currency requirements.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    protected function validateEnabledRates($validator): void
    {
        $rates = $this->input('rates', []);
        $pricingType = $this->input('pricing_type');

        if (empty($rates) || $pricingType === 'free') {
            return;
        }

        $currencies = Currency::whereIn('code', array_keys($rates))
            ->get()
            ->keyBy('code');

        foreach ($rates as $code => $rate) {
            $currency = $currencies[$code] ?? null;

            if ($currency && $currency->is_default) {
                $rate['enabled'] = true;
            }

            $enabled = $rate['enabled'] ?? false;

            if (!$enabled) {
                continue;
            }

            if (!isset($rate['price']) || $rate['price'] === '' || $rate['price'] === null || $rate['price'] <= 0) {
                $validator->errors()->add(
                    "rates.$code.price",
                    __('validation.required_if', [
                        'attribute' => __('admin/packages.pricing.price_label'),
                        'other' => __('common.enable'),
                        'value' => 'true',
                    ])
                );
            }

            if (!isset($rate['setup_fee']) || $rate['setup_fee'] === '' || $rate['setup_fee'] === null) {
                $validator->errors()->add(
                    "rates.$code.setup_fee",
                    __('validation.required_if', [
                        'attribute' => __('admin/packages.pricing.setup_fee_label'),
                        'other' => __('common.enable'),
                        'value' => 'true',
                    ])
                );
            }
        }
    }

    /**
     * Get the package instance from route parameter with caching.
     *
     * @return \App\Models\Package
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getPackage(): Package
    {
        if (!$this->package) {
            $this->package = Package::findOrFail($this->route('id'));
        }

        return $this->package;
    }

    /**
     * Get the package pricing instance from route parameter with caching.
     *
     * @return \App\Models\PackagePrice|null
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getPricing(): ?PackagePrice
    {
        if (!$this->pricing && $this->route('pricing')) {
            $package = $this->getPackage();
            $pricingId = $this->route('pricing');
            
            $this->pricing = $package->prices()->findOrFail($pricingId);
        }

        return $this->pricing;
    }

    /**
     * Get formatted pricing data for package price creation or update.
     *
     * @return array<string, mixed>
     */
    public function getPricingData(): array
    {
        return [
            'name' => $this->input('pricing_name'),
            'type' => $this->input('pricing_type'),
            'time_interval' => $this->input('pricing_time_interval'),
            'billing_period' => $this->input('pricing_billing_period'),
            'rates' => $this->getTransformedRates(),
        ];
    }

    /**
     * Get transformed currency rates with default currency enforcement.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getTransformedRates(): array
    {
        $rates = $this->input('rates', []);
        $transformed = [];

        if (empty($rates)) {
            return $transformed;
        }

        $currencies = Currency::whereIn('code', array_keys($rates))
            ->get()
            ->keyBy('code');

        foreach ($rates as $code => $data) {
            $currency = $currencies[$code] ?? null;
            $enabled = $data['enabled'] ?? false;

            if ($currency && $currency->is_default) {
                $enabled = true;
            }

            $transformed[$code] = [
                'currency' => $data['currency'],
                'price' => $data['price'] ?? null,
                'setup_fee' => $data['setup_fee'] ?? null,
                'enabled' => (bool) $enabled,
            ];
        }

        return $transformed;
    }
}