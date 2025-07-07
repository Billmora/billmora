<x-client::modal modal="preferenceModal" title="{{ __('common.preference_title') }}" description="{{ __('common.preference_description') }}" icon="lucide-bolt">
  <form action="{{ route('preference.update') }}" method="POST">
    @csrf
    <div class="flex flex-col gap-2 mb-6">
      <x-client::select label="{{ __('common.language') }}" name="language" required>
        @foreach ($langs as $lang => $name)
          <option value="{{ $lang }}" {{ session('locale', config('app.locale')) == $lang ? 'selected' : '' }}>
            {{ $name }}
          </option>
        @endforeach
      </x-client::select>
    </div>
    <div class="flex justify-end gap-2">
      <x-client::button type="button" x-on:click="$store.modal.close()" variant="secondary">
        <span class="font-semibold">{{ __('common.cancel') }}</span>
      </x-client::button>
      <x-client::button type="submit">
        <span class="font-semibold">{{ __('common.save') }}</span>
      </x-client::button>
    </div>
  </form>
</x-client::modal>
<div class="xl:bg-billmora-2 md:border-b-4 border-b-billmora-3">
  <div x-data="{ navOpen: false }" class="xl:max-w-[87.5rem] mx-auto md:px-4 2xl:px-0">
    <div x-data="{ actionOpen: false}" class="md:flex md:justify-between md:items-center md:bg-billmora-2 relative z-10">
      <header class="flex items-center bg-billmora-2 py-4 px-6 md:px-0 relative z-9 border-b-4 border-b-billmora-3 md:border-b-0">
        <x-lucide-menu class="h-6 w-auto md:hidden text-slate-700 cursor-pointer" x-on:click="navOpen = !navOpen"/>
        <a href="/" class="mx-auto md:ml-0">
          <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company logo" class="h-12 w-auto">
        </a>
        <x-lucide-layout-panel-left class="h-6 w-auto md:hidden text-slate-700 cursor-pointer" x-on:click="actionOpen = !actionOpen"/>
      </header>
      <div x-show="actionOpen" x-on:click.away="actionOpen = false" class="flex md:flex! justify-between flex-wrap gap-2 bg-billmora-2 md:bg-billmora-2 py-4 px-6 md:px-0 z-8 border-b-4 border-b-billmora-3 md:border-b-0"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-y-100"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="-translate-y-100">
        <x-client::button variant="secondary" icon="lucide-languages" modal="preferenceModal">
          <span class="font-semibold">{{ $langActive }} (USD)</span>
        </x-client::button>
        <span class="hidden mx-2 w-1 h-auto bg-billmora-3 md:inline"></span>
        @auth
        <x-client::dropdown>
          <x-slot name="trigger">
            <img src="{{ auth()->user()->avatar }}" alt="user avatar" class="w-10 h-auto rounded-full cursor-pointer">
          </x-slot>
          <div>
            <span class="text-xl text-slate-600 font-semibold">{{ auth()->user()->name }}</span>
            <span class="text-slate-500">{{ auth()->user()->email }}</span>
          </div>
          <span class="w-auto h-0.5 bg-billmora-3 my-2"></span>
          <div class="space-y-2">
            <x-client::link variant="text" href="/account/detail" icon="lucide-circle-user-round" class="font-semibold">Account Detail</x-client::link>
            <x-client::link variant="text" href="/account/security" icon="lucide-fingerprint" class="font-semibold">Account Security</x-client::link>
          </div>
          <span class="w-auto h-0.5 bg-billmora-3 my-2"></span>
          <div class="space-y-2">
            @if (auth()->user()->is_admin)
              <x-client::link variant="text" href="/admin" icon="lucide-user-round-cog" class="font-semibold">Admin</x-client::link>
            @endif
            <form action="{{ route('client.logout') }}" method="POST">
              @csrf
              <x-client::button type="submit" variant="text" class="w-full font-semibold" icon="lucide-log-out">{{ __('common.sign_out') }}</x-client::button>
            </form>
          </div>
        </x-client::dropdown>
        @else
          <div class="flex items-center gap-2 ml-auto">
            <x-client::link variant="secondary" href="/auth/login">
              <span class="font-semibold">{{ __('common.sign_in') }}</span>
            </x-client::link>
            <x-client::link variant="primary" href="/auth/register">
              <span class="font-semibold">{{ __('common.sign_up') }}</span>
            </x-client::link>
          </div>
        @endauth
      </div>
    </div>
    <div x-show="navOpen" class="md:block!">
      <div class="bg-black w-full h-full fixed top-0 opacity-40 z-10 md:-z-10 md:hidden"></div>
      <nav class="fixed md:static md:block! top-0 left-0 z-10 w-[20rem] md:w-full h-full bg-billmora-2 p-6 md:py-4 md:px-0 border-r-4 border-r-billmora-3 md:border-r-0" x-show="navOpen" x-on:click.away="navOpen = false"
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="-translate-x-100"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-400"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-100">
        <div class="flex items-center justify-between mb-6 md:hidden">
          <a href="/">
            <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company logo" class="h-12 w-auto">
          </a>
          <x-lucide-x class="h-8 w-auto text-slate-400 cursor-pointer" x-on:click="navOpen = false"/>
        </div>
        <div class="space-y-2 md:flex md:flex-row md:space-y-0 md:space-x-2">
          <x-client::link href="/dashboard" variant="{{ request()->is('/dasboard*') ? 'primary' : 'text' }}" icon="lucide-layout-dashboard" active="{{ request()->is('/dashboard*') ? true : false }}">
            <span class="font-semibold">{{ __('common.dashboard') }}</span>
          </x-client::link>
          <x-client::link href="/store" variant="{{ request()->is('/store*') ? 'primary' : 'text' }}" icon="lucide-store" active="{{ request()->is('/store*') ? true : false }}">
            <span class="font-semibold">{{ __('common.store') }}</span>
          </x-client::link>
          <x-client::link href="/news" variant="{{ request()->is('/news') ? 'primary' : 'text' }}" icon="lucide-newspaper" active="{{ request()->is('/news') ? true : false }}">
            <span class="font-semibold">{{ __('common.news') }}</span>
          </x-client::link>
          @if (Billmora::getGeneral('term_tos'))
            <x-client::link variant="{{ request()->is('/terms-of-service*') ? 'primary' : 'text' }}" href="{{ Billmora::getGeneral('term_tos_url') ? Billmora::getGeneral('term_tos_url') : '/terms-of-service' }}" icon="lucide-handshake" active="{{ request()->is('/terms-of-service*') ? true : false }}">
              <span class="font-semibold">{{ __('common.terms_of_service') }}</span>
            </x-client::link>
          @endif
        </div>
      </nav>
    </div>
  </div>
</div>