@extends('client::layouts.app')

@section('body')
@if(session('success'))
  <div class="w-full flex mx-auto mb-6">
    <x-client::alert variant="success" icon="lucide-badge-check" description="{{ session('success') }}" />
  </div>
@endif
@if(session('error'))
  <div class="w-full flex mx-auto mb-6">
    <x-client::alert variant="danger" icon="lucide-triangle-alert" description="{{ session('error') }}" />
  </div>
@endif
<div class="flex flex-col md:flex-row gap-4">
  <div class="w-full md:w-[28rem] h-fit bg-billmora-2 p-6 rounded-lg border-3 border-billmora-3">
    <div class="flex flex-col gap-4 text-center">
      <img src="{{ $user->avatar }}" alt="user avatar" class="w-30 h-auto mx-auto rounded-full">
      <div class="flex flex-col">
        <span class="text-xl font-semibold text-slate-600">{{ $user->name }}</span>
        <span class="text-slate-500">{{ $user->email }}</span> 
      </div>
    </div>
    <x-client::link variant="primary" href="https://gravatar.com" target="_blank" icon="lucide-external-link" class="w-full justify-center mt-4 mx-auto font-semibold">Change Avatar</x-client::link>
  </div>
  <form action="{{ route('client.account.detail.update') }}" method="POST" class="w-full h-auto bg-billmora-2 p-6 rounded-lg border-3 border-billmora-3">
  @csrf
  <div class="flex flex-col gap-2">
    <h3 class="text-lg text-slate-600 font-bold">{{ __('client.personal_information') }}</h3>
    <div class="w-full flex flex-col md:flex-row gap-4">
      <x-client::input type="text" name="first_name" label="{{ __('client.first_name') }}" value="{{ old('first_name', $user->first_name) }}" required/>
      <x-client::input type="text" name="last_name" label="{{ __('client.last_name') }}" value="{{ old('last_name', $user->last_name) }}" required/>
    </div>
    <div class="w-full flex flex-col md:flex-row gap-4">
      <x-client::input type="email" name="email" label="{{ __('client.email') }}" value="{{ $user->email }}" required disabled/>
      @if (!Billmora::hasAuth('form_disable', 'phone_number'))
        <x-client::input type="tel" name="phone_number" label="{{ __('client.phone_number') }}" value="{{ old('phone_number', $user->billing->phone_number) }}" :required="Billmora::hasAuth('form_required', 'phone_number')"/>
      @endif
    </div>
    <h3 class="text-lg text-slate-600 font-bold mt-4">{{ __('client.billing_information') }}</h3>
    <div class="w-full flex flex-col md:flex-row gap-4">
      @if (!Billmora::hasAuth('form_disable', 'company_name'))
        <x-client::input type="text" name="company_name" label="{{ __('client.company_name') }}" value="{{ old('company_name', $user->billing->company_name) }}" :required="Billmora::hasAuth('form_required', 'company_name')"/>
      @endif
      @if (!Billmora::hasAuth('form_disable', 'street_address_1'))
        <x-client::input type="text" name="street_address_1" label="{{ __('client.street_address_1') }}" value="{{ old('street_address_1', $user->billing->street_address_1) }}" :required="Billmora::hasAuth('form_required', 'street_address_1')"/>
      @endif
    </div>
    <div class="w-full flex flex-col md:flex-row gap-4">
      @if (!Billmora::hasAuth('form_disable', 'street_address_2'))
        <x-client::input type="text" name="street_address_2" label="{{ __('client.street_address_2') }}" value="{{ old('street_address_2', $user->billing->street_address_2) }}" :required="Billmora::hasAuth('form_required', 'street_address_2')"/>
      @endif
      @if (!Billmora::hasAuth('form_disable', 'city'))
        <x-client::input type="text" name="city" label="{{ __('client.city') }}" value="{{ old('city', $user->billing->city) }}" :required="Billmora::hasAuth('form_required', 'city')"/>
      @endif
    </div>
    <div class="w-full flex flex-col md:flex-row gap-4">
      @if (!Billmora::hasAuth('form_disable', 'country'))
      <x-client::select name="country" label="{{ __('client.country') }}" value="{{ old('country', $user->billing->country) }}" :required="Billmora::hasAuth('form_required', 'country')">
        @foreach (config('utils.countries') as $code => $name)
          <option value="{{ $code }}" {{ old('country', $user->billing->country) == $code ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
      </x-client::select>
      @endif
      <div class="w-full flex flex-col md:flex-row gap-4">
        @if (!Billmora::hasAuth('form_disable', 'state'))
          <x-client::input type="text" name="state" label="{{ __('client.state') }}" value="{{ old('state', $user->billing->state) }}" :required="Billmora::hasAuth('form_required', 'state')"/>
        @endif
        @if (!Billmora::hasAuth('form_disable', 'postcode'))
          <x-client::input type="text" name="postcode" label="{{ __('client.postcode') }}" value="{{ old('postcode', $user->billing->postcode) }}" :required="Billmora::hasAuth('form_required', 'postcode')"/>
        @endif
      </div>
    </div>
  </div>
  <x-client::button type="submit" class="ml-auto mt-6 font-semibold">{{ __('common.save') }}</x-client::button>
</form>
</div>
@endsection