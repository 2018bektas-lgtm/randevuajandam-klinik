@php
    $seoTr = is_array($doktor['seo'] ?? null) ? $doktor['seo'] : [];
    $gtm = preg_replace('/[^A-Za-z0-9\-]/', '', (string) ($seoTr['gtm_container_id'] ?? ''));
@endphp
@if($gtm !== '')
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
