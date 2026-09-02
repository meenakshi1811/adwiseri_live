<script>
(function ($) {
    function ensureConfirmField($form) {
        if (!$form.find('input[name="confirm_duplicate"]').length) {
            $form.append('<input type="hidden" name="confirm_duplicate" value="0">');
        }

        return $form.find('input[name="confirm_duplicate"]');
    }

    function promptDuplicateConfirm(message, onConfirm) {
        Swal.fire({
            icon: 'warning',
            customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Duplicate application',
            text: message || 'An application of this type already exists for this client. Do you still want to create another application?',
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'No, cancel',
            reverseButtons: true,
            focusCancel: true
        }).then(function (result) {
            if (result.isConfirmed) {
                onConfirm();
            }
        });
    }

    function checkDuplicateApplication(clientId, applicationName, callback) {
        if (!clientId || !applicationName) {
            callback(false, null);
            return;
        }

        $.ajax({
            url: "{{ url('/check_duplicate_application') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                client_id: clientId,
                application_name: applicationName
            },
            success: function (response) {
                callback(Boolean(response && response.duplicate), response ? response.message : null);
            },
            error: function () {
                callback(false, null);
            }
        });
    }

    window.bindApplicationDuplicateConfirm = function (formSelector, options) {
        var settings = $.extend({
            clientField: '#client, [name="client_id"], [name="client"]',
            applicationField: '#job_role, [name="job_role"]',
            submitButton: null,
            skipIfEdit: true
        }, options || {});

        var $form = $(formSelector);
        if (!$form.length) {
            return;
        }

        if (settings.skipIfEdit && $form.find('input[name="id"]').length) {
            return;
        }

        var $confirmField = ensureConfirmField($form);
        var $submitButton = settings.submitButton ? $(settings.submitButton) : $form.find('[type="submit"]').first();
        var allowSubmit = false;

        function finalizeSubmit() {
            allowSubmit = true;
            if ($submitButton.length) {
                $submitButton.prop('disabled', true);
            }
            $form[0].submit();
        }

        $form.on('submit', function (event) {
            if (allowSubmit) {
                return true;
            }

            event.preventDefault();

            var clientId = $form.find(settings.clientField).first().val();
            var applicationName = $form.find(settings.applicationField).first().val();

            checkDuplicateApplication(clientId, applicationName, function (isDuplicate, message) {
                if (!isDuplicate) {
                    finalizeSubmit();
                    return;
                }

                promptDuplicateConfirm(message, function () {
                    $confirmField.val('1');
                    finalizeSubmit();
                });
            });
        });
    };

    window.submitApplicationWithDuplicateCheck = function (options) {
        var settings = $.extend({
            clientId: '',
            applicationName: '',
            onSubmit: function () {},
            confirmField: null
        }, options || {});

        checkDuplicateApplication(settings.clientId, settings.applicationName, function (isDuplicate, message) {
            if (!isDuplicate) {
                settings.onSubmit(false);
                return;
            }

            promptDuplicateConfirm(message, function () {
                if (settings.confirmField) {
                    settings.confirmField.val('1');
                }
                settings.onSubmit(true);
            });
        });
    };
})(jQuery);
</script>
