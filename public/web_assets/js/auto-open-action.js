/**
 * One-click actions across module tabs.
 *
 * A button that lives on another tab links to that tab's page with ?open=<action>, e.g.
 * /client_documents?open=add_document. The page marks the button that opens the form with
 * data-auto-open="<action>"; once the page has loaded it is clicked automatically, so the tab
 * appears selected with its form already open. The parameter is then dropped from the address
 * bar so a refresh doesn't reopen the form.
 */
(function (window, document) {
    'use strict';

    var params = new URLSearchParams(window.location.search);
    var action = params.get('open');
    if (!action) {
        return;
    }

    function openAction() {
        var trigger = Array.prototype.find.call(
            document.querySelectorAll('[data-auto-open]'),
            function (element) {
                return element.getAttribute('data-auto-open') === action;
            }
        );

        if (trigger) {
            trigger.click();
        }

        params.delete('open');
        var query = params.toString();
        window.history.replaceState(null, '', window.location.pathname + (query ? '?' + query : '') + window.location.hash);
    }

    // Pages bind their click handlers in $(document).ready, which jQuery runs asynchronously and can
    // finish after 'load'. Queue the click behind those callbacks so it reaches a bound handler.
    window.addEventListener('load', function () {
        var afterReady = function () {
            setTimeout(openAction, 0);
        };

        if (window.jQuery) {
            window.jQuery(afterReady);
        } else {
            afterReady();
        }
    });
})(window, document);
