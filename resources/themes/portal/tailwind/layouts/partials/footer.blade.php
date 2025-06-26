@php
  $socials = ['discord', 'youtube', 'whatsapp', 'instagram', 'facebook', 'twitter', 'linkedin', 'github', 'reddit', 'skype', 'telegram'];
@endphp
<footer class="bg-billmora-2 w-full py-8 border-t-4 border-t-billmora-3">
  <div class="flex flex-col gap-8 xl:max-w-[87.5rem] mx-auto px-4 2xl:px-0">
    <div class="flex flex-col gap-4 lg:flex-row">
      <div class="lg:max-w-1/4">
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
      </div>
      <div class="flex flex-col gap-2 md:flex-row flex-1 justify-around">
        <div class="flex flex-col gap-1">
          <h4 class="text-slate-800 font-bold">{{ __('common.support') }}</h4>
          <x-portal::link href="/">Ticket</x-portal::link>
        </div>
        <div class="flex flex-col gap-1">
          <h4 class="text-slate-800 font-bold">Store</h4>
          <x-portal::link href="/">Minecraft Hosting</x-portal::link>
        </div>
        @if (Billmora::getGeneral('term_tos') || Billmora::getGeneral('term_toc') || Billmora::getGeneral('term_privacy'))
          <div class="flex flex-col gap-1">
            <h4 class="text-slate-800 font-bold">{{ __('common.company') }}</h4>
            <div>
              @if (Billmora::getGeneral('term_tos'))
              <x-portal::link href="{{ Billmora::getGeneral('term_tos_url') ? Billmora::getGeneral('term_tos_url') : '/terms-of-service' }}">
                <span>{{ __('common.terms_of_service') }}</span>
              </x-portal::link>
              @endif
              @if (Billmora::getGeneral('term_toc'))
              <x-portal::link href="{{ Billmora::getGeneral('term_toc_url') ? Billmora::getGeneral('term_toc_url') : '/terms-of-condition' }}">
                <span>{{ __('common.terms_of_condition') }}</span>
              </x-portal::link>
              @endif
              @if (Billmora::getGeneral('term_privacy'))
              <x-portal::link href="{{ Billmora::getGeneral('term_privacy_url') ? Billmora::getGeneral('term_privacy_url') : '/privacy-policy' }}">
                <span>{{ __('common.privacy_policy') }}</span>
              </x-portal::link>
              @endif
            </div>
          </div>
        @endif
      </div>
    </div>
    <div class="text-center">
      <span class="text-slate-600 font-semibold">Copyright © {{ date('Y') }} <a href="https://billmora.com" target="_blank" class="text-billmora-primary">Billmora</a> - All Rights Reserved</span>
    </div>
  </div>
</footer>