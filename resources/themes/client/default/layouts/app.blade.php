<!DOCTYPE html>
<html lang="en">
 @include('client::layouts.wrapper')
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