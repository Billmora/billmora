<?php

namespace App\Http\Requests\Admin\Variants;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class OptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare/sanitize input before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $pricings = $this->input('pricings', []);

        if (!is_array($pricings) || $pricings === []) {
            return;
        }

        $currencyCodes = collect($pricings)
            ->flatMap(fn ($p) => array_keys($p['rates'] ?? []))
            ->unique()
            ->values()
            ->all();

        if ($currencyCodes === []) {
            return;
        }

        $defaultCurrencyCodes = Currency::query()
            ->whereIn('code', $currencyCodes)
            ->where('is_default', true)
            ->pluck('code')
            ->all();

        if ($defaultCurrencyCodes === []) {
            return;
        }

        foreach ($pricings as $pIndex => $pricing) {
            foreach ($defaultCurrencyCodes as $code) {
                if (!isset($pricings[$pIndex]['rates'][$code])) {
                    continue;
                }

                $pricings[$pIndex]['rates'][$code]['enabled'] = true;
            }
        }

        $this->merge([
            'pricings' => $pricings,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'variant_options_name'  => ['required', 'string', 'max:255'],
            'variant_options_value' => ['required', 'string', 'max:255'],
            'pricings' => ['required', 'array', 'min:1'],
            'pricings.*.name' => ['required', 'string', 'max:255', 'distinct'],
            'pricings.*.type' => ['required', Rule::in(['free', 'onetime', 'recurring'])],
            'pricings.*.time_interval' => ['nullable', 'integer', 'min:1'],
            'pricings.*.billing_period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'pricings.*.rates' => ['nullable', 'array'],
            'pricings.*.rates.*.currency' => ['required', 'string', Rule::exists('currencies', 'code')],
            'pricings.*.rates.*.price' => ['nullable', 'numeric', 'min:0.01'],
            'pricings.*.rates.*.setup_fee' => ['nullable', 'numeric', 'min:0'],
            'pricings.*.rates.*.enabled' => ['boolean'],
        ];
    }

    /**
     * Additional validation after base rules are validated.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $pricings = $this->input('pricings', []);

                foreach ($pricings as $pIndex => $pricing) {
                    $type = $pricing['type'] ?? 'free';

                    if ($type === 'recurring') {
                        if (empty($pricing['time_interval']) || (int) $pricing['time_interval'] < 1) {
                            $validator->errors()->add(
                                "pricings.$pIndex.time_interval",
                                __('validation.required', [
                                    'attribute' => __('admin/variants.options.pricing.time_interval_label'),
                                ])
                            );
                        }

                        if (empty($pricing['billing_period'])) {
                            $validator->errors()->add(
                                "pricings.$pIndex.billing_period",
                                __('validation.required', [
                                    'attribute' => __('admin/variants.options.pricing.billing_period_label'),
                                ])
                            );
                        }
                    }

                    if (in_array($type, ['onetime', 'recurring'], true) && empty($pricing['rates'])) {
                        $validator->errors()->add(
                            "pricings.$pIndex.rates",
                            __('validation.required', [
                                'attribute' => __('admin/variants.options.pricing_label'),
                            ])
                        );
                        continue;
                    }

                    foreach (($pricing['rates'] ?? []) as $code => $rate) {
                        $enabled = (bool) ($rate['enabled'] ?? false);

                        if (!$enabled || $type === 'free') {
                            continue;
                        }

                        if (empty($rate['price']) || $rate['price'] <= 0) {
                            $validator->errors()->add(
                                "pricings.$pIndex.rates.$code.price",
                                __('validation.required_if', [
                                    'attribute' => __('admin/variants.options.pricing.price_label'),
                                    'other' => __('common.enable'),
                                    'value' => 'true',
                                ])
                            );
                        }

                        if (!array_key_exists('setup_fee', $rate)) {
                            $validator->errors()->add(
                                "pricings.$pIndex.rates.$code.setup_fee",
                                __('validation.required_if', [
                                    'attribute' => __('admin/variants.options.pricing.setup_fee_label'),
                                    'other' => __('common.enable'),
                                    'value' => 'true',
                                ])
                            );
                        }
                    }
                }
            },
        ];
    }
}
