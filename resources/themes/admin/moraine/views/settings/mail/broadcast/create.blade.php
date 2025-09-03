@extends('admin::layouts.app')

@section('title', 'Broadcast Mail - Create')

@section('body')
<form action="{{ route('admin.settings.mail.broadcast.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input type="text" name="broadcast_subject" label="{{ __('admin/settings/mail.broadcast_subject_label') }}" helper="{{ __('admin/settings/mail.broadcast_subject_helper') }}" value="{{ old('broadcast_subject') }}" required />
        <x-admin::editor.text name="broadcast_body" label="{{ __('admin/settings/mail.broadcast_body_label') }}" helper="{{ __('admin/settings/mail.broadcast_body_helper') }}" required>{{ old('broadcast_body') }}</x-admin::editor.text>
        <div x-data="{ recipient_custom: '{{ old('broadcast_recipient_group') }}' }" class="grid gap-4">
            <x-admin::select 
                name="broadcast_recipient_group" 
                label="{{ __('admin/settings/mail.broadcast_recipient_group_label') }}" 
                helper="{{ __('admin/settings/mail.broadcast_recipient_group_helper') }}" 
                x-model="recipient_custom"
                required
            >
                <option value="all_users" @if (old('broadcast_recipient_group') === 'all_users') selected @endif>All Users</option>
                <option value="custom_users" @if (old('broadcast_recipient_group') === 'custom_users') selected @endif>Custom Users</option>
            </x-admin::select>

            <div x-show="recipient_custom === 'custom_users'">
                <x-admin::multiselect
                    name="broadcast_recipient_custom"
                    :options="[
                        ['value' => 'billmora@billmora.com', 'title' => 'Billmora', 'subtitle' => 'billmora@billmora.com'], // TODO: Will be replaced by real user
                    ]"
                    :selected="old('broadcast_recipient_custom', [])"
                    helper="{{ __('admin/settings/mail.broadcast_recipient_custom_helper') }}"
                />
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <x-admin::tags name="broadcast_cc" label="{{ __('admin/settings/mail.broadcast_cc_label') }}" helper="{{ __('admin/settings/mail.broadcast_cc_helper') }}" :value="old('broadcast_cc')" />
            <x-admin::tags name="broadcast_bcc" label="{{ __('admin/settings/mail.broadcast_bcc_label') }}" helper="{{ __('admin/settings/mail.broadcast_bcc_helper') }}" :value="old('broadcast_bcc')" />
        </div>
        <x-admin::input type="datetime-local" name="broadcast_schedule" label="{{ __('admin/settings/mail.broadcast_schedule_label') }}" helper="{{ __('admin/settings/mail.broadcast_schedule_helper') }}" value="{{ old('broadcast_schedule') }}" />
        <div class="min-w-full flex flex-col gap-1">
            <label class="block text-slate-600 font-semibold mb-0.5">
                {{ __('admin/settings/mail.broadcast_placeholder_label') }}
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
                        <tr class="text-slate-500">
                            <td class="border-t-2 border-r-2 border-billmora-2 px-4 py-2"><pre>{client_name}</pre></td>
                            <td class="border-t-2 border-billmora-2 px-4 py-2"><pre>Client name</pre></td>
                        </tr>
                        <tr class="text-slate-500">
                            <td class="border-t-2 border-r-2 border-billmora-2 px-4 py-2"><pre>{company_name}</pre></td>
                            <td class="border-t-2 border-billmora-2 px-4 py-2"><pre>Company name</pre></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ __('admin/settings/mail.broadcast_placeholder_helper') }}</p>
        </div>
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.settings.mail.broadcast') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.cancel') }}</a>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.create') }}</button>
    </div>
</form>
@endsection