@extends('client::services.show')

@section('workspaces')
    @if($variantOptions->isNotEmpty())
        <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden">
            <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
                <h3 class="flex gap-2 items-center font-semibold text-slate-600">
                    <x-lucide-boxes class="w-auto h-5" />
                    {{ __('client/services.variant_label') }}
                </h3>
            </div>
            <ul class="grid gap-4 p-6">
                @foreach($variantOptions->groupBy('variant.name') as $variantName => $options)
                    @if(!$loop->first)
                        <hr class="border-t-2 border-billmora-2">
                    @endif
                    <li class="grid grid-cols-2 text-start">
                        <span class="text-slate-500 font-semibold">
                            {{ $variantName }}
                        </span>
                        <span class="text-slate-600 font-semibold">
                            {{ $options->pluck('name')->join(', ') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(!empty($checkoutData))
        <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden">
            <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2 flex justify-between items-center">
                <h3 class="flex gap-2 items-center font-semibold text-slate-600">
                    <x-lucide-server class="w-auto h-5" />
                    {{ __('client/services.configuration_label') }}
                </h3>
            </div>
            <ul class="grid gap-4 p-6">
                @foreach($checkoutData as $data)
                    @if(!$loop->first)
                        <hr class="border-t-2 border-billmora-2">
                    @endif
                    <li class="grid grid-cols-2 text-start items-center">
                        <span class="text-slate-500 font-semibold">
                            {{ $data['label'] }}
                        </span>
                        <span class="text-slate-600 font-semibold flex items-center justify-between gap-4">
                            @if($data['type'] === 'password')
                                <div x-data="{ shown: false, value: '{{ addslashes($data['value']) }}' }" class="flex items-center justify-between gap-4">
                                    <span class="tracking-widest text-slate-500" x-text="shown ? value : '********'"></span>
                                    <button type="button" 
                                        x-on:click="shown = !shown"
                                        :class="{ 'text-slate-400': !shown }"
                                        class="text-sm px-2 py-1 bg-billmora-1 border border-billmora-2 rounded-md hover:bg-billmora-2 transition-colors cursor-pointer text-slate-500">
                                        <span x-text="shown ? '{{ __('common.hide') }}' : '{{ __('common.show') }}'"></span>
                                    </button>
                                </div>
                            @elseif ($data['type'] === 'toggle')
                                {{ $data['value'] ? 'true' : 'false' }}
                            @else
                                @if(is_array($data['value']))
                                    {{ implode(', ', $data['value']) }}
                                @else
                                    {{ $data['value'] }}
                                @endif
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection