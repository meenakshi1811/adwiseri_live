{{--
    Bell icon that emails a payment reminder for the latest outstanding amount (the server works it out again
    on click). With $blockedReason it is shown disabled, with the reason as its tooltip.
    Params: $reminderUrl, $reminderParams (POST fields), $invoiceLabel, $recipientLabel, $outstanding, $blockedReason.
--}}
@if(!empty($blockedReason))
    <span class="d-inline-block payment-reminder-disabled" tabindex="0" role="button" aria-disabled="true"
        aria-label="Send payment reminder unavailable: {{ $blockedReason }}"
        data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $blockedReason }}">
        <i class="fa-solid fa-bell btn p-1 payment-reminder-btn is-disabled" aria-hidden="true"></i>
    </span>
@else
    <i class="fa-solid fa-bell btn p-1 text-warning payment-reminder-btn" role="button" tabindex="0"
        aria-label="Send payment reminder for invoice {{ $invoiceLabel }}"
        data-bs-toggle="tooltip" data-bs-placement="top" title="Send payment reminder"
        data-reminder-url="{{ $reminderUrl }}"
        data-reminder-params="{{ json_encode($reminderParams) }}"
        data-reminder-invoice="{{ $invoiceLabel }}"
        data-reminder-recipient="{{ $recipientLabel }}"
        data-reminder-outstanding="{{ $outstanding }}"></i>
@endif

@once
@push('css')
<style>
    .payment-reminder-btn:focus-visible {
        outline: 2px solid #695EEE;
        outline-offset: 2px;
    }

    .payment-reminder-disabled,
    .column-client td i.fa-solid.payment-reminder-btn.is-disabled {
        color: #b9b6d3 !important;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    function reminderErrorMessage(xhr) {
        var json = xhr && xhr.responseJSON;

        if (json && json.errors) {
            var field = Object.keys(json.errors)[0];
            if (field && json.errors[field][0]) {
                return json.errors[field][0];
            }
        }

        return json && json.message ? json.message : 'Unable to send the reminder. Please try again.';
    }

    function sendPaymentReminder(trigger) {
        var $trigger = $(trigger);
        var tooltip = window.bootstrap && bootstrap.Tooltip ? bootstrap.Tooltip.getInstance(trigger) : null;
        if (tooltip) {
            tooltip.hide();
        }

        var params = {};
        try {
            params = JSON.parse($trigger.attr('data-reminder-params') || '{}');
        } catch (error) {
            params = {};
        }

        var details = $('<div>')
            .append($('<p class="mb-2">').append(
                'Invoice ', $('<strong>').text($trigger.attr('data-reminder-invoice')),
                ' · Outstanding ', $('<strong>').text($trigger.attr('data-reminder-outstanding'))
            ))
            .append($('<p class="mb-0">').append(
                'The latest outstanding amount will be emailed to ',
                $('<strong>').text($trigger.attr('data-reminder-recipient') || 'the recipient'),
                '.'
            ));

        Swal.fire({
            icon: 'question',
            title: 'Send Payment Reminder?',
            html: details.html(),
            showCancelButton: true,
            confirmButtonText: 'Send Reminder',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#695EEE',
            showLoaderOnConfirm: true,
            allowOutsideClick: function () {
                return !Swal.isLoading();
            },
            preConfirm: function () {
                return $.ajax({
                    url: $trigger.attr('data-reminder-url'),
                    type: 'POST',
                    dataType: 'json',
                    data: $.extend({
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        local_time: new Date().toString()
                    }, params)
                }).then(function (response) {
                    return response;
                }, function (xhr) {
                    return { success: false, message: reminderErrorMessage(xhr) };
                });
            }
        }).then(function (result) {
            if (!result.isConfirmed || !result.value) {
                return;
            }

            Swal.fire({
                icon: result.value.success ? 'success' : 'error',
                title: result.value.success ? 'Reminder Sent' : 'Reminder Not Sent',
                text: result.value.message
            });
        });
    }

    $(document)
        .on('click', '.payment-reminder-btn:not(.is-disabled)', function (event) {
            event.preventDefault();
            sendPaymentReminder(this);
        })
        .on('keydown', '.payment-reminder-btn:not(.is-disabled)', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                sendPaymentReminder(this);
            }
        });
})();
</script>
@endpush
@endonce
