<div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden relative">
    @if ($step == 1)
        <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
            <h3 class="flex gap-2 items-center font-semibold text-slate-600">
                <x-lucide-arrow-up-down class="w-auto h-5" />
                {{ __('client/services.scale_label') }}
            </h3>
            <p class="text-slate-500">{{ __('client/services.scale_package_helper') }}</p>
        </div>
        <div class="grid gap-4 p-6">
            @error('general')
                <div class="bg-red-50 text-red-500 p-4 rounded-xl border-2 border-red-200 font-semibold mb-2">
                    {{ $message }}
                </div>
            @enderror
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->availablePackages as $package)
                    @php
                        $isCurrent = $service->package_id == $package->id;
                        $priceData = $package->prices->first();
                    @endphp
                    <div class="relative">
                        <input type="radio" wire:model="selectedPackageId" id="package_id_{{ $package->id }}" value="{{ $package->id }}" class="peer hidden">
                        <label for="package_id_{{ $package->id }}" class="flex flex-col h-full gap-4 p-6 border-2 rounded-2xl cursor-pointer transition-all duration-200 ease-in-out bg-billmora-bg border-billmora-2 hover:border-billmora-primary peer-checked:border-billmora-primary-500">
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
                                        <span class="text-xl text-slate-500 font-semibold">{{ __('billing.cycles.free') }}</span>
                                    </div>
                                @elseif ($priceData && isset($priceData->rates[$service->currency]))
                                    <div class="grid">
                                        <span class="text-xl text-slate-500 font-semibold">
                                            {{ Currency::format($priceData->rates[$service->currency]['price'], $service->currency) }}
                                        </span>
                                        <span class="text-sm text-slate-400 font-semibold">
                                            {{ $priceData->name }}
                                        </span>
                                    </div>
                                @endif
                                <p class="text-slate-500 text-sm leading-relaxed line-clamp-3">{!! $package->description !!}</p>
                            </div>
                            @if ($package->stock >= 1 && !$isCurrent)
                                <div class="mt-auto pt-4 border-t-2 border-billmora-2">
                                    <span class="block text-center text-xs text-emerald-600 font-semibold bg-emerald-50 py-1 rounded">
                                        {{ __('client/store.stock_available', ['item' => $package->stock]) }}
                                    </span>
                                </div>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <a href="{{ route('client.services.show', ['service' => $service->service_number]) }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.cancel') }}
                </a>
                <button type="button" wire:click="goToStep2" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.next') }}
                </button>
            </div>
        </div>
    @elseif ($step == 2)
        <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2 flex justify-between items-center">
            <div>
                <h3 class="flex gap-2 items-center font-semibold text-slate-600">
                    <x-lucide-arrow-up-down class="w-auto h-5" />
                    {{ __('client/services.scale_label') }} ({{ $this->targetPackage->name }})
                </h3>
                <p class="text-slate-500">{{ __('client/services.scale_variant_helper') }}</p>
            </div>
        </div>
        <div class="grid gap-6 p-6">
            @error('general')
                <div class="bg-red-50 text-red-500 p-4 rounded-xl border-2 border-red-200 font-semibold">
                    {{ $message }}
                </div>
            @enderror
            <div class="grid gap-4 relative">
                @foreach ($this->targetPackage->variants as $variant)
                    @switch($variant->type)
                        @case('select')
                            <x-client::select name="variants[{{ $variant->id }}]" wire:model.live="variantSelections.{{ $variant->id }}" label="{{ $variant->name }}" required>
                                @foreach ($variant->options as $option)
                                    <option value="{{ $option->id }}">
                                        {{ $option->name }} | {{ $this->formatOptionPrice($option) }}
                                    </option>
                                @endforeach
                            </x-client::select>
                            @break
                        @case('radio')
                            <x-client::radio.group name="variants[{{ $variant->id }}]" wire:model.live="variantSelections.{{ $variant->id }}" label="{{ $variant->name }}" required>
                                @foreach ($variant->options as $option)
                                    <x-client::radio.option
                                        name="variants[{{ $variant->id }}]"
                                        label="{{ $option->name }} | {{ $this->formatOptionPrice($option) }}"
                                        value="{{ $option->id }}"
                                    />
                                @endforeach
                            </x-client::radio.group>
                            @break
                        @case('checkbox')
                            @php
                                $checkboxOpts = [];
                                foreach($variant->options as $opt) {
                                    $checkboxOpts[$opt->id] = $opt->name . ' | ' . $this->formatOptionPrice($opt);
                                }
                            @endphp
                            <x-client::checkbox 
                                name="variants[{{ $variant->id }}]" 
                                wire:model.live="variantSelections.{{ $variant->id }}" 
                                label="{{ $variant->name }}" 
                                :options="$checkboxOpts" 
                            />
                            @break
                        @case('slider')
                            @php
                                $sliderOpts = [];
                                foreach($variant->options as $opt) {
                                    $sliderOpts[] = [
                                        'value' => $opt->id, 
                                        'title' => $opt->name . ' | ' . $this->formatOptionPrice($opt)
                                    ];
                                }
                            @endphp
                            <x-client::slider 
                                name="variants[{{ $variant->id }}]" 
                                wire:model.live="variantSelections.{{ $variant->id }}" 
                                label="{{ $variant->name }}" 
                                :options="$sliderOpts" 
                                required 
                            />
                            @break
                    @endswitch
                @endforeach
                @if($this->targetPackage->variants->isEmpty())
                    <p class="text-slate-500">{{ __('client/services.scaling.no_variants') }}</p>
                @endif
            </div>
            @if(!empty($calculation) && !$errors->has('general'))
                <div class="bg-billmora-1 border-2 border-billmora-2 rounded-xl p-5 mt-4">
                    <h4 class="font-semibold text-slate-700 mb-3">{{ __('client/store.package.order_summary') }}</h4>
                    <div class="space-y-2 text-sm font-medium text-slate-500">
                        <div class="flex justify-between">
                            <span>{{ __('client/services.scaling.current_amount_label') }}</span>
                            <span>{{ Currency::format($calculation['old_recurring'], $service->currency) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('client/services.scaling.new_amount_label') }}</span>
                            <span class="text-slate-700">{{ Currency::format($calculation['new_recurring'], $service->currency) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>{{ __('client/services.scaling.remaining_days_label') }}</span>
                            <span>{{ $calculation['remaining_days'] }} {{ __('billing.units.daily') }}</span>
                        </div>
                        <hr class="border-t-2 border-billmora-2 my-2">
                        <div class="flex justify-between items-center text-lg">
                            <span class="font-bold text-slate-700">{{ __('client/store.package.due_today') }}</span>
                            <span class="font-bold text-billmora-primary-500">
                                {{ Currency::format($calculation['payable_amount'], $service->currency) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" wire:click="goToStep1" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.back') }}
                </button>
                <button type="button" wire:click="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-2 text-white font-medium rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </div>
    @endif
</div>