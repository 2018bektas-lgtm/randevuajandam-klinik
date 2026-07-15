<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.layouts.partials.head')
</head>
@php
    $nav = site_nav(is_array($doktor ?? null) ? $doktor : null);
@endphp
{{-- Sıcak: modern iskelet + sicak CSS --}}
<body class="theme-sicak layout-sicak theme-pack-sicak layout-modern">
    @include('frontend.layouts.partials.tracking-body')
    @include('frontend.themes.modern.layouts.partials.header', ['doktor' => $doktor ?? [], 'nav' => $nav])

    <main class="site-main theme-main">
        @yield('icerik')
    </main>

    @include('frontend.themes.modern.layouts.partials.footer', ['doktor' => $doktor ?? [], 'nav' => $nav])
    @include('frontend.layouts.partials.script', ['doktor' => $doktor ?? []])
</body>
</html>
