<nav id="sidebar" class="fixed z-100 xl:sticky top-0 left-0 xl:block shrink-0 p-5 xl:pr-0 w-[350px] sm:w-[400px] h-dvh -translate-x-full xl:translate-x-0 transition-transform duration-300 ease-in-out">
  <div class="bg-white flex flex-col w-full h-full border-2 border-billmora-2 rounded-2xl p-8">
    <a href="#" class="relative flex gap-3 items-center">
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
      <a href="{{ route('admin.users') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.users*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-users class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.users') }}</span>
      </a>
      <a href="{{ route('admin.invoices') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.invoices*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-receipt-text class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.invoices') }}</span>
      </a>
      <a href="{{ route('admin.orders') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.orders*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-shopping-bag class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.orders') }}</span>
      </a>
      <span class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.product') }}</span>
      <a href="{{ route('admin.catalogs') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.catalogs*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-box class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.catalogs') }}</span>
      </a>
      <a href="{{ route('admin.packages') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.packages*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-package class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.packages') }}</span>
      </a>
      <a href="{{ route('admin.variants') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.variants*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-boxes class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.variants') }}</span>
      </a>
      <a href="{{ route('admin.coupons') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.coupons*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-tags class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.coupons') }}</span>
      </a>
      <span class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.system') }}</span>
      <a href="{{ route('admin.settings') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.settings*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-settings class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.settings') }}</span>
      </a>
      <a href="{{ route('admin.audits') }}" class="flex gap-2 items-center {{ request()->routeIs('admin.audits*') ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary' }} px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-file-text class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/navigation.audits') }}</span>
      </a>
    </div>
  </div>
</nav>
<!-- Backdrop -->
<div id="backdrop" class="fixed inset-0 bg-black/25 z-99 xl:hidden opacity-0 pointer-events-none transition-opacity duration-300"></div>