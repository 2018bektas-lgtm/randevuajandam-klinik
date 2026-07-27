@php
    $dg = rtrim(asset('themes/delogis'), '/');
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&family=Castoro:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ $dg }}/vendors/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/animate/animate.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/animate/custom-animate.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/fontawesome/css/all.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/jarallax/jarallax.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/jquery-magnific-popup/jquery.magnific-popup.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/nouislider/nouislider.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/nouislider/nouislider.pips.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/odometer/odometer.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/swiper/swiper.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/delogis-icons/style.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/tiny-slider/tiny-slider.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/reey-font/stylesheet.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/alagambe-font/stylesheet.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/owl-carousel/owl.carousel.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/owl-carousel/owl.theme.default.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/bxslider/jquery.bxslider.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/bootstrap-select/css/bootstrap-select.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/vegas/vegas.min.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/jquery-ui/jquery-ui.css">
<link rel="stylesheet" href="{{ $dg }}/vendors/timepicker/timePicker.css">
<link rel="stylesheet" href="{{ $dg }}/css/delogis.css">
<link rel="stylesheet" href="{{ $dg }}/css/delogis-color-1.css">
{{-- İç sayfa / randevu wizard (modern bileşenler) uyumu --}}
<link rel="stylesheet" href="{{ asset('css/themes/modern.css') }}?v=dg1">
<style>
/* Delogis + modern: brand butonları */
body.theme-delogis {
  --mp-blue: var(--delogis-base, #B9905D);
  --mp-blue-dark: var(--brand-600, #9a7549);
}
body.theme-delogis .mp-btn-primary,
body.theme-delogis .mp-btn.mp-btn-primary {
  background: var(--delogis-base, #B9905D) !important;
  border-color: var(--delogis-base, #B9905D) !important;
}
body.theme-delogis .mp-page-hero {
  background: linear-gradient(135deg, #384E5C 0%, #293B46 100%);
  color: #fff;
}
body.theme-delogis .mp-page-hero a { color: #f5e6d3; }
body.theme-delogis .mp-topbar,
body.theme-delogis .mp-header { display: none !important; }
body.theme-delogis .mp-footer { display: none !important; }
</style>
