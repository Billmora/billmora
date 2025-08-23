@extends('admin::layouts.app')

@section('body')
<form action="{{ route('admin.settings.mail.template.update', $template->id) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid md:grid-cols-2 gap-4">
            <x-admin::input type="text" name="template_key" label="{{ __('admin/settings/mail.template_key_label') }}" helper="{{ __('admin/settings/mail.template_key_helper') }}" value="{{ old('key', $template->key) }}" disabled required />
            <x-admin::input type="text" name="template_name" label="{{ __('admin/settings/mail.template_name_label') }}" helper="{{ __('admin/settings/mail.template_name_helper') }}" value="{{ old('name', $template->name) }}" disabled required />
        </div>
        <x-admin::input type="text" name="template_subject" label="{{ __('admin/settings/mail.template_subject_label') }}" helper="{{ __('admin/settings/mail.template_subject_helper') }}" value="{{ old('subject', $template->subject) }}" required />
        <x-admin::editor.text name="template_body" label="{{ __('admin/settings/mail.template_body_label') }}" helper="{{ __('admin/settings/mail.template_body_helper') }}" required>{{ old('body', $template->body) }}</x-admin::editor.text>
        <x-admin::toggle name="template_active" label="{{ __('admin/settings/mail.template_active_label') }}" helper="{{ __('admin/settings/mail.template_active_helper') }}" :checked="old('active', $template->active)" required />
        <x-admin::textarea rows="5" name="template_placeholder" label="{{ __('admin/settings/mail.template_placeholder_label') }}" helper="{{ __('admin/settings/mail.template_placeholder_helper') }}" disabled>
            {{ old('template_placeholder', collect($template->placeholder)->map(fn ($desc, $key) => '{' . $key . '} = ' . $desc)->implode("\n")) }}
        </x-admin::textarea>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.mail.template') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.update') }}</button>
    </div>
</form>
@endsection