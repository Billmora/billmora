@extends('admin::layouts.app')

@section('title', "User Profile - $user->email")

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    @if (session('error'))
        <x-admin::alert variant="danger" title="{{ session('error') }}" />
    @endif
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.users.summary', ['id' => $user->id]),
                'icon' => 'lucide-contact',
                'label' => __('admin/users/manage.tabs.summary'),
            ],
            [
                'route' => route('admin.users.profile', ['id' => $user->id]),
                'icon' => 'lucide-user-pen',
                'label' => __('admin/users/manage.tabs.profile'),
            ],
            [
                'route' => route('admin.users.activity', ['id' => $user->id]),
                'icon' => 'lucide-activity',
                'label' => __('admin/users/manage.tabs.activity'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <form action="{{ route('admin.users.profile.update', ['id' => $user->id]) }}" method="POST" class="flex flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="flex flex-col lg:flex-row gap-5">
            <div class="w-full lg:w-2/3 h-fit bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <div class="grid grid-cols-none md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-4">
                        <x-admin::input type="text" name="first_name" label="{{ __('common.first_name') }}" value="{{ old('first_name', $user->first_name) }}" required />
                        <x-admin::input type="text" name="last_name" label="{{ __('common.last_name') }}" value="{{ old('last_name', $user->last_name) }}" required />
                        <x-admin::input type="email" name="email" label="{{ __('common.email') }}" value="{{ old('email', $user->email) }}" required />
                        <x-admin::input type="password" name="password" label="{{ __('common.password') }}" />
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-admin::input type="tel" name="phone_number" label="{{ __('common.phone_number') }}" value="{{ old('phone_number', $user->billing->phone_number) }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'phone_number')" />
                        <x-admin::input type="text" name="company_name" label="{{ __('common.company_name') }}" value="{{ old('company_name', $user->billing->company_name) }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'company_name')" />
                        <x-admin::input type="text" name="street_address_1" label="{{ __('common.street_address_1') }}" value="{{ old('street_address_1', $user->billing->street_address_1) }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'street_address_1')" />
                        <x-admin::input type="text" name="street_address_2" label="{{ __('common.street_address_2') }}" value="{{ old('street_address_2', $user->billing->street_address_2) }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'street_address_1')" />
                        <x-admin::input type="text" name="city" label="{{ __('common.city') }}" value="{{ old('city', $user->billing->city) }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'city')" />
                        <x-admin::input type="text" name="state" label="{{ __('common.state') }}" value="{{ old('state', $user->billing->state) }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'state')" />
                        <x-admin::input type="number" name="postcode" label="{{ __('common.postcode') }}" value="{{ old('postcode', $user->billing->postcode) }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'postcode')" />
                        <x-admin::select name="country" label="{{ __('common.country') }}" :required="Billmora::hasAuth('user_billing_required_inputs', 'country')">
                            @foreach (config('utils.countries') as $country => $label)
                                <option value="{{ $country }}"
                                    {{ old('country', $user->billing->country) == $country ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </x-admin::select>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/3 h-fit flex flex-col gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
                <x-admin::select name="role" label="{{ __('common.role') }}" :disabled="($user->id === Auth::id()) || ($user->isRootAdmin() && !Auth::user()->isRootAdmin())">
                    @if (Auth::user()->isRootAdmin())
                        <option value="root" {{ old('role', $user->isRootAdmin() ? 'root' : null) === 'root' ? 'selected' : '' }}>
                            Administrator
                        </option>
                    @endif
                    <option value="client" {{ old('role', $user->isClient() ? 'client' : null) === 'client' ? 'selected' : '' }}>
                        Client
                    </option>
                    @foreach ($roles as $id => $name)
                        <option value="{{ $name }}" {{ old('role', $user->roles->first()->name ?? null) === $name ? 'selected' : '' }}>
                            {{ ucfirst($name) }}
                        </option>
                    @endforeach
                </x-admin::select>
                <x-admin::select name="status" label="{{ __('common.status') }}" required>
                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="closed" {{ old('status', $user->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                </x-admin::select>
                {{-- TODO: List currency from data and add old value --}}
                <x-admin::select name="currency" label="{{ __('common.currency') }}" required>
                    <option value="USD" {{ old('currency', $user->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="IDR" {{ old('currency', $user->currency) === 'IDR' ? 'selected' : '' }}>IDR</option>
                </x-admin::select>
                <x-admin::select name="language" label="{{ __('common.language') }}" required>
                    @foreach ($langs as $lang)
                        <option value="{{ $lang['lang'] }}"
                            {{ old('language', $user->language) == $lang['lang'] ? 'selected' : '' }}>
                            {{ $lang['name'] }}
                        </option>
                    @endforeach
                </x-admin::select>
            </div>
        </div>
        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover ml-auto px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('common.save') }}</button>
    </form>
</div>
@endsection