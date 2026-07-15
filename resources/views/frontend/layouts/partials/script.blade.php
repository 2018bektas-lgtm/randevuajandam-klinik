<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
(function () {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Mobile menu
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    btn?.addEventListener('click', () => menu?.classList.toggle('open'));

    // Sticky header
    const header = document.getElementById('site-header');
    const onScroll = () => header?.classList.toggle('is-scrolled', window.scrollY > 12);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    function initSwiper(el, options) {
        if (!el || typeof Swiper === 'undefined') return null;
        try {
            return new Swiper(el, options);
        } catch (err) {
            console.warn('Swiper init skipped:', err);
            return null;
        }
    }

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function animateCount(el) {
        if (!el || el.dataset.done === '1') return;
        el.dataset.done = '1';
        const target = parseFloat(el.getAttribute('data-count') || el.dataset.counter || '0');
        if (Number.isNaN(target)) return;
        const suffix = el.getAttribute('data-suffix') || el.dataset.suffix || '';
        const isFloat = String(el.getAttribute('data-count') || el.dataset.counter || '').includes('.');
        if (reduceMotion) {
            el.textContent = (isFloat ? target.toFixed(1) : Math.floor(target).toLocaleString('tr-TR')) + suffix;
            return;
        }
        const duration = 1600;
        const start = performance.now();
        const step = (now) => {
            const p = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            const val = target * eased;
            el.textContent = (isFloat ? val.toFixed(1) : Math.floor(val).toLocaleString('tr-TR')) + suffix;
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }

    function playHeroAnims(slide) {
        if (!slide) return;
        slide.querySelectorAll('.dn-anim').forEach((el) => {
            el.classList.remove('is-in');
            void el.offsetWidth;
            if (!reduceMotion) {
                el.classList.add('is-in');
            } else {
                el.classList.add('is-in');
            }
        });
        slide.querySelectorAll('.dn-count, .yk-hero-stat-number[data-count]').forEach((el) => {
            delete el.dataset.done;
            animateCount(el);
        });
    }

    // ---------- Dentaire-style HERO SLIDER ----------
    const heroEl = document.querySelector('.dn-hero-swiper') || document.querySelector('.yk-hero-swiper');
    if (heroEl) {
        const slides = heroEl.querySelectorAll('.swiper-slide');
        const multi = slides.length > 1;
        const nextEl = heroEl.querySelector('.dn-hero-next, .yk-hero-next');
        const prevEl = heroEl.querySelector('.dn-hero-prev, .yk-hero-prev');
        const pagEl = heroEl.querySelector('.dn-hero-pagination, .yk-hero-pagination');
        const progressFill = heroEl.querySelector('.dn-hero-progress-fill');
        const curEl = heroEl.querySelector('.dn-hero-cur');
        const autoplayDelay = 5500;

        let progressRaf = null;
        let progressStart = 0;

        function stopProgress() {
            if (progressRaf) cancelAnimationFrame(progressRaf);
            progressRaf = null;
            if (progressFill) progressFill.style.width = '0%';
        }

        function startProgress() {
            if (!progressFill || reduceMotion || !multi) return;
            stopProgress();
            progressStart = performance.now();
            const tick = (now) => {
                const p = Math.min((now - progressStart) / autoplayDelay, 1);
                progressFill.style.width = (p * 100) + '%';
                if (p < 1) progressRaf = requestAnimationFrame(tick);
            };
            progressRaf = requestAnimationFrame(tick);
        }

        function updateFraction(sw) {
            if (!curEl) return;
            const real = (typeof sw.realIndex === 'number' ? sw.realIndex : sw.activeIndex) + 1;
            curEl.textContent = pad2(real);
        }

        const heroOpts = {
            loop: multi && slides.length > 2,
            rewind: multi && slides.length <= 2,
            speed: reduceMotion ? 0 : 950,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            grabCursor: multi,
            allowTouchMove: true,
            keyboard: { enabled: true },
            watchSlidesProgress: true,
            on: {
                init(sw) {
                    playHeroAnims(sw.slides[sw.activeIndex]);
                    updateFraction(sw);
                    startProgress();
                },
                slideChangeTransitionStart(sw) {
                    stopProgress();
                    const active = sw.slides[sw.activeIndex];
                    active?.querySelectorAll('.dn-anim').forEach((el) => el.classList.remove('is-in'));
                },
                slideChangeTransitionEnd(sw) {
                    playHeroAnims(sw.slides[sw.activeIndex]);
                    updateFraction(sw);
                    startProgress();
                },
                autoplayTimeLeft(sw, time, progress) {
                    if (progressFill && !reduceMotion) {
                        progressFill.style.width = ((1 - progress) * 100) + '%';
                    }
                },
            },
        };

        if (multi && !reduceMotion) {
            heroOpts.autoplay = {
                delay: autoplayDelay,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            };
        }
        if (multi && nextEl && prevEl) {
            heroOpts.navigation = { nextEl, prevEl };
        }
        if (multi && pagEl) {
            heroOpts.pagination = {
                el: pagEl,
                clickable: true,
                dynamicBullets: true,
            };
        }

        const heroSwiper = initSwiper(heroEl, heroOpts);

        // Pause progress on hover if autoplayTimeLeft not firing
        if (heroSwiper && multi) {
            heroEl.addEventListener('mouseenter', () => {
                if (progressRaf) cancelAnimationFrame(progressRaf);
            });
            heroEl.addEventListener('mouseleave', () => {
                if (heroSwiper.autoplay?.running) startProgress();
            });
        }

        // Soft mouse parallax on float cards (Dentaire feel)
        if (!reduceMotion) {
            const visual = heroEl.querySelector('.dn-hero-visual');
            if (visual) {
                visual.addEventListener('mousemove', (e) => {
                    const rect = visual.getBoundingClientRect();
                    const x = (e.clientX - rect.left) / rect.width - 0.5;
                    const y = (e.clientY - rect.top) / rect.height - 0.5;
                    visual.querySelectorAll('[data-parallax]').forEach((card) => {
                        const f = parseFloat(card.getAttribute('data-parallax') || '1');
                        card.style.setProperty('--px', (x * 14 * f) + 'px');
                        card.style.setProperty('--py', (y * 12 * f) + 'px');
                    });
                });
                visual.addEventListener('mouseleave', () => {
                    visual.querySelectorAll('[data-parallax]').forEach((card) => {
                        card.style.setProperty('--px', '0px');
                        card.style.setProperty('--py', '0px');
                    });
                });
            }
        }
    }

    // Services slider
    const servicesEl = document.querySelector('.services-swiper');
    if (servicesEl) {
        const section = servicesEl.closest('section') || document;
        const svcNext = section.querySelector('.svc-next');
        const svcPrev = section.querySelector('.svc-prev');
        const svcOpts = {
            slidesPerView: 1.08,
            spaceBetween: 20,
            grabCursor: true,
            speed: reduceMotion ? 0 : 700,
            autoplay: reduceMotion ? false : {
                delay: 3800,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            breakpoints: {
                640: { slidesPerView: 1.45 },
                900: { slidesPerView: 2.2 },
                1100: { slidesPerView: 3 },
            },
        };
        if (svcNext && svcPrev) {
            svcOpts.navigation = { nextEl: svcNext, prevEl: svcPrev };
        }
        initSwiper(servicesEl, svcOpts);
    }

    // Team slider
    const teamEl = document.querySelector('.team-swiper');
    if (teamEl) {
        const section = teamEl.closest('section') || document;
        const tNext = section.querySelector('.team-next');
        const tPrev = section.querySelector('.team-prev');
        const tCount = teamEl.querySelectorAll('.swiper-slide').length;
        const teamOpts = {
            slidesPerView: 1.15,
            spaceBetween: 20,
            grabCursor: true,
            speed: reduceMotion ? 0 : 650,
            loop: tCount > 3,
            autoplay: (!reduceMotion && tCount > 2) ? {
                delay: 4200,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            } : false,
            breakpoints: {
                640: { slidesPerView: 2 },
                980: { slidesPerView: 3 },
                1200: { slidesPerView: Math.min(4, tCount) },
            },
        };
        if (tNext && tPrev) {
            teamOpts.navigation = { nextEl: tNext, prevEl: tPrev };
        }
        initSwiper(teamEl, teamOpts);
    }

    // Reviews / testimonials
    document.querySelectorAll('.reviews-swiper, .testimonials-swiper').forEach((el) => {
        const section = el.closest('section') || document;
        const rNext = section.querySelector('.rev-next');
        const rPrev = section.querySelector('.rev-prev');
        const rCount = el.querySelectorAll('.swiper-slide').length;
        const revOpts = {
            slidesPerView: 1,
            spaceBetween: 20,
            grabCursor: true,
            loop: rCount > 2,
            speed: reduceMotion ? 0 : 750,
            autoplay: (!reduceMotion && rCount > 1) ? {
                delay: 4200,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            } : false,
            breakpoints: {
                720: { slidesPerView: Math.min(2, rCount) },
                1024: { slidesPerView: Math.min(3, rCount) },
            },
        };
        if (rNext && rPrev) {
            revOpts.navigation = { nextEl: rNext, prevEl: rPrev };
        }
        initSwiper(el, revOpts);
    });

    // Scroll reveal + counters (AOS-like)
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            el.classList.add('is-visible', 'visible');
            el.querySelectorAll('[data-counter]:not([data-done="1"]), .dn-count:not([data-done="1"])').forEach(animateCount);
            if ((el.matches('[data-counter]') || el.matches('.dn-count')) && el.dataset.done !== '1') {
                animateCount(el);
            }
            io.unobserve(el);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -48px 0px' });

    document.querySelectorAll(
        '.reveal, .stats-panel, .dn-reveal, .feature, .team-card, .service-card, .process-step, .blog-card, .gallery-item, .cta-band, .card, .review-card, .stat-item'
    ).forEach((el) => {
        el.classList.add('dn-reveal');
        if (reduceMotion) {
            el.classList.add('is-visible', 'visible');
        } else {
            io.observe(el);
        }
    });

    // Stagger children in grids
    document.querySelectorAll('.team-grid, .features, .process, .grid-2, .blog-grid, .gallery-grid, .stats-panel').forEach((grid) => {
        [...grid.children].forEach((child, i) => {
            child.style.setProperty('--delay', (i * 80) + 'ms');
        });
    });

    // Soft entrance for whole page
    document.documentElement.classList.add('dn-ready');
})();
</script>
@stack('scripts')
