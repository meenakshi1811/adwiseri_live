(function () {
    'use strict';

    var STORAGE_KEY = 'adwiseri_cookie_consent';
    var COOKIE_NAME = 'adwiseri_cookie_consent';
    var COOKIE_MAX_AGE_DAYS = 365;

    function readConsent() {
        try {
            if (window.localStorage && localStorage.getItem(STORAGE_KEY) === 'accepted') {
                return 'accepted';
            }
        } catch (error) {
            // Ignore storage access errors (private browsing, blocked storage, etc.).
        }

        var match = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_NAME + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function persistConsent() {
        try {
            if (window.localStorage) {
                localStorage.setItem(STORAGE_KEY, 'accepted');
            }
        } catch (error) {
            // Ignore storage access errors.
        }

        var maxAge = COOKIE_MAX_AGE_DAYS * 24 * 60 * 60;
        document.cookie = COOKIE_NAME + '=accepted; path=/; max-age=' + maxAge + '; SameSite=Lax';
    }

    function hideNotice(notice) {
        notice.classList.remove('is-visible');
        notice.setAttribute('aria-hidden', 'true');
    }

    function showNotice(notice) {
        notice.classList.add('is-visible');
        notice.setAttribute('aria-hidden', 'false');
    }

    function initCookieNotice() {
        var notice = document.getElementById('adwiseri-cookie-notice');
        if (!notice) {
            return;
        }

        if (readConsent() === 'accepted') {
            hideNotice(notice);
            return;
        }

        showNotice(notice);

        var acceptButton = notice.querySelector('[data-cookie-accept]');
        if (acceptButton) {
            acceptButton.addEventListener('click', function () {
                persistConsent();
                hideNotice(notice);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCookieNotice);
    } else {
        initCookieNotice();
    }
})();
