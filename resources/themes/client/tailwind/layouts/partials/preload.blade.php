<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 800)" x-show="show" class="fixed top-0 left-0 w-full h-full bg-billmora-1 z-50 flex items-center justify-center"
  x-transition:leave="transition-opacity ease-in duration-300"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0">
  <div class="flex-col gap-4 w-full flex items-center justify-center">
    <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company logo" class="h-16 w-auto absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 animate-none">
    <div class="w-30 h-30 border-4 border-transparent text-white text-4xl animate-spin flex items-center justify-center border-t-billmora-primary rounded-full">
      <div class="w-26 h-26 border-4 border-transparent text-billmora-primary  text-2xl animate-spin flex items-center justify-center border-t-billmora-primary  rounded-full">
      </div>
    </div>
  </div>
</div>