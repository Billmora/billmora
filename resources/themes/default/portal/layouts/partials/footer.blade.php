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
                <a href="{{ Config::setting('term_tos_url') ?? '/terms-of-service' }}">{{ __('portal.tos') }}</a>
                <a href="{{ Config::setting('term_toc_url') ?? '/terms-of-condition' }}">{{ __('portal.toc') }}</a>
                <a href="{{ Config::setting('term_privacy_url') ?? '/terms-of-service' }}">{{ __('portal.privacy_policy') }}</a>
            </div>
            <div class="brand">
                <img src="{{ Config::setting('company_logo') ?? 'https://viidev.com/assets/img/logo/logo.png' }}">
                <p>{{ Config::setting('company_description') ?? 'Free and Open source Billing Management Operations & Recurring Automation' }}</p>
            </div>
        </div>
        <div class="copyright">
            <div class="social">
                @if (Config::setting('social_discord'))
                <a href="{{ Config::setting('social_discord') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-discord/>
                </a>
                @endif
                @if (Config::setting('social_youtube'))
                <a href="{{ Config::setting('social_youtube') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-youtube/>
                </a>
                @endif
                @if (Config::setting('social_whatsapp'))
                <a href="{{ Config::setting('social_whatsapp') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-whatsapp/>
                </a>
                @endif
                @if(Config::setting('social_instagram'))
                <a href="{{ Config::setting('social_instagram') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-instagram/>
                </a>
                @endif
                @if (Config::setting('social_facebook'))
                <a href="{{ Config::setting('social_facebook') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-facebook/>
                </a>
                @endif
                @if (Config::setting('social_twitter'))
                <a href="{{ Config::setting('social_twitter') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-twitter/>
                </a>
                @endif
                @if (Config::setting('social_linkedin'))
                <a href="{{ Config::setting('social_linkedin') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-linkedin/>
                </a>
                @endif
                @if (Config::setting('social_github'))
                <a href="{{ Config::setting('social_github') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-github/>
                </a>
                @endif
                @if (Config::setting('social_reddit'))
                <a href="{{ Config::setting('social_reddit') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-reddit/>
                </a>
                @endif
                @if (Config::setting('social_skype'))
                <a href="{{ Config::setting('social_skype') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-skype/>
                </a>
                @endif
                @if (Config::setting('social_telegram'))
                <a href="{{ Config::setting('social_telegram') }}" target="_blank" class="btn btn-secondary btn-square">
                    <x-tabler-brand-telegram/>
                </a>
                @endif
            </div>
            <span>Copyright © {{ date('Y') }} Billmora - All Rights Reserved</span>
        </div>
    </div>
</footer>