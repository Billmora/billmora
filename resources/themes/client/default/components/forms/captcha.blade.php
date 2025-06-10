<div class="form-group start">
    {!! \App\Services\CaptchaService::render() !!}
    @error('captcha')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>