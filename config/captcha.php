<?php

/*
|--------------------------------------------------------------------------
| Captcha Configuration
|--------------------------------------------------------------------------
|
| Here you may configure the captcha credentials used by your application
| to protect forms from spam and bot submissions. The active provider is
| stored in the application settings and can be changed via the admin panel.
|
| Supported providers: "turnstile", "recaptcha_v2", "hcaptcha"
|
*/

return [

        /*
    |--------------------------------------------------------------------------
    | Captcha Credentials
    |--------------------------------------------------------------------------
    |
    | These keys are shared across all supported captcha providers. Switch
    | the active provider anytime via admin settings without needing to
    | change the credentials stored here.
    |
    */

    'site_key' => env('CAPTCHA_SITE_KEY', ''),
    'secret_key' => env('CAPTCHA_SECRET_KEY', ''),

];
