<?php

namespace App\Services;

use Billmora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CaptchaService
{

    /**
     * Determine if captcha is enabled.
     *
     * @param string|null $context The context to check (e.g., login_form, register_form).
     *
     * @return bool True if captcha is enabled globally or for the given context, false otherwise.
     */
    public static function enabled(string $context = null): bool
    {
        $captchaEnabled = in_array(Billmora::getCaptcha('provider_type'), ['turnstile', 'recaptchav2', 'hcaptcha']);

        if (!$captchaEnabled) {
            return false;
        }

        if ($context) {
            return in_array($context, Billmora::getCaptcha('placements_enabled_forms'));
        }

        return true;
    }

    /**
     * Verify the captcha response token with the provider.
     *
     * @param string|null $token The captcha response token submitted by the client.
     * @param string|null $ip    The client's IP address (optional).
     *
     * @return bool True if the captcha verification succeeds, false otherwise.
     */
    protected static function verify(?string $token, ?string $ip = null): bool
    {
        if (empty($token)) {
            return false;
        }

        $endpoint = match (Billmora::getCaptcha('provider_type')) {
            'turnstile'   => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            'recaptchav2' => 'https://www.google.com/recaptcha/api/siteverify',
            'hcaptcha'    => 'https://hcaptcha.com/siteverify',
            default       => null,
        };

        $secret = env('CAPTCHA_SECRET_KEY');

        if (!$endpoint || !$secret) {
            return false;
        }

        $response = Http::asForm()->post($endpoint, [
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->json('success', false);
    }

    /**
     * Verify captcha from the incoming request.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the captcha response.
     *
     * @return bool True if captcha verification succeeds, true by default if provider is not configured.
     */
    public static function verifyRequest(Request $request): bool
    {
        return match (Billmora::getCaptcha('provider_type')) {
            'turnstile' => self::verify($request->input('cf-turnstile-response'), $request->ip()),
            'recaptchav2' => self::verify($request->input('g-recaptcha-response'), $request->ip()),
            'hcaptcha' => self::verify($request->input('h-captcha-response'), $request->ip()),
            default => true,
        };
    }

    /**
     * Verify captcha for a given context or throw a validation exception.
     *
     * @param string                    $context The context in which captcha should be validated.
     * @param \Illuminate\Http\Request  $request The incoming HTTP request containing captcha response.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException If captcha verification fails.
     */
    public static function verifyOrFail(string $context, Request $request): void
    {
        if (!self::enabled($context)) {
            return;
        }

        if (!self::verifyRequest($request)) {
            throw ValidationException::withMessages(['captcha' => __('auth.captcha.invalid')]);
        }
    }
}