@extends('client::services.show')

@section('workspaces')
    @if($variantOptions->isNotEmpty())
        <div class="bg-white border-2 border-billmora-2 rounded-2xl overflow-hidden">
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
@endsection