@php
    $editorUploadUrl = $uploadUrl ?? '';
    $editorDisabled = (bool) ($disabled ?? false);
    $editorBodyMaxLength = (int) ($bodyMaxLength ?? 50000);
    $editorUploadUrlJson = json_encode($editorUploadUrl !== '' ? $editorUploadUrl : null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $editorDisabledJson = json_encode($editorDisabled, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $editorCsrfTokenJson = json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $editorBodyMaxLengthJson = json_encode($editorBodyMaxLength, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
@endphp

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
(function () {
    const options = window.emailBroadcastEditorOptions || {};
    const uploadUrl = {!! $editorUploadUrlJson !!} || options.uploadUrl || '';
    const disabled = {!! $editorDisabledJson !!} || !!options.disabled;
    const csrfToken = {!! $editorCsrfTokenJson !!};
    const bodyMaxLength = options.bodyMaxLength || {!! $editorBodyMaxLengthJson !!};

    class BroadcastUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return this.loader.file.then(function (file) {
                return new Promise(function (resolve, reject) {
                    if (!uploadUrl) {
                        reject('Image upload is not configured.');
                        return;
                    }

                    const data = new FormData();
                    data.append('image', file);
                    data.append('_token', csrfToken);

                    fetch(uploadUrl, {
                        method: 'POST',
                        body: data,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                        .then(function (response) {
                            return response.json().then(function (payload) {
                                if (!response.ok) {
                                    throw new Error(payload.message || 'Unable to upload image.');
                                }
                                return payload;
                            });
                        })
                        .then(function (result) {
                            if (!result.url) {
                                throw new Error('Unable to upload image.');
                            }
                            resolve({ default: result.url });
                        })
                        .catch(function (error) {
                            reject(error.message || 'Unable to upload image.');
                        });
                });
            });
        }

        abort() {}
    }

    function BroadcastUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = function (loader) {
            return new BroadcastUploadAdapter(loader);
        };
    }

    function isBroadcastBodyEmpty(html) {
        if (!html) {
            return true;
        }
        const container = document.createElement('div');
        container.innerHTML = html;
        return container.textContent.replace(/\u00a0/g, ' ').trim().length === 0;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const bodyField = document.getElementById('broadcast_body');
        const bodyCount = document.getElementById('body_char_count');
        const form = document.getElementById('email_broadcast_form');
        let broadcastEditor = null;

        function currentBodyHtml() {
            return broadcastEditor ? broadcastEditor.getData() : (bodyField ? bodyField.value : '');
        }

        function currentBodyLength() {
            return currentBodyHtml().length;
        }

        function syncBodyField() {
            if (!bodyField) {
                return;
            }
            bodyField.value = currentBodyHtml();
        }

        function updateBodyCount() {
            if (!bodyCount) {
                return;
            }
            bodyCount.textContent = currentBodyLength();
        }

        function showBodyValidationError(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: message
                });
                return;
            }
            window.alert(message);
        }

        function validateBodyField(event) {
            syncBodyField();

            const bodyHtml = currentBodyHtml();
            const bodyLength = bodyHtml.length;

            if (isBroadcastBodyEmpty(bodyHtml)) {
                event.preventDefault();
                showBodyValidationError('Please enter an email body before sending.');
                return false;
            }

            if (bodyLength < 3) {
                event.preventDefault();
                showBodyValidationError('Email body must be at least 3 characters.');
                return false;
            }

            if (bodyLength > bodyMaxLength) {
                event.preventDefault();
                showBodyValidationError('Email body must be ' + bodyMaxLength.toLocaleString() + ' characters or fewer.');
                return false;
            }

            return true;
        }

        if (bodyField) {
            // CKEditor hides this textarea; native HTML5 validation cannot focus it.
            bodyField.removeAttribute('required');
            bodyField.removeAttribute('minlength');
            bodyField.removeAttribute('maxlength');
        }

        if (form) {
            form.addEventListener('submit', validateBodyField);
        }

        if (!bodyField || !window.ClassicEditor) {
            if (bodyField) {
                bodyField.addEventListener('input', updateBodyCount);
            }
            updateBodyCount();
            return;
        }

        window.ClassicEditor.create(bodyField, {
            extraPlugins: [BroadcastUploadAdapterPlugin],
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', 'uploadImage', '|',
                    'undo', 'redo'
                ],
                shouldNotGroupWhenFull: true
            },
            image: {
                toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side']
            }
        }).then(function (editor) {
            broadcastEditor = editor;
            window.emailBroadcastEditor = editor;

            if (disabled) {
                editor.enableReadOnlyMode('broadcast-disabled');
            }

            editor.model.document.on('change:data', function () {
                syncBodyField();
                updateBodyCount();
            });
            syncBodyField();
            updateBodyCount();
        }).catch(function () {
            broadcastEditor = null;
            if (bodyField) {
                bodyField.style.display = '';
                bodyField.addEventListener('input', updateBodyCount);
            }
            updateBodyCount();
        });
    });
})();
</script>
