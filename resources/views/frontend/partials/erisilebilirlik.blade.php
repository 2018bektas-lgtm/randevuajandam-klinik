{{--
    Erişilebilirlik: içeriğe atlama bağlantısı + görünür odak halkası.

    NEDEN:
      - Skip-link: klavyeyle gezen kullanıcı her sayfada tüm menüyü tek tek
        geçmek zorunda kalıyordu (WCAG 2.4.1 "Bypass Blocks").
      - :focus-visible: projede neredeyse hiç tanımlı değildi; klavye
        kullanıcısı odağın nerede olduğunu göremiyordu (WCAG 2.4.7).

    Skip-link yalnızca odaklandığında görünür; fare kullanıcısını etkilemez.
    Hedef `#ana-icerik`, layout'taki ana içerik konteynerine verilir.
--}}
<a class="ra-skip-link" href="#ana-icerik">İçeriğe atla</a>

<style>
.ra-skip-link {
    position: absolute;
    left: -9999px;
    top: 0;
    z-index: 100000;
    padding: 0.75rem 1.25rem;
    background: var(--primary-color, #1a1a1a);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    border-radius: 0 0 0.5rem 0;
}
.ra-skip-link:focus {
    left: 0;
    outline: 3px solid var(--accent-color, #9B9A84);
    outline-offset: 2px;
    color: #fff;
}

/* Klavye odagi her zaman gorunur olsun (fare tiklamasinda gosterilmez) */
a:focus-visible,
button:focus-visible,
input:focus-visible,
select:focus-visible,
textarea:focus-visible,
summary:focus-visible,
[tabindex]:focus-visible {
    outline: 3px solid var(--accent-color, #9B9A84);
    outline-offset: 2px;
    border-radius: 2px;
}

/* Odak hedefi olarak kullanilan ana icerik konteyneri odak halkasi almasin */
#ana-icerik:focus {
    outline: none;
}

/* ---------------------------------------------------------------------
   P3-6: Hareket azaltma tercihi.
   Projede 81 blade `wow fadeInUp` kullaniyor; ayrica GSAP/SplitText/
   ScrollTrigger/SmoothScroll/magiccursor aktif. Vestibuler rahatsizligi olan
   kullanicilar icin bunlar sorunlu — saglik alanindaki bir uruntte daha da
   onemli. Tercih belirtilmisse tum animasyon ve gecisler etkisiz hale gelir.
   --------------------------------------------------------------------- */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.001ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.001ms !important;
        scroll-behavior: auto !important;
    }

    /* Animasyonla gorunur hale gelen ogeler gizli kalmasin */
    .wow,
    .reveal,
    .image-anime,
    .text-anime-style-2 {
        visibility: visible !important;
        opacity: 1 !important;
        transform: none !important;
        clip-path: none !important;
    }
}

/* ---------------------------------------------------------------------
   P3-7: Ozel imlec (magiccursor) yalnizca gercek isaretci cihazlarda.
   Dokunmatik ekranda islevsiz; isaretci hassasiyeti dusuk kullanicilarda
   gercek imleci gizlemesi risk olusturuyordu.
   --------------------------------------------------------------------- */
@media (hover: none), (pointer: coarse) {
    .custom-cursor__cursor,
    .custom-cursor__cursor-two {
        display: none !important;
    }

    body.custom-cursor {
        cursor: auto !important;
    }

    body.custom-cursor a,
    body.custom-cursor button {
        cursor: pointer !important;
    }
}
</style>
