@php
  $socials = ['discord', 'youtube', 'whatsapp', 'instagram', 'facebook', 'twitter', 'linkedin', 'github', 'reddit', 'skype', 'telegram'];
@endphp
<div class="bg-billmora-2 w-full py-8 border-t-4 border-t-billmora-3">
  <div class="flex flex-col gap-8 xl:max-w-[87.5rem] mx-auto px-4 2xl:px-0">
    <div class="grid grid-rows-4 gap-4 md:grid-cols-2 md:grid-rows-none xl:grid-cols-4">
      <div class="flex flex-col gap-2">
        <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company logo" class="h-auto w-24">
        <p class="text-slate-700">{{ Billmora::getGeneral('company_description') }}</p>
        <div class="flex flex-row gap-2 flex-wrap">
          @foreach ($socials as $platform)
            @if ($url = Billmora::getGeneral('social_' . $platform))
              <x-portal::link href="{{ $url }}" target="_blank" variant="secondary" icon="tabler-brand-{{ $platform }}" class="p-2!"/>
            @endif
          @endforeach
        </div>
      </div>
      <div class="flex flex-col gap-1">
        <h4 class="text-slate-800 font-bold">Store</h4>
        <x-portal::link href="/">Minecraft Hosting</x-portal::link>
      </div>
      <div class="flex flex-col gap-1">
        <h4 class="text-slate-800 font-bold">Store</h4>
        <x-portal::link href="/">Minecraft Hosting</x-portal::link>
      </div>
      <div class="flex flex-col gap-1">
        <h4 class="text-slate-800 font-bold">Company</h4>
        <div>
          <x-portal::link href="/">Term of Service</x-portal::link>
          <x-portal::link href="/">Term of Condition</x-portal::link>
          <x-portal::link href="/">Privacy Policy</x-portal::link>
        </div>
      </div>
    </div>
    <div class="text-center">
      <span class="text-slate-600 font-semibold">Copyright © {{ date('Y') }} <a href="https://billmora.com" target="_blank" class="text-billmora-primary">Billmora</a> - All Rights Reserved</span>
    </div>
  </div>
</div>