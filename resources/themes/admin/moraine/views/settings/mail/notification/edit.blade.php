@extends('admin::layouts.app')

@section('title', "Notification Edit - {$notification->id}")

@section('body')
<form action="{{ route('admin.settings.mail.notification.update', $notification->id) }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @method('PUT')
    @if ($noTranslation)
        <x-admin::alert variant="warning" title="{{ __('admin/settings/mail.translation_missing_title') }}">{{ __('admin/settings/mail.translation_missing_desc', ['lang' => request()->query('lang', config('app.fallback_locale'))]) }}</x-admin::alert>
    @endif
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::select name="notification_language" label="{{ __('admin/settings/mail.notification_language_label') }}" onchange="window.location='{{ url()->current() }}?lang=' + this.value" required>
            @foreach ($langs as $lang)
                <option value="{{ $lang['lang'] }}" @selected(request()->query('lang', config('app.fallback_locale')) === $lang['lang'])>
                    {{ $lang['name'] }}
                </option>
            @endforeach
        </x-admin::select>
    </div>
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="grid md:grid-cols-2 gap-4">
            <x-admin::input type="text" name="notification_key" label="{{ __('admin/settings/mail.notification_key_label') }}" helper="{{ __('admin/settings/mail.notification_key_helper') }}" value="{{ old('notification_key', $notification->key) }}" disabled required />
            <x-admin::input type="text" name="notification_name" label="{{ __('admin/settings/mail.notification_name_label') }}" helper="{{ __('admin/settings/mail.notification_name_helper') }}" value="{{ old('notification_name', $notification->name) }}" disabled required />
        </div>
        <x-admin::input type="text" name="notification_subject" label="{{ __('admin/settings/mail.notification_subject_label') }}" helper="{{ __('admin/settings/mail.notification_subject_helper') }}" value="{{ old('notification_subject', $translation->subject) }}" required />
        <x-admin::editor.text name="notification_body" label="{{ __('admin/settings/mail.notification_body_label') }}" helper="{{ __('admin/settings/mail.notification_body_helper') }}" required>{{ old('notification_body', $translation->body) }}</x-admin::editor.text>
        <x-admin::toggle name="notification_is_active" label="{{ __('admin/settings/mail.notification_is_active_label') }}" helper="{{ __('admin/settings/mail.notification_is_active_helper') }}" :checked="old('notification_is_active', $notification->is_active)" />
        <div class="grid md:grid-cols-2 gap-4">
            <x-admin::tags name="notification_cc" label="{{ __('admin/settings/mail.notification_cc_label') }}" helper="{{ __('admin/settings/mail.notification_cc_helper') }}" :value="old('notification_cc', $notification->cc)" />
            <x-admin::tags name="notification_bcc" label="{{ __('admin/settings/mail.notification_bcc_label') }}" helper="{{ __('admin/settings/mail.notification_bcc_helper') }}" :value="old('notification_bcc', $notification->bcc)" />
        </div>
        <div class="min-w-full flex flex-col gap-1">
            <label class="block text-slate-600 font-semibold mb-0.5">
                {{ __('admin/settings/mail.notification_placeholder_label') }}
            </label>
            <div class="border-2 border-billmora-2 rounded-xl overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="text-slate-600">
                            <th class="bg-billmora-2 border-r-2 border-billmora-2 px-4 py-2">{{ __('admin/common.key') }}</th>
                            <th class="bg-billmora-2 border-billmora-2 px-4 py-2">{{ __('admin/common.value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notification->placeholder as $key => $desc)
                            <tr class="text-slate-500">
                                <td class="border-t-2 border-r-2 border-billmora-2 px-4 py-2"><pre>{{ '{' . $key . '}' }}</pre></td>
                                <td class="border-t-2 border-billmora-2 px-4 py-2"><pre>{{ $desc }}</pre></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ __('admin/settings/mail.notification_placeholder_helper') }}</p>
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.mail.notification') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.update') }}</button>
    </div>
</form>
@endsection