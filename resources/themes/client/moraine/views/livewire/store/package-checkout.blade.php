<form action="{{ route('client.checkout.cart.add') }}" method="POST" class="flex flex-col lg:flex-row gap-5">
    @csrf
    <div class="w-full lg:w-2/3 h-fit grid gap-4">
        <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
            <h1 class="text-xl font-semibold text-slate-600">
                {{ $package->catalog->name }} – {{ $package->name }}
            </h1>
            <p class="text-slate-500">{!! $package->description !!}</p>
        </div>
        <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
            <span class="text-xl text-slate-600 font-semibold">
                {{ __('client/store.package.billing_cycle') }}
            </span>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                @foreach ($pricesPayload as $p)
                    <label class="relative cursor-pointer">
                        <input
                            type="radio"
                            name="price_id"
                            wire:model.live="selectedBillingId"
                            value="{{ $p['id'] }}"
                            class="hidden"
                        >
                        <div class="h-full bg-billmora-bg p-4 border-2 rounded-xl transition-all hover:border-billmora-primary-500 {{ $selectedBillingId == $p['id'] ? 'border-billmora-primary-500' : 'border-billmora-2' }}">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 h-4 w-4 rounded-full border-2 transition-all {{ $selectedBillingId == $p['id'] ? 'border-billmora-primary-500 bg-billmora-primary-500' : 'border-slate-500' }}"></div>
                                <div class="flex flex-col">
                                    <h4 class="text-sm font-semibold text-slate-600">{{ $p['name'] }}</h4>
                                    <span class="text-sm font-semibold text-slate-500">
                                        {{ Currency::format($p['price']) }}
                                        @if ($p['setup_fee'] > 0)
                                            + {{ Currency::format($p['setup_fee']) }} {{ __('client/store.package.setup_fee') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
        @if (!empty($variantsPayload))
            @php 
                $hasAvailableOptions = collect($variantsPayload)->contains(fn($v) => count($this->getAvailableOptions($v)) > 0);
            @endphp
            @if($hasAvailableOptions)
                <div class="bg-billmora-bg p-6 border-2 border-billmora-2 rounded-2xl grid gap-4">
                    @foreach ($variantsPayload as $variant)
                        @php $options = array_values($this->getAvailableOptions($variant)); @endphp
                        @if(count($options) > 0)
                            <div>
                                <h3 class="text-lg font-semibold text-slate-600">{{ $variant['name'] }}</h3>
                                @if ($variant['type'] === 'radio')
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @foreach($options as $option)
                                            <label class="group relative cursor-pointer">
                                                <input type="radio" name="variants[{{ $variant['id'] }}]" wire:model.live="variantSelections.{{ $variant['id'] }}" value="{{ $option['id'] }}" class="hidden">
                                                <div class="h-full bg-billmora-bg p-4 border-2 border-billmora-2 rounded-xl transition-all group-has-[:checked]:border-billmora-primary-500 hover:border-billmora-primary-500">
                                                    <div class="flex items-start gap-3">
                                                        <div class="mt-1 h-4 w-4 rounded-full border-2 border-slate-500 group-has-[:checked]:border-billmora-primary-500 group-has-[:checked]:bg-billmora-primary-500 transition-all"></div>
                                                        <div class="flex flex-col">
                                                            <h4 class="text-sm font-semibold text-slate-600">{{ $option['name'] }}</h4>
                                                            <span class="text-sm font-semibold text-slate-500">{{ $this->formatOptionPrice($option) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($variant['type'] === 'select')
                                    <div class="flex items-center w-full my-1 mt-4">
                                        <select name="variants[{{ $variant['id'] }}]" wire:model.live="variantSelections.{{ $variant['id'] }}" class="w-full text-slate-700 rounded-lg px-3 py-2.5 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary-500 appearance-none">
                                            <option class="text-slate-500" value="" disabled>{{ __('common.choose_option') }}</option>
                                            @foreach($options as $option)
                                                <option value="{{ $option['id'] }}">
                                                    {{ $option['name'] }} {{ $this->formatOptionPrice($option) ? ' - ' . $this->formatOptionPrice($option) : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-lucide-chevrons-up-down class="w-auto h-5 -ml-7 text-slate-700 pointer-events-none" />
                                    </div>
                                @elseif ($variant['type'] === 'checkbox')
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @foreach($options as $option)
                                            <label class="group relative cursor-pointer">
                                                <input type="checkbox" name="variants_multi[{{ $variant['id'] }}][]" wire:model.live="variantSelections.{{ $variant['id'] }}" value="{{ $option['id'] }}" class="hidden">
                                                <div class="h-full bg-billmora-bg p-4 border-2 border-billmora-2 rounded-xl transition-all group-has-[:checked]:border-billmora-primary-500 hover:border-billmora-primary-500">
                                                    <div class="flex items-start gap-3">
                                                        <div class="mt-1 h-4 w-4 border-2 border-slate-500 rounded group-has-[:checked]:border-billmora-primary-500 group-has-[:checked]:bg-billmora-primary-500 transition-all"></div>
                                                        <div class="flex flex-col">
                                                            <h4 class="text-sm font-semibold text-slate-600">{{ $option['name'] }}</h4>
                                                            <span class="text-sm font-semibold text-slate-500">{{ $this->formatOptionPrice($option) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($variant['type'] === 'slider')
                                    <div class="mt-4 relative mb-10">
                                        <input type="hidden" name="variants[{{ $variant['id'] }}]" value="{{ $variantSelections[$variant['id']] ?? '' }}">
                                        <input type="range" wire:model.live="sliderIndexes.{{ $variant['id'] }}" min="0" max="{{ max(0, count($options) - 1) }}" step="1" class="w-full h-2 cursor-pointer accent-billmora-primary-500">
                                        @foreach($options as $index => $option)
                                            @php
                                                $n = count($options);
                                                if ($n <= 1 || $index === 0) {
                                                    $style = "left: 0%;";
                                                    $class = "items-start text-start";
                                                } elseif ($index === $n - 1) {
                                                    $style = "right: 0%;";
                                                    $class = "items-end text-end";
                                                } else {
                                                    $style = "left: " . (($index / ($n - 1)) * 100) . "%;";
                                                    $class = "items-center text-center -translate-x-1/2";
                                                }
                                            @endphp
                                            <span class="grid text-sm absolute {{ $class }}" style="{{ $style }}">
                                                <span class="text-slate-600 font-semibold">{{ $option['name'] }}</span>
                                                <span class="text-slate-500 font-medium">{{ $this->formatOptionPrice($option) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endif
        @if (!empty($checkoutSchema))
            <div wire:ignore class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
                <span class="text-xl text-slate-600 font-semibold">
                    {{ __('client/store.package.configuration') }}
                </span>
                <div class="mt-4 grid gap-4">
                    @foreach ($checkoutSchema as $key => $field)
                        @if (in_array($field['type'], ['text', 'email', 'url', 'number', 'password']))
                            <x-client::input
                                name="configuration[{{$key}}]"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                type="{{ $field['type'] }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                                :value="old('configuration.' . $key, $field['default'] ?? '')"
                            />
                        @elseif ($field['type'] === 'textarea')
                            <x-client::textarea
                                name="configuration[{{$key}}]"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                            >{{ old('configuration.' . $key, $field['default'] ?? '') }}</x-client::textarea>
                        @elseif ($field['type'] === 'toggle')
                            <x-client::toggle
                                name="configuration[{{$key}}]"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                :checked="(bool) old('configuration.' . $key, $field['default'] ?? false)"
                            />
                        @elseif ($field['type'] === 'select')
                            <x-client::select
                                name="configuration[{{$key}}]"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                            >
                                @foreach ($field['options'] ?? [] as $optValue => $optLabel)
                                    <option value="{{ $optValue }}" @selected(old('configuration.' . $key, $field['default'] ?? '') == $optValue)>
                                        {{ $optLabel }}
                                    </option>
                                @endforeach
                            </x-client::select>
                        @elseif ($field['type'] === 'radio')
                            <x-client::radio.group
                                name="configuration[{{$key}}]"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                :required="str_contains(implode('|', (array)($field['rules'] ?? [])), 'required')"
                            >
                                @foreach ($field['options'] ?? [] as $optVal => $optLabel)
                                    <x-client::radio.option
                                        name="configuration[{{$key}}]"
                                        value="{{ $optVal }}"
                                        label="{{ $optLabel }}"
                                        :checked="old('configuration.' . $key, $field['default'] ?? '') == $optVal"
                                    />
                                @endforeach
                            </x-client::radio.group>
                        @elseif ($field['type'] === 'checkbox')
                            <x-client::checkbox
                                name="configuration[{{$key}}]"
                                label="{{ $field['label'] }}"
                                helper="{{ $field['helper'] ?? '' }}"
                                :options="$field['options'] ?? []"
                                :checked="old('configuration.' . $key, $field['default'] ?? [])"
                            />
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    <div class="w-full lg:w-1/3 h-fit bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl space-y-4 relative overflow-hidden">
        <h2 class="text-xl font-semibold text-slate-600 mb-4">
            {{ __('client/store.package.order_summary') }}
        </h2>
        @if(!empty($pricingSummary))
            <div class="grid gap-2">
                <div class="flex justify-between font-semibold text-slate-600">
                    <span>{{ $package->catalog->name }} – {{ $package->name }}</span>
                    <span>{{ Currency::format($pricingSummary['base_price']) }}</span>
                </div>
                @foreach ($pricingSummary['variant_items'] as $vItem)
                    <div class="flex justify-between text-slate-500 text-sm font-medium">
                        <span>{{ $vItem['description'] }}</span>
                        <span>{{ Currency::format($vItem['price']) }}</span>
                    </div>
                @endforeach
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex justify-between font-semibold text-slate-600">
                <span>{{ __('client/store.package.subtotal') }}</span>
                <span>{{ Currency::format($pricingSummary['subtotal']) }}</span>
            </div>
            <div class="flex justify-between text-slate-500 font-semibold">
                <span>{{ __('client/store.package.setup_fee') }}</span>
                <span>{{ Currency::format($pricingSummary['setup_fee_total']) }}</span>
            </div>
            <hr class="border-t-2 border-billmora-2">
            <div class="flex flex-col">
                <span class="text-slate-600 font-semibold">
                    {{ __('client/store.package.due_today') }}
                </span>
                <span class="text-2xl text-billmora-primary-500 font-bold">
                    {{ Currency::format($pricingSummary['total']) }}
                </span>
            </div>
            @if($pricingSummary['setup_fee_total'] > 0)
                <div class="mt-2 p-2 bg-billmora-1 rounded-lg text-sm font-medium text-slate-500 border border-slate-100">
                    <p>
                        {{ __('client/store.package.next_billing') }}:
                        <span class="font-semibold text-slate-600">{{ Currency::format($pricingSummary['subtotal']) }}</span>
                    </p>
                </div>
            @endif
        @endif
        <button type="submit" class="w-full bg-billmora-primary-500 hover:bg-billmora-primary-600 p-3 text-white rounded-xl font-semibold transition-all cursor-pointer">
            {{ __('client/store.package.add_to_cart') }}
        </button>
    </div>
</form>