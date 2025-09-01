@extends('admin::layouts.app')

@section('body')
<form action="{{ route('admin.settings.mail.broadcast.store') }}" method="POST" class="flex flex-col gap-5">
    @csrf
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input type="text" name="broadcast_subject" label="{{ __('admin/settings/mail.broadcast_subject_label') }}" helper="{{ __('admin/settings/mail.broadcast_subject_helper') }}" value="{{ old('broadcast_subject') }}" required />
        <x-admin::editor.text name="broadcast_body" label="{{ __('admin/settings/mail.broadcast_body_label') }}" helper="{{ __('admin/settings/mail.broadcast_body_helper') }}" required>{{ old('broadcast_body') }}</x-admin::editor.text>
        <div x-data="{ recipients: '{{ old('broadcast_recipients') }}' }" class="grid gap-4">
            <x-admin::select 
                name="broadcast_recipients" 
                label="{{ __('admin/settings/mail.broadcast_recipients_label') }}" 
                helper="{{ __('admin/settings/mail.broadcast_recipients_helper') }}" 
                x-model="recipients"
                required
            >
                <option value="all_users">All Users</option>
                <option value="custom_users">Custom Users</option>
            </x-admin::select>

            <div x-show="recipients === 'custom_users'">
                <x-admin::multiselect
                    name="custom_users"
                    :options="[
                        ['value' => 'billmora@billmora.com', 'title' => 'Billmora', 'subtitle' => 'billmora@billmora.com'], // TODO: Will be replaced by real user
                    ]"
                    helper="{{ __('admin/settings/mail.broadcast_recipients_custom_helper') }}"
                />
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <x-admin::tags name="broadcast_cc" label="{{ __('admin/settings/mail.broadcast_cc_label') }}" helper="{{ __('admin/settings/mail.broadcast_cc_helper') }}" :value="old('broadcast_cc')" />
            <x-admin::tags name="broadcast_bcc" label="{{ __('admin/settings/mail.broadcast_bcc_label') }}" helper="{{ __('admin/settings/mail.broadcast_bcc_helper') }}" :value="old('broadcast_bcc')" />
        </div>
        <x-admin::input type="datetime-local" name="broadcast_schedule" label="{{ __('admin/settings/mail.broadcast_schedule_label') }}" helper="{{ __('admin/settings/mail.broadcast_schedule_helper') }}" />
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