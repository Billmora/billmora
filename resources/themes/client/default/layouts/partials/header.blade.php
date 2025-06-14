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
            <button class="btn btn-secondary btn-square preference" id="modal-open" modal-data="modalPreference">
                <div class="language">
                    <x-tabler-language />
                    <span>{{ $langActive }}</span>
                </div>
                <span class="currency">(USD)</span>
            </button>
            <div class="divider-y"></div>
            @auth
                <div class="account">
                    <button id="dropdown-open" dropdown-data="dropdownAccountA">
                        <img src="{{ auth()->user()->avatar }}">
                    </button>
                    <div class="dropdown" id="dropdownAccountA">
                        <div class="card">
                            <h3>{{ auth()->user()->name }}</h3>
                            <div class="divider-x"></div>
                            <a href="/user/account">Account</a>
                            <a href="/user/security">Security</a>
                            <a href="/user/emails">Email History</a>
                            <div class="divider-x"></div>
                            @if (auth()->user()->is_admin)
                                <a href="/admin">Admin</a>
                            @endif
                            <form action="{{ route('client.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-logout">
                                    {{ __('auth.sign_out') }}
                                </button>
                            </form>
                        </div>
                    </div>
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
        <button class="btn btn-secondary preference" id="modal-open" modal-data="modalPreference">
            <div class="language">
                <x-tabler-language />
                <span>{{ $langActive }}</span>
            </div>
            <span class="currency">(USD)</span>
        </button>
        <div class="divider-y"></div>
        @auth
            <div class="account">
                <button id="dropdown-open" dropdown-data="dropdownAccountB">
                    <img src="{{ auth()->user()->avatar }}">
                </button>
                <div class="dropdown" id="dropdownAccountB">
                    <div class="card">
                        <h3>{{ auth()->user()->name }}</h3>
                        <div class="divider-x"></div>
                        <a href="/user/account">Account</a>
                        <a href="/user/security">Security</a>
                        <a href="/user/emails">Email History</a>
                        <div class="divider-x"></div>
                        @if (auth()->user()->is_admin)
                            <a href="/admin">Admin</a>
                        @endif
                        <form action="{{ route('client.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-logout">
                                {{ __('auth.sign_out') }}
                            </button>
                        </form>
                    </div>
                </div>
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
<form action="{{ route('preference.update') }}" method="POST">
    @csrf
    <div class="modal" id="modalPreference">
        <div class="card">
            <div class="header">
                <h2>{{ __('client.modal_preference') }}</h2>
                <button class="btn btn-secondary btn-square" id="modal-close">
                    <x-tabler-x/>
                </button>
            </div>
            <div class="body">
                <div class="form-group">
                    <label for="language">{{ __('client.language') }}</label>
                    <select name="language" id="language">
                        @foreach ($langs as $lang => $name)
                            <option value="{{ $lang }}" {{ session('locale', config('app.locale')) == $lang ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="footer">
                <button type="button" class="btn btn-secondary" id="modal-close">{{ __('client.modal_cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('client.modal_save') }}</button>
            </div>
        </div>
    </div>
</form>