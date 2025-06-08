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
    <form action="{{ route('client.password.reset.store') }}" method="POST">
      @csrf
      <div class="card">
        <div class="header">
            <h2>{{ __('auth.reset_password_title') }}</h2>
        </div>
        <div class="body">
          <input name="token" id="token" type="text" value="{{ $token }}" hidden>
          <div class="form-group">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <input name="email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ $email }}" readonly>
            @error('email')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
          <div class="form-group">
            <label for="password" class="form-label">{{ __('auth.password') }}<span class="text-danger">*</span></label>
            <input name="password" id="password" type="password" class="form-control @error('password') is-invalid @enderror">
            @error('password')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
          <div class="form-group">
            <label for="password_confirmation" class="form-label">{{ __('auth.password_confirmation') }}<span class="text-danger">*</span></label>
            <input name="password_confirmation" id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror">
            @error('password_confirmation')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary form-button">{{ __('auth.confirm_update') }}</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection