<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || @json(csrf_token());

    function postMessageAction(url, onSuccess) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return response.json();
        })
        .then(function (data) {
            if (data && data.success) {
                onSuccess(data);
            }
        })
        .catch(function () {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to complete the action. Please try again.'
                });
            } else {
                alert('Unable to complete the action. Please try again.');
            }
        });
    }

    document.querySelectorAll('.js-mark-message-read').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.getAttribute('data-id');
            if (!id) {
                return;
            }

            button.disabled = true;
            postMessageAction(@json(url('/messages')) + '/' + id + '/mark-read', function () {
                window.location.reload();
            });
        });
    });

    document.querySelectorAll('.js-delete-message').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.getAttribute('data-id');
            if (!id) {
                return;
            }

            var confirmed = window.confirm('Are you sure you want to delete this message?');
            if (!confirmed) {
                return;
            }

            button.disabled = true;
            postMessageAction(@json(url('/messages')) + '/' + id + '/delete', function () {
                window.location.reload();
            });
        });
    });
});
</script>
