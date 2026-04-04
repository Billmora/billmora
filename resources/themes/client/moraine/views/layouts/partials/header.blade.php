<header class="sticky z-90 top-5 right-0 flex justify-between items-center w-full bg-billmora-bg p-3 border-2 border-billmora-2 rounded-2xl">
  <!-- Toggle Sidebar -->
  <button id="toggleSidebar" class="block xl:hidden bg-billmora-1 hover:bg-billmora-primary-500 p-2.5 mr-4 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer">
    <x-lucide-menu class="w-auto h-5" />
  </button>
  <div class="flex gap-4 ml-auto">
    <a href="{{ route('client.checkout.cart') }}" class="block items-center bg-billmora-1 hover:bg-billmora-primary-500 p-2.5 mr-auto text-billmora-primary-500 hover:text-white rounded-full transition-colors duration-150 cursor-pointer">
      <x-lucide-shopping-cart class="w-5 h-auto" />
    </a>
    {{-- Toggle Preferences --}}
    <x-client::modal.trigger modal="preferenceModal" class="flex gap-3 items-center bg-billmora-2 hover:bg-billmora-primary-500 px-2.5 py-1.5 ml-auto text-billmora-primary-500 hover:text-white font-semibold rounded-lg transition-colors duration-300 group cursor-pointer">
      <x-dynamic-component component="flag-country-{{ strtolower($langActive['country']) }}" class="w-auto h-5 pointer-events-none" />
      <div class="w-1 h-5 bg-billmora-3"></div>
      <span>{{ $currencyActive['code'] }}</span>
    </x-client::modal.trigger>
  </div>
  @auth
    <!-- Profile -->
    <div class="relative w-fit flex items-center"
        x-data="{ isOpen: false, openedWithKeyboard: false }">
      <!-- Toggle Button -->
      <button type="button" class="cursor-pointer ml-4"
          x-on:click="isOpen = ! isOpen" 
          aria-haspopup="true">
        <img src="{{ auth()->user()->avatar }}" alt="user profile" class="w-8 h-8 rounded-full">
      </button>
      <!-- Dropdown Menu -->
      <div class="absolute top-14 right-0 flex w-[260px] flex-col gap-2 bg-billmora-bg p-3 border-2 border-billmora-2 rounded-2xl" role="menu"
          x-cloak x-show="isOpen || openedWithKeyboard"
          x-transition
          x-on:click.outside="isOpen = false, openedWithKeyboard = false">
        {{-- Dropdown Content --}}
        <div class="flex flex-col">
          <span class="text-xl text-slate-600 font-bold">{{ auth()->user()->fullname }}</span>
          <span class="text-md text-slate-500 font-semibold">{{ auth()->user()->email }}</span>
        </div>
        <hr class="border-t-2 border-billmora-2 my-2">
        <a href="{{ route('client.account.settings') }}" class="flex gap-2 items-center hover:bg-billmora-primary-500 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
          <x-lucide-circle-user-round class="w-5 h-auto" />
          <span class="font-semibold">{{ __('common.account_settings') }}</span>
        </a>
        <a href="{{ route('client.account.security') }}" class="flex gap-2 items-center hover:bg-billmora-primary-500 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
          <x-lucide-fingerprint class="w-5 h-auto" />
          <span class="font-semibold">{{ __('common.account_security') }}</span>
        </a>
        @if (Billmora::getGeneral('credit_use'))
          <a href="{{ route('client.account.credits') }}" class="flex gap-2 items-center hover:bg-billmora-primary-500 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
            <x-lucide-badge-cent class="w-5 h-auto" />
            <span class="font-semibold">{{ __('common.account_credits') }}</span>
          </a>
        @endif
        <hr class="border-t-2 border-billmora-2 my-2">
        <a href="{{ route('portal.home') }}" class="flex gap-2 items-center hover:bg-billmora-primary-500 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
          <x-lucide-layers-2 class="w-5 h-auto" />
          <span class="font-semibold">{{ __('common.page.portal') }}</span>
        </a>
        @if (auth()->user()->isAdmin())
          <a href="{{ route('admin.dashboard') }}" class="flex gap-2 items-center hover:bg-billmora-primary-500 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
            <x-lucide-shield class="w-5 h-auto" />
            <span class="font-semibold">{{ __('common.page.admin') }}</span>
          </a>
        @endif
        <hr class="border-t-2 border-billmora-2 my-2">
        <form action="{{ route('client.logout.store') }}" method="POST">
          @csrf
          <button class="w-full flex gap-2 items-center hover:bg-red-400 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300 cursor-pointer" role="menuitem">
            <x-lucide-log-out class="w-5 h-auto" />
            <span class="font-semibold">{{ __('common.sign_out') }}</span>
          </button>
        </form>
      </div>
    </div>
  @else
    <div class="hidden sm:flex gap-4 ml-4">
      <a href="{{ route('client.login') }}" class="flex gap-1 items-center bg-billmora-2 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white not-hover:font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
          {{ __('common.login') }}
      </a>
      <a href="{{ route('client.register') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
          {{ __('common.register') }}
      </a>
    </div>
  @endauth
</header>
<x-client::modal.content 
  modal="preferenceModal"
  title="{{ __('preference.title') }}"
  description="{{ __('preference.description') }}"
>
  <form action="{{ route('common.preference.update') }}" method="POST" class="space-y-4">
    @csrf
    <x-client::select name="language" label="{{ __('preference.language') }}" required>
      @foreach ($langs as $lang)
        <option value="{{ $lang['lang'] }}" @if ($lang['lang'] === app()->getLocale()) selected @endif>
          {{ $lang['name'] }}
        </option>
      @endforeach
    </x-client::select>
    <x-client::select name="currency" label="{{ __('preference.currency') }}" required>
      @foreach ($currencies as $currency)
          <option value="{{ $currency->code }}"
              @selected($currency->code === $currencyActive?->code)>
              {{ $currency->code }}
          </option>
      @endforeach
    </x-client::select>
    <div class="flex justify-end gap-2 pt-4">
      <x-client::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-client::modal.trigger>
      <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
    </div>
  </form>
</x-client::modal.content>