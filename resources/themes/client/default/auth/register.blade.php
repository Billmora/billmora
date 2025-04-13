@extends('client::layouts.app')

@section('body')
<div class="container">
  <div class="auth register">
    @if(session('error'))
    <p class="alert alert-danger">
      {{ session('error') }}
    </p>
    @endif
    <form action="{{ route('client.register.store') }}" method="POST">
      @csrf
      <div class="card">
        <div class="header">
            <h2>{{ __('auth.register_title') }}</h2>
        </div>
        <div class="body">
          <h3>{{ __('auth.personal_information') }}</h3>
          <div class="col-lg-2">
            <div class="form-group">
              <label for="first_name" class="form-label">{{ __('auth.first_name') }}</label>
              <input name="first_name" id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
              @error('first_name')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="last_name" class="form-label">{{ __('auth.last_name') }}</label>
              <input name="last_name" id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
              @error('last_name')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="col-lg-2">
            <div class="form-group">
              <label for="email" class="form-label">{{ __('auth.email') }}</label>
              <input name="email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
              @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="phone_number" class="form-label">{{ __('auth.phone_number') }}</label>
              <input name="phone_number" id="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number') }}">
              @error('phone_number')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <h3>{{ __('auth.billing_information') }}</h3>
          <div class="col-lg-2">
            <div class="form-group">
              <label for="company_name" class="form-label">{{ __('auth.company_name') }}</label>
              <input name="company_name" id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}">
              @error('company_name')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="street_address_1" class="form-label">{{ __('auth.street_address_1') }}</label>
              <input name="street_address_1" id="street_address_1" type="text" class="form-control @error('street_address_1') is-invalid @enderror" value="{{ old('street_address_1') }}">
              @error('street_address_1')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="street_address_2" class="form-label">{{ __('auth.street_address_2') }}</label>
              <input name="street_address_2" id="street_address_2" type="text" class="form-control @error('street_address_2') is-invalid @enderror" value="{{ old('street_address_2') }}">
              @error('street_address_2')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="city" class="form-label">{{ __('auth.city') }}</label>
              <input name="city" id="city" type="text" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}">
              @error('city')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="country" class="form-label">{{ __('auth.country') }}</label>
              <input name="country" id="country" type="text" class="form-control @error('country') is-invalid @enderror" value="{{ old('country') }}">
              @error('country')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-lg-2">
              <div class="form-group">
                <label for="state" class="form-label">{{ __('auth.state') }}</label>
                <input name="state" id="state" type="text" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}">
                @error('state')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
              <div class="form-group">
                <label for="postcode" class="form-label">{{ __('auth.postcode') }}</label>
                <input name="postcode" id="postcode" type="text" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode') }}">
                @error('postcode')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>
          <h3>{{ __('auth.account_security') }}</h3>
          <div class="col-lg-2">
            <div class="form-group">
              <label for="password" class="form-label">{{ __('auth.password') }}</label>
              <input name="password" id="password" type="password" class="form-control @error('password') is-invalid @enderror">
              @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="password_confirmation" class="form-label">{{ __('auth.password_confirmation') }}</label>
              <input name="password_confirmation" id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror">
              @error('password_confirmation')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <button type="submit" class="btn btn-primary form-button">{{ __('auth.sign_up') }}</button>
        </div>
        <div class="footer">
          <div class="form-group">
            <p>{{ __('auth.have_account') }} <a href="/auth/login" class="form-label">{{ __('auth.sign_in') }}</a></p>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection