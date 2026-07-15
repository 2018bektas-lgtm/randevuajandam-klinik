<!DOCTYPE html>
<html lang="tr">
<head>
    @include('frontend.layouts.partials.head')
</head>
@php
    $bodyTema = current_theme_id($doktor ?? null);
    $nav = site_nav(is_array($doktor ?? null) ? $doktor : null);
@endphp
<body class="theme-{{ $bodyTema }} layout-{{ theme_pack_id($bodyTema) }}">
    @include('frontend.layouts.partials.tracking-body')
    @include(theme_view_name('layouts.partials.header', $bodyTema), ['doktor' => $doktor ?? [], 'nav' => $nav])
    <main class="site-main theme-main">@yield('icerik')</main>
    @include(theme_view_name('layouts.partials.footer', $bodyTema), ['doktor' => $doktor ?? [], 'nav' => $nav])
    @include(theme_view_name('layouts.partials.script', $bodyTema), ['doktor' => $doktor ?? []])
</body>
</html>
