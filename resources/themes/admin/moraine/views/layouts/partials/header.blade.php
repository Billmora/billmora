<header class="sticky z-90 top-5 right-0 flex justify-between items-center w-full bg-white p-4 border-2 border-billmora-2 rounded-2xl">
  <!-- Toggle Sidebar -->
  <button id="toggleSidebar" class="block xl:hidden bg-billmora-1 hover:bg-billmora-primary p-2.5 mr-4 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer">
    <x-lucide-menu class="w-auto h-5" />
  </button>

  <!-- Quick Search (DESKTOP) -->
  <div class="hidden md:block w-[400px] mr-auto">
    <button type="button" id="quickSearch" class="flex gap-2 items-center w-full bg-billmora-1 px-2 py-2 text-slate-500 text-start outline-none ring-billmora-primary hover:ring-2 rounded-lg transition-all cursor-pointer group">
      <x-lucide-search class="w-auto h-5 pointer-events-none group-hover:text-billmora-primary transition-colors duration-150" />
      <span class="text-slate-400">{{ __('admin/common.quick_search') }}</span>
      <div class="flex gap-2 ml-auto pointer-events-none text-slate-400 group-hover:text-billmora-primary transition-colors duration-150">
        <span class="bg-white px-1 py-0.25 text-sm font-semibold rounded-lg">CTRL</span>
        <span class="bg-white px-1 py-0.25 text-sm font-semibold rounded-lg">K</span>
      </div>
    </button>
  </div>

  <!-- Quick Search (MOBILE) -->
  <button type="button" id="quickSearch" class="block md:hidden bg-billmora-1 hover:bg-billmora-primary p-2.5 mr-auto text-slate-600 hover:text-white rounded-full transition-colors duration-150 cursor-pointer">
    <x-lucide-search class="w-auto h-5 pointer-events-none" />
  </button>

  <!-- Language -->
  <div class="relative w-fit"
      x-data="{ isOpen: false, openedWithKeyboard: false }">
    {{-- Language Toggle Button --}}
    <button type="button" class="flex gap-2 items-center bg-billmora-1 hover:bg-billmora-primary p-2 rounded-lg transition-colors duration-300 group cursor-pointer"
        x-on:click="isOpen = ! isOpen" 
        aria-haspopup="true">
      <x-dynamic-component component="flag-country-{{ strtolower($langActive['country']) }}" class="w-auto h-5 pointer-events-none" />
      <span class="font-semibold text-slate-600 group-hover:text-white">{{ $langActive['name'] }}</span>
    </button>
    {{-- Language Dropdown Menu --}}
    <div class="absolute top-16 right-0 flex w-[300px] max-h-[500px] flex-col gap-2 bg-white p-4 border-2 border-billmora-2 rounded-2xl overflow-y-auto " role="menu"
        x-cloak x-show="isOpen || openedWithKeyboard"
        x-transition
        x-on:click.outside="isOpen = false, openedWithKeyboard = false">
      {{-- Language Dropdown Content --}}
      @foreach ($langs as $lang)
        <a href="{{ route('common.language.update', ['lang' => $lang['lang']]) }}" class="w-full flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300 cursor-pointer">
          <x-dynamic-component component="flag-country-{{ strtolower($lang['country']) }}" class="w-auto h-5 pointer-events-none" />
          <span class="font-semibold">{{ $lang['name'] }}</span>
        </a>
      @endforeach
    </div>
  </div>

  <!-- Profile -->
  <div class="relative w-fit flex items-center"
      x-data="{ isOpen: false, openedWithKeyboard: false }">
    <!-- Toggle Button -->
    <button type="button" class="cursor-pointer ml-4"
        x-on:click="isOpen = ! isOpen" 
        aria-haspopup="true">
      <img src="{{ Billmora::getGeneral('company_logo') }}" alt="billmora profile" class="w-10 h-10 rounded-full">
    </button>
    <!-- Dropdown Menu -->
    <div class="absolute top-16 right-0 flex w-[300px] flex-col gap-2 bg-white p-4 border-2 border-billmora-2 rounded-2xl" role="menu"
        x-cloak x-show="isOpen || openedWithKeyboard"
        x-transition
        x-on:click.outside="isOpen = false, openedWithKeyboard = false">
      {{-- Dropdown Content --}}
      <div class="flex flex-col gap-2">
        <span class="text-xl text-slate-600 font-bold">Mafly</span>
        <span class="text-lg text-slate-500 font-semibold">Administrator</span>
      </div>
      <hr class="border-t-2 border-billmora-2 mt-2 mb-4">
      <a href="#" class="flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-layers-2 class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/common.portal_area') }}</span>
      </a>
      <a href="{{ route('client.dashboard') }}" class="flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-copy class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/common.client_area') }}</span>
      </a>
      <hr class="border-t-2 border-billmora-2 mt-4 mb-2">
      <button class="flex gap-2 items-center hover:bg-red-400 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300 cursor-pointer" role="menuitem">
        <x-lucide-log-out class="w-5 h-auto" />
        <span class="font-semibold">{{ __('admin/common.sign_out') }}</span>
      </button>
    </div>
  </div>
</header>