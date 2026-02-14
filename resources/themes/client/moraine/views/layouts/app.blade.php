<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @include('client::layouts.meta')
</head>
<body class="bg-billmora-1">
  <div class="flex flex-row gap-5">
    <!-- Sidebar -->
    @include('client::layouts.partials.sidebar')

    <!-- Main -->
    <div class="w-full flex flex-col gap-5 min-h-dvh p-5 xl:pl-0">
      {{-- Header --}}
      @include('client::layouts.partials.header')

      {{-- Alert --}}
      @if (session('success'))
        <x-client::alert variant="success" title="{{ session('success') }}" />
      @endif
      @if (session('warning'))
          <x-client::alert variant="warning" title="{{ session('warning') }}" />
      @endif
      @if (session('error'))
          <x-client::alert variant="danger" title="{{ session('error') }}" />
      @endif
      <!-- Content -->
      <main>
        @yield('body')
      </main>

      {{-- Footer --}}
      @include('client::layouts.partials.footer')

    </div>
  </div>
  {{-- Scripts --}}
  @include('client::layouts.script')
  @livewireScripts
</body>
</html>