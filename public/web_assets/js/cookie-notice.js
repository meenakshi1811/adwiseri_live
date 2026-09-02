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

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function recordConsentOnServer(notice) {
        var consentUrl = notice.getAttribute('data-consent-url');
        if (!consentUrl || !window.fetch) {
            return;
        }

        fetch(consentUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ consent_action: 'accepted' })
        }).catch(function () {
            // Consent is still stored locally even if the audit log request fails.
        });
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
                recordConsentOnServer(notice);
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
