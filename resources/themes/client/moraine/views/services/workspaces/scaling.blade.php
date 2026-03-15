@extends('client::services.show')

@section('workspaces')
<form action="{{ route('client.services.scaling.store', ['service' => $service->service_number]) }}" method="POST" class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden">
    @csrf
    @if ($step == 1)
        <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
            <h3 class="flex gap-2 items-center font-semibold text-slate-600">
                <x-lucide-arrow-up-down class="w-auto h-5" />
                {{ __('client/services.scale_label') }}
            </h3>
            <p class="text-slate-500">{{ __('client/services.scale_package_helper') }}</p>
        </div>
        <div class="grid gap-4 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($availablePackages as $package)
                    @php
                        $isCurrent = $service->package_id == $package->id;
                        $priceData = $package->prices->first();
                    @endphp
                    <div class="relative">
                        <input type="radio" 
                            name="package_id" 
                            id="package_id_{{ $package->id }}" 
                            value="{{ $package->id }}" 
                            class="peer hidden" 
                            {{ $isCurrent ? 'checked' : '' }} 
                            required
                        >
                        <label for="package_id_{{ $package->id }}" 
                            class="flex flex-col h-full gap-4 p-6 border-2 rounded-2xl cursor-pointer transition-all duration-200 ease-in-out
                                    bg-billmora-bg border-billmora-2 hover:border-billmora-primary
                                    peer-checked:border-billmora-primary-500">
                            @if($isCurrent)
                                <span class="absolute top-0 right-0 bg-billmora-primary-500 text-white text-xs uppercase tracking-wider font-bold px-3 py-1 rounded-bl-xl rounded-tr-lg z-10">
                                    {{ __('client/services.scaling.current_label') }}
                                </span>
                            @endif
                            @if ($package->icon)
                                <img src="{{ Storage::url($package->icon) }}" alt="package icon" class="max-w-48 h-auto m-auto object-cover rounded-lg">
                            @endif
                            <div class="space-y-2 text-center mt-2">
                                <h4 class="text-xl text-billmora-primary-500 font-semibold">{{ $package->name }}</h4>
                                @if ($package->prices->contains(fn ($p) => $p->type === 'free'))
                                    <div class="grid">
                                        <span class="text-xl text-slate-500 font-semibold">Free</span>
                                    </div>
                                @elseif ($priceData && isset($priceData->rates[$service->currency]))
                                    <div class="grid">
                                        <span class="text-xl text-slate-500 font-semibold">
                                            {{ Currency::format(
                                                $priceData->rates[$service->currency]['price'],
                                                $service->currency
                                            ) }}
                                        </span>
                                        <span class="text-sm text-slate-400 font-semibold">
                                            {{ $priceData->name }}
                                        </span>
                                    </div>
                                @endif
                                <p class="text-slate-500 text-sm leading-relaxed line-clamp-3">
                                    {!! $package->description !!}
                                </p>
                            </div>
                            @if ($package->stock !== 0)
                                @if ($package->stock >= 1 && !$isCurrent)
                                    <div class="mt-auto pt-4 border-t-2 border-billmora-2">
                                        <span class="block text-center text-xs text-emerald-600 font-semibold bg-emerald-50 py-1 rounded">
                                            {{ __('client/store.stock_available', ['item' => $package->stock]) }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('client.services.show', ['service' => $service->service_number]) }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.next') }}
                </button>
            </div>
        </div>
    @elseif ($step == 2)
        <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
            <h3 class="flex gap-2 items-center font-semibold text-slate-600">
                <x-lucide-arrow-up-down class="w-auto h-5" />
                {{ __('client/services.scale_label') }}
            </h3>
            <p class="text-slate-500">{{ __('client/services.scale_variant_helper') }}</p>
        </div>
        <div class="grid gap-4 p-6">
            @foreach ($targetPackage->variants as $variant)
                @switch($variant->type)
                    @case('select')
                        <x-client::select
                            name="variants[{{ $variant->id }}]"
                            label="{{ $variant->name }}"
                            required
                        >
                            @foreach ($variant->options as $option)
                                @php
                                    $priceModel = $option->target_price_model;
                                    $isFree = strtolower($priceModel->type) === 'free';
                                    $rates = $isFree ? [] : (is_string($priceModel->rates) ? json_decode($priceModel->rates, true) : $priceModel->rates);
                                    
                                    $price = $isFree ? 0 : ($rates[$service->currency]['price'] ?? 0);
                                    $setupFee = $isFree ? 0 : ($rates[$service->currency]['setup_fee'] ?? 0);
                                    
                                    $selected = old("variants.{$variant->id}", $service->variant_selections[$variant->id] ?? null);
                                @endphp
                                <option value="{{ $option->id }}" {{ $selected == $option->id ? 'selected' : '' }}>
                                    {{ $option->name }}
                                    @if ($isFree)
                                        | Free
                                    @else
                                        @if ($price > 0) | {{ Currency::format($price, $service->currency) }} @endif
                                        @if ($setupFee > 0) + {{ Currency::format($setupFee, $service->currency) }} Setup Fee @endif
                                    @endif
                                </option>
                            @endforeach
                        </x-client::select>
                        @break
                    @case('radio')
                        <x-client::radio.group
                            name="variants[{{ $variant->id }}]"
                            label="{{ $variant->name }}"
                            required
                        >
                            @foreach ($variant->options as $option)
                                @php
                                    $priceModel = $option->target_price_model;
                                    $isFree = strtolower($priceModel->type) === 'free';
                                    $rates = $isFree ? [] : (is_string($priceModel->rates) ? json_decode($priceModel->rates, true) : $priceModel->rates);
                                    
                                    $price = $isFree ? 0 : ($rates[$service->currency]['price'] ?? 0);
                                    $setupFee = $isFree ? 0 : ($rates[$service->currency]['setup_fee'] ?? 0);

                                    $priceSuffix = $isFree ? ' | Free' : 
                                        ($price > 0 ? ' | ' . Currency::format($price, $service->currency) : '') . 
                                        ($setupFee > 0 ? ' + ' . Currency::format($setupFee, $service->currency) . ' Setup Fee' : '');

                                    $priceLabel = $option->name . $priceSuffix;
                                @endphp
                                <x-client::radio.option
                                    name="variants[{{ $variant->id }}]"
                                    label="{{ $priceLabel }}"
                                    value="{{ $option->id }}"
                                    :checked="old('variants.' . $variant->id, $service->variant_selections[$variant->id] ?? null) == $option->id"
                                />
                            @endforeach
                        </x-client::radio.group>
                        @break
                    @case('checkbox')
                        <x-client::checkbox
                            name="variants[{{ $variant->id }}]"
                            label="{{ $variant->name }}"
                            :options="collect($variant->options)->mapWithKeys(function ($option) use ($service) {
                                $priceModel = $option->target_price_model;
                                $isFree = strtolower($priceModel->type) === 'free';
                                $rates = $isFree ? [] : (is_string($priceModel->rates) ? json_decode($priceModel->rates, true) : $priceModel->rates);
                                
                                $price = $isFree ? 0 : ($rates[$service->currency]['price'] ?? 0);
                                $setupFee = $isFree ? 0 : ($rates[$service->currency]['setup_fee'] ?? 0);

                                $priceSuffix = $isFree ? ' | Free' : 
                                    ($price > 0 ? ' | ' . Currency::format($price, $service->currency) : '') . 
                                    ($setupFee > 0 ? ' + ' . Currency::format($setupFee, $service->currency) . ' Setup Fee' : '');

                                $label = $option->name . $priceSuffix;
                                return [$option->id => $label];
                            })->toArray()"
                            :checked="old('variants.' . $variant->id, $service->variant_selections[$variant->id] ?? [])"
                        />
                        @break
                    @case('slider')
                        <x-client::slider
                            name="variants[{{ $variant->id }}]"
                            label="{{ $variant->name }}"
                            :options="collect($variant->options)->map(function ($option) use ($service) {
                                $priceModel = $option->target_price_model;
                                $isFree = strtolower($priceModel->type) === 'free';
                                $rates = $isFree ? [] : (is_string($priceModel->rates) ? json_decode($priceModel->rates, true) : $priceModel->rates);
                                
                                $price = $isFree ? 0 : ($rates[$service->currency]['price'] ?? 0);
                                $setupFee = $isFree ? 0 : ($rates[$service->currency]['setup_fee'] ?? 0);

                                $priceSuffix = $isFree ? ' | Free' : 
                                    ($price > 0 ? ' | ' . Currency::format($price, $service->currency) : '') . 
                                    ($setupFee > 0 ? ' + ' . Currency::format($setupFee, $service->currency) . ' Setup Fee' : '');

                                $title = $option->name . $priceSuffix;
                                return ['value' => $option->id, 'title' => $title];
                            })->toArray()"
                            value="{{ old('variants.' . $variant->id, $service->variant_selections[$variant->id] ?? null) }}"
                            required
                        />
                        @break
                @endswitch
            @endforeach
            @if($targetPackage->variants->isEmpty())
                <p class="text-slate-500">{{ __('client/services.scaling.no_variants') }}</p>
            @endif
            <div class="flex justify-end gap-2">
                <a href="{{ route('client.services.show', ['service' => $service->service_number]) }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </div>
    @endif
</form>
@endsection