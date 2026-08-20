/**
 * Standardized SweetAlert2 helpers for Adwiseri.
 * - success: green checkmark
 * - oops:    green "!" for validation, empty states, and informational notices
 * - error:   red "X" for genuine failures (save failed, server error, etc.)
 */
(function (window) {
    'use strict';

    var OOPS_CLASS = { icon: 'adwiseri-oops-icon' };

    function merge(target, source) {
        var result = {};
        var key;
        for (key in target) {
            if (Object.prototype.hasOwnProperty.call(target, key)) {
                result[key] = target[key];
            }
        }
        for (key in source) {
            if (Object.prototype.hasOwnProperty.call(source, key)) {
                result[key] = source[key];
            }
        }
        return result;
    }

    function fire(options) {
        if (typeof Swal === 'undefined') {
            window.alert(options.text || options.title || 'Alert');
            return Promise.resolve();
        }
        return Swal.fire(merge({ confirmButtonText: 'OK' }, options));
    }

    window.AdwiseriAlert = {
        success: function (text, title) {
            return fire({
                icon: 'success',
                title: title || 'Success',
                text: text || ''
            });
        },

        oops: function (text, title, extra) {
            return fire(merge({
                icon: 'warning',
                title: title || 'Oops!',
                text: text || '',
                customClass: OOPS_CLASS
            }, extra || {}));
        },

        noData: function (text, title) {
            return fire({
                icon: 'warning',
                title: title || 'Oops!',
                text: text || 'No data found.',
                customClass: OOPS_CLASS
            });
        },

        error: function (text, title, extra) {
            return fire(merge({
                icon: 'error',
                title: title || 'Oops!',
                text: text || ''
            }, extra || {}));
        },

        info: function (text, title, extra) {
            return fire(merge({
                icon: 'warning',
                title: title || 'Oops!',
                text: text || '',
                customClass: OOPS_CLASS
            }, extra || {}));
        }
    };
})(window);
