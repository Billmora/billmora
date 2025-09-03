<!-- Prevent indexing -->
<meta name="robots" content="noindex, nofollow">
{{-- Meta --}}
<link rel="icon" href="https://media.billmora.com/logo/main-bgnone.svg">
<title>@yield('title', 'Admin') | {{ Billmora::getGeneral('company_name') }}</title>
<!-- Styles -->
<link rel="stylesheet" href="{{ $adminTheme['assets'] }}/css/style.css">
<script src="{{ $adminTheme['assets'] }}/js/app.js" type="module"></script>
{{-- Frola Editor --}}
<link href='https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_editor.pkgd.min.css' rel='stylesheet' type='text/css' />
@livewireStyles