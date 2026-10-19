<script>
    // Report tables load their rows over Ajax. When a request fails, DataTables' default is a raw
    // browser alert ("DataTables warning: table id=... - Ajax error") on whichever tab is open.
    // Show a readable message instead, and reload the page when the session has ended so the
    // user lands back on the login screen.
    document.addEventListener('DOMContentLoaded', function () {
        var $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.dataTable) {
            return;
        }

        var alertOpen = false;

        function serverMessage(xhr) {
            var json = xhr ? xhr.responseJSON : null;
            if (!json && xhr && xhr.responseText) {
                try {
                    json = JSON.parse(xhr.responseText);
                } catch (e) {
                    json = null;
                }
            }
            var message = json && typeof json.message === 'string' ? json.message.trim() : '';
            return message === 'Unauthenticated.' ? '' : message;
        }

        function showAlert(kind, text, title) {
            if (window.AdwiseriAlert && typeof window.AdwiseriAlert[kind] === 'function') {
                return Promise.resolve(window.AdwiseriAlert[kind](text, title));
            }
            window.alert(text);
            return Promise.resolve();
        }

        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            var xhr = settings ? settings.jqXHR : null;
            var status = xhr ? xhr.status : 0;

            console.error('Report table failed to load', {
                table: settings ? settings.sTableId : null,
                status: status,
                message: message
            });

            // 1 = invalid JSON, 7 = Ajax error. Other notes are column-config warnings meant for developers.
            if ((techNote !== 1 && techNote !== 7) || alertOpen) {
                return;
            }
            alertOpen = true;

            var shown;
            if (status === 401 || status === 419) {
                shown = showAlert('oops', serverMessage(xhr) || 'Your session has expired. Please log in again.', 'Session expired')
                    .then(function () {
                        window.location.reload();
                    });
            } else if (status === 403) {
                shown = showAlert('oops', serverMessage(xhr) || 'You do not have access to this report.');
            } else {
                shown = showAlert('error', 'We could not load this report right now. Please try again in a moment.' + (status ? ' (Error ' + status + ')' : ''));
            }

            shown.then(function () {
                alertOpen = false;
            }, function () {
                alertOpen = false;
            });
        };
    });
</script>
