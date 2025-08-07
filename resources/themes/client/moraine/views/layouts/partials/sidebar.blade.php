<nav id="sidebar" class="fixed z-10 xl:sticky top-0 left-0 xl:block shrink-0 p-5 xl:pr-0 w-[350px] sm:w-[400px] h-dvh -translate-x-full xl:translate-x-0 transition-transform duration-300 ease-in-out">
  <div class="bg-white flex flex-col w-full h-full border-2 border-billmora-2 rounded-2xl p-8 pr-0">
    <a href="#" class="relative flex gap-3 items-center mr-8">
      <img src="{{ Billmora::getGeneral('company_logo') }}" alt="billmora logo" class="w-auto h-11 rounded-lg">
      <h3 class="text-2xl font-extrabold uppercase text-billmora-primary">Billmora</h3>
      {{-- Sidebar close toggle --}}
      <div id="closeSidebar" role="button" class="absolute top-0 -right-13 xl:hidden bg-white hover:bg-billmora-primary border-2 border-billmora-2 text-slate-600 hover:text-white shadow p-2 rounded-full cursor-pointer transition">
        <x-lucide-x class="w-auto h-5" />
      </div>
    </a>
    <hr class="border-t-2 border-billmora-2 my-7 mr-8">
    <div class="space-y-2 overflow-y-auto pr-6" id="sidemenu">
      {{-- Sidebar content --}}
      <a href="{{ route('client.dashboard') }}" class="flex gap-2 items-center hover:bg-billmora-primary px-3 py-3 rounded-lg text-slate-600 hover:text-white transition-colors duration-300">
        <x-lucide-layout-grid class="w-5 h-auto" />
        <span class="font-semibold">{{ __('client/navigation.dashboard') }}</span>
      </a>
      {{-- <span class="mt-4 block text-slate-600 font-semibold text-md">{{ __('client/navigation.group.system') }}</span> --}}
    </div>
  </div>
</nav>
<!-- Backdrop -->
<div id="backdrop" class="fixed inset-0 bg-black/25 z-5 xl:hidden opacity-0 pointer-events-none transition-opacity duration-300"></div>