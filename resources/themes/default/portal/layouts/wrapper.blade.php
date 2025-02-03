<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @yield('meta')
    <meta property="og:site_name" content="{{ Config::setting('company_name') }}">
    <meta property="og:image" content="{{ Config::setting('company_logo') }}">
    <link rel="shortcut icon" href="{{ Config::setting('company_logo') }}">
    <link rel="stylesheet" href="{{ $theme['assets'] }}/css/style.css">
    <link rel="stylesheet" href="{{ $theme['assets'] }}/css/header.css">
    <link rel="stylesheet" href="{{ $theme['assets'] }}/css/footer.css">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css@4.1.7/css/flag-icons.min.css"> --}}
    @livewireStyles
</head>