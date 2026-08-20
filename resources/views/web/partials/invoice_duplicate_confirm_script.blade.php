<script>
(function ($) {
    function bindInvoiceDuplicateConfirm(formSelector, options) {
        var settings = $.extend({
            type: 'client',
            clientField: '#client_id',
            applicationField: '#application_id',
            submitButton: null,
            checkUrl: "{{ route('check_duplicate_invoice') }}",
            csrfToken: "{{ csrf_token() }}"
        }, options || {});

        var $form = $(formSelector);
        if (!$form.length) {
            return;
        }

        if (!$form.find('input[name="confirm_duplicate"]').length) {
            $form.append('<input type="hidden" name="confirm_duplicate" value="0">');
        }

        var $confirmField = $form.find('input[name="confirm_duplicate"]');
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

            var clientId = $(settings.clientField).val();
            var applicationId = $(settings.applicationField).val();

            if (!clientId || !applicationId || String(applicationId).toLowerCase() === 'other') {
                finalizeSubmit();
                return;
            }

            $.ajax({
                url: settings.checkUrl,
                method: 'POST',
                data: {
                    _token: settings.csrfToken,
                    type: settings.type,
                    client_id: clientId,
                    application_id: applicationId
                },
                success: function (response) {
                    if (!response || !response.duplicate) {
                        finalizeSubmit();
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops !',
                        text: 'Invoice found for same Client & Application. Do you still want to create invoice ?',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No, Cancel',
                        reverseButtons: true,
                        focusCancel: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $confirmField.val('1');
                            finalizeSubmit();
                        }
                    });
                },
                error: function () {
                    finalizeSubmit();
                }
            });
        });
    }

    window.bindInvoiceDuplicateConfirm = bindInvoiceDuplicateConfirm;
})(jQuery);
</script>
