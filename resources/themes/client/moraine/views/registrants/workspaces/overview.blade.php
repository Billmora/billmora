@extends('client::registrants.show')

@section('workspaces')
    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl overflow-hidden">
        <div class="bg-billmora-1 px-6 py-4 border-b-2 border-billmora-2">
            <h3 class="flex gap-2 items-center font-semibold text-slate-600">
                <x-lucide-globe class="w-auto h-5" />
                {{ __('client/services.action.overview') }}
            </h3>
        </div>
        <ul class="grid gap-4 p-6">
            <li class="grid grid-cols-2 text-start items-center">
                <span class="text-slate-500 font-semibold">
                    Registrar
                </span>
                <span class="text-slate-600 font-semibold">
                    {{ $registrant->plugin->name ?? '-' }}
                </span>
            </li>
            <hr class="border-t-2 border-billmora-2">
            @for($i = 0; $i < 4; $i++)
                <li class="grid grid-cols-2 text-start items-center">
                    <span class="text-slate-500 font-semibold">
                        Nameserver {{ $i + 1 }}
                    </span>
                    <span class="text-slate-600 font-semibold wrap-break-word">
                        {{ $registrant->nameservers[$i] ?? '-' }}
                    </span>
                </li>
                @if ($i < 3)
                    <hr class="border-t-2 border-billmora-2">
                @endif
            @endfor
        </ul>
    </div>
@endsection