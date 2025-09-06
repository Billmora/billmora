<header class="sticky top-5 right-0 flex justify-between items-center w-full bg-white p-4 border-2 border-billmora-2 rounded-2xl">
  <!-- Toggle Sidebar -->
  <button id="toggleSidebar" class="block xl:hidden bg-billmora-1 hover:bg-billmora-primary p-2.5 mr-4 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer">
    <x-lucide-menu class="w-auto h-5" />
  </button>

  <!-- Language -->
  <div class="relative w-fit ml-auto"
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
      <img src="{{ auth()->user()->avatar }}" alt="user profile" class="w-10 h-10 rounded-full">
    </button>
    <!-- Dropdown Menu -->
    <div class="absolute top-16 right-0 flex w-[300px] flex-col gap-2 bg-white p-4 border-2 border-billmora-2 rounded-2xl" role="menu"
        x-cloak x-show="isOpen || openedWithKeyboard"
        x-transition
        x-on:click.outside="isOpen = false, openedWithKeyboard = false">
      {{-- Dropdown Content --}}
      <div class="flex flex-col">
        <span class="text-xl text-slate-600 font-bold">{{ auth()->user()->fullname }}</span>
        <span class="text-md text-slate-500 font-semibold">{{ auth()->user()->email }}</span>
      </div>
      <hr class="border-t-2 border-billmora-2 my-2">
      <a href="#" class="flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-layers-2 class="w-5 h-auto" />
        <span class="font-semibold">{{ __('client/common.portal_area') }}</span>
      </a>
      <a href="{{ route('admin.dashboard') }}" class="flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-shield class="w-5 h-auto" />
        <span class="font-semibold">{{ __('client/common.admin_area') }}</span>
      </a>
      <hr class="border-t-2 border-billmora-2 my-2">
      <form action="{{ route('client.logout.store') }}" method="POST">
        @csrf
        <button class="w-full flex gap-2 items-center hover:bg-red-400 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300 cursor-pointer" role="menuitem">
          <x-lucide-log-out class="w-5 h-auto" />
          <span class="font-semibold">{{ __('client/common.sign_out') }}</span>
        </button>
      </form>
    </div>
</div>
</header>