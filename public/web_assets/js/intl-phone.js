(function (window, document, $) {
    'use strict';

    var PHONE_SELECTOR = 'input[name="phone"], input[name="contact_no"], input[name="alternate_no"], input[name="line_manager_phone"]';
    var PRIMARY_PHONE_NAMES = ['phone', 'contact_no'];
    var NATIONAL_MAX_LENGTH = 10;
    var phoneInstances = new WeakMap();
    var config = window.AdwiseriIntlPhoneConfig || {};

    function getValidateUrl() {
        return config.validateUrl || '/validate-phone';
    }

    function getUtilsScript() {
        return config.utilsScript || 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js';
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function getDefaultCountry(input) {
        if (input.dataset.defaultCountry) {
            return input.dataset.defaultCountry.toLowerCase();
        }

        return 'gb';
    }

    function isPrimaryPhoneField(input) {
        return PRIMARY_PHONE_NAMES.indexOf(input.name) !== -1;
    }

    function getPrimaryPhoneInput(form) {
        for (var i = 0; i < PRIMARY_PHONE_NAMES.length; i++) {
            var input = form.querySelector('input[name="' + PRIMARY_PHONE_NAMES[i] + '"]');

            if (input) {
                return input;
            }
        }

        return null;
    }

    function isSyncing(form) {
        return form && form.dataset.phoneCountrySyncing === '1';
    }

    function beginSync(form) {
        if (form) {
            form.dataset.phoneCountrySyncing = '1';
        }
    }

    function endSync(form) {
        if (form) {
            form.dataset.phoneCountrySyncing = '0';
        }
    }

    function countrySelectUsesIds(countrySelect) {
        var usesIds = false;

        Array.prototype.forEach.call(countrySelect.options, function (option) {
            if (option.value && /^\d+$/.test(option.value)) {
                usesIds = true;
            }
        });

        return usesIds;
    }

    function resolveIsoFromCountrySelect(countrySelect) {
        var value = (countrySelect.value || '').trim();

        if (!value) {
            return null;
        }

        var countryIdToIso = config.countryIdToIso || {};
        var countryNameToIso = config.countryNameToIso || {};

        if (/^\d+$/.test(value)) {
            return countryIdToIso[value] || countryIdToIso[String(value)] || null;
        }

        var iso = countryNameToIso[value.toLowerCase()];

        if (iso) {
            return iso;
        }

        var selectedOption = countrySelect.options[countrySelect.selectedIndex];

        if (selectedOption && selectedOption.text) {
            return countryNameToIso[selectedOption.text.trim().toLowerCase()] || null;
        }

        return null;
    }

    function setCountrySelectValue(countrySelect, iso2, countryName) {
        var countryMap = config.countryMap || {};
        var iso = (iso2 || '').toLowerCase();
        var countryId = countryMap[iso];
        var targetValue = null;

        if (countrySelectUsesIds(countrySelect) && countryId) {
            targetValue = String(countryId);
        } else if (countryName) {
            var normalizedName = countryName.toLowerCase();
            var matched = false;

            Array.prototype.forEach.call(countrySelect.options, function (option) {
                if (matched || !option.value) {
                    return;
                }

                if (option.value.toLowerCase() === normalizedName || option.text.trim().toLowerCase() === normalizedName) {
                    targetValue = option.value;
                    matched = true;
                }
            });
        }

        if (!targetValue) {
            return false;
        }

        countrySelect.value = targetValue;

        return countrySelect.value === targetValue;
    }

    function triggerCountrySelectChange(countrySelect) {
        if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined') {
            $(countrySelect).trigger('change');
            return;
        }

        countrySelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function syncPhoneFromCountry(countrySelect) {
        var form = countrySelect.closest('form');

        if (!form || isSyncing(form)) {
            return;
        }

        var input = getPrimaryPhoneInput(form);

        if (!input) {
            return;
        }

        var instance = phoneInstances.get(input);

        if (!instance) {
            initPhoneInput(input);
            instance = phoneInstances.get(input);
        }

        if (!instance || typeof instance.setCountry !== 'function') {
            return;
        }

        var iso = resolveIsoFromCountrySelect(countrySelect);

        if (!iso) {
            return;
        }

        beginSync(form);

        try {
            instance.setCountry(iso);
            validateNationalInput(input);
            clearPhoneError(input);
        } finally {
            endSync(form);
        }
    }

    function bindFormCountryPhoneSync(form) {
        if (!form || form.dataset.countryPhoneSyncBound === '1') {
            return;
        }

        var countrySelect = form.querySelector('select[name="country"]');

        if (!countrySelect) {
            return;
        }

        form.dataset.countryPhoneSyncBound = '1';

        countrySelect.addEventListener('change', function () {
            syncPhoneFromCountry(countrySelect);
        });
    }

    function syncCountryFromPhone(input, options) {
        options = options || {};

        if (!isPrimaryPhoneField(input)) {
            return;
        }

        var instance = phoneInstances.get(input);

        if (!instance) {
            return;
        }

        var form = input.closest('form');

        if (!form || isSyncing(form)) {
            return;
        }

        var countrySelect = form.querySelector('select[name="country"]');

        if (!countrySelect) {
            return;
        }

        if (!options.force && countrySelect.value) {
            return;
        }

        var countryData = instance.getSelectedCountryData();

        beginSync(form);

        try {
            if (!setCountrySelectValue(countrySelect, countryData.iso2, countryData.name)) {
                return;
            }

            triggerCountrySelectChange(countrySelect);
        } finally {
            endSync(form);
        }
    }

    function normalizeStoredPhone(raw) {
        var phone = (raw || '').trim();

        if (!phone) {
            return '';
        }

        if (phone.charAt(0) !== '+') {
            var digits = phone.replace(/\D/g, '');
            phone = digits ? '+' + digits : '';
        }

        return phone;
    }

    function getStoredPhone(input) {
        return normalizeStoredPhone(input.getAttribute('data-phone-e164') || input.value || '');
    }

    function stripDialCodePrefix(national, dialCode) {
        if (!dialCode || !national) {
            return national;
        }

        if (national.indexOf(dialCode) === 0) {
            national = national.substring(dialCode.length);

            if (national.charAt(0) === '0') {
                national = national.substring(1);
            }
        }

        return national;
    }

    function sanitizeNationalDigits(raw, dialCode) {
        var national = (raw || '').replace(/\D/g, '');

        national = stripDialCodePrefix(national, dialCode || '');

        if (national.charAt(0) === '0') {
            national = national.substring(1);
        }

        if (national.length > NATIONAL_MAX_LENGTH) {
            national = national.substring(0, NATIONAL_MAX_LENGTH);
        }

        return national;
    }

    function removeLegacyPhoneConstraints(input) {
        input.removeAttribute('pattern');
        input.removeAttribute('minlength');

        if (input.type !== 'tel') {
            input.type = 'tel';
        }

        input.setAttribute('autocomplete', input.getAttribute('autocomplete') || 'tel');
        input.setAttribute('maxlength', String(NATIONAL_MAX_LENGTH));
        input.setAttribute('inputmode', 'numeric');
        input.classList.add('intl-phone-input');
    }

    function showPhoneError(input, message) {
        clearPhoneError(input);
        input.classList.add('intl-phone-invalid');

        var error = document.createElement('div');
        error.className = 'intl-phone-error form-validation-message';
        error.setAttribute('data-intl-phone-error', '1');
        error.textContent = message;

        var container = input.closest('.iti') ? input.closest('.iti').parentNode : input.parentNode;
        if (container) {
            container.appendChild(error);
        }
    }

    function clearPhoneError(input) {
        input.classList.remove('intl-phone-invalid');

        var container = input.closest('.iti') ? input.closest('.iti').parentNode : input.parentNode;
        if (!container) {
            return;
        }

        var existing = container.querySelector('[data-intl-phone-error="1"]');
        if (existing) {
            existing.remove();
        }
    }

    function buildFullNumber(input, instance) {
        if (!instance) {
            return sanitizeNationalDigits(input.value || '', '');
        }

        var countryData = instance.getSelectedCountryData();
        var national = sanitizeNationalDigits(input.value || '', countryData.dialCode);

        if (!national) {
            return '';
        }

        return '+' + countryData.dialCode + national;
    }

    function applyNormalizedValue(input, phone) {
        var instance = phoneInstances.get(input);

        if (!phone || !instance || typeof instance.setNumber !== 'function') {
            return;
        }

        input.setAttribute('data-phone-e164', phone);
        restorePhoneNumber(input, instance, phone);
    }

    function preparePhoneForSubmit(input) {
        var instance = phoneInstances.get(input);
        var e164 = buildFullNumber(input, instance);

        input.value = e164;
        input.setAttribute('data-phone-e164', e164);
    }

    function validateNationalInput(input) {
        var instance = phoneInstances.get(input);
        var dialCode = instance ? instance.getSelectedCountryData().dialCode : '';
        var national = sanitizeNationalDigits(input.value || '', dialCode);

        if (input.value !== national) {
            input.value = national;
        }

        return national;
    }

    function setPhoneNumberWhenReady(instance, input, phone) {
        var apply = function () {
            instance.setNumber(phone);
            validateNationalInput(input);
            syncCountryFromPhone(input, { force: false });
        };

        if (instance.promise && typeof instance.promise.then === 'function') {
            instance.promise.then(apply);
            return;
        }

        apply();
    }

    function restorePhoneNumber(input, instance, phone) {
        if (!phone) {
            return;
        }

        if (input.offsetParent === null) {
            waitUntilVisible(input, function () {
                setPhoneNumberWhenReady(instance, input, phone);
            });
            return;
        }

        setPhoneNumberWhenReady(instance, input, phone);
    }

    function waitUntilVisible(input, callback) {
        if (input.offsetParent !== null) {
            callback();
            return;
        }

        var container = input.closest('[id$="_box"], [id*="update_"], [id*="modal"], .modal, .loginouter-box');

        if (!container) {
            callback();
            return;
        }

        var observer = new MutationObserver(function () {
            if (input.offsetParent !== null) {
                observer.disconnect();
                callback();
            }
        });

        observer.observe(container, {
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }

    function refreshPhoneInput(input) {
        var instance = phoneInstances.get(input);
        var storedPhone = getStoredPhone(input);

        if (!instance) {
            initPhoneInput(input);
            return;
        }

        if (storedPhone) {
            restorePhoneNumber(input, instance, storedPhone);
        }
    }

    function validatePhoneAjax(input, callback) {
        var instance = phoneInstances.get(input);
        var isRequired = input.required || input.hasAttribute('required');

        clearPhoneError(input);

        var national = validateNationalInput(input);

        if (!national) {
            if (isRequired) {
                showPhoneError(input, 'Contact number is required.');
                callback(false);
            } else {
                callback(true);
            }
            return;
        }

        if (!instance) {
            initPhoneInput(input);
            instance = phoneInstances.get(input);
        }

        var phone = buildFullNumber(input, instance);

        $.ajax({
            url: getValidateUrl(),
            method: 'POST',
            dataType: 'json',
            data: {
                _token: getCsrfToken(),
                phone: phone
            },
            success: function (response) {
                if (response.valid) {
                    applyNormalizedValue(input, response.phone);
                    clearPhoneError(input);
                    callback(true, response.phone);
                } else {
                    showPhoneError(input, response.message || 'Please enter a valid contact number (digits only, up to 10 digits after country code).');
                    callback(false);
                }
            },
            error: function () {
                showPhoneError(input, 'Unable to validate phone number. Please try again.');
                callback(false);
            }
        });
    }

    function initPhoneInput(input) {
        if (!window.intlTelInput || input.dataset.intlPhoneInit === '1') {
            return;
        }

        removeLegacyPhoneConstraints(input);

        var storedPhone = getStoredPhone(input);

        if (storedPhone) {
            input.setAttribute('data-phone-e164', storedPhone);
        }

        // Prevent intl-tel-input from mis-parsing E.164 as a national number before setNumber runs.
        input.value = '';

        var instance = window.intlTelInput(input, {
            initialCountry: getDefaultCountry(input),
            preferredCountries: ['gb', 'in', 'us', 'au', 'ca', 'ae'],
            separateDialCode: true,
            nationalMode: true,
            autoPlaceholder: 'polite',
            formatOnDisplay: false,
            utilsScript: getUtilsScript()
        });

        input.dataset.intlPhoneInit = '1';
        phoneInstances.set(input, instance);

        if (storedPhone) {
            restorePhoneNumber(input, instance, storedPhone);
        }

        input.addEventListener('input', function () {
            validateNationalInput(input);
            clearPhoneError(input);
        });

        input.addEventListener('paste', function () {
            window.setTimeout(function () {
                validateNationalInput(input);
            }, 0);
        });

        input.addEventListener('blur', function () {
            if (!input.value.trim()) {
                clearPhoneError(input);
                return;
            }

            validatePhoneAjax(input, function () {});
        });

        input.addEventListener('countrychange', function () {
            validateNationalInput(input);
            clearPhoneError(input);
            syncCountryFromPhone(input, { force: true });
        });

        bindFormCountryPhoneSync(input.closest('form'));
    }

    function initAllPhoneInputs(root) {
        var scope = root || document;
        scope.querySelectorAll(PHONE_SELECTOR).forEach(function (input) {
            initPhoneInput(input);
        });

        if (scope.tagName === 'FORM') {
            bindFormCountryPhoneSync(scope);
            return;
        }

        scope.querySelectorAll('form').forEach(bindFormCountryPhoneSync);
    }

    function getPhoneInputs(form) {
        return Array.from(form.querySelectorAll(PHONE_SELECTOR));
    }

    function validatePhoneInputs(form, callback) {
        var inputs = getPhoneInputs(form);

        if (!inputs.length) {
            callback(true);
            return;
        }

        var remaining = inputs.length;
        var valid = true;

        inputs.forEach(function (input) {
            validatePhoneAjax(input, function (isValid) {
                if (!isValid) {
                    valid = false;
                }

                remaining--;

                if (remaining === 0) {
                    if (valid) {
                        inputs.forEach(preparePhoneForSubmit);
                    }
                    callback(valid);
                }
            });
        });
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || form.tagName !== 'FORM' || form.dataset.phoneValidated === '1') {
            return;
        }

        if (!form.querySelector(PHONE_SELECTOR)) {
            return;
        }

        if (typeof $ === 'undefined') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        validatePhoneInputs(form, function (valid) {
            if (!valid) {
                var firstInvalid = form.querySelector('.intl-phone-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
                return;
            }

            form.dataset.phoneValidated = '1';
            form.submit();
        });
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        initAllPhoneInputs(document);
    });

    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        initAllPhoneInputs(node);
                    }
                });
            });
        });

        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    }

    window.AdwiseriIntlPhone = {
        init: initAllPhoneInputs,
        refresh: refreshPhoneInput,
        validate: validatePhoneInputs,
        validateInput: validatePhoneAjax,
        syncCountryFromPhone: syncCountryFromPhone,
        syncPhoneFromCountry: syncPhoneFromCountry,
        buildFullNumber: function (input) {
            return buildFullNumber(input, phoneInstances.get(input));
        }
    };
})(window, document, window.jQuery);
