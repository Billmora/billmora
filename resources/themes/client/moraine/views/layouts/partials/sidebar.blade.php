@inject('pluginManager', 'App\Services\PluginManager')
@php
    $pluginClientMenus = $pluginManager->getNavigationClient();
@endphp

<nav id="sidebar" class="fixed z-100 xl:sticky top-0 left-0 xl:block shrink-0 p-5 xl:pr-0 w-[300px] xl:w-[320px] h-dvh -translate-x-full xl:translate-x-0 transition-transform duration-300 ease-in-out">
  <div class="bg-billmora-bg flex flex-col w-full h-full border-2 border-billmora-2 rounded-2xl p-6">
    <a href="{{ route('client.dashboard') }}" class="relative flex gap-3 items-center">
      <img src="{{ Billmora::getGeneral('company_logo') }}" alt="billmora logo" class="w-auto h-9 rounded-lg">
      <h3 class="text-xl font-extrabold uppercase text-billmora-primary-500">{{ Billmora::getGeneral('company_name') }}</h3>
    </a>
    
    <div id="closeSidebar" role="button" class="absolute top-14 right-0 xl:hidden bg-billmora-bg hover:bg-billmora-primary-500 border-2 border-billmora-2 text-slate-600 hover:text-white shadow p-2 rounded-full cursor-pointer transition">
      <x-lucide-x class="w-auto h-5" />
    </div>
    <hr class="border-t-2 border-billmora-2 my-7">
    <div class="space-y-2 overflow-y-auto" id="sidemenu">
      
      @auth
        <a href="{{ route('client.dashboard') }}" class="flex gap-2 items-center {{ request()->routeIs('client.dashboard') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-layout-grid class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.dashboard') }}</span>
        </a>
      @endauth
      <a href="{{ route('client.store') }}" class="flex gap-2 items-center {{ request()->routeIs('client.store') || request()->routeIs('client.store.catalog*') || request()->routeIs('client.store.domains*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-store class="w-5 h-auto" />
        <span class="font-semibold">{{ __('client/navigation.store') }}</span>
      </a>
      @auth
        <a href="{{ route('client.services') }}" class="flex gap-2 items-center {{ request()->routeIs('client.services*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-scan-text class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.services') }}</span>
        </a>
        @if(Billmora::getGeneral('domain_registration_enabled') || Billmora::getGeneral('domain_transfer_enabled'))
        <a href="{{ route('client.registrants') }}" class="flex gap-2 items-center {{ request()->routeIs('client.registrants*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-globe class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.registrants') }}</span>
        </a>
        @endif
        <a href="{{ route('client.invoices') }}" class="flex gap-2 items-center {{ request()->routeIs('client.invoices*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-receipt-text class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.invoices') }}</span>
        </a>
        <a href="{{ route('client.tickets') }}" class="flex gap-2 items-center {{ request()->routeIs('client.tickets*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-ticket class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/navigation.tickets') }}</span>
        </a>
      @endauth
      @if(!empty($pluginClientMenus))
          @foreach($pluginClientMenus as $groupTitle => $menuItems)
              @foreach($menuItems as $menu)
                  <a href="{{ $menu['route'] }}" 
                    class="flex gap-2 items-center {{ request()->url() === $menu['route'] ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300"
                  >
                      @if(str_starts_with($menu['icon'], 'lucide-'))
                          <x-dynamic-component :component="$menu['icon']" class="w-5 h-auto" />
                      @else
                          <i class="{{ $menu['icon'] }} w-5 text-center"></i>
                      @endif
                      <span class="font-semibold">{{ $menu['label'] }}</span>
                  </a>
              @endforeach
          @endforeach
      @endif
    </div>
    @guest
      <div class="grid sm:hidden grid-cols-2 gap-4 mt-auto pt-4">
        <a href="{{ route('client.login') }}" class="flex justify-center gap-1 bg-billmora-2 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white not-hover:font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.login') }}
        </a>
        <a href="{{ route('client.register') }}" class="flex justify-center gap-1 bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.register') }}
        </a>
      </div>
    @endguest
  </div>
</nav>
<!-- Backdrop -->
<div id="backdrop" class="fixed inset-0 bg-black/25 z-99 xl:hidden opacity-0 pointer-events-none transition-opacity duration-300"></div>