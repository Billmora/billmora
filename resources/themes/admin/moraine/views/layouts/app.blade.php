<!DOCTYPE html>
<html lang="{{ explode('_', $langActive['lang'])[0] }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @include('admin::layouts.meta')
</head>
<body class="bg-billmora-1">
  <div class="flex flex-row gap-5">
    <!-- Sidebar -->
    @include('admin::layouts.partials.sidebar')

    <!-- Main -->
    <div class="w-full flex flex-col gap-5 min-h-dvh p-5 xl:pl-0">
      {{-- Header --}}
      @include('admin::layouts.partials.header')

      {{-- Alert --}}
      @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
      @endif
      @if (session('warning'))
          <x-admin::alert variant="warning" title="{{ session('warning') }}" />
      @endif
      @if (session('error'))
          <x-admin::alert variant="danger" title="{{ session('error') }}" />
      @endif
      <!-- Content -->
      <main>
        @yield('body')
      </main>

      {{-- Footer --}}
      @include('admin::layouts.partials.footer')

    </div>
  </div>
  <x-admin::quick-search />
  {{-- Scripts --}}
  @include('admin::layouts.script')
  @livewireScripts
</body>
</html>