(function (window, document) {
    'use strict';

    var EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var NAME_PATTERN = /^[A-Za-z\s'\-.]{3,}$/;
    var POSTCODE_PATTERN = /^[A-Za-z0-9\s\-]{3,10}$/;
    var PASSPORT_PATTERN = /^[A-Z0-9]{6,14}$/;

    function clearFieldError(input) {
        input.classList.remove('form-validation-error');

        var group = input.closest('.mb-3, .col-md-6, .col-md-4, .col-md-8, .p-1, .form-group, .form-box');
        if (!group) {
            group = input.parentNode;
        }

        if (!group) {
            return;
        }

        var existing = group.querySelector('[data-form-validation-error="1"]');
        if (existing) {
            existing.remove();
        }
    }

    function showFieldError(input, message) {
        clearFieldError(input);
        input.classList.add('form-validation-error');

        var group = input.closest('.mb-3, .col-md-6, .col-md-4, .col-md-8, .p-1, .form-group, .form-box');
        if (!group) {
            group = input.parentNode;
        }

        if (!group) {
            return;
        }

        var error = document.createElement('div');
        error.className = 'form-validation-message';
        error.setAttribute('data-form-validation-error', '1');
        error.textContent = message;
        group.appendChild(error);
    }

    function isVisible(input) {
        return !!(input.offsetWidth || input.offsetHeight || input.getClientRects().length);
    }

    function validateInput(input) {
        if (!isVisible(input) || input.disabled || input.readOnly) {
            clearFieldError(input);
            return true;
        }

        var value = (input.value || '').trim();
        var name = input.name || '';
        var isRequired = input.required || input.hasAttribute('required');
        var type = (input.type || '').toLowerCase();

        clearFieldError(input);

        if (isRequired && !value && type !== 'checkbox' && type !== 'radio' && input.tagName !== 'SELECT') {
            showFieldError(input, 'This field is required.');
            return false;
        }

        if (isRequired && input.tagName === 'SELECT' && !value) {
            showFieldError(input, 'Please select a value.');
            return false;
        }

        if (!value) {
            return true;
        }

        if (type === 'email' || name === 'email') {
            if (!EMAIL_PATTERN.test(value)) {
                showFieldError(input, 'Please enter a valid email address.');
                return false;
            }
        }

        if (name === 'name' || name === 'full_name') {
            if (!NAME_PATTERN.test(value)) {
                showFieldError(input, 'Name must be at least 3 characters and contain only letters.');
                return false;
            }
        }

        if (name === 'postcode' || name === 'pincode') {
            if (!POSTCODE_PATTERN.test(value)) {
                showFieldError(input, 'Please enter a valid postcode (3-10 letters/numbers).');
                return false;
            }
        }

        if (name === 'passport_no') {
            var passportValue = value.toUpperCase();
            input.value = passportValue;

            if (!PASSPORT_PATTERN.test(passportValue)) {
                showFieldError(input, 'Passport number must be 6-14 uppercase letters or numbers.');
                return false;
            }
        }

        if (input.classList.contains('datepicker') || input.classList.contains('date')) {
            var datePattern = /^\d{2}-\d{2}-\d{4}$/;
            if (!datePattern.test(value)) {
                showFieldError(input, 'Please enter a valid date in DD-MM-YYYY format.');
                return false;
            }
        }

        if (input.minLength > 0 && value.length < input.minLength) {
            showFieldError(input, 'Please enter at least ' + input.minLength + ' characters.');
            return false;
        }

        if (input.maxLength > 0 && value.length > input.maxLength) {
            showFieldError(input, 'Please enter no more than ' + input.maxLength + ' characters.');
            return false;
        }

        return true;
    }

    function validateForm(form) {
        var valid = true;
        var firstInvalid = null;

        form.querySelectorAll('input, select, textarea').forEach(function (input) {
            if (input.type === 'hidden' || input.type === 'submit' || input.type === 'button') {
                return;
            }

            if (!validateInput(input)) {
                valid = false;
                if (!firstInvalid) {
                    firstInvalid = input;
                }
            }
        });

        if (!valid && firstInvalid) {
            firstInvalid.focus();
        }

        return valid;
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || form.tagName !== 'FORM' || form.dataset.skipValidation === '1') {
            return;
        }

        if (!validateForm(form)) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);

    document.addEventListener('blur', function (event) {
        var input = event.target;

        if (!input || !input.matches || !input.matches('input, select, textarea')) {
            return;
        }

        validateInput(input);
    }, true);

    window.AdwiseriFormValidation = {
        validate: validateForm,
        validateInput: validateInput
    };
})(window, document);
