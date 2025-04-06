<footer>
    <div class="container">
        <div class="detail">
            <div class="services">
                <h4>{{ __('portal.store') }}</h4>
                <a href="/store">Minecraft Hosting</a>
            </div>
            <div class="support">
                <h4>{{ __('portal.support') }}</h4>
                <a href="/client/ticket" target="_blank">{{ __('portal.ticket') }}</a>
            </div>
            <div class="company">
                <h4>{{ __('portal.company') }}</h4>
                <a href="{{ Billmora::getGeneral('term_tos_url') ?? '/terms-of-service' }}">{{ __('portal.tos') }}</a>
                <a href="{{ Billmora::getGeneral('term_toc_url') ?? '/terms-of-condition' }}">{{ __('portal.toc') }}</a>
                <a href="{{ Billmora::getGeneral('term_privacy_url') ?? '/terms-of-service' }}">{{ __('portal.privacy_policy') }}</a>
            </div>
            <div class="brand">
                <img src="{{ Billmora::getGeneral('company_logo') ?? 'https://viidev.com/assets/img/logo/logo.png' }}">
                <p>{{ Billmora::getGeneral('company_description') ?? 'Free and Open source Billing Management Operations & Recurring Automation' }}</p>
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