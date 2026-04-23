<form action="{{ route('admin.orders.store') }}" method="POST">
    @csrf
    <div class="grid gap-4">
        <div wire:key="order-metadata" class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::singleselect 
                wire:ignore
                name="order_user" 
                label="{{ __('admin/orders.user_label') }}" 
                helper="{{ __('admin/orders.user_helper') }}" 
                :options="$this->userOptions" 
                :selected="old('order_user')" 
                required
            />
            <x-admin::select 
                wire:key="select-currency" 
                wire:model.live="order_currency"
                name="order_currency" 
                label="{{ __('admin/orders.currency_label') }}" 
                helper="{{ __('admin/orders.currency_helper') }}" 
                required
            >
                @foreach ($this->currencies as $currency)
                    <option value="{{ $currency->code }}" wire:key="opt-cur-{{ $currency->code }}">
                        {{ $currency->code }}
                    </option>
                @endforeach
            </x-admin::select>
            <x-admin::singleselect 
                name="order_coupon" 
                label="{{ __('admin/orders.coupon_label') }}" 
                helper="{{ __('admin/orders.coupon_helper') }}" 
                :options="$this->couponOptions" 
                :selected="old('order_coupon')" 
            />
            <x-admin::select 
                wire:key="select-status"
                name="order_status" 
                label="{{ __('admin/orders.status_label') }}" 
                helper="{{ __('admin/orders.status_helper') }}" 
                required
            >
                @foreach(['pending', 'processing', 'completed', 'cancelled', 'failed'] as $status)
                    <option value="{{ $status }}" wire:key="opt-stat-{{ $status }}" @selected(old('order_status', 'pending') == $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-admin::select>
            <x-admin::toggle 
                name="order_email" 
                label="{{ __('admin/orders.email_label') }}" 
                helper="{{ __('admin/orders.email_helper') }}" 
                :checked="old('order_email')" 
            />
        </div>

        <div wire:key="package-header">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.package_configuration_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/orders.package_configuration_helper') }}</span>
                </div>
                @if(!empty($order_currency))
                    <button type="button" wire:click="addPackageItem"
                        class="flex items-center gap-1 bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white rounded-lg transition-colors cursor-pointer">
                        <x-lucide-plus class="w-4 h-4" />
                        {{ __('admin/orders.add_package_label') }}
                    </button>
                @endif
            </div>
        </div>

        <div wire:key="packages-container" class="grid gap-4">
            @foreach ($packageItems as $pi => $pkgItem)
                <div wire:key="pkg-item-{{ $pi }}" class="w-full bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <div class="flex justify-end mb-4">
                        <button type="button" wire:click="removePackageItem('{{ $pi }}')" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-2 py-1 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.delete') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php $pkgOptions = $this->getAvailablePackagesFor($pi); @endphp
                        <x-admin::select 
                            wire:key="pkg-sel-{{ $pi }}-{{ $order_currency ?: 'none' }}"
                            wire:model.live="packageItems.{{ $pi }}.package_id"
                            name="package_items[{{ $pi }}][package_id]" 
                            label="{{ __('admin/orders.package_label') }}" 
                            helper="{{ __('admin/orders.package_helper') }}"
                            required 
                            :disabled="empty($order_currency)"
                        >
                            @foreach ($pkgOptions as $pkg)
                                <option value="{{ $pkg->id }}" wire:key="opt-pkg-{{ $pi }}-{{ $pkg->id }}">
                                    {{ $pkg->catalog->name ?? '' }} - {{ $pkg->name }}
                                </option>
                            @endforeach
                        </x-admin::select>

                        @php $billingPrices = $this->getAvailablePricesFor($pi); @endphp
                        <x-admin::select 
                            wire:key="bill-sel-{{ $pi }}-{{ $pkgItem['package_id'] ?: 'none' }}"
                            wire:model.live="packageItems.{{ $pi }}.billing_id"
                            name="package_items[{{ $pi }}][billing_id]" 
                            label="{{ __('admin/orders.package_billing_label') }}" 
                            helper="{{ __('admin/orders.package_billing_helper') }}"
                            required 
                            :disabled="empty($pkgItem['package_id'])"
                        >
                            @foreach ($billingPrices as $price)
                                <option value="{{ $price->id }}" wire:key="opt-bill-{{ $pi }}-{{ $price->id }}">
                                    {{ $price->name }}
                                </option>
                            @endforeach
                        </x-admin::select>

                        <x-admin::input 
                            wire:key="qty-inp-{{ $pi }}"
                            wire:model="packageItems.{{ $pi }}.quantity"
                            name="package_items[{{ $pi }}][quantity]" 
                            label="{{ __('admin/orders.quantity_label') }}" 
                            helper="{{ __('admin/orders.quantity_helper') }}"
                            type="number" 
                            min="1" 
                        />
                    </div>

                    @php $variants = $this->getAvailableVariantsFor($pi); @endphp
                    @if ($variants->isNotEmpty())
                        <div class="mt-4 pt-4 border-t border-billmora-2">
                            <h5 class="text-sm font-semibold text-slate-500 mb-3">{{ __('admin/orders.variant_option_label') }}</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($variants as $variant)
                                    <div wire:key="var-{{ $pi }}-{{ $variant->id }}-{{ $pkgItem['billing_id'] }}">
                                        @php $defaultValue = $variant->type !== 'checkbox' ? ($variant->options->first()->id ?? null) : []; @endphp
                                        @switch($variant->type)
                                            @case('select')
                                                <x-admin::select 
                                                    name="package_items[{{ $pi }}][variant_options][{{ $variant->id }}]" 
                                                    label="{{ $variant->name }}" 
                                                    required
                                                >
                                                    @foreach ($variant->options as $option)
                                                        <option value="{{ $option->id }}" @selected(old("package_items.{$pi}.variant_options.{$variant->id}", $defaultValue) == $option->id)>{{ $option->name }}</option>
                                                    @endforeach
                                                </x-admin::select>
                                                @break
                                            @case('radio')
                                                <x-admin::radio.group 
                                                    name="package_items[{{ $pi }}][variant_options][{{ $variant->id }}]" 
                                                    label="{{ $variant->name }}" 
                                                    required
                                                >
                                                    @foreach ($variant->options as $option)
                                                        <x-admin::radio.option 
                                                            name="package_items[{{ $pi }}][variant_options][{{ $variant->id }}]" 
                                                            label="{{ $option->name }}" 
                                                            value="{{ $option->id }}" 
                                                            :checked="old('package_items.' . $pi . '.variant_options.' . $variant->id, $defaultValue) == $option->id" 
                                                        />
                                                    @endforeach
                                                </x-admin::radio.group>
                                                @break
                                            @case('checkbox')
                                                <x-admin::checkbox 
                                                    name="package_items[{{ $pi }}][variant_options][{{ $variant->id }}]" 
                                                    label="{{ $variant->name }}" 
                                                    :options="$variant->options->pluck('name', 'id')->toArray()" 
                                                    :checked="old('package_items.' . $pi . '.variant_options.' . $variant->id, $defaultValue)" 
                                                />
                                                @break
                                            @case('slider')
                                                <x-admin::slider 
                                                    name="package_items[{{ $pi }}][variant_options][{{ $variant->id }}]" 
                                                    label="{{ $variant->name }}" 
                                                    :options="$variant->options->map(fn($o) => ['value' => $o->id, 'title' => $o->name])->toArray()" 
                                                    value="{{ old('package_items.' . $pi . '.variant_options.' . $variant->id, $defaultValue) }}" 
                                                    required 
                                                />
                                                @break
                                        @endswitch
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @php $schema = $this->getCheckoutSchemaFor($pi); @endphp
                    @if (!empty($schema))
                        <div class="mt-4 pt-4 border-t border-billmora-2">
                            <h5 class="text-sm font-semibold text-slate-500 mb-3">{{ __('admin/orders.additional_configuration_label') }}</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($schema as $key => $field)
                                    @php $isRequired = str_contains(implode('|', (array)($field['rules'] ?? [])), 'required'); @endphp
                                    <div wire:key="sch-{{ $pi }}-{{ $key }}">
                                        @if (in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                                            <x-admin::input 
                                                name="package_items[{{ $pi }}][configuration][{{ $key }}]" 
                                                label="{{ $field['label'] }}" 
                                                helper="{{ $field['helper'] ?? '' }}" 
                                                type="{{ $field['type'] }}" 
                                                placeholder="{{ $field['placeholder'] ?? '' }}" 
                                                :required="$isRequired" 
                                                :value="old('package_items.' . $pi . '.configuration.' . $key, $field['default'] ?? '')" 
                                            />
                                        @elseif ($field['type'] === 'textarea')
                                            <x-admin::textarea 
                                                name="package_items[{{ $pi }}][configuration][{{ $key }}]" 
                                                label="{{ $field['label'] }}" 
                                                helper="{{ $field['helper'] ?? '' }}" 
                                                placeholder="{{ $field['placeholder'] ?? '' }}" 
                                                :required="$isRequired"
                                            >
                                                {{ old('package_items.' . $pi . '.configuration.' . $key, $field['default'] ?? '') }}
                                            </x-admin::textarea>
                                        @elseif ($field['type'] === 'toggle')
                                            <x-admin::toggle 
                                                name="package_items[{{ $pi }}][configuration][{{ $key }}]" 
                                                label="{{ $field['label'] }}" 
                                                helper="{{ $field['helper'] ?? '' }}" 
                                                :checked="(bool) old('package_items.' . $pi . '.configuration.' . $key, $field['default'] ?? false)" 
                                            />
                                        @elseif ($field['type'] === 'select')
                                            <x-admin::select 
                                                name="package_items[{{ $pi }}][configuration][{{ $key }}]" 
                                                label="{{ $field['label'] }}" 
                                                helper="{{ $field['helper'] ?? '' }}" 
                                                :required="$isRequired"
                                            >
                                                @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                                                    <option value="{{ $optValue }}" @selected(old('package_items.' . $pi . '.configuration.' . $key, $field['default'] ?? '') == $optValue)>{{ $optLabel }}</option>
                                                @endforeach
                                            </x-admin::select>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            @if (empty($packageItems))
                <div wire:key="empty-packages" class="w-full bg-white/50 p-6 border-2 border-dashed border-billmora-2 rounded-2xl text-center text-slate-400 text-sm">
                    {{ __('admin/orders.no_package_items') }}
                </div>
            @endif
        </div>

        <div wire:key="domain-header">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/orders.domain_configuration_label') }}</h4>
                    <span class="text-slate-500">{{ __('admin/orders.domain_configuration_helper') }}</span>
                </div>
                @if(!empty($order_currency))
                    <button type="button" wire:click="addDomainItem"
                        class="flex items-center gap-1 bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white rounded-lg transition-colors cursor-pointer">
                        <x-lucide-plus class="w-4 h-4" />
                        {{ __('admin/orders.add_domain_label') }}
                    </button>
                @endif
            </div>
        </div>

        <div wire:key="domains-container" class="grid gap-4">
            @foreach ($domainItems as $di => $domItem)
                <div wire:key="dom-item-{{ $di }}" class="w-full bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    <div class="flex justify-end mb-4">
                        <button type="button" wire:click="removeDomainItem('{{ $di }}')" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-2 py-1 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                            {{ __('common.delete') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-admin::select 
                            wire:key="dom-type-{{ $di }}"
                            wire:model.live="domainItems.{{ $di }}.type"
                            name="domain_items[{{ $di }}][type]" 
                            label="{{ __('admin/orders.domain_type_label') }}" 
                            helper="{{ __('admin/orders.domain_type_helper') }}"
                            required
                        >
                            <option value="register">{{ __('admin/orders.domain_type_register') }}</option>
                            <option value="transfer">{{ __('admin/orders.domain_type_transfer') }}</option>
                        </x-admin::select>

                        <x-admin::input 
                            wire:key="dom-name-{{ $di }}"
                            wire:model.live="domainItems.{{ $di }}.domain"
                            name="domain_items[{{ $di }}][domain]" 
                            label="{{ __('admin/orders.domain_name_label') }}" 
                            helper="{{ __('admin/orders.domain_name_helper') }}" 
                            placeholder="example.com" 
                            required 
                        />

                        <x-admin::select 
                            wire:key="dom-tld-{{ $di }}-{{ $order_currency ?: 'none' }}"
                            wire:model.live="domainItems.{{ $di }}.tld_id"
                            name="domain_items[{{ $di }}][tld_id]" 
                            label="{{ __('admin/orders.domain_tld_label') }}" 
                            helper="{{ __('admin/orders.domain_tld_helper') }}"
                            required
                            :disabled="empty($order_currency)"
                        >
                            @foreach ($this->availableTlds as $tld)
                                <option value="{{ $tld->id }}" wire:key="opt-tld-{{ $di }}-{{ $tld->id }}">
                                    {{ $tld->tld }}
                                </option>
                            @endforeach
                        </x-admin::select>

                        <x-admin::input 
                            wire:key="dom-years-{{ $di }}"
                            wire:model.live="domainItems.{{ $di }}.years"
                            name="domain_items[{{ $di }}][years]" 
                            label="{{ __('admin/orders.domain_years_label') }}" 
                            helper="{{ __('admin/orders.domain_years_helper') }}" 
                            type="number" 
                            min="1" 
                            required 
                        />

                        @if (($domItem['type'] ?? 'register') === 'transfer')
                            <x-admin::input 
                                wire:key="dom-epp-{{ $di }}"
                                wire:model.live="domainItems.{{ $di }}.epp_code"
                                name="domain_items[{{ $di }}][epp_code]" 
                                label="{{ __('admin/orders.domain_epp_label') }}" 
                                helper="{{ __('admin/orders.domain_epp_helper') }}" 
                            />
                        @endif


                    </div>
                </div>
            @endforeach

            @if (empty($domainItems))
                <div wire:key="empty-domains" class="w-full bg-white/50 p-6 border-2 border-dashed border-billmora-2 rounded-2xl text-center text-slate-400 text-sm">
                    {{ __('admin/orders.no_domain_items') }}
                </div>
            @endif
        </div>
        <div wire:key="form-actions" class="flex gap-4 ml-auto">
            <a href="{{ route('admin.orders') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.cancel') }}
            </a>
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.create') }}
            </button>
        </div>
    </div>
</form>
