<header class="w-full bg-billmora-bg/80 border-b-2 border-billmora-2 backdrop-blur-lg fixed top-0 z-50"
    x-data="{ open: false }"
>
    <div class="max-w-7xl h-16 px-4 flex justify-between items-center mx-auto">
        <a href="{{ route('portal.home') }}" class="inline-flex items-center gap-2">
            <img src="{{ Billmora::getGeneral('company_logo') }}" alt="brand logo" class="w-auto h-10 rounded-lg">
            <span class="text-2xl text-slate-600 font-bold">{{ Billmora::getGeneral('company_name') }}</span>
        </a>
        <div class="hidden md:flex gap-6">
            <a href="{{ route('portal.home') }}" class="text-slate-500 hover:text-billmora-primary-500 font-semibold">
                {{ __('portal.home') }}
            </a>
            <a href="{{ route('client.store') }}" class="text-slate-500 hover:text-billmora-primary-500 font-semibold">
                {{ __('portal.store') }}
            </a>
            @if (Billmora::getGeneral('term_tos'))
                <a href="{{ Billmora::getGeneral('term_tos_url') ?: route('portal.terms.service') }}" class="text-slate-500 hover:text-billmora-primary-500 font-semibold">
                    {{ __('portal.terms_of_service') }}
                </a>
            @endif
        </div>
        <div class="hidden md:flex items-center gap-2">
            @auth
                <a href="{{ route('client.dashboard') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white rounded-lg transition">
                    {{ __('portal.client_area') }}
                </a>
            @else
                <a href="{{ route('client.login') }}" class="bg-billmora-2 hover:bg-billmora-primary-500 px-4 py-2 text-billmora-primary-500 hover:text-billmora-bg rounded-lg transition">
                    {{ __('common.sign_in') }}
                </a>
                <a href="{{ route('client.register') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white rounded-lg transition">
                    {{ __('common.sign_up') }}
                </a>
            @endauth
        </div>
        <button
            type="button"
            class="md:hidden p-2 rounded-lg text-slate-500 hover:text-billmora-primary-500 hover:bg-billmora-2 transition"
            x-on:click="open = !open"
            :aria-expanded="open"
        >
            <template x-if="!open">
                <x-lucide-menu class="w-6 h-6" />
            </template>
            <template x-if="open">
                <x-lucide-x class="w-6 h-6" />
            </template>
        </button>
    </div>
    <div
        x-show="open"
        x-on:click.away="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden border-t-2 border-billmora-2 bg-billmora-bg/80 backdrop-blur-lg px-4 py-4 flex flex-col gap-3"
    >
        <a href="{{ route('portal.home') }}" class="text-slate-500 hover:text-billmora-primary-500 font-semibold py-1">
            {{ __('portal.home') }}
        </a>
        <a href="{{ route('client.store') }}" class="text-slate-500 hover:text-billmora-primary-500 font-semibold py-1">
            {{ __('portal.store') }}
        </a>
        @if (Billmora::getGeneral('term_tos'))
            <a href="{{ Billmora::getGeneral('term_tos_url') ?: route('portal.terms.service') }}" class="text-slate-500 hover:text-billmora-primary-500 font-semibold py-1">
                {{ __('portal.terms_of_service') }}
            </a>
        @endif

        <div class="border-t-2 border-billmora-2 pt-3 flex flex-col gap-2">
            @auth
                <a href="{{ route('client.dashboard') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white text-center rounded-lg transition">
                    {{ __('portal.client_area') }}
                </a>
            @else
                <a href="{{ route('client.login') }}" class="bg-billmora-2 hover:bg-billmora-primary-500 px-4 py-2 text-billmora-primary-500 hover:text-billmora-bg text-center rounded-lg transition">
                    {{ __('common.sign_in') }}
                </a>
                <a href="{{ route('client.register') }}" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-4 py-2 text-white text-center rounded-lg transition">
                    {{ __('common.sign_up') }}
                </a>
            @endauth
        </div>
    </div>
</header>