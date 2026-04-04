<!-- Prevent indexing -->
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- Meta --}}
<link rel="icon" href="{{ Billmora::getGeneral('company_favicon') }}">
<title>@yield('title', 'Admin') | {{ Billmora::getGeneral('company_name') }}</title>
<!-- Styles -->
<link rel="stylesheet" href="{{ $adminTheme['assets'] }}/css/style.css">
<script src="{{ $adminTheme['assets'] }}/js/app.js" type="module"></script>
{{-- Additional Styles --}}
<link href='https://cdn.jsdelivr.net/npm/froala-editor@latest/css/froala_editor.pkgd.min.css' rel='stylesheet'
    type='text/css' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
@livewireStyles