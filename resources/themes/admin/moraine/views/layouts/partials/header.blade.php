<header class="sticky top-5 right-0 flex justify-between items-center w-full bg-white p-4 border-2 border-billmora-2 rounded-2xl">
  <!-- Toggle Sidebar -->
  <button id="toggleSidebar" class="block xl:hidden bg-billmora-1 hover:bg-billmora-primary p-2.5 mr-4 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer">
    <x-lucide-menu class="w-auto h-5" />
  </button>

  <!-- Search (DESKTOP) -->
  <div class="hidden md:block relative mr-auto group">
    <x-lucide-search class="w-auto h-5 absolute top-1/2 left-2 -translate-y-1/2 pointer-events-none text-slate-400 group-focus-within:text-billmora-primary transition-colors duration-150" />
    <input type="text" placeholder="Search something..." class="bg-billmora-1 px-2 py-2 pl-9 placeholder:text-slate-500 outline-none ring-billmora-primary group-focus-within:ring-2 rounded-lg transition-all" />
  </div>

  <!-- Search (MOBILE) -->
  <button class="block md:hidden bg-billmora-1 hover:bg-billmora-primary p-2.5 mr-auto text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer">
    <x-lucide-search class="w-auto h-5" />
  </button>

  <!-- Language -->
  <button class="flex gap-2 items-center bg-billmora-1 hover:bg-billmora-primary p-2 rounded-lg transition-colors duration-300 group cursor-pointer">
    <x-flag-country-uk class="w-auto h-5 pointer-events-none" />
    <span class="font-semibold text-slate-600 group-hover:text-white">English</span>
  </button>

  <!-- Profile -->
  <div class="relative w-fit"
      x-data="{ isOpen: false, openedWithKeyboard: false }">
    <!-- Toggle Button -->
    <button type="button" class="cursor-pointer ml-4"
        x-on:click="isOpen = ! isOpen" 
        aria-haspopup="true">
      <img src="https://media.billmora.com/logo/main-invert-bgwhite-small.png" alt="billmora profile" class="w-10 h-10 rounded-full">
    </button>
    <!-- Dropdown Menu -->
    <div class="absolute top-16 right-0 flex w-[300px] flex-col gap-2 bg-white p-4 border-2 border-billmora-2 rounded-2xl" role="menu"
        x-cloak x-show="isOpen || openedWithKeyboard"
        x-transition x-trap="openedWithKeyboard"
        x-on:click.outside="isOpen = false, openedWithKeyboard = false">
      {{-- Dropdown Content --}}
      <div class="flex flex-col gap-2">
        <span class="text-xl text-slate-600 font-bold">Mafly</span>
        <span class="text-lg text-slate-500 font-semibold">Administrator</span>
      </div>
      <hr class="border-t-2 border-billmora-2 mt-2 mb-4">
      <a href="#" class="flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-layers-2 class="w-5 h-auto" />
        <span class="font-semibold">Portal Area</span>
      </a>
      <a href="#" class="flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300" role="menuitem">
        <x-lucide-copy class="w-5 h-auto" />
        <span class="font-semibold">Client Area</span>
      </a>
      <hr class="border-t-2 border-billmora-2 mt-4 mb-2">
      <button class="flex gap-2 items-center hover:bg-red-400 px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300 cursor-pointer" role="menuitem">
        <x-lucide-log-out class="w-5 h-auto" />
        <span class="font-semibold">Sign Out</span>
      </button>
    </div>
</div>
</header>