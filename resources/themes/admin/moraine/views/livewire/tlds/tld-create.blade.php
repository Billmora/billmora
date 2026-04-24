<form 
    action="{{ route('admin.tlds.store') }}" 
    method="POST" 
    class="flex flex-col gap-5"
>
    @csrf
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="tld"
            type="text"
            label="{{ __('admin/tlds.tld_label') }}"
            helper="{{ __('admin/tlds.tld_helper') }}"
            placeholder="com"
            value="{{ old('tld') }}"
            required 
        />
        <x-admin::select
            name="plugin_id"
            label="{{ __('admin/tlds.registrar_label') }}"
            helper="{{ __('admin/tlds.registrar_helper') }}"
        >
            <option value="">{{ __('common.none') }}</option>
            @foreach($this->registrars as $registrar)
                <option value="{{ $registrar->id }}" {{ old('plugin_id') == $registrar->id ? 'selected' : '' }}>
                    {{ $registrar->name }}
                </option>
            @endforeach
        </x-admin::select>

        <x-admin::input 
            name="min_years"
            type="number"
            label="{{ __('admin/tlds.min_years_label') }}"
            helper="{{ __('admin/tlds.min_years_helper') }}"
            value="{{ old('min_years', 1) }}"
            required 
        />
        <x-admin::input 
            name="max_years"
            type="number"
            label="{{ __('admin/tlds.max_years_label') }}"
            helper="{{ __('admin/tlds.max_years_helper') }}"
            value="{{ old('max_years', 10) }}"
            required 
        />
        <x-admin::input 
            name="grace_period_days"
            type="number"
            label="{{ __('admin/tlds.grace_period_label') }}"
            helper="{{ __('admin/tlds.grace_period_helper') }}"
            value="{{ old('grace_period_days', 0) }}"
            required 
        />
        <x-admin::input 
            name="redemption_period_days"
            type="number"
            label="{{ __('admin/tlds.redemption_period_label') }}"
            helper="{{ __('admin/tlds.redemption_period_helper') }}"
            value="{{ old('redemption_period_days', 0) }}"
            required 
        />
        <x-admin::select
            name="status"
            label="{{ __('admin/tlds.status_label') }}"
            helper="{{ __('admin/tlds.status_helper') }}"
            required
        >
            <option value="visible" {{ old('status', 'visible') === 'visible' ? 'selected' : '' }}>Visible</option>
            <option value="hidden" {{ old('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
        </x-admin::select>
    </div>

    
    <div class="flex flex-col sm:flex-row justify-between gap-4 sm:items-center">
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/tlds.pricing_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/tlds.pricing_helper') }}</span>
        </div>
    </div>
    <div class="flex flex-col gap-4">
        @foreach ($this->currencies as $currency)
            <div
                wire:key="price-{{ $currency->code }}"
                class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-4 gap-5 w-full bg-white p-8 border-2 border-billmora-2 rounded-2xl"
            >
                <x-admin::input
                    name="prices[{{ $currency->code }}][currency]"
                    type="text"
                    label="{{ __('admin/tlds.currency_code_label') }}"
                    helper="{{ __('admin/tlds.currency_code_helper') }}"
                    value="{{ $currency->code }}"
                    readonly
                    required
                />
                <x-admin::input
                    name="prices[{{ $currency->code }}][register_price]"
                    wire:model="prices.{{ $currency->code }}.register_price"
                    value="{{ $prices[$currency->code]['register_price'] ?? '' }}"
                    type="number"
                    step="0.01"
                    min="0"
                    label="{{ __('admin/tlds.register_price_label') }}"
                    helper="{{ __('admin/tlds.register_price_helper') }}"
                    :required="$currency->is_default"
                />
                <x-admin::input
                    name="prices[{{ $currency->code }}][transfer_price]"
                    wire:model="prices.{{ $currency->code }}.transfer_price"
                    value="{{ $prices[$currency->code]['transfer_price'] ?? '' }}"
                    type="number"
                    step="0.01"
                    min="0"
                    label="{{ __('admin/tlds.transfer_price_label') }}"
                    helper="{{ __('admin/tlds.transfer_price_helper') }}"
                    :required="$currency->is_default"
                />
                <x-admin::input
                    name="prices[{{ $currency->code }}][renew_price]"
                    wire:model="prices.{{ $currency->code }}.renew_price"
                    value="{{ $prices[$currency->code]['renew_price'] ?? '' }}"
                    type="number"
                    step="0.01"
                    min="0"
                    label="{{ __('admin/tlds.renew_price_label') }}"
                    helper="{{ __('admin/tlds.renew_price_helper') }}"
                    :required="$currency->is_default"
                />
                @if (!$currency->is_default)
                    <x-admin::toggle
                        name="prices[{{ $currency->code }}][enabled]"
                        wire:model="prices.{{ $currency->code }}.enabled"
                        label="{{ __('admin/tlds.enabled_label') }}"
                        helper="{{ __('admin/tlds.enabled_helper') }}"
                        :checked="(bool) ($prices[$currency->code]['enabled'] ?? false)"
                    />
                @else
                    <input
                        type="hidden"
                        name="prices[{{ $currency->code }}][enabled]"
                        value="1"
                    />
                @endif
            </div>
        @endforeach
    </div>

    <div class="flex gap-4 ml-auto">
        <a 
            href="{{ route('admin.tlds') }}" 
            class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
        >
            {{ __('common.cancel') }}
        </a>
        <button 
            type="submit" 
            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
        >
            {{ __('common.create') }}
        </button>
    </div>
</form>
