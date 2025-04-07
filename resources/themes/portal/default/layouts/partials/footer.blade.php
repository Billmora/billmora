<footer>
    <div class="container">
        <div class="detail">
            <div class="services">
                <h4>{{ __('portal.store') }}</h4>
                <a href="/store">Minecraft Hosting</a>
            </div>
            <div class="support">
                <h4>{{ __('portal.support') }}</h4>
                <a href="/ticket" target="_blank">{{ __('portal.ticket') }}</a>
            </div>
            <div class="company">
                @if (Billmora::getGeneral('term_tos') || Billmora::getGeneral('term_toc') || Billmora::getGeneral('term_privacy'))
                    <h4>{{ __('portal.company') }}</h4>
                @endif
                @if (Billmora::getGeneral('term_tos'))
                    @if (Billmora::getGeneral('term_tos_url'))
                        <a href="{{ Billmora::getGeneral('term_tos_url') }}" target="_blank">{{ __('portal.tos') }}</a>
                    @else
                        <a href="/terms-of-service">{{ __('portal.tos') }}</a>
                    @endif
                @endif
                @if (Billmora::getGeneral('term_toc'))
                    @if (Billmora::getGeneral('term_toc_url'))
                        <a href="{{ Billmora::getGeneral('term_toc_url') }}" target="_blank">{{ __('portal.toc') }}</a>
                    @else
                        <a href="/terms-of-condition">{{ __('portal.toc') }}</a>
                    @endif
                @endif
                @if (Billmora::getGeneral('term_privacy'))
                    @if (Billmora::getGeneral('term_privacy_url'))
                        <a href="{{ Billmora::getGeneral('term_privacy_url') }}" target="_blank">{{ __('portal.privacy_policy') }}</a>
                    @else
                        <a href="/privacy-policy">{{ __('portal.privacy_policy') }}</a>
                    @endif
                @endif
            </div>
            <div class="brand">
                <img src="{{ Billmora::getGeneral('company_logo') }}">
                <p>{{ Billmora::getGeneral('company_description') }}</p>
            </div>
        </div>
        <div class="copyright">
            <div class="social">
                @if (Billmora::getGeneral('social_discord'))
                <a href="{{ Billmora::getGeneral('social_discord') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-discord/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_youtube'))
                <a href="{{ Billmora::getGeneral('social_youtube') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-youtube/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_whatsapp'))
                <a href="{{ Billmora::getGeneral('social_whatsapp') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-whatsapp/>
                </a>
                @endif
                @if(Billmora::getGeneral('social_instagram'))
                <a href="{{ Billmora::getGeneral('social_instagram') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-instagram/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_facebook'))
                <a href="{{ Billmora::getGeneral('social_facebook') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-facebook/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_twitter'))
                <a href="{{ Billmora::getGeneral('social_twitter') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-twitter/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_linkedin'))
                <a href="{{ Billmora::getGeneral('social_linkedin') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-linkedin/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_github'))
                <a href="{{ Billmora::getGeneral('social_github') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-github/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_reddit'))
                <a href="{{ Billmora::getGeneral('social_reddit') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-reddit/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_skype'))
                <a href="{{ Billmora::getGeneral('social_skype') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-skype/>
                </a>
                @endif
                @if (Billmora::getGeneral('social_telegram'))
                <a href="{{ Billmora::getGeneral('social_telegram') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-telegram/>
                </a>
                @endif
            </div>
            <span>Copyright © {{ date('Y') }} Billmora - All Rights Reserved</span>
        </div>
    </div>
</footer>