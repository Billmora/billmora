@extends('admin::layouts.app')

@section('body')
<form action="{{ route('admin.settings.mail.template.update', $template->id) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
    @if ($noTranslation)
        <x-admin::alert variant="warning" title="{{ __('admin/settings/mail.translation_missing_title') }}">{{ __('admin/settings/mail.translation_missing_desc', ['lang' => request()->query('lang', config('app.fallback_locale'))]) }}</x-admin::alert>
    @endif
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::select name="template_language" label="{{ __('admin/settings/mail.template_language_label') }}" onchange="window.location='{{ url()->current() }}?lang=' + this.value" required>
            @foreach ($langs as $lang)
                <option value="{{ $lang['lang'] }}" @selected(request()->query('lang', config('app.fallback_locale')) === $lang['lang'])>
                    {{ $lang['name'] }}
                </option>
            @endforeach
        </x-admin::select>
    </div>
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid md:grid-cols-2 gap-4">
            <x-admin::input type="text" name="template_key" label="{{ __('admin/settings/mail.template_key_label') }}" helper="{{ __('admin/settings/mail.template_key_helper') }}" value="{{ old('template_key', $template->key) }}" disabled required />
            <x-admin::input type="text" name="template_name" label="{{ __('admin/settings/mail.template_name_label') }}" helper="{{ __('admin/settings/mail.template_name_helper') }}" value="{{ old('template_name', $template->name) }}" disabled required />
        </div>
        <x-admin::input type="text" name="template_subject" label="{{ __('admin/settings/mail.template_subject_label') }}" helper="{{ __('admin/settings/mail.template_subject_helper') }}" value="{{ old('template_subject', $translation->subject) }}" required />
        <x-admin::editor.text name="template_body" label="{{ __('admin/settings/mail.template_body_label') }}" helper="{{ __('admin/settings/mail.template_body_helper') }}" required>{{ old('template_body', $translation->body) }}</x-admin::editor.text>
        <x-admin::toggle name="template_active" label="{{ __('admin/settings/mail.template_active_label') }}" helper="{{ __('admin/settings/mail.template_active_helper') }}" :checked="old('template_active', $template->active)" required />
        <div class="grid md:grid-cols-2 gap-4">
            <x-admin::tags name="template_cc" label="{{ __('admin/settings/mail.template_cc_label') }}" helper="{{ __('admin/settings/mail.template_cc_helper') }}" :value="old('template_cc', $template->cc)" required />
            <x-admin::tags name="template_bcc" label="{{ __('admin/settings/mail.template_bcc_label') }}" helper="{{ __('admin/settings/mail.template_bcc_helper') }}" :value="old('template_bcc', $template->bcc)" required />
        </div>
        <x-admin::textarea rows="5" name="template_placeholder" label="{{ __('admin/settings/mail.template_placeholder_label') }}" helper="{{ __('admin/settings/mail.template_placeholder_helper') }}" readonly>
            {{ collect($template->placeholder)->map(fn ($desc, $key) => '{' . $key . '} = ' . $desc)->implode("\n") }}
        </x-admin::textarea>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.mail.template') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.update') }}</button>
    </div>
</form>
@endsection