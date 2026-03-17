<form action="{{ route('admin.orders.store') }}" method="POST">
    @csrf
    <div class="grid gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::singleselect 
                name="order_user" 
                label="{{ __('admin/orders.user_label') }}" 
                helper="{{ __('admin/orders.user_helper') }}" 
                :options="$this->userOptions" 
                :selected="old('order_user')" 
                required
            />
            <div wire:key="bridge-currency" x-on:change="$wire.set('order_currency', $event.target.value)">
                <x-admin::select 
                    wire:key="select-currency" 
                    name="order_currency" 
                    label="{{ __('admin/orders.currency_label') }}" 
                    helper="{{ __('admin/orders.currency_helper') }}" 
                    required
                >
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->code }}" wire:key="opt-cur-{{ $currency->code }}" @selected($order_currency == $currency->code)>
                            {{ $currency->code }}
                        </option>
                    @endforeach
                </x-admin::select>
            </div>
            <x-admin::singleselect 
                name="order_coupon" 
                label="{{ __('admin/orders.coupon_label') }}" 
                helper="{{ __('admin/orders.coupon_helper') }}" 
                :options="$this->couponOptions" 
                :selected="old('order_coupon')" 
            />
            <div wire:key="bridge-status">
                <x-admin::select 
                    name="order_status" 
                    label="{{ __('admin/orders.status_label') }}" 
                    helper="{{ __('admin/orders.status_helper') }}" 
                    required
                >
                    @foreach(['pending', 'processing', 'completed', 'cancelled', 'failed'] as $status)
                        <option value="{{ $status }}" @selected(old('order_status', 'pending') == $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </x-admin::select>
            </div>
            <x-admin::toggle 
                name="order_email" 
                label="{{ __('admin/orders.email_label') }}" 
                helper="{{ __('admin/orders.email_helper') }}" 
                :checked="old('order_email')" 
            />
        </div>
        <div>
            <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.package_configuration_label') }}</h4>
            <span class="text-slate-500">{{ __('admin/orders.package_configuration_helper') }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <div wire:key="bridge-package" x-on:change="$wire.set('order_package', $event.target.value)">
                <x-admin::select 
                    wire:key="select-pkg-{{ $order_currency ?: 'empty' }}" 
                    name="order_package" 
                    label="{{ __('admin/orders.package_label') }}" 
                    helper="{{ __('admin/orders.package_helper') }}" 
                    required 
                    :disabled="empty($order_currency)"
                >
                    @foreach ($this->availablePackages as $pkg)
                        <option value="{{ $pkg->id }}" wire:key="opt-pkg-{{ $pkg->id }}" @selected($order_package == $pkg->id)>
                            {{ $pkg->catalog->name ?? '' }} - {{ $pkg->name }}
                        </option>
                    @endforeach
                </x-admin::select>
            </div>
            <div wire:key="bridge-billing" x-on:change="$wire.set('order_package_billing', $event.target.value)">
                <x-admin::select 
                    wire:key="select-bill-{{ $order_package ?: 'empty' }}" 
                    name="order_package_billing" 
                    label="{{ __('admin/orders.package_billing_label') }}" 
                    helper="{{ __('admin/orders.package_billing_helper') }}" 
                    required 
                    :disabled="empty($order_package)"
                >
                    @foreach ($this->availablePrices as $price)
                        <option value="{{ $price->id }}" wire:key="opt-bill-{{ $price->id }}" @selected($order_package_billing == $price->id)>
                            {{ $price->name }}
                        </option>
                    @endforeach
                </x-admin::select>
            </div>
        </div>
        @if ($this->availableVariants->isNotEmpty())
            <div>
                <div class="mb-2">
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.variant_option_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/orders.variant_option_helper') }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    @foreach ($this->availableVariants as $variant)
                        <div wire:key="var-{{ $variant->id }}-{{ $order_package_billing }}">
                            @php
                                $defaultValue = $variant->type !== 'checkbox' ? ($variant->options->first()->id ?? null) : [];
                            @endphp
                            @switch($variant->type)
                                @case('select')
                                    <x-admin::select 
                                        name="variant_options[{{ $variant->id }}]" 
                                        label="{{ $variant->name }}" 
                                        required
                                    >
                                        @foreach ($variant->options as $option)
                                            <option value="{{ $option->id }}" @selected(old("variant_options.{$variant->id}", $defaultValue) == $option->id)>{{ $option->name }}</option>
                                        @endforeach
                                    </x-admin::select>
                                    @break
                                @case('radio')
                                    <x-admin::radio.group 
                                        name="variant_options[{{ $variant->id }}]" 
                                        label="{{ $variant->name }}" 
                                        required
                                    >
                                        @foreach ($variant->options as $option)
                                            <x-admin::radio.option 
                                                name="variant_options[{{ $variant->id }}]" 
                                                label="{{ $option->name }}" 
                                                value="{{ $option->id }}" 
                                                :checked="old('variant_options.' . $variant->id, $defaultValue) == $option->id" 
                                            />
                                        @endforeach
                                    </x-admin::radio.group>
                                    @break
                                @case('checkbox')
                                    <x-admin::checkbox 
                                        name="variant_options[{{ $variant->id }}]" 
                                        label="{{ $variant->name }}" 
                                        :options="$variant->options->pluck('name', 'id')->toArray()" 
                                        :checked="old('variant_options.' . $variant->id, $defaultValue)" 
                                    />
                                    @break
                                @case('slider')
                                    <x-admin::slider 
                                        name="variant_options[{{ $variant->id }}]" 
                                        label="{{ $variant->name }}" 
                                        :options="$variant->options->map(fn($o) => ['value' => $o->id, 'title' => $o->name])->toArray()" 
                                        value="{{ old('variant_options.' . $variant->id, $defaultValue) }}" 
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
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.additional_configuration_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/orders.additional_configuration_helper') }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    @foreach ($this->checkoutSchema as $key => $field)
                        @php $isRequired = str_contains(implode('|', (array)($field['rules'] ?? [])), 'required'); @endphp
                        <div wire:key="sch-{{ $key }}-{{ $order_package }}">
                            @if (in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                                <x-admin::input 
                                    name="configuration[{{$key}}]" 
                                    label="{{ $field['label'] }}" 
                                    helper="{{ $field['helper'] ?? '' }}" 
                                    type="{{ $field['type'] }}" 
                                    placeholder="{{ $field['placeholder'] ?? '' }}" 
                                    :required="$isRequired" 
                                    :value="old('configuration.' . $key, $field['default'] ?? '')" 
                                />
                            @elseif ($field['type'] === 'textarea')
                                <x-admin::textarea 
                                    name="configuration[{{$key}}]" 
                                    label="{{ $field['label'] }}" 
                                    helper="{{ $field['helper'] ?? '' }}" 
                                    placeholder="{{ $field['placeholder'] ?? '' }}" 
                                    :required="$isRequired"
                                >
                                    {{ old('configuration.' . $key, $field['default'] ?? '') }}
                                </x-admin::textarea>
                            @elseif ($field['type'] === 'toggle')
                                <x-admin::toggle 
                                    name="configuration[{{$key}}]" 
                                    label="{{ $field['label'] }}" 
                                    helper="{{ $field['helper'] ?? '' }}" 
                                    :checked="(bool) old('configuration.' . $key, $field['default'] ?? false)" 
                                />
                            @elseif ($field['type'] === 'select')
                                <x-admin::select 
                                    name="configuration[{{$key}}]" 
                                    label="{{ $field['label'] }}" 
                                    helper="{{ $field['helper'] ?? '' }}" 
                                    :required="$isRequired"
                                >
                                    @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                                        <option value="{{ $optValue }}" @selected(old('configuration.' . $key, $field['default'] ?? '') == $optValue)>{{ $optLabel }}</option>
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
                                            :checked="old('configuration.' . $key, $field['default'] ?? '') == $optVal" 
                                        />
                                    @endforeach
                                </x-admin::radio.group>
                            @elseif ($field['type'] === 'checkbox')
                                <x-admin::checkbox 
                                    name="configuration[{{$key}}]" 
                                    label="{{ $field['label'] }}" 
                                    helper="{{ $field['helper'] ?? '' }}" 
                                    :options="$field['options'] ?? []" 
                                    :checked="old('configuration.' . $key, $field['default'] ?? [])" 
                                />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.orders') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.cancel') }}
            </a>
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.create') }}
            </button>
        </div>
    </div>
</form>