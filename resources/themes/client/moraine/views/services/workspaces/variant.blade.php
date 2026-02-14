@extends('client::services.show')

@section('workspaces')
    @if($variantOptions->isNotEmpty())
        <div class="bg-white border-2 border-billmora-2 rounded-2xl overflow-hidden">
            <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
                <h3 class="font-semibold text-slate-600">{{ __('client/services.variant_label') }}</h3>
            </div>
            <ul class="grid gap-4 p-6">
                @foreach($variantOptions as $option)
                    @if(!$loop->first)
                        <hr class="border-t-2 border-billmora-2">
                    @endif
                    <li class="grid grid-cols-2 text-start">
                        <span class="text-slate-500 font-semibold">
                            {{ $option->variant->name }}
                        </span>
                        <span class="text-slate-600 font-semibold">
                            {{ $option->name }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection