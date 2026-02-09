<!-- Prevent indexing -->
<meta name="robots" content="noindex, nofollow">
{{-- Meta --}}
<link rel="icon" href="https://media.billmora.com/logo/main-bgnone.svg">
<title>@yield('title', 'Client Area') | {{ Billmora::getGeneral('company_name') }}</title>
<!-- Styles -->
<link rel="stylesheet" href="{{ $clientTheme['assets'] }}/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="{{ $clientTheme['assets'] }}/js/app.js" type="module"></script>
@livewireStyles