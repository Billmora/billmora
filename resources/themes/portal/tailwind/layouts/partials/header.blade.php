<x-portal::modal modal="preferenceModal" title="{{ __('common.preference_title') }}" description="{{ __('common.preference_description') }}" icon="lucide-bolt">
  <form action="{{ route('preference.update') }}" method="POST">
    @csrf
    <div class="flex flex-col gap-2 mb-6">
      <label for="language" class="text-slate-700 font-semibold">{{ __('common.language') }}</label>
      <x-portal::select label="{{ __('common.language') }}" name="language">
        @foreach ($langs as $lang => $name)
          <option value="{{ $lang }}" {{ session('locale', config('app.locale')) == $lang ? 'selected' : '' }}>
            {{ $name }}
          </option>
        @endforeach
      </x-portal::select>
    </div>
    <div class="flex justify-end gap-2">
      <x-portal::button type="button" x-on:click="$store.modal.close()" variant="secondary">
        <span class="font-semibold">{{ __('common.cancel') }}</span>
      </x-portal::button>
      <x-portal::button type="submit">
        <span class="font-semibold">{{ __('common.save') }}</span>
      </x-portal::button>
    </div>
  </form>
</x-portal::modal>
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
        <x-portal::button variant="secondary" icon="lucide-languages" modal="preferenceModal">
          <span class="font-semibold">{{ $langActive }} (USD)</span>
        </x-portal::button>
        <span class="hidden mx-2 w-1 h-auto bg-billmora-3 md:inline"></span>
        @auth
          <x-portal::link variant="secondary" href="/dashboard">
            <span class="font-semibold">{{ __('common.client_area') }}</span>
          </x-portal::link>
        @else 
          <div class="flex items-center gap-2 ml-auto">
            <x-portal::link variant="secondary" href="/auth/login">
              <span class="font-semibold">{{ __('common.sign_in') }}</span>
            </x-portal::link>
            <x-portal::link variant="primary" href="/auth/login">
              <span class="font-semibold">{{ __('common.sign_up') }}</span>
            </x-portal::link>
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
          <x-portal::link href="/" variant="primary" icon="lucide-home" active="{{ request()->is('/') ? true : false }}">
            <span class="font-semibold">{{ __('common.home') }}</span>
          </x-portal::link>
          <x-portal::link href="/store" variant="{{ request()->is('/store*') ? 'primary' : 'text' }}" icon="lucide-store" active="{{ request()->is('/store*') ? true : false }}">
            <span class="font-semibold">{{ __('common.store') }}</span>
          </x-portal::link>
          <x-portal::link href="/news" variant="{{ request()->is('/news') ? 'primary' : 'text' }}" icon="lucide-newspaper" active="{{ request()->is('/news') ? true : false }}">
            <span class="font-semibold">{{ __('common.news') }}</span>
          </x-portal::link>
          @if (Billmora::getGeneral('term_tos'))
            <x-portal::link variant="{{ request()->is('/terms-of-service*') ? 'primary' : 'text' }}" href="{{ Billmora::getGeneral('term_tos_url') ? Billmora::getGeneral('term_tos_url') : '/terms-of-service' }}" icon="lucide-handshake" active="{{ request()->is('/terms-of-service*') ? true : false }}">
              <span class="font-semibold">{{ __('common.terms_of_service') }}</span>
            </x-portal::link>
          @endif
        </div>
      </nav>
    </div>
  </div>
</div>