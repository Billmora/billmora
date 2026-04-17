@extends('admin::layouts.app')

@section('title', "Registrant Edit - {$registrant->domain}")

@section('body')
    <div class="flex flex-col-reverse lg:flex-row gap-5">
        <form action="{{ route('admin.registrants.update', $registrant) }}" method="POST"
            class="w-full lg:w-5/7 flex flex-col gap-5">
            @csrf
            @method('PUT')

            
            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <x-admin::input name="domain_display" type="text" label="{{ __('admin/registrants.domain_label') }}"
                    helper="{{ __('admin/registrants.domain_helper') }}" value="{{ $registrant->domain }}" disabled />
                <x-admin::input name="registrant_number_display" type="text"
                    label="{{ __('admin/registrants.number_label') }}" helper="{{ __('admin/registrants.number_helper') }}"
                    value="{{ $registrant->registrant_number }}" disabled />
                <div class="w-full">
                    <label for="user_id" class="flex text-slate-600 font-semibold mb-1">
                        {{ __('admin/registrants.user_label') }}
                    </label>
                    <div class="relative inline-block w-full group">
                        <input type="text" name="user_display" id="user_display" value="{{ $registrant->user->email }}"
                            class="w-full px-3 py-2.25 bg-billmora-1 text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl cursor-not-allowed"
                            disabled>
                        <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                            <a href="{{ route('admin.users.summary', $registrant->user_id) }}" target="_blank"
                                class="block bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white text-sm rounded-lg transition duration-300 cursor-pointer">
                                {{ __('admin/registrants.go_to_user') }}
                            </a>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('admin/registrants.user_helper') }}
                    </p>
                </div>
                <x-admin::input name="tld_display" type="text" label="{{ __('admin/registrants.tld_label') }}"
                    helper="{{ __('admin/registrants.tld_helper') }}" value="{{ $registrant->tld->tld ?? '-' }}" disabled />
                <x-admin::select name="status" label="{{ __('admin/registrants.status_label') }}"
                    helper="{{ __('admin/registrants.status_helper') }}" required>
                    @foreach(['pending', 'active', 'expired', 'suspended', 'pending_transfer', 'transferred_away', 'cancelled', 'redemption', 'terminated'] as $status)
                        <option value="{{ $status }}" {{ old('status', $registrant->status) === $status ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </x-admin::select>
                <x-admin::select
                    name="plugin_id"
                    label="{{ __('admin/registrants.registrar_label') }}"
                    helper="{{ __('admin/registrants.registrar_helper') }}"
                >
                    @foreach($registrars as $registrar)
                        <option value="{{ $registrar->id }}" {{ old('plugin_id', $registrant->plugin_id) == $registrar->id ? 'selected' : '' }}>{{ $registrar->name }}</option>
                    @endforeach
                </x-admin::select>
                <x-admin::input name="expires_at" type="date" label="{{ __('admin/registrants.expires_label') }}"
                    helper="{{ __('admin/registrants.expires_helper') }}"
                    value="{{ old('expires_at', $registrant->expires_at?->format('Y-m-d')) }}" required />
                <div class="flex flex-col gap-4">
                    <x-admin::toggle name="auto_renew" label="{{ __('admin/registrants.auto_renew_label') }}"
                        helper="{{ __('admin/registrants.auto_renew_helper') }}" :checked="(bool) old('auto_renew', $registrant->auto_renew)" />
                    <x-admin::toggle name="whois_privacy" label="{{ __('admin/registrants.whois_privacy_label') }}"
                        helper="{{ __('admin/registrants.whois_privacy_helper') }}" :checked="(bool) old('whois_privacy', $registrant->whois_privacy)" />
                </div>
            </div>

            
            <div>
                <div class="mb-2">
                    <h4 class="text-lg font-semibold text-slate-600">{{ __('admin/registrants.nameservers_label') }}</h4>
                    <p class="text-sm text-slate-500">{{ __('admin/registrants.nameservers_helper') }}</p>
                </div>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                    @for($i = 0; $i < 4; $i++)
                        <x-admin::input name="nameservers[{{ $i }}]" type="text" label="NS{{ $i + 1 }}"
                            placeholder="ns{{ $i + 1 }}.example.com"
                            value="{{ old('nameservers.' . $i, $registrant->nameservers[$i] ?? '') }}" />
                    @endfor
                </div>
            </div>

            <div class="flex gap-4 ml-auto">
                <a href="{{ route('admin.registrants') }}"
                    class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit"
                    class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>

        
        <div class="w-full lg:w-2/7 h-fit grid gap-5">
            
            <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl h-fit">
                <h3 class="text-lg font-semibold text-slate-600 border-b-2 border-billmora-2 pb-4 mb-2">
                    {{ __('admin/registrants.registrar_actions_label') }}
                </h3>

                @if(in_array($registrant->status, ['pending', 'terminated', 'cancelled']))
                    <x-admin::modal.trigger modal="createModal" type="button"
                        class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                        <x-lucide-globe class="w-5 h-5" />
                        {{ __('admin/registrants.registrar_create_label') }}
                    </x-admin::modal.trigger>
                @endif

                @if(in_array($registrant->status, ['pending', 'pending_transfer']))
                    <x-admin::modal.trigger modal="transferModal" type="button"
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                        <x-lucide-arrow-right-left class="w-5 h-5" />
                        {{ __('admin/registrants.registrar_transfer_label') }}
                    </x-admin::modal.trigger>
                @endif

                @if(in_array($registrant->status, ['active', 'expired', 'suspended', 'redemption']))
                    <x-admin::modal.trigger modal="renewModal" type="button"
                        class="w-full flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                        <x-lucide-refresh-cw class="w-5 h-5" />
                        {{ __('admin/registrants.registrar_renew_label') }}
                    </x-admin::modal.trigger>
                @endif

                @if($registrant->status !== 'transferred_away')
                    <x-admin::modal.trigger modal="syncModal" type="button"
                        class="w-full flex items-center justify-center gap-2 bg-slate-600 hover:bg-slate-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                        <x-lucide-refresh-ccw class="w-5 h-5" />
                        {{ __('admin/registrants.registrar_sync_label') }}
                    </x-admin::modal.trigger>
                @endif
            </div>
        </div>
    </div>

    
    @foreach(['create' => 'admin.registrants.registrar.create', 'transfer' => 'admin.registrants.registrar.transfer', 'renew' => 'admin.registrants.registrar.renew', 'sync' => 'admin.registrants.registrar.sync'] as $action => $routeName)
        <x-admin::modal.content modal="{{ $action }}Modal" variant="info" size="xl" position="centered"
            title="{{ __('common.confirm_modal_title') }}"
            description="{{ __('common.confirm_modal_description', ['item' => __('admin/registrants.registrar_' . $action . '_label')]) }}">
            <form action="{{ route($routeName, ['registrant' => $registrant->id]) }}" method="POST">
                @csrf
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close"
                        class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.cancel') }}
                    </x-admin::modal.trigger>
                    <button type="submit"
                        class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                        {{ __('common.submit') }}
                    </button>
                </div>
            </form>
        </x-admin::modal.content>
    @endforeach
@endsection