@php
  $socials = ['discord', 'youtube', 'whatsapp', 'instagram', 'facebook', 'twitter', 'linkedin', 'github', 'reddit', 'skype', 'telegram'];
@endphp
<footer class="w-full bg-billmora-bg m-auto border-b-2 border-billmora-2">
    <div class="max-w-7xl py-12 px-4 flex flex-col gap-8 mx-auto">
        <div class="flex flex-col gap-4 lg:flex-row">
            <div class="lg:max-w-1/4">
                <div class="flex flex-col gap-4">
                    <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company logo" class="h-auto w-24">
                    <p class="text-slate-600 font-medium">{{ Billmora::getGeneral('company_description') }}</p>
                    <div class="flex flex-row gap-2 flex-wrap">
                        @foreach ($socials as $platform)
                            @if ($url = Billmora::getGeneral('social_' . $platform))
                                <a href="{{ $url }}" class="text-slate-500 hover:text-billmora-primary-500">
                                    <i class="fa-brands fa-{{ $platform }}"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-6 md:flex-row flex-1 justify-around">
                <div class="flex flex-col gap-3">
                    <h4 class="text-slate-600 font-bold">{{ __('portal.store') }}</h4>
                    <div class="grid gap-2">
                        @foreach ($catalogs as $catalog)
                            <a href="{{ route('client.store.catalog', ['catalog' => $catalog->slug]) }}" class="text-slate-600 hover:text-billmora-primary-500">
                                {{ $catalog->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <h4 class="text-slate-600 font-bold">{{ __('portal.support') }}</h4>
                    <div class="grid gap-2">
                        <a href="{{ route('client.tickets') }}" class="text-slate-600 hover:text-billmora-primary-500">
                            {{ __('portal.ticket') }}
                        </a>
                    </div>
                </div>
                @if (Billmora::getGeneral('term_tos') || Billmora::getGeneral('term_toc') || Billmora::getGeneral('term_privacy'))
                    <div class="h-fit grid gap-3">
                        <h4 class="text-slate-600 font-bold">{{ __('portal.company') }}</h4>
                        <div class="grid gap-2">
                            @if (Billmora::getGeneral('term_tos'))
                                <a href="{{ Billmora::getGeneral('term_tos_url') ?: route('portal.terms.service') }}" class="text-slate-600 hover:text-billmora-primary-500">
                                    {{ __('portal.terms_of_service') }}
                                </a>
                            @endif
                            @if (Billmora::getGeneral('term_toc'))
                                <a href="{{ Billmora::getGeneral('term_toc_url') ?: route('portal.terms.condition') }}" class="text-slate-600 hover:text-billmora-primary-500">
                                    {{ __('portal.terms_of_condition') }}
                                </a>
                            @endif
                            @if (Billmora::getGeneral('term_privacy'))
                                <a href="{{ Billmora::getGeneral('term_privacy_url') ?: route('portal.terms.privacy') }}" class="text-slate-600 hover:text-billmora-primary-500">
                                    {{ __('portal.terms_privacy') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="text-center">
            <span class="text-slate-500 font-semibold">© {{ date('Y') }} {{ Billmora::getGeneral('company_name') }} - Powered by <a href="https://billmora.com" target="_blank" class="text-billmora-primary-500 font-semibold">Billmora</a></span>
        </div>
    </div>
</footer>