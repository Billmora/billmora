<header>
    <div class="container">
        <div class="navbar-toggle toggle-menu">
            <x-tabler-category />
        </div>
        <a href="/" class="logo">
            <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company_logo">
        </a>
        <div class="navbar-toggle toggle-action">
            <x-tabler-dots />
        </div>
        <div class="action">
            <button class="btn btn-secondary btn-square preference" id="modal-open" modal-data="modal_preference">
                <div class="language">
                    <x-tabler-language />
                    <span>{{ $langActive }}</span>
                </div>
                <span class="currency">(USD)</span>
            </button>
            <div class="divider-y"></div>
            @auth
                <div class="my-account">
                    <button id="dropdown-open" dropdown-data="dropdownAccountA">
                        <img src="{{ auth()->user()->avatar }}">
                    </button>
                    <x-client.dropdowns.account dropdown_data="dropdownAccountA" />
                </div>
            @else
                <a href="/auth/login" class="btn btn-secondary">{{ __('auth.sign_in') }}</a>
                <a href="/auth/register" class="btn btn-primary">{{ __('auth.sign_up') }}</a>
            @endauth
        </div>
    </div>
</header>
<nav>
    <div class="container">
        <div class="menu">
            <a href="/dashboard" class="btn nav-btn {{ request()->is('dashboard') ? 'active' : '' }}">
                <x-tabler-home/>
                {{ __('client.dashboard') }}
            </a>
            <a href="/store" class="btn nav-btn {{ request()->is('store*') ? 'active' : '' }}">
                <x-tabler-building-store />
                {{ __('client.store') }}
            </a>
            <a href="/news" class="btn nav-btn {{ request()->is('news*') ? 'active' : '' }}">
                <x-tabler-news/>
                {{ __('client.news') }}
            </a>
            @if (Billmora::getGeneral('term_tos'))
                @if (Billmora::getGeneral('term_tos_url'))
                    <a href="{{ Billmora::getGeneral('term_tos_url') }}" target="_blank" class="btn nav-btn">
                        <x-tabler-circle-dashed-check/>
                        {{ __('client.tos') }}
                    </a>
                @else
                    <a href="/terms-of-service" class="btn nav-btn {{ request()->is('/terms-of-service*') ? 'active' : '' }}">
                        <x-tabler-circle-dashed-check/>
                        {{ __('client.tos') }}
                    </a>
                @endif
            @endif
        </div>
    </div>
</nav>
<div class="nav-action">
    <div class="action">
        <button class="btn btn-secondary preference" id="modal-open" modal-data="modal_preference">
            <div class="language">
                <x-tabler-language />
                <span>{{ $langActive }}</span>
            </div>
            <span class="currency">(USD)</span>
        </button>
        <div class="divider-y"></div>
        @auth
            <div class="my-account">
                <button id="dropdown-open" dropdown-data="dropdownAccountB">
                    <img src="{{ auth()->user()->avatar }}">
                </button>
                <x-client.dropdowns.account dropdown_data="dropdownAccountB" />
            </div>
        @else
            <a href="/auth/login" class="btn btn-secondary">{{ __('auth.sign_in') }}</a>
            <a href="/auth/register" class="btn btn-primary">{{ __('auth.sign_up') }}</a>
        @endauth
    </div>
</div>
<div class="nav-menu">
    <div class="menu">
        <a href="/" class="btn nav-btn {{ request()->is('dashboard') ? 'active' : '' }}">
            <x-tabler-home/>
            {{ __('client.dashboard') }}
        </a>
        <a href="/store" class="btn nav-btn {{ request()->is('store*') ? 'active' : '' }}">
            <x-tabler-building-store />
            {{ __('client.store') }}
        </a>
        <a href="/news" class="btn nav-btn {{ request()->is('news*') ? 'active' : '' }}">
            <x-tabler-news/>
            {{ __('client.news') }}
        </a>
        @if (Billmora::getGeneral('term_tos'))
            @if (Billmora::getGeneral('term_tos_url'))
                <a href="{{ Billmora::getGeneral('term_tos_url') }}" target="_blank" class="btn nav-btn">
                    <x-tabler-circle-dashed-check/>
                    {{ __('client.tos') }}
                </a>
            @else
                <a href="/terms-of-service" class="btn nav-btn {{ request()->is('/terms-of-service*') ? 'active' : '' }}">
                    <x-tabler-circle-dashed-check/>
                    {{ __('client.tos') }}
                </a>
            @endif
        @endif
    </div>
</div>
<x-client.modals.preference />