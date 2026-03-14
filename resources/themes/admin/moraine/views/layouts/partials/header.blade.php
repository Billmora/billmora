<header class="sticky z-90 top-5 right-0 flex justify-between items-center w-full bg-white p-4 border-2 border-billmora-2 rounded-2xl">
  <!-- Toggle Sidebar -->
  <button id="toggleSidebar" class="block xl:hidden bg-billmora-1 hover:bg-billmora-primary-500 p-2.5 mr-4 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer">
    <x-lucide-menu class="w-auto h-5" />
  </button>

  <!-- Browse (DESKTOP) -->
  <div class="hidden md:block w-[400px] mr-auto">
    <button type="button" id="browse" class="flex gap-2 items-center w-full bg-billmora-1 px-2 py-2 text-slate-500 text-start outline-none ring-billmora-primary-500 hover:ring-2 rounded-lg transition-all cursor-pointer group">
      <x-lucide-search class="w-auto h-5 pointer-events-none group-hover:text-billmora-primary-500 transition-colors duration-150" />
      <span class="text-slate-400">{{ __('admin/common.browse') }}</span>
      <div class="flex gap-2 ml-auto pointer-events-none text-slate-400 group-hover:text-billmora-primary-500 transition-colors duration-150">
        <span class="bg-white px-1 py-0.25 text-sm font-semibold rounded-lg">CTRL</span>
        <span class="bg-white px-1 py-0.25 text-sm font-semibold rounded-lg">K</span>
      </div>
    </button>
  </div>

  <!-- Browse (MOBILE) -->
  <button type="button" id="browse" class="block md:hidden bg-billmora-1 hover:bg-billmora-primary-500 p-2.5 mr-auto text-slate-600 hover:text-white rounded-full transition-colors duration-150 cursor-pointer">
    <x-lucide-search class="w-auto h-5 pointer-events-none" />
  </button>

  {{-- Toggle Preferences --}}
  <x-admin::modal.trigger modal="preferenceModal" class="flex gap-3 items-center bg-billmora-2 hover:bg-billmora-primary-500 px-3 py-2 ml-auto text-billmora-primary-500 hover:text-white font-semibold rounded-lg transition-colors duration-300 group cursor-pointer">
    <x-dynamic-component component="flag-country-{{ strtolower($langActive['country']) }}" class="w-auto h-5 pointer-events-none" />
    <div class="w-1 h-5 bg-billmora-3"></div>
    <span>{{ $currencyActive['code'] }}</span>
  </x-admin::modal.trigger>

  <!-- Profile -->
  <div class="relative w-fit flex items-center"
      x-data="{ isOpen: false, openedWithKeyboard: false }">
    <!-- Toggle Button -->
    <button type="button" class="cursor-pointer ml-4"
        x-on:click="isOpen = ! isOpen" 
        aria-haspopup="true">
      <img src="{{ auth()->user()->avatar }}" alt="billmora profile" class="w-10 h-10 rounded-full">
    </button>
    <!-- Dropdown Menu -->
    <div class="absolute top-16 right-0 flex w-[300px] flex-col gap-2 bg-white p-4 border-2 border-billmora-2 rounded-2xl" role="menu"
        x-cloak x-show="isOpen || openedWithKeyboard"
        x-transition
        x-on:click.outside="isOpen = false, openedWithKeyboard = false">
      {{-- Dropdown Content --}}
      <div class="flex flex-col">
        <span class="text-xl text-slate-600 font-bold">{{ auth()->user()->fullname }}</span>
        <span class="text-md text-slate-500 font-semibold">
          @if (auth()->user()->isRootAdmin())
              Administrator
          @elseif (auth()->user()->roles->isNotEmpty())
              {{ auth()->user()->roles->pluck('name')->implode(', ') }}
          @endif
        </span>
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
      <a href="#" class="flex gap-2 items-center hover:bg-billmora-primary-500 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-layers-2 class="w-5 h-auto" />
        <span class="font-semibold">{{ __('common.page.portal') }}</span>
      </a>
      <a href="{{ route('client.dashboard') }}" class="flex gap-2 items-center hover:bg-billmora-primary-500 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-copy class="w-5 h-auto" />
        <span class="font-semibold">{{ __('common.page.client') }}</span>
      </a>
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
</header>
<x-admin::modal.content 
  modal="preferenceModal"
  title="{{ __('preference.title') }}"
  description="{{ __('preference.description') }}"
>
  <form action="{{ route('common.preference.update') }}" method="POST" class="space-y-4">
    @csrf
    <x-admin::select name="language" label="{{ __('preference.language') }}" required>
      @foreach ($langs as $lang)
        <option value="{{ $lang['lang'] }}" @if ($lang['lang'] === app()->getLocale()) selected @endif>
          {{ $lang['name'] }}
        </option>
      @endforeach
    </x-admin::select>
    <x-admin::select name="currency" label="{{ __('preference.currency') }}" required>
      @foreach ($currencies as $currency)
          <option value="{{ $currency->code }}"
              @selected($currency->code === $currencyActive?->code)>
              {{ $currency->code }}
          </option>
      @endforeach
    </x-admin::select>
    <div class="flex justify-end gap-2 pt-4">
      <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
      <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.save') }}</button>
    </div>
  </form>
</x-admin::modal.content>