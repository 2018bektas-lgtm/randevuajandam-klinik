<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.layouts.partials.head')
</head>
@php
    $nav = site_nav(is_array($doktor ?? null) ? $doktor : null);
@endphp
<body class="theme-ocean layout-ocean theme-pack-ocean">
    @include('frontend.partials.erisilebilirlik')

    @include('frontend.layouts.partials.tracking-body')
    @include('frontend.themes.ocean.layouts.partials.header', ['doktor' => $doktor ?? [], 'nav' => $nav])

    <main id="ana-icerik" tabindex="-1" class="site-main theme-main">
        @yield('icerik')
    </main>

    @include('frontend.themes.ocean.layouts.partials.footer', ['doktor' => $doktor ?? [], 'nav' => $nav])
    @include('frontend.layouts.partials.script', ['doktor' => $doktor ?? []])
</body>
</html>
