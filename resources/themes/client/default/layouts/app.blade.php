<!DOCTYPE html>
<html lang="en">
<head>
    @include('client::layouts.wrapper')
</head>
<body>
    @include('client::layouts.partials.header')

    <main>
        @yield('body')
    </main>

    @include('client::layouts.partials.footer')
    @include('client::layouts.script')
    @livewireScripts
</body>
</html>
