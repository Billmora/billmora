@extends('client::layouts.app')

@section('body')
@if(session('error'))
  <div class="max-w-[40rem] flex mx-auto mb-6">
    <x-client::alert variant="danger" icon="lucide-triangle-alert" description="{{ session('error') }}" />
  </div>
@endif
<div class="max-w-[46rem] flex m-auto">
  <form action="{{ route('client.register.store') }}" method="POST" class="flex flex-col bg-billmora-2 w-full p-6 rounded-xl border-4 border-billmora-3">
    @csrf
    <h2 class="text-2xl text-center text-slate-700 font-bold mb-6">{{ __('auth.register_title') }}</h2>
    <div class="flex flex-col gap-2">
      <h3 class="text-lg text-slate-600 font-bold">{{ __('client.personal_information') }}</h3>
      <div class="w-full flex flex-col md:flex-row gap-4">
        <x-client::input type="text" name="first_name" label="{{ __('client.first_name') }}" required/>
        <x-client::input type="text" name="last_name" label="{{ __('client.last_name') }}" required/>
      </div>
      <div class="w-full flex flex-col md:flex-row gap-4">
        <x-client::input type="email" name="email" label="{{ __('client.email') }}" required/>
        @if (!Billmora::hasAuth('form_disable', 'phone_number'))
          <x-client::input type="tel" name="phone_number" label="{{ __('client.phone_number') }}" :required="Billmora::hasAuth('form_required', 'phone_number')"/>
        @endif
      </div>
      <h3 class="text-lg text-slate-600 font-bold mt-4">{{ __('client.billing_information') }}</h3>
      <div class="w-full flex flex-col md:flex-row gap-4">
        @if (!Billmora::hasAuth('form_disable', 'company_name'))
          <x-client::input type="text" name="company_name" label="{{ __('client.company_name') }}" :required="Billmora::hasAuth('form_required', 'company_name')"/>
        @endif
        @if (!Billmora::hasAuth('form_disable', 'street_address_1'))
          <x-client::input type="text" name="street_address_1" label="{{ __('client.street_address_1') }}" :required="Billmora::hasAuth('form_required', 'street_address_1')"/>
        @endif
      </div>
      <div class="w-full flex flex-col md:flex-row gap-4">
        @if (!Billmora::hasAuth('form_disable', 'street_address_2'))
          <x-client::input type="text" name="street_address_2" label="{{ __('client.street_address_2') }}" :required="Billmora::hasAuth('form_required', 'street_address_2')"/>
        @endif
        @if (!Billmora::hasAuth('form_disable', 'city'))
          <x-client::input type="text" name="city" label="{{ __('client.city') }}" :required="Billmora::hasAuth('form_required', 'city')"/>
        @endif
      </div>
      <div class="w-full flex flex-col md:flex-row gap-4">
        @if (!Billmora::hasAuth('form_disable', 'country'))
        <x-client::select name="country" label="{{ __('client.country') }}" :required="Billmora::hasAuth('form_required', 'country')">
          @foreach (config('utils.countries') as $code => $name)
            <option value="{{ $code }}" {{ old('country') == $code ? 'selected' : '' }}>{{ $name }}</option>
          @endforeach
        </x-client::select>
        @endif
        <div class="w-full flex flex-col md:flex-row gap-4">
          @if (!Billmora::hasAuth('form_disable', 'state'))
            <x-client::input type="text" name="state" label="{{ __('client.state') }}" :required="Billmora::hasAuth('form_required', 'state')"/>
          @endif
          @if (!Billmora::hasAuth('form_disable', 'postcode'))
            <x-client::input type="text" name="postcode" label="{{ __('client.postcode') }}" :required="Billmora::hasAuth('form_required', 'postcode')"/>
          @endif
        </div>
      </div>
      <h3 class="text-lg text-slate-600 font-bold mt-4">{{ __('client.account_security') }}</h3>
      <div class="w-full flex flex-col md:flex-row gap-4">
        <x-client::input type="password" name="password" label="{{ __('client.password') }}" required/>
        <x-client::input type="password" name="password_confirmation" label="{{ __('client.confirm_password') }}" required/>
      </div>
    </div>
    <x-client::captcha form="user_register" class="mt-8 mx-auto"/>
    <x-client::button type="submit" class="justify-center mt-6 font-semibold">{{ __('common.sign_up') }}</x-client::button>
    <p class="flex gap-1 text-slate-700 mt-4">{{ __('auth.have_account') }} <x-client::link href="/auth/login" class="font-semibold">{{ __('common.sign_in') }}</x-client::link></p>
  </form>
</div>
@endsection