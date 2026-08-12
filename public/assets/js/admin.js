/* ============================================================
   Rozhin Admin — Vuexy-style vertical layout behavior
   - Mobile (< 1200px): hamburger toggles the drawer via the
     `layout-menu-expanded` class on <html>; overlay, Esc and
     menu links close it; scroll is locked while open.
   - Desktop (>= 1200px): menu always visible, no collapse.
   - Profile dropdown in the navbar.
   Internal asset. No external dependencies, no CDN.
   ============================================================ */

(function () {
    'use strict';

    var DESKTOP_QUERY = '(min-width: 1200px)';

    var menuToggle = document.getElementById('adminMenuToggle');
    var overlay = document.getElementById('layoutOverlay');
    var userBtn = document.getElementById('navbarUserBtn');
    var userMenu = document.getElementById('navbarUserMenu');

    function isDesktop() {
        return window.matchMedia(DESKTOP_QUERY).matches;
    }

    function setExpanded(expanded) {
        document.documentElement.classList.toggle('layout-menu-expanded', expanded);
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function closeDrawer() {
        setExpanded(false);
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var expanded = document.documentElement.classList.contains('layout-menu-expanded');
            setExpanded(!expanded);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeDrawer);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.documentElement.classList.contains('layout-menu-expanded')) {
            closeDrawer();
        }
    });

    /* Close the drawer when a menu link is clicked (mobile) — but never for
       group toggles (.menu-toggle): tapping a group header only expands its
       submenu and must keep the drawer open so the submenu item can be chosen
       (matches the Vuexy reference menu.js behavior). */
    document.querySelectorAll('.layout-menu .menu-link[href]:not(.menu-toggle), .layout-menu .menu-link[href]:not(.menu-toggle) *').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var hrefEl = e.target.closest('.menu-link[href]:not(.menu-toggle)');
            if (hrefEl && !isDesktop()) {
                closeDrawer();
            }
        });
    });

    /* Re-close the drawer when crossing to desktop */
    window.matchMedia(DESKTOP_QUERY).addEventListener('change', function (ev) {
        if (ev.matches) {
            closeDrawer();
        }
    });

    /* Profile dropdown */
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = userMenu.classList.toggle('open');
            userBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#navbarUser')) {
                userMenu.classList.remove('open');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                userMenu.classList.remove('open');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
})();
