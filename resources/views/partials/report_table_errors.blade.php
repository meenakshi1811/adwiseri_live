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

        // A successful load clears the one-off retry below, so a later failure on the same table is retried too.
        $(document).on('xhr.dt', function (event, settings, json) {
            if (json) {
                settings._reportRetried = false;
            }
        });

        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            var xhr = settings ? settings.jqXHR : null;
            var status = xhr ? xhr.status : 0;
            var appMessage = serverMessage(xhr);

            console.error('Report table failed to load', {
                table: settings ? settings.sTableId : null,
                status: status,
                message: message,
                response: xhr && xhr.responseText ? xhr.responseText.slice(0, 300) : ''
            });

            // A 403 without the app's JSON message comes from the web server's firewall / rate limiting rather
            // than the app (which always explains its own refusals). It is usually temporary, so retry once quietly.
            if (techNote === 7 && status === 403 && !appMessage && settings && !settings._reportRetried) {
                settings._reportRetried = true;
                setTimeout(function () {
                    new $.fn.dataTable.Api(settings).ajax.reload(null, false);
                }, 1500);
                return;
            }

            // 1 = invalid JSON, 7 = Ajax error. Other notes are column-config warnings meant for developers.
            if ((techNote !== 1 && techNote !== 7) || alertOpen) {
                return;
            }
            alertOpen = true;

            var shown;
            if (status === 401 || status === 419) {
                shown = showAlert('oops', appMessage || 'Your session has expired. Please log in again.', 'Session expired')
                    .then(function () {
                        window.location.reload();
                    });
            } else if (status === 403 && appMessage) {
                shown = showAlert('oops', appMessage);
            } else if (status === 403) {
                shown = showAlert('error', 'The server blocked this report request (Error 403). Please wait a moment and try again. If it keeps happening, please contact support.');
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
