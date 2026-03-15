{{-- Meta --}}
<link rel="icon" href="{{ Billmora::getGeneral('company_favicon') }}">
<title>@yield('title', Billmora::getGeneral('company_name'))</title>
<!-- Styles -->
@php
    $themeCssVariables = '';
    if (!empty($clientThemeConfig)) {
        foreach ($clientThemeConfig as $key => $value) {
            if (str_starts_with($key, 'billmora_') && !empty($value)) {
                $cssVarName = str_replace('_', '-', $key);
                $themeCssVariables .= "--{$cssVarName}: {$value} !important;\n\t\t";
            }
        }
    }
@endphp
@if($themeCssVariables !== '')
<style>
    :root {
        {!! $themeCssVariables !!}
    }
</style>
@endif
<link rel="stylesheet" href="{{ $portalTheme['assets'] }}/css/style.css">
<script src="{{ $portalTheme['assets'] }}/js/app.js" type="module"></script>
{{-- Additional Styles --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />