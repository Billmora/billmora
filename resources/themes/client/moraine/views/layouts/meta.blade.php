<!-- Prevent indexing -->
<meta name="robots" content="noindex, nofollow">
{{-- Meta --}}
<link rel="icon" href="https://media.billmora.com/logo/main-bgnone.svg">
<title>@yield('title', 'Client Area') | {{ Billmora::getGeneral('company_name') }}</title>
<!-- Styles -->
<link rel="stylesheet" href="{{ $clientTheme['assets'] }}/css/style.css">
<script src="{{ $clientTheme['assets'] }}/js/app.js" type="module"></script>
@livewireStyles