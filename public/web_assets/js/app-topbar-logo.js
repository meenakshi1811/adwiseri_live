/*
 * App top bar: centre the adwiseri logo over the sidebar menu box below it (desktop only).
 * The sidebar's position depends on page margins and screen width, so it is measured rather than
 * guessed; on tablets and phones the sidebar stacks below the content and the logo keeps its CSS spot.
 */
(function () {
    'use strict';

    function align() {
        var bar = document.querySelector('.navbar.app-topbar');
        var brand = bar && bar.querySelector('.navbar-brand');
        if (!brand) {
            return;
        }

        var side = document.querySelector('.column-dashbox');
        var sideRect = side ? side.getBoundingClientRect() : null;
        var sidebarBeside = sideRect && sideRect.width > 0
            && window.innerWidth >= 992
            && sideRect.width < window.innerWidth * 0.5;

        if (!sidebarBeside) {
            brand.style.marginLeft = '';
            return;
        }

        brand.style.marginLeft = '0px';
        var brandRect = brand.getBoundingClientRect();
        var offset = (sideRect.left + sideRect.width / 2) - (brandRect.left + brandRect.width / 2);
        brand.style.marginLeft = Math.max(0, Math.round(offset)) + 'px';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', align);
    } else {
        align();
    }
    // The logo image's width is known only once it has loaded.
    window.addEventListener('load', align);
    window.addEventListener('resize', align);
})();
