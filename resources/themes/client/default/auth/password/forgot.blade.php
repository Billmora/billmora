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
    <div class="alert alert-danger">
      <p>{{ session('error') }}</p>
    </div>
    @endif
    <form action="{{ route('client.password.forgot.store') }}" method="POST">
      @csrf
      <div class="card">
        <div class="header">
            <h2>{{ __('auth.forgot_password_title') }}</h2>
        </div>
        <div class="body">
          <div class="form-group">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <input name="email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
            @error('email')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary form-button">{{ __('auth.send_request') }}</button>
        </div>
        <div class="footer">
          <div class="form-group">
            <p>{{ __('auth.remembered_password') }} <a href="/auth/login" class="form-label">{{ __('auth.sign_in') }}</a></p>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection