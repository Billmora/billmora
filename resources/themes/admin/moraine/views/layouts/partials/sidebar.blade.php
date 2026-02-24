@inject('pluginManager', 'App\Services\PluginManager')
@php
    $pluginAdminMenus = $pluginManager->getNavigationAdmin();
@endphp

<nav id="sidebar" class="fixed z-100 xl:sticky top-0 left-0 xl:block shrink-0 p-5 xl:pr-0 w-[350px] sm:w-[400px] h-dvh -translate-x-full xl:translate-x-0 transition-transform duration-300 ease-in-out">
  <div class="bg-white flex flex-col w-full h-full border-2 border-billmora-2 rounded-2xl p-8">
    <a href="{{ route('admin.dashboard') }}" class="relative flex gap-3 items-center">
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
      <a href="{{ route('admin.dashboard') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.dashboard') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-layout-grid class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.dashboard') }}</span>
      </a>
      <span class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.management') }}</span>
      @can('users.view')
        <a href="{{ route('admin.users') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.users*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-users class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.users') }}</span>
        </a>
      @endcan
      @can('orders.view')
        <a href="{{ route('admin.orders') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.orders*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-shopping-bag class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.orders') }}</span>
        </a>
      @endcan
      @can('services.view')
        <a href="{{ route('admin.services') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.services*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-scan-text class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.services') }}</span>
        </a>
      @endcan
      @can('invoices.view')
        <a href="{{ route('admin.invoices') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.invoices*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-receipt-text class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.invoices') }}</span>
        </a>
      @endcan
      @can('transactions.view')
        <a href="{{ route('admin.transactions') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.transactions*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-landmark class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.transactions') }}</span>
        </a>
      @endcan
      @can('broadcasts.view')
        <a href="{{ route('admin.broadcasts') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.broadcasts*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-radio class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.broadcasts') }}</span>
        </a>
      @endcan
      <span class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.product') }}</span>
      @can('catalogs.view')
        <a href="{{ route('admin.catalogs') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.catalogs*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-box class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.catalogs') }}</span>
        </a>
      @endcan
      @can('packages.view')
        <a href="{{ route('admin.packages') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.packages*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-package class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.packages') }}</span>
        </a>
      @endcan
      @can('variants.view')
        <a href="{{ route('admin.variants') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.variants*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-boxes class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.variants') }}</span>
        </a>
      @endcan
      @can('coupons.view')
        <a href="{{ route('admin.coupons') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.coupons*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-tags class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.coupons') }}</span>
        </a>
      @endcan
      @if(!empty($pluginAdminMenus))
          @foreach($pluginAdminMenus as $groupTitle => $menuItems)
              <span class="mt-4 block text-slate-600 font-semibold text-md">{{ $groupTitle }}</span>
              @foreach($menuItems as $menu)
                  <a href="{{ $menu['route'] }}" 
                    class="flex gap-2 items-center {{ request()->url() === $menu['route'] ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300"
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
      <span class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.plugin') }}</span>
      @can('provisionings.view')
        <a href="{{ route('admin.provisionings') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.provisionings*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-plug class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.provisionings') }}</span>
        </a>
      @endcan
      @can('gateways.view')
        <a href="{{ route('admin.gateways') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.gateways*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-credit-card class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.gateways') }}</span>
        </a>
      @endcan
      @can('modules.view')
        <a href="{{ route('admin.modules') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.modules*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-codesandbox class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.modules') }}</span>
        </a>
      @endcan
      <span class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.system') }}</span>
      <a href="{{ route('admin.settings') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.settings*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-settings class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.settings') }}</span>
      </a>
      @can('plugins.view')
        <a href="{{ route('admin.plugins') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.plugins*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
          <x-lucide-puzzle class="w-5 h-auto" />
          <span class="font-semibold">{{ __('admin/navigation.plugins') }}</span>
        </a>
      @endcan
      <a href="{{ route('admin.audits') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.audits*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-file-text class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.audits') }}</span>
      </a>
    </div>
  </div>
</nav>
<!-- Backdrop -->
<div id="backdrop" class="fixed inset-0 bg-black/25 z-99 xl:hidden opacity-0 pointer-events-none transition-opacity duration-300"></div>