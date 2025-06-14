<?php

namespace App\Services;

use App\Services\BillmoraService as Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CaptchaService
{
    public static function enabled(string $context = null): bool
    {
        $captchaEnabled = in_array(env('CAPTCHA_DRIVER'), ['turnstile', 'recaptchav2', 'hcaptcha']);

        if (!$captchaEnabled) {
            return false;
        }

        if ($context) {
            return in_array($context, Billmora::getAuth('captcha_active'));
        }

        return true;
    }


    public static function render(): string
    {
        return match (env('CAPTCHA_DRIVER')) {
            'turnstile' => '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>' .
                '<div class="cf-turnstile" data-sitekey="' . env('TURNSTILE_SITE_KEY') . '"></div>',
            'recaptchav2' => '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' .
                '<div class="g-recaptcha" data-sitekey="' . env('RECAPTCHAV2_SITE_KEY') . '"></div>',
            'hcaptcha' => '<script src="https://hcaptcha.com/1/api.js" async defer></script>' .
                '<div class="h-captcha" data-sitekey="' . env('HCAPTCHA_SITE_KEY') . '"></div>',
            default => '',
        };
    }

    public static function verifyRequest(Request $request): bool
    {
        return match (env('CAPTCHA_DRIVER')) {
            'turnstile' => self::verify($request->input('cf-turnstile-response'), $request->ip()),
            'recaptchav2' => self::verify($request->input('g-recaptcha-response'), $request->ip()),
            'hcaptcha' => self::verify($request->input('h-captcha-response'), $request->ip()),
            default => true,
        };
    }

    protected static function verify(?string $token, ?string $ip = null): bool
    {
        if (empty($token)) {
            return false;
        }

        $endpoint = match (env('CAPTCHA_DRIVER')) {
            'turnstile' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            'recaptchav2' => 'https://www.google.com/recaptcha/api/siteverify',
            'hcaptcha' => 'https://hcaptcha.com/siteverify',
            default => null,
        };

        $secret = match (env('CAPTCHA_DRIVER')) {
            'turnstile' => env('TURNSTILE_SECRET_KEY'),
            'recaptchav2' => env('RECAPTCHAV2_SECRET_KEY'),
            'hcaptcha' => env('HCAPTCHA_SECRET_KEY'),
            default => null,
        };

        if (!$endpoint || !$secret) {
            return false;
        }

        $response = Http::asForm()->post($endpoint, [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->json('success', false);
    }

    public static function verifyOrFail(string $context, Request $request): void
    {
        if (!self::enabled($context)) {
            return;
        }

        if (!self::verifyRequest($request)) {
            throw ValidationException::withMessages([
                'captcha' => __('auth.invalid_captcha'),
            ]);
        }
    }
}