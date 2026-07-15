<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.themes.klasik.layouts.partials.head')
</head>
@php
    $bodyTema = current_theme_id($doktor ?? null);
    $nav = site_nav(is_array($doktor ?? null) ? $doktor : null);
@endphp
<body class="theme-{{ $bodyTema }} layout-klasik theme-pack-klasik">
    @include('frontend.layouts.partials.tracking-body')
    @include('frontend.themes.klasik.layouts.partials.header', ['doktor' => $doktor ?? [], 'nav' => $nav])

    <main class="site-main theme-main">
        @yield('icerik')
    </main>

    @include('frontend.themes.klasik.layouts.partials.footer', ['doktor' => $doktor ?? [], 'nav' => $nav])
    @include('frontend.themes.klasik.layouts.partials.script', ['doktor' => $doktor ?? []])
</body>
</html>
