@extends('client::layouts.app')

@section('body')
  <section>
    <div class="container">
      @if(session('success'))
      <p class="alert alert-success">
        {{ session('success') }}
      </p>
      @endif
      @if(session('error'))
      <p class="alert alert-danger">
        {{ session('error') }}
      </p>
      @endif
      <x-client.breadcrumb title="Account Details" route="{{ route('client.account.detail') }}" />
      <div class="account">
        <div class="profile">
          <div class="header">
            <img class="avatar" src="{{ $user->avatar }}" alt="user avatar">
            <div class="info">
              <h2>{{ $user->name }}</h2>
              <p>{{ $user->billing->company_name }}</p>
            </div>
          </div>
          <div class="action">
            <a href="https://gravatar.com" target="_blank" class="btn btn-primary">
              <x-tabler-external-link/>
              {{ __('client.update_avatar') }}
            </a>
          </div>
        </div>
        <div class="detail">
          <form action="{{ route('client.account.detail.personal') }}" class="card" method="POST">
            @csrf
            <div class="header">
              <h3>{{ __('client.personal_information') }}</h3>
            </div>
            <div class="body">
              <div class="row">
                <div class="form-group col-lg-2">
                  <label for="first_name" class="form-label">{{ __('client.first_name') }} <span class="text-danger">{{ __('client.required_symbol') }}</span> </label>
                  <input name="first_name" id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name) }}">
                  @error('first_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group col-lg-2">
                  <label for="last_name" class="form-label">{{ __('client.last_name') }} <span class="text-danger">{{ __('client.required_symbol') }}</span> </label>
                  <input name="last_name" id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $user->last_name) }}">
                  @error('last_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row">
                <div class="form-group col-lg-2">
                  <label for="email" class="form-label">{{ __('client.email') }} <span class="text-danger">{{ __('client.required_symbol') }}</span> </label>
                  <input name="email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" disabled>
                </div>
                <div class="form-group col-lg-2">
                  <label for="phone_number" class="form-label">
                    {{ __('client.phone_number') }}
                    @if (Billmora::hasAuth('form_required', 'phone_number'))
                      <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="phone_number" id="phone_number" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone_number', $user->billing->phone_number) }}">
                  @error('phone_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
            <div class="footer end">
              <button type="submit" class="btn btn-primary">
                {{ __('client.save_changes') }}
              </button>
            </div>
          </form>
          <form action="{{ route('client.account.detail.billing') }}" class="card" method="POST">
            @csrf
            <div class="header">
              <h3>{{ __('client.billing_information') }}</h3>
            </div>
            <div class="body">
              <div class="row">
                <div class="form-group col-lg-2">
                  <label for="company_name" class="form-label">
                  {{ __('client.company_name') }}
                  @if (Billmora::hasAuth('form_required', 'company_name'))
                    <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                  @else
                    <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                  @endif
                  </label>
                  <input name="company_name" id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $user->billing->company_name) }}">
                  @error('company_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group col-lg-2">
                  <label for="street_address_1" class="form-label">
                    {{ __('client.street_address_1') }}
                    @if (Billmora::hasAuth('form_required', 'street_address_1'))
                      <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="street_address_1" id="street_address_1" type="text" class="form-control @error('street_address_1') is-invalid @enderror" value="{{ old('street_address_1', $user->billing->street_address_1) }}">
                  @error('street_address_1')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="row">
                <div class="form-group col-lg-2">
                  <label for="street_address_2" class="form-label">
                    {{ __('client.street_address_2') }}
                    @if (Billmora::hasAuth('form_required', 'street_address_2'))
                      <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="street_address_2" id="street_address_2" type="text" class="form-control @error('street_address_2') is-invalid @enderror" value="{{ old('street_address_2', $user->billing->street_address_2) }}">
                  @error('street_address_2')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group col-lg-2">
                  <label for="city" class="form-label">
                    {{ __('client.city') }}
                    @if (Billmora::hasAuth('form_required', 'city'))
                      <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="city" id="city" type="text" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $user->billing->city) }}">
                  @error('city')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
              <div class="form-group col-lg-2">
                <label for="country" class="form-label">
                  {{ __('client.country') }}
                  @if (Billmora::hasAuth('form_required', 'country'))
                    <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                  @else
                    <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                  @endif
                </label>
                <select name="country" id="country" class="form-control @error('country') is-invalid @enderror">
                  @foreach (config('utils.countries') as $code => $name)
                    <option value="{{ $code }}" {{ old('country', $user->billing->country) == $code ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
                @error('country')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              <div class="row">
                <div class="form-group col-lg-2">
                  <label for="state" class="form-label">
                    {{ __('client.state') }}
                    @if (Billmora::hasAuth('form_required', 'state'))
                    <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                    @else
                    <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="state" id="state" type="text" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $user->billing->state) }}">
                  @error('state')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group col-lg-2">
                  <label for="postcode" class="form-label">
                    {{ __('client.postcode') }}
                    @if (Billmora::hasAuth('form_required', 'postcode'))
                      <span class="text-danger">{{ __('client.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('client.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="postcode" id="postcode" type="text" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode', $user->billing->postcode) }}">
                  @error('postcode')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              </div>
            </div>
            <div class="footer end">
              <button type="submit" class="btn btn-primary">
                {{ __('client.save_changes') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>  
  </section>
@endsection