<!DOCTYPE html>
<html lang="tr">
<head>
    {{-- Ortak head (SEO/font/css); tema CSS body class ile --}}
    @include('frontend.layouts.partials.head')
</head>
@php
    $bodyTema = 'modern';
    $nav = site_nav(is_array($doktor ?? null) ? $doktor : null);
@endphp
<body class="theme-modern layout-modern theme-pack-modern">
    @include('frontend.layouts.partials.tracking-body')
    @include('frontend.themes.modern.layouts.partials.header', ['doktor' => $doktor ?? [], 'nav' => $nav])

    <main class="site-main theme-main">
        @yield('icerik')
    </main>

    @include('frontend.themes.modern.layouts.partials.footer', ['doktor' => $doktor ?? [], 'nav' => $nav])
    @include('frontend.layouts.partials.script', ['doktor' => $doktor ?? []])
</body>
</html>
