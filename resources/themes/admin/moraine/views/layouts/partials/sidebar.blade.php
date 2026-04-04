@inject('pluginManager', 'App\Services\PluginManager')
@php
    $pluginAdminMenus = $pluginManager->getNavigationAdmin();
@endphp

<nav id="sidebar"
    class="fixed z-100 xl:sticky top-0 left-0 xl:block shrink-0 p-5 xl:pr-0 w-[300px] xl:w-[320px] h-dvh -translate-x-full xl:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="bg-white flex flex-col w-full h-full border-2 border-billmora-2 rounded-2xl p-6">
        <a href="{{ route('admin.dashboard') }}" class="relative flex gap-3 items-center">
            <img src="{{ Billmora::getGeneral('company_logo') }}" alt="billmora logo" class="w-auto h-9 rounded-lg">
            <h3 class="text-xl font-extrabold uppercase text-billmora-primary-500">
                {{ Billmora::getGeneral('company_name') }}
            </h3>
        </a>
        {{-- Sidebar close toggle --}}
        <div id="closeSidebar" role="button"
            class="absolute top-14 right-0 xl:hidden bg-white hover:bg-billmora-primary-500 border-2 border-billmora-2 text-slate-600 hover:text-white shadow p-2 rounded-full cursor-pointer transition">
            <x-lucide-x class="w-auto h-5" />
        </div>
        <hr class="border-t-2 border-billmora-2 my-7">
        <div class="space-y-2 overflow-y-auto" id="sidemenu">
            {{-- Sidebar content --}}
            <a href="{{ route('admin.dashboard') }}"
                class="flex gap-2 items-center {{ request()->routeIs('admin.dashboard') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                <x-lucide-layout-grid class="w-5 h-auto" />
                <span class="font-semibold">{{ __('admin/navigation.dashboard') }}</span>
            </a>
            <span
                class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.management') }}</span>
            @can('users.view')
                <a href="{{ route('admin.users') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.users*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-users class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.users') }}</span>
                </a>
            @endcan
            @can('orders.view')
                <a href="{{ route('admin.orders') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.orders*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-shopping-bag class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.orders') }}</span>
                </a>
            @endcan
            @canany(['services.view', 'services.cancellations.view'])
            <a href="{{ route('admin.services') }}"
                class="flex gap-2 items-center {{ request()->routeIs('admin.services*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                <x-lucide-scan-text class="w-5 h-auto" />
                <span class="font-semibold">{{ __('admin/navigation.services') }}</span>
            </a>
            @endcan
            @can('invoices.view')
                <a href="{{ route('admin.invoices') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.invoices*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-receipt-text class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.invoices') }}</span>
                </a>
            @endcan
            @can('transactions.view')
                <a href="{{ route('admin.transactions') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.transactions*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-landmark class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.transactions') }}</span>
                </a>
            @endcan
            @can('broadcasts.view')
                <a href="{{ route('admin.broadcasts') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.broadcasts*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-radio class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.broadcasts') }}</span>
                </a>
            @endcan
            @can('tickets.view')
                <a href="{{ route('admin.tickets') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.tickets*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-ticket class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.tickets') }}</span>
                </a>
            @endcan
            <span
                class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.product') }}</span>
            @can('catalogs.view')
                <a href="{{ route('admin.catalogs') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.catalogs*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-box class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.catalogs') }}</span>
                </a>
            @endcan
            @can('packages.view')
                <a href="{{ route('admin.packages') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.packages*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-package class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.packages') }}</span>
                </a>
            @endcan
            @can('variants.view')
                <a href="{{ route('admin.variants') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.variants*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-boxes class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.variants') }}</span>
                </a>
            @endcan
            @can('coupons.view')
                <a href="{{ route('admin.coupons') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.coupons*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-tags class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.coupons') }}</span>
                </a>
            @endcan
            @if(!empty($pluginAdminMenus))
                @foreach($pluginAdminMenus as $groupTitle => $menuItems)
                    <span class="mt-4 block text-slate-600 font-semibold text-md">{{ $groupTitle }}</span>
                    @foreach($menuItems as $menu)
                        <a href="{{ $menu['route'] }}"
                            class="flex gap-2 items-center {{ Str::startsWith(request()->url(), $menu['route']) ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
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
            <span
                class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.system') }}</span>
            <a href="{{ route('admin.settings') }}"
                class="flex gap-2 items-center {{ request()->routeIs('admin.settings*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                <x-lucide-settings class="w-5 h-auto" />
                <span class="font-semibold">{{ __('admin/navigation.settings') }}</span>
            </a>
            @can('plugins.view')
                <a href="{{ route('admin.plugins') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.plugins*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-puzzle class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.plugins') }}</span>
                </a>
            @endcan
            @can('themes.view')
                <a href="{{ route('admin.themes') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.themes*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-paintbrush class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.themes') }}</span>
                </a>
            @endcan
            @can('automations.view')
                <a href="{{ route('admin.automations') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.automations*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-clock class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.automations') }}</span>
                </a>
            @endcan
            @can('tasks.view')
                <a href="{{ route('admin.tasks') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.tasks*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-clipboard-list class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.tasks') }}</span>
                </a>
            @endcan
            <a href="{{ route('admin.audits') }}"
                class="flex gap-2 items-center {{ request()->routeIs('admin.audits*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                <x-lucide-file-text class="w-5 h-auto" />
                <span class="font-semibold">{{ __('admin/navigation.audits') }}</span>
            </a>
            @can('health.view')
                <a href="{{ route('admin.health') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.health*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-activity class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.health') }}</span>
                </a>
            @endcan
            <span
                class="mt-4 block text-slate-600 font-semibold text-md">{{ __('admin/navigation.group.plugin') }}</span>
            @can('provisionings.view')
                <a href="{{ route('admin.provisionings') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.provisionings*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-plug class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.provisionings') }}</span>
                </a>
            @endcan
            @can('gateways.view')
                <a href="{{ route('admin.gateways') }}"
                    class="flex gap-2 items-center {{ request()->routeIs('admin.gateways*') ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-credit-card class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.gateways') }}</span>
                </a>
            @endcan
            @can('modules.view')
                <a href="{{ route('admin.modules') }}"
                    class="flex gap-2 items-center {{ request()->routeIs(['admin.modules', 'admin.modules.create', 'admin.modules.edit']) ? 'bg-billmora-primary-500 text-white' : 'hover:bg-billmora-primary-500' }} px-2.5 py-2.5 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
                    <x-lucide-codesandbox class="w-5 h-auto" />
                    <span class="font-semibold">{{ __('admin/navigation.modules') }}</span>
                </a>
            @endcan
        </div>
        <div class="w-full grid grid-cols-2 gap-4 mt-auto pt-4">
            <a href="https://billmora.com/docs/introduction" target="_blank"
                class="flex justify-center gap-1 items-center bg-billmora-2 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-book class="w-auto h-5" />
                Docs
            </a>
            <a href="https://github.com/sponsors/Billmora" target="_blank"
                class="flex justify-center gap-1 items-center bg-billmora-2 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-heart-handshake class="w-auto h-5" />
                Sponsor
            </a>
        </div>
    </div>
</nav>
<!-- Backdrop -->
<div id="backdrop"
    class="fixed inset-0 bg-black/25 z-99 xl:hidden opacity-0 pointer-events-none transition-opacity duration-300">
</div>