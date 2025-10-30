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

// Set CSS variable --nav-height so hero can size to (100vh - navHeight)
(function () {
    'use strict';

    function setNavHeightVar() {
        const nav = document.querySelector('nav');
        const fallback = '4rem';
        if (!nav) {
            document.documentElement.style.setProperty('--nav-height', fallback);
            return;
        }
        const h = Math.ceil(nav.getBoundingClientRect().height) + 'px';
        document.documentElement.style.setProperty('--nav-height', h);
    }

    // Initialize and update on resize (debounce lightly)
    let resizeTimer = null;
    setNavHeightVar();
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(setNavHeightVar, 80);
    });

    // Also observe navbar size changes if available
    const navEl = document.querySelector('nav');
    if (navEl && 'ResizeObserver' in window) {
        const navRo = new ResizeObserver(setNavHeightVar);
        navRo.observe(navEl);
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

    // Smooth animator: duration in ms, easing function
    function easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    }

    function animateScrollTo(targetY, duration = 700, easingFn = easeInOutCubic) {
        const startY = window.scrollY || window.pageYOffset;
        const change = targetY - startY;
        const startTime = performance.now();

        return new Promise((resolve) => {
            function step(now) {
                const elapsed = now - startTime;
                const t = Math.min(1, elapsed / duration);
                const eased = easingFn(t);
                window.scrollTo(0, Math.round(startY + change * eased));
                if (t < 1) {
                    requestAnimationFrame(step);
                } else {
                    resolve();
                }
            }
            requestAnimationFrame(step);
        });
    }

    // Classes to toggle on active/inactive links
    const ACTIVE_CLASSES = ['bg-indigo-500', 'text-white', 'hover:bg-indigo-600'];
    const INACTIVE_CLASSES = ['text-gray-700', 'hover:bg-gray-100/80'];

    // Intercept clicks on internal anchors and perform a smooth scroll that accounts for the sticky nav
    // Note: listener must NOT be passive because we call preventDefault()
    document.addEventListener('click', function (e) {
        const anchor = e.target.closest('a[href^="#"]');
        if (!anchor) return;

        const hash = anchor.getAttribute('href');
        // ignore plain '#' anchors
        if (!hash || hash === '#') return;

        const target = document.querySelector(hash);
        if (!target) return;

        // Apply active styling immediately to the clicked link
        try {
            const navAnchors = document.querySelectorAll('nav a[href^="#"]');
            navAnchors.forEach(a => {
                a.classList.remove(...ACTIVE_CLASSES);
                a.classList.add(...INACTIVE_CLASSES);
            });
        } catch (err) {
            // noop
        }
        anchor.classList.add(...ACTIVE_CLASSES);
        anchor.classList.remove(...INACTIVE_CLASSES);

        e.preventDefault();

        const navHeight = getNavHeight();
        const offset = 8; // small gap below nav
        const targetY = target.getBoundingClientRect().top + window.scrollY - navHeight - offset;

        // Use animated scroll with easing
        animateScrollTo(targetY, 700).then(() => {
            // update the address bar without causing an immediate jump
            if (history.pushState) {
                history.pushState(null, '', hash);
            } else {
                location.hash = hash;
            }
        });
    }, { passive: false });
})();

// Mobile menu toggle
(function () {
    'use strict';
    
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function(e) {
            e.preventDefault();
            mobileMenu.classList.toggle('hidden');
        });
        
        // Close menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    }
})();
