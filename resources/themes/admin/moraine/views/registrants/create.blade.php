@extends('admin::layouts.app')

@section('title', 'Create Registrant')

@section('body')
<form 
    action="{{ route('admin.registrants.store') }}" 
    method="POST" 
    class="flex flex-col gap-5"
>
    @csrf

    @php

    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <x-admin::input 
            name="domain"
            type="text"
            label="{{ __('admin/registrants.domain_label') }}"
            helper="{{ __('admin/registrants.domain_helper') }}"
            value="{{ old('domain') }}"
            required 
            placeholder="example.com"
        />
        
        <x-admin::singleselect
            name="user_id"
            label="{{ __('admin/registrants.user_label') }}"
            helper="{{ __('admin/registrants.user_helper') }}"
            :options="$users"
            :selected="old('user_id')"
            required
        />
        
        <x-admin::singleselect
            name="tld_id"
            label="{{ __('admin/registrants.tld_label') }}"
            helper="{{ __('admin/registrants.tld_helper') }}"
            :options="$tlds"
            :selected="old('tld_id')"
        />

        <x-admin::singleselect
            name="plugin_id"
            label="{{ __('admin/registrants.registrar_label') }}"
            helper="{{ __('admin/registrants.registrar_helper') }}"
            :options="$registrars"
            :selected="old('plugin_id')"
        />
        
        <x-admin::select
            name="status"
            label="{{ __('admin/registrants.status_label') }}"
            helper="{{ __('admin/registrants.status_helper') }}"
            required
        >
            @foreach(['pending', 'active', 'expired', 'suspended', 'pending_transfer', 'transferred_away', 'cancelled', 'redemption', 'terminated'] as $status)
                <option value="{{ $status }}" {{ old('status', 'pending') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </x-admin::select>

        <x-admin::select
            name="registration_type"
            label="{{ __('admin/registrants.registration_type_label') }}"
            helper="{{ __('admin/registrants.registration_type_helper') }}"
            required
        >
            <option value="register" {{ old('registration_type', 'register') === 'register' ? 'selected' : '' }}>Register</option>
            <option value="transfer" {{ old('registration_type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
        </x-admin::select>

        <x-admin::input 
            name="years"
            type="number"
            label="{{ __('admin/registrants.years_label') }}"
            helper="{{ __('admin/registrants.years_helper') }}"
            value="{{ old('years', 1) }}"
            required 
            min="1"
        />

        <x-admin::input 
            name="price"
            type="number"
            step="0.01"
            label="{{ __('admin/registrants.price_label') }}"
            helper="{{ __('admin/registrants.price_helper') }}"
            value="{{ old('price', '0.00') }}"
            required 
            min="0"
        />

        <x-admin::input 
            name="expires_at"
            type="date"
            label="{{ __('admin/registrants.expires_label') }}"
            helper="{{ __('admin/registrants.expires_helper') }}"
            value="{{ old('expires_at') }}"
        />
        
        <x-admin::toggle
            name="auto_renew"
            label="{{ __('admin/registrants.auto_renew_label') }}"
            helper="{{ __('admin/registrants.auto_renew_helper') }}"
            :checked="(bool)old('auto_renew', true)"
        />
        
        <x-admin::toggle
            name="whois_privacy"
            label="{{ __('admin/registrants.whois_privacy_label') }}"
            helper="{{ __('admin/registrants.whois_privacy_helper') }}"
            :checked="(bool)old('whois_privacy', false)"
        />
    </div>

    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.registrants') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.cancel') }}
        </a>
        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            {{ __('common.save') }}
        </button>
    </div>
</form>
@endsection
