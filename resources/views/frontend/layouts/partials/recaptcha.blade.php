@php
    $rc = app(\App\Services\RecaptchaService::class);
    $rcKey = $rc->siteKey();
    $rcOn = $rc->isEnabled();
@endphp
@if($rcOn && $rcKey !== '')
<script src="https://www.google.com/recaptcha/api.js?render={{ $rcKey }}"></script>
<script>
window.raRecaptchaSiteKey = @json($rcKey);
window.raGetRecaptchaToken = function (action) {
    action = action || 'randevu';
    return new Promise(function (resolve) {
        if (typeof grecaptcha === 'undefined' || !window.raRecaptchaSiteKey) {
            resolve('');
            return;
        }
        grecaptcha.ready(function () {
            grecaptcha.execute(window.raRecaptchaSiteKey, { action: action })
                .then(function (t) { resolve(t || ''); })
                .catch(function () { resolve(''); });
        });
    });
};
</script>
@else
<script>
window.raRecaptchaSiteKey = '';
window.raGetRecaptchaToken = function () { return Promise.resolve(''); };
</script>
@endif
