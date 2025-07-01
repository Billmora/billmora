@props(['form' => null])

@if (\App\Services\CaptchaService::enabled($form))
  <div {{ $attributes->class("") }}>
    @switch(env('CAPTCHA_DRIVER'))
      @case('turnstile')
          <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
          <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}"></div>
        @break
      @case('recaptchav2')
          <script src="https://www.google.com/recaptcha/api.js" async defer></script>
          <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHAV2_SITE_KEY') }}"></div>
        @break
      @case('hcaptcha')
          <script src="https://hcaptcha.com/1/api.js" async defer></script>
          <div class="g-captcha" data-sitekey="{{ env('HCAPTCHA_SITE_KEY') }}"></div>
        @break
      @default
    @endswitch
    @error('captcha')
      <p class="mt-1 text-sm text-red-400 font-semibold">{{ $message }}</p>
    @enderror
  </div>
@endif