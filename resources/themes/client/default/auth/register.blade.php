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
          <div class="row">
            <div class="form-group col-lg-2">
              <label for="first_name" class="form-label">{{ __('auth.first_name') }} {{ __('auth.required_symbol') }}</label>
              <input name="first_name" id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
              @error('first_name')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group col-lg-2">
              <label for="last_name" class="form-label">{{ __('auth.last_name') }} {{ __('auth.required_symbol') }}</label>
              <input name="last_name" id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
              @error('last_name')
              <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="row">
            <div class="form-group col-lg-2">
              <label for="email" class="form-label">{{ __('auth.email') }} {{ __('auth.required_symbol') }}</label>
              <input name="email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
              @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            @if (!Billmora::hasAuth('form_disable', 'phone_number'))
              <div class="form-group col-lg-2">
                <label for="phone_number" class="form-label">
                  {{ __('auth.phone_number') }}
                  @if (Billmora::hasAuth('form_required', 'phone_number'))
                    <span class="text-danger">{{ __('auth.required_symbol') }}</span> 
                  @else
                    <span class="text-muted">{{ __('auth.optional_symbol') }}</span>
                  @endif
                </label>
                <input name="phone_number" id="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number') }}">
                @error('phone_number')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            @endif
          </div>
          @if (
            !Billmora::hasAuth('form_disable', 'company_name') ||
            !Billmora::hasAuth('form_disable', 'street_address_1') ||
            !Billmora::hasAuth('form_disable', 'street_address_2') ||
            !Billmora::hasAuth('form_disable', 'city') ||
            !Billmora::hasAuth('form_disable', 'country') ||
            !Billmora::hasAuth('form_disable', 'state') ||
            !Billmora::hasAuth('form_disable', 'postcode')
          )
            <h3>{{ __('auth.billing_information') }}</h3>
            <div class="row">
              @foreach(['company_name', 'street_address_1', 'street_address_2', 'city'] as $field)
                @if (!Billmora::hasAuth('form_disable', $field))
                  <div class="form-group col-lg-2">
                    <label for="{{ $field }}" class="form-label">
                      {{ __('auth.' . $field) }}
                      @if (Billmora::hasAuth('form_required', $field))
                        <span class="text-danger">*</span> 
                      @else
                        <span class="text-muted">(Optional)</span>
                      @endif
                    </label>
                    <input name="{{ $field }}" id="{{ $field }}" type="text" class="form-control @error($field) is-invalid @enderror" value="{{ old($field) }}">
                    @error($field)
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>
                @endif
              @endforeach
              @if (!Billmora::hasAuth('form_disable', 'country'))
                <div class="form-group col-lg-2">
                  <label for="country" class="form-label">
                    {{ __('auth.country') }}
                    @if (Billmora::hasAuth('form_required', 'country'))
                      <span class="text-danger">{{ __('auth.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('auth.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="country" id="country" type="text" class="form-control @error('country') is-invalid @enderror" value="{{ old('country') }}">
                  @error('country')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              @endif
            </div>
            <div class="row">
              @if (!Billmora::hasAuth('form_disable', 'state'))
                <div class="form-group col-lg-2">
                  <label for="state" class="form-label">
                    {{ __('auth.state') }}
                    @if (Billmora::hasAuth('form_required', 'state'))
                      <span class="text-danger">{{ __('auth.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('auth.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="state" id="state" type="text" class="form-control @error('state') is-invalid @enderror" value="{{ old('state') }}">
                  @error('state')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              @endif
              @if (!Billmora::hasAuth('form_disable', 'postcode'))
                <div class="form-group col-lg-2">
                  <label for="postcode" class="form-label">
                    {{ __('auth.postcode') }}
                    @if (Billmora::hasAuth('form_required', 'postcode'))
                      <span class="text-danger">{{ __('auth.required_symbol') }}</span> 
                    @else
                      <span class="text-muted">{{ __('auth.optional_symbol') }}</span>
                    @endif
                  </label>
                  <input name="postcode" id="postcode" type="text" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode') }}">
                  @error('postcode')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
              @endif
            </div>
          @endif
          <h3>{{ __('auth.account_security') }}</h3>
          <div class="row">
            <div class="form-group col-lg-2">
              <label for="password" class="form-label">{{ __('auth.password') }}<span class="text-danger">*</span></label>
              <input name="password" id="password" type="password" class="form-control @error('password') is-invalid @enderror">
              @error('password')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group col-lg-2">
              <label for="password_confirmation" class="form-label">{{ __('auth.password_confirmation') }}<span class="text-danger">*</span></label>
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