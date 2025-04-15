@extends('client::layouts.app')

@section('body')
<div class="container">
  <div class="auth login">
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
    <form action="{{ route('client.login.store') }}" method="POST">
      @csrf
      <div class="card">
        <div class="header">
            <h2>{{ __('auth.login_title') }}</h2>
        </div>
        <div class="body">
          <div class="form-group">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <input name="email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
            @error('email')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
          <div class="form-group">
            <div class="form-label-group">
              <label for="password" class="form-label">{{ __('auth.password') }}</label>
              <a href="/auth/password" class="form-label">{{ __('auth.forgot_password') }}</a>
            </div>
            <input name="password" id="password" type="password" class="form-control @error('password') is-invalid @enderror">
            @error('password')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary form-button">{{ __('auth.sign_in') }}</button>
        </div>
        <div class="footer">
          <div class="form-group">
            <p>{{ __('auth.dont_have_account') }} <a href="/auth/register" class="form-label">{{ __('auth.sign_up') }}</a></p>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection