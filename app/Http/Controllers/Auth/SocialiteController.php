<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AuditsUser;
use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    use AuditsUser;

    /**
     * Supported OAuth providers.
     */
    protected array $providers = ['google', 'discord', 'github'];

    /**
     * Redirect the user to the OAuth provider's authentication page.
     *
     * @param string $provider The OAuth provider name.
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(string $provider)
    {
        if (!$this->isValidProvider($provider)) {
            return redirect()->route('client.login')->with('error', __('auth.oauth.invalid_provider'));
        }

        if (!$this->isProviderEnabled($provider)) {
            return redirect()->route('client.login')->with('error', __('auth.oauth.provider_disabled'));
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the OAuth provider.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $provider The OAuth provider name.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, string $provider)
    {
        if (!$this->isValidProvider($provider) || !$this->isProviderEnabled($provider)) {
            return redirect()->route('client.login')->with('error', __('auth.oauth.invalid_provider'));
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('client.login')->with('error', __('auth.oauth.login_failed', ['provider' => ucfirst($provider)]));
        }

        $user = User::where('oauth_provider', $provider)
            ->where('oauth_provider_id', $socialUser->getId())
            ->first();

        if (!$user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'oauth_provider' => $provider,
                    'oauth_provider_id' => $socialUser->getId(),
                ]);
            }
        }

        if (!$user) {
            $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
            $nameParts = explode(' ', $name, 2);

            $user = User::create([
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $socialUser->getEmail(),
                'password' => null,
                'oauth_provider' => $provider,
                'oauth_provider_id' => $socialUser->getId(),
                'email_verified_at' => now(),
            ]);

            $user->billing()->create([]);
        }

        if ($user->status !== 'active') {
            return redirect()->route('client.login')->with('error', __('auth.oauth.account_inactive'));
        }

        Auth::login($user, true);

        $request->session()->regenerate();

        if ($user->twoFactor?->isActive()) {
            session()->forget('2fa_passed');
            return redirect()->route('client.two-factor.verify');
        }

        $this->recordActivity('account.login', ['method' => 'oauth', 'provider' => $provider], $request);

        return redirect()->intended(route('client.dashboard'));
    }

    /**
     * Check if the given provider is a valid OAuth provider.
     *
     * @param string $provider
     * @return bool
     */
    private function isValidProvider(string $provider): bool
    {
        return in_array($provider, $this->providers);
    }

    /**
     * Check if the given provider is enabled in settings.
     *
     * @param string $provider
     * @return bool
     */
    private function isProviderEnabled(string $provider): bool
    {
        return (bool) Billmora::getAuth("oauth_{$provider}_enabled");
    }
}
