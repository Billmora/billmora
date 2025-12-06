@extends('admin::layouts.app')

@section('title', "Create User")

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    @if (session('error'))
        <x-admin::alert variant="danger" title="{{ session('error') }}" />
    @endif
    <form action="{{ route('admin.users.store') }}" method="POST" class="flex flex-col gap-5">
        @csrf
        <div class="flex flex-col lg:flex-row gap-5">
            <div class="w-full lg:w-2/3 h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="grid grid-cols-none md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-4">
                        <x-admin::input type="text" name="first_name" label="{{ __('common.first_name') }}" value="{{ old('first_name') }}" required />
                        <x-admin::input type="text" name="last_name" label="{{ __('common.last_name') }}" value="{{ old('last_name') }}" required />
                        <x-admin::input type="email" name="email" label="{{ __('common.email') }}" value="{{ old('email') }}" required />
                        <x-admin::input type="password" name="password" label="{{ __('common.password') }}" />
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-admin::input type="tel" name="phone_number" label="{{ __('common.phone_number') }}" value="{{ old('phone_number') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'phone_number')" />
                        <x-admin::input type="text" name="company_name" label="{{ __('common.company_name') }}" value="{{ old('company_name') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'company_name')" />
                        <x-admin::input type="text" name="street_address_1" label="{{ __('common.street_address_1') }}" value="{{ old('street_address_1') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'street_address_1')" />
                        <x-admin::input type="text" name="street_address_2" label="{{ __('common.street_address_2') }}" value="{{ old('street_address_2') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'street_address_2')" />
                        <x-admin::input type="text" name="city" label="{{ __('common.city') }}" value="{{ old('city') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'city')" />
                        <x-admin::input type="text" name="state" label="{{ __('common.state') }}" value="{{ old('state') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'state')" />
                        <x-admin::input type="number" name="postcode" label="{{ __('common.postcode') }}" value="{{ old('postcode') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'postcode')" />
                        <x-admin::select name="country" label="{{ __('common.country') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'country')">
                            @foreach (config('utils.countries') as $country => $label)
                                <option value="{{ $country }}"
                                    {{ old('country') == $country ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </x-admin::select>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/3 h-fit flex flex-col gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <x-admin::select name="role" label="{{ __('common.role') }}" required>
                    @if (Auth::user()->isRootAdmin())
                        <option value="root" {{ old('role') === 'root' ? 'selected' : '' }}>
                            Administrator
                        </option>
                    @endif
                    <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>
                        Client
                    </option>
                    @foreach ($roles as $id => $name)
                        <option value="{{ $name }}" {{ old('role') === $name ? 'selected' : '' }}>
                            {{ ucfirst($name) }}
                        </option>
                    @endforeach
                </x-admin::select>
                <x-admin::select name="status" label="{{ __('common.status') }}" required>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </x-admin::select>
                <x-admin::select name="language" label="{{ __('common.language') }}" required>
                    @foreach ($langs as $lang)
                        <option value="{{ $lang['lang'] }}"
                            {{ old('language') == $lang['lang'] ? 'selected' : '' }}>
                            {{ $lang['name'] }}
                        </option>
                    @endforeach
                </x-admin::select>
            </div>
        </div>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.users') }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
        </div>
    </form>
</div>
@endsection