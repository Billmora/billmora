<header class="sticky top-5 right-0 flex justify-between items-center w-full bg-white p-4 border-2 border-billmora-2 rounded-2xl">
  <!-- Toggle Sidebar -->
  <button id="toggleSidebar" class="block xl:hidden bg-billmora-1 hover:bg-billmora-primary p-2.5 mr-4 text-slate-600 hover:text-white rounded-full transition-colors duration-300">
    <x-lucide-menu class="w-auto h-5" />
  </button>

  <!-- Search (DESKTOP) -->
  <div class="hidden md:block relative mr-auto group">
    <x-lucide-search class="w-auto h-5 absolute top-1/2 left-2 -translate-y-1/2 pointer-events-none text-slate-400 group-focus-within:text-billmora-primary transition-colors duration-150" />
    <input type="text" placeholder="Search something..." class="bg-billmora-1 px-2 py-1.5 pl-9 placeholder:text-slate-500 outline-none ring-billmora-primary group-focus-within:ring-2 rounded-lg transition-all" />
  </div>

  <!-- Search (MOBILE) -->
  <button class="block md:hidden bg-billmora-1 hover:bg-billmora-primary p-2.5 mr-auto text-slate-600 hover:text-white rounded-full transition-colors duration-300">
    <x-lucide-search class="w-auto h-5" />
  </button>

  <!-- Language -->
  <button class="flex gap-2 items-center bg-billmora-1 hover:bg-billmora-primary p-2 rounded-lg transition-colors duration-300 group">
    <x-flag-country-uk class="w-auto h-5 pointer-events-none" />
    <span class="font-semibold text-slate-600 group-hover:text-white">English</span>
  </button>

  <!-- Profile -->
  <button class="cursor-pointer ml-4">
    <img src="https://media.billmora.com/logo/main-invert-bgwhite-small.png" alt="billmora profile" class="w-10 h-10 rounded-full">
  </button>
</header>