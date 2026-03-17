<form action="{{ route('admin.services.update', ['service' => $service->id]) }}" method="POST" class="w-full lg:w-5/7 flex flex-col gap-5">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="w-full">
            <label for="user_id" class="flex text-slate-600 font-semibold mb-1">
                {{ __('admin/services.user_label') }}
            </label>
            <a href="{{ route('admin.users.summary', ['user' => $service->user_id]) }}" class="relative inline-block max-w-150 w-full group" target="_blank">
                <input type="text" name="user_id" id="user_id" value="{{ $service->user->email }}" class="w-full px-3 py-2.25 bg-billmora-1 text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl cursor-not-allowed" disabled>
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="button" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">
                        {{ __('admin/services.go_to_user') }}
                    </button>
                </div>
            </a>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('admin/services.user_helper') }}
            </p>
        </div>
        <x-admin::input 
            name="service_number" 
            label="{{ __('admin/services.number_label') }}" 
            helper="{{ __('admin/services.number_helper') }}" 
            value="{{ $service->service_number }}" 
            required 
            disabled 
        />
        <x-admin::input 
            name="service_name" 
            label="{{ __('admin/services.name_label') }}" 
            helper="{{ __('admin/services.name_helper') }}" 
            value="{{ $service->name }}" 
            required 
            disabled 
        />
        <x-admin::input 
            name="service_subscription" 
            label="{{ __('admin/services.subscription_label') }}" 
            helper="{{ __('admin/services.subscription_helper') }}" 
            value="{{ old('service_subscription', $service->subscription_id) }}" 
        />
        <div wire:key="bridge-currency" x-on:change="$wire.set('service_currency', $event.target.value)">
            <x-admin::select 
                name="service_currency" 
                label="{{ __('admin/services.currency_label') }}" 
                helper="{{ __('admin/services.currency_helper') }}" 
                required 
            >
                @foreach ($this->currencies as $currency)
                    <option value="{{ $currency->code }}" wire:key="cur-{{ $currency->code }}" @selected($service_currency == $currency->code)>
                        {{ $currency->code }}
                    </option>
                @endforeach
            </x-admin::select>
        </div>
        <x-admin::select 
            name="service_status" 
            label="{{ __('admin/services.status_label') }}" 
            helper="{{ __('admin/services.status_helper') }}" 
            required 
        >
            @foreach(['pending', 'active', 'suspended', 'terminated', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('service_status', $service->status) === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-admin::select>
        <x-admin::input 
            name="service_next_due_date" 
            label="{{ __('admin/services.expires_label') }}" 
            helper="{{ __('admin/services.expires_helper') }}" 
            type="date" 
            value="{{ old('service_next_due_date', $service->next_due_date?->format('Y-m-d')) }}" 
            required 
        />
        <x-admin::toggle 
            name="service_recalculate_price" 
            label="{{ __('admin/services.recalculate_label') }}" 
            helper="{{ __('admin/services.recalculate_helper') }}" 
            :checked="(bool) old('service_recalculate_price')" 
        />
        <x-admin::input 
            name="service_price" 
            label="{{ __('admin/services.price_label') }}" 
            helper="{{ __('admin/services.price_helper') }}" 
            type="number" 
            step="0.01" 
            value="{{ old('service_price', $service->price) }}" 
        />
        <x-admin::input 
            name="service_setup_fee" 
            label="{{ __('admin/services.setup_fee_label') }}" 
            helper="{{ __('admin/services.setup_fee_helper') }}" 
            type="number" 
            step="0.01" 
            value="{{ old('service_setup_fee', $service->setup_fee) }}" 
        />
    </div>
    <div>
        <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/services.package_configuration_label') }}</h4>
        <span class="text-slate-500">{{ __('admin/services.package_configuration_helper') }}</span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div wire:key="bridge-package" x-on:change="$wire.set('package_id', $event.target.value)">
            <x-admin::select 
                wire:key="select-pkg-{{ $service_currency ?: 'empty' }}" 
                name="package_id" 
                label="{{ __('admin/services.package_label') }}" 
                helper="{{ __('admin/services.package_helper') }}" 
                required 
                :disabled="empty($service_currency)"
            >
                @foreach ($this->availablePackages as $pkg)
                    <option value="{{ $pkg->id }}" wire:key="opt-pkg-{{ $pkg->id }}" @selected($package_id == $pkg->id)>
                        {{ $pkg->catalog->name ?? '' }} - {{ $pkg->name }}
                    </option>
                @endforeach
            </x-admin::select>
        </div>
        <div wire:key="bridge-billing" x-on:change="$wire.set('package_price_id', $event.target.value)">
            <x-admin::select 
                wire:key="select-bill-{{ $package_id ?: 'empty' }}" 
                name="package_price_id" 
                label="{{ __('admin/services.billing_cycle_label') }}" 
                helper="{{ __('admin/services.billing_cycle_helper') }}" 
                required 
                :disabled="empty($package_id)"
            >
                @foreach ($this->availablePrices as $price)
                    <option value="{{ $price->id }}" wire:key="opt-bill-{{ $price->id }}" @selected($package_price_id == $price->id)>
                        {{ $price->name }}
                    </option>
                @endforeach
            </x-admin::select>
        </div>
    </div>
    @if ($this->availableVariants->isNotEmpty())
        <div>
            <div class="mb-2">
                <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/services.variant_option_label') }}</h4>
                <span class="text-slate-500">{{ __('admin/services.variant_option_helper') }}</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                @foreach ($this->availableVariants as $variant)
                    <div wire:key="var-{{ $variant->id }}-{{ $package_price_id }}">
                        @php
                            $oldVal = old("variant_selections.{$variant->id}", $service->variant_selections[$variant->id] ?? null);
                            if ($variant->type !== 'checkbox' && is_array($oldVal)) {
                                $oldVal = $oldVal[0] ?? null;
                            }
                            $defaultValue = $variant->type !== 'checkbox' 
                                ? ($oldVal ?? $variant->options->first()->id ?? null) 
                                : (is_array($oldVal) ? $oldVal : []);
                        @endphp
                        
                        @switch($variant->type)
                            @case('select')
                                <x-admin::select 
                                    name="variant_selections[{{ $variant->id }}]" 
                                    label="{{ $variant->name }}" 
                                    required
                                >
                                    @foreach ($variant->options as $option)
                                        <option value="{{ $option->id }}" @selected($defaultValue == $option->id)>{{ $option->name }}</option>
                                    @endforeach
                                </x-admin::select>
                                @break
                            @case('radio')
                                <x-admin::radio.group 
                                    name="variant_selections[{{ $variant->id }}]" 
                                    label="{{ $variant->name }}" 
                                    required
                                >
                                    @foreach ($variant->options as $option)
                                        <x-admin::radio.option 
                                            name="variant_selections[{{ $variant->id }}]" 
                                            label="{{ $option->name }}" 
                                            value="{{ $option->id }}" 
                                            :checked="$defaultValue == $option->id" 
                                        />
                                    @endforeach
                                </x-admin::radio.group>
                                @break
                            @case('checkbox')
                                <x-admin::checkbox 
                                    name="variant_selections[{{ $variant->id }}]" 
                                    label="{{ $variant->name }}" 
                                    :options="$variant->options->pluck('name', 'id')->toArray()" 
                                    :checked="$defaultValue" 
                                />
                                @break
                            @case('slider')
                                <x-admin::slider 
                                    name="variant_selections[{{ $variant->id }}]" 
                                    label="{{ $variant->name }}" 
                                    :options="$variant->options->map(fn($o) => ['value' => $o->id, 'title' => $o->name])->toArray()" 
                                    value="{{ $defaultValue }}" 
                                    required 
                                />
                                @break
                        @endswitch
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if (!empty($this->checkoutSchema))
        <div class="space-y-2">
            <div>
                <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/services.additional_configuration_label') }}</h4>
                <span class="text-slate-500">{{ __('admin/services.additional_configuration_helper') }}</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                @foreach ($this->checkoutSchema as $key => $field)
                    @php $isRequired = str_contains(implode('|', (array)($field['rules'] ?? [])), 'required'); @endphp
                    
                    <div wire:key="sch-{{ $key }}-{{ $package_id }}">
                        @if (in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                            <x-admin::input 
                                name="configuration[{{$key}}]" 
                                label="{{ $field['label'] }}" 
                                helper="{{ $field['helper'] ?? '' }}" 
                                type="{{ $field['type'] }}" 
                                placeholder="{{ $field['placeholder'] ?? '' }}" 
                                :required="$isRequired" 
                                :value="old('configuration.' . $key, $service->configuration[$key] ?? $field['default'] ?? '')" 
                            />
                        @elseif ($field['type'] === 'textarea')
                            <x-admin::textarea 
                                name="configuration[{{$key}}]" 
                                label="{{ $field['label'] }}" 
                                helper="{{ $field['helper'] ?? '' }}" 
                                placeholder="{{ $field['placeholder'] ?? '' }}" 
                                :required="$isRequired"
                            >
                                {{ old('configuration.' . $key, $service->configuration[$key] ?? $field['default'] ?? '') }}
                            </x-admin::textarea>
                        @elseif ($field['type'] === 'toggle')
                            <x-admin::toggle 
                                name="configuration[{{$key}}]" 
                                label="{{ $field['label'] }}" 
                                helper="{{ $field['helper'] ?? '' }}" 
                                :checked="(bool) old('configuration.' . $key, $service->configuration[$key] ?? $field['default'] ?? false)" 
                            />
                        @elseif ($field['type'] === 'select')
                            <x-admin::select 
                                name="configuration[{{$key}}]" 
                                label="{{ $field['label'] }}" 
                                helper="{{ $field['helper'] ?? '' }}" 
                                :required="$isRequired"
                            >
                                @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                                    <option value="{{ $optValue }}" @selected(old('configuration.' . $key, $service->configuration[$key] ?? $field['default'] ?? '') == $optValue)>{{ $optLabel }}</option>
                                @endforeach
                            </x-admin::select>
                        @elseif ($field['type'] === 'radio')
                            <x-admin::radio.group 
                                name="configuration[{{$key}}]" 
                                label="{{ $field['label'] }}" 
                                helper="{{ $field['helper'] ?? '' }}" 
                                :required="$isRequired"
                            >
                                @foreach ($field['options'] ?? [] as $optVal => $optLabel)
                                    <x-admin::radio.option 
                                        name="configuration[{{$key}}]" 
                                        value="{{ $optVal }}" 
                                        label="{{ $optLabel }}" 
                                        :checked="old('configuration.' . $key, $service->configuration[$key] ?? $field['default'] ?? '') == $optVal" 
                                    />
                                @endforeach
                            </x-admin::radio.group>
                        @elseif ($field['type'] === 'checkbox')
                            <x-admin::checkbox 
                                name="configuration[{{$key}}]" 
                                label="{{ $field['label'] }}" 
                                helper="{{ $field['helper'] ?? '' }}" 
                                :options="$field['options'] ?? []" 
                                :checked="old('configuration.' . $key, $service->configuration[$key] ?? $field['default'] ?? [])" 
                            />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.services') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.update') }}
        </button>
    </div>
</form>