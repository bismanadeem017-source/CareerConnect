/* CareerConnect – scroll reveal + count-up.
   Load at the end of the page (footer.php) with: <script src=".../animations.js" defer></script> */
(function () {
    'use strict';

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Elements that fade/slide in as they enter the viewport
    var selectors = [
        '.section-heading', '.stat-card', '.category-card',
        '.job-card', '.process-card', '.empty-state', '.cta-content'
    ];

    var items = document.querySelectorAll(selectors.join(','));
    items.forEach(function (el, i) {
        el.classList.add('cc-reveal');
        el.style.setProperty('--d', ((i % 6) * 90) + 'ms'); // stagger
    });

    if (reduce || !('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('in-view'); });
    } else {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        items.forEach(function (el) { io.observe(el); });
    }

    // Count-up for numeric stats such as "12+"
    document.querySelectorAll('.stat-card strong').forEach(function (el) {
        var match = el.textContent.trim().match(/^(\d+)(\+?)$/);
        if (!match || reduce) return;

        var target = parseInt(match[1], 10), suffix = match[2];
        var started = false;

        var obs = new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting || started) return;
            started = true;
            var t0 = performance.now(), dur = 1200;
            (function tick(now) {
                var p = Math.min((now - t0) / dur, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased) + suffix;
                if (p < 1) requestAnimationFrame(tick);
            })(t0);
            obs.disconnect();
        }, { threshold: 0.6 });
        obs.observe(el);
    });

    // Subtle parallax tilt on the hero card
    var card = document.querySelector('.art-card');
    var hero = document.querySelector('.home-hero');
    if (card && hero && !reduce && window.matchMedia('(hover: hover)').matches) {
        hero.addEventListener('mousemove', function (e) {
            var r = hero.getBoundingClientRect();
            var x = (e.clientX - r.left) / r.width - 0.5;
            var y = (e.clientY - r.top) / r.height - 0.5;
            card.style.transform = 'perspective(900px) rotateY(' + (x * 6) + 'deg) rotateX(' + (-y * 6) + 'deg)';
            card.style.animation = 'none';
        });
        hero.addEventListener('mouseleave', function () {
            card.style.transform = '';
            card.style.animation = '';
        });
    }
})();