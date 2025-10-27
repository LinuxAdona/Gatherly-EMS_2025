// Hero background panning on scroll
// Targets element with class "hero-section" and reads data-pan-speed attribute (default 0.45)
(function () {
    'use strict';

    const hero = document.querySelector('.hero-section');
    if (!hero) return;

    // Read speed from data attribute; lower = slower movement
    const dataSpeed = parseFloat(hero.dataset.panSpeed);
    const speed = Number.isFinite(dataSpeed) ? dataSpeed : 0.45;

    // Ensure will-change for smoother animations
    hero.style.willChange = 'background-position';

    let ticking = false;

    function update() {
        // Use getBoundingClientRect to determine hero position relative to viewport
        const rect = hero.getBoundingClientRect();
        const viewportHeight = window.innerHeight || document.documentElement.clientHeight;

        // Compute an offset based on the hero's top relative to viewport
        // When hero is at top (rect.top = 0) offset = 0; when scrolled up, rect.top negative -> positive offset
        const offset = -rect.top * speed;

        // Set background position (center horizontal, offset vertical)
        hero.style.backgroundPosition = `center ${offset}px`;

        ticking = false;
    }

    function requestUpdate() {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }

    // Initial update (in case page is loaded scrolled)
    update();

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);

    // Also observe changes to the hero's position (images loading, layout changes)
    if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(requestUpdate);
        ro.observe(hero);
    }
})();

// Smooth scrolling / snapping for internal navbar links
(function () {
    'use strict';

    // Helper to get current navbar height (sticky nav)
    function getNavHeight() {
        const nav = document.querySelector('nav');
        return nav ? nav.getBoundingClientRect().height : 0;
    }

    // Intercept clicks on internal anchors and perform a smooth scroll that accounts for the sticky nav
    document.addEventListener('click', function (e) {
        const anchor = e.target.closest('a[href^="#"]');
        if (!anchor) return;

        const hash = anchor.getAttribute('href');
        // ignore plain '#' anchors
        if (!hash || hash === '#') return;

        const target = document.querySelector(hash);
        if (!target) return;

        e.preventDefault();

        const navHeight = getNavHeight();
        const offset = 8; // small gap below nav
        const targetY = target.getBoundingClientRect().top + window.scrollY - navHeight - offset;

        window.scrollTo({ top: targetY, behavior: 'smooth' });

        // update the address bar without causing an immediate jump
        if (history.pushState) {
            history.pushState(null, '', hash);
        } else {
            // fallback (may cause jump in very old browsers, but those are rare)
            location.hash = hash;
        }
    }, { passive: true });
})();
