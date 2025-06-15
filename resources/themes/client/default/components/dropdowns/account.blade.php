@props(['dropdown_data'])

<div class="dropdown" id="{{ $dropdown_data }}">
  <div class="card">
    <h3>{{ auth()->user()->name }}</h3>
    <div class="divider-x"></div>
      <a href="/account/detail">Account Detail</a>
      <a href="/account/security">Account Security</a>
      <a href="/account/emails">Email History</a>
    <div class="divider-x"></div>
    @if (auth()->user()->is_admin)
      <a href="/admin">Admin</a>
    @endif
    <form action="{{ route('client.logout') }}" method="POST">
      @csrf
      <button type="submit" class="btn-logout">
        {{ __('auth.sign_out') }}
      </button>
    </form>
  </div>
</div>