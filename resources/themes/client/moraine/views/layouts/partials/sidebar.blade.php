<nav id="sidebar" class="fixed z-100 xl:sticky top-0 left-0 xl:block shrink-0 p-5 xl:pr-0 w-[350px] sm:w-[400px] h-dvh -translate-x-full xl:translate-x-0 transition-transform duration-300 ease-in-out">
  <div class="bg-white flex flex-col w-full h-full border-2 border-billmora-2 rounded-2xl p-8">
    <a href="{{ route('client.dashboard') }}" class="relative flex gap-3 items-center">
      <img src="{{ Billmora::getGeneral('company_logo') }}" alt="billmora logo" class="w-auto h-11 rounded-lg">
      <h3 class="text-2xl font-extrabold uppercase text-billmora-primary">{{ Billmora::getGeneral('company_name') }}</h3>
    </a>
    {{-- Sidebar close toggle --}}
    <div id="closeSidebar" role="button" class="absolute top-14 right-0 xl:hidden bg-white hover:bg-billmora-primary border-2 border-billmora-2 text-slate-600 hover:text-white shadow p-2 rounded-full cursor-pointer transition">
      <x-lucide-x class="w-auto h-5" />
    </div>
    <hr class="border-t-2 border-billmora-2 my-7">
    <div class="space-y-2 overflow-y-auto" id="sidemenu">
      {{-- Sidebar content --}}
      @auth
        <a href="{{ route('client.dashboard') }}" class="flex gap-2 items-center {{ request()->routeIs('client.dashboard') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-layout-grid class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.dashboard') }}</span>
        </a>
      @endauth
      <a href="{{ route('client.store') }}" class="flex gap-2 items-center {{ request()->routeIs('client.store*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-store class="w-5 h-auto" />
        <span class="font-semibold">{{ __('client/navigation.store') }}</span>
      </a>
      @auth
        <a href="{{ route('client.services') }}" class="flex gap-2 items-center {{ request()->routeIs('client.services*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-scan-text class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.services') }}</span>
        </a>
        <a href="{{ route('client.invoices') }}" class="flex gap-2 items-center {{ request()->routeIs('client.invoices*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-receipt-text class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.invoices') }}</span>
        </a>
      @endauth
    </div>
    @guest
      <div class="grid sm:hidden grid-cols-2 gap-4 mt-auto pt-4">
        <a href="{{ route('client.login') }}" class="flex justify-center gap-1 bg-billmora-2 hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white not-hover:font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.login') }}
        </a>
        <a href="{{ route('client.register') }}" class="flex justify-center gap-1 bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.register') }}
        </a>
      </div>
    @endguest
  </div>
</nav>
<!-- Backdrop -->
<div id="backdrop" class="fixed inset-0 bg-black/25 z-99 xl:hidden opacity-0 pointer-events-none transition-opacity duration-300"></div>