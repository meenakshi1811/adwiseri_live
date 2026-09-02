<script>
    window.adwiseriConfirmApplicationClosed = function () {
        return Swal.fire({
            icon: 'warning',
            customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Close application?',
            text: 'Are you sure you want to change this application status to Closed?',
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        }).then(function (firstResult) {
            if (!firstResult.isConfirmed) {
                return false;
            }

            return Swal.fire({
                icon: 'warning',
                customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Final confirmation',
                text: 'This marks the application as fully processed to end. Confirm closure?',
                showCancelButton: true,
                confirmButtonText: 'Yes, close application',
                cancelButtonText: 'Go back'
            }).then(function (secondResult) {
                return secondResult.isConfirmed;
            });
        });
    };

    window.adwiseriBindClosedStatusFormConfirm = function (formSelector, statusSelector) {
        $(document).on('submit', formSelector, function (event) {
            const statusField = $(this).find(statusSelector).first();
            if (!statusField.length || statusField.val() !== 'Closed') {
                return true;
            }

            event.preventDefault();
            const form = this;

            window.adwiseriConfirmApplicationClosed().then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                let confirmedInput = form.querySelector('input[name="closed_confirmed"]');
                if (!confirmedInput) {
                    confirmedInput = document.createElement('input');
                    confirmedInput.type = 'hidden';
                    confirmedInput.name = 'closed_confirmed';
                    form.appendChild(confirmedInput);
                }

                confirmedInput.value = '1';
                form.submit();
            });
        });
    };
</script>
