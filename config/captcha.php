<?php

/*
|--------------------------------------------------------------------------
| Captcha Configuration
|--------------------------------------------------------------------------
|
| Here you may configure the captcha providers used by your application
| to protect forms from spam and bot submissions. Each provider requires
| a site key (used on the frontend) and a secret key (used on the backend
| for server-side verification).
|
| Supported providers: "turnstile", "recaptcha_v2", "hcaptcha"
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile
    |--------------------------------------------------------------------------
    |
    | Turnstile is Cloudflare's CAPTCHA alternative that provides a
    | privacy-preserving, user-friendly challenge without visual puzzles.
    | https://developers.cloudflare.com/turnstile
    |
    */

    'turnstile' => [
        'site_key'   => env('TURNSTILE_SITE_KEY', ''),
        'secret_key' => env('TURNSTILE_SECRET_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v2
    |--------------------------------------------------------------------------
    |
    | reCAPTCHA v2 requires users to check a checkbox or solve an image
    | challenge to verify they are human.
    | https://developers.google.com/recaptcha/docs/display
    |
    */

    'recaptcha_v2' => [
        'site_key'   => env('RECAPTCHAV2_SITE_KEY', ''),
        'secret_key' => env('RECAPTCHAV2_SECRET_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | hCaptcha
    |--------------------------------------------------------------------------
    |
    | hCaptcha is a privacy-focused CAPTCHA service that serves as a
    | drop-in replacement for reCAPTCHA.
    | https://docs.hcaptcha.com
    |
    */

    'hcaptcha' => [
        'site_key'   => env('HCAPTCHA_SITE_KEY', ''),
        'secret_key' => env('HCAPTCHA_SECRET_KEY', ''),
    ],

];
