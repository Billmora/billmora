@props(['form' => null])

@if (\App\Services\CaptchaService::enabled($form))
    <div class="form-group">
        {!! \App\Services\CaptchaService::render() !!}
    </div>
@endif