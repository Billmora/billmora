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
      <x-client.breadcrumb title="{{ __('client.account_security') }}" route="{{ route('client.account.security') }}" />
      <div class="account">
        <form action="" class="card w-full h-full">
          <div class="header">
            <h3>{{ __('client.update_email') }}</h3>
          </div>
          <div class="body">
            <div class="form-group">
              <label for="email" class="form-label">{{ __('client.email') }}</label>
              <input name="email" id="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
              @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="confirm_password" class="form-label">{{ __('client.confirm_password') }}</label>
              <input name="confirm_password" id="confirm_password" type="password" class="form-control @error('confirm_password') is-invalid @enderror">
              @error('confirm_password')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="footer end">
            <button type="submit" class="btn btn-primary">{{ __('client.save_changes') }}</button>
          </div>
        </form>
        <form action="" class="card w-full h-full">
          <div class="header">
            <h3>{{ __('client.update_password') }}</h3>
          </div>
          <div class="body">
            <div class="form-group">
              <label for="current_password" class="form-label">{{ __('client.current_password') }}</label>
              <input name="current_password" id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror">
              @error('current_password')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="new_password" class="form-label">{{ __('client.new_password') }}</label>
              <input name="new_password" id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror">
              @error('new_password')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-group">
              <label for="confirm_new_password" class="form-label">{{ __('client.confirm_new_password') }}</label>
              <input name="confirm_new_password" id="confirm_new_password" type="password" class="form-control @error('confirm_new_password') is-invalid @enderror">
              @error('confirm_new_password')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="footer end">
            <button type="submit" class="btn btn-primary">{{ __('client.save_changes') }}</button>
          </div>
        </form>
        <form action="" class="card w-full h-full">
          <div class="header">
            <h3>{{ __('client.2fa_title') }}</h3>
          </div>
          <div class="body">
            {{ __('client.2fa_description') }}
          </div>
          <div class="footer end">
            {{-- progress --}}
          </div>
        </form>
      </div>
    </div>  
  </section>
@endsection