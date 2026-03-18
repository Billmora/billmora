<?php

namespace App\Providers;

use Billmora;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Discord\Provider as DiscordProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * 
     * Dynamically configures Socialite providers from database settings.
     */
    public function boot(): void
    {
        $this->app['events']->listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event) {
            $event->extendSocialite('discord', DiscordProvider::class);
        });

        $this->app->booted(function () {
            try {
                $providers = ['google', 'discord', 'github'];

                foreach ($providers as $provider) {
                    $clientId = Billmora::getAuth("oauth_{$provider}_client_id");
                    $clientSecret = Billmora::getAuth("oauth_{$provider}_client_secret");

                    if ($clientId && $clientSecret) {
                        config([
                            "services.{$provider}.client_id" => $clientId,
                            "services.{$provider}.client_secret" => $clientSecret,
                            "services.{$provider}.redirect" => url("/auth/oauth/{$provider}/callback"),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore errors during setup/migrations when DB isn't available
            }
        });
    }
}
