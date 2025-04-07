<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @yield('meta')
    <meta property="og:site_name" content="{{ Billmora::getGeneral('company_name') }}">
    <meta property="og:image" content="{{ Billmora::getGeneral('company_logo') }}">
    <link rel="shortcut icon" href="{{ Billmora::getGeneral('company_favicon') }}">
    <link rel="stylesheet" href="{{ $clientTheme['assets'] }}/css/style.css">
    <link rel="stylesheet" href="{{ $clientTheme['assets'] }}/css/header.css">
    <link rel="stylesheet" href="{{ $clientTheme['assets'] }}/css/footer.css">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css@4.1.7/css/flag-icons.min.css"> --}}
    @livewireStyles
</head>