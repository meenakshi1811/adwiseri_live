@php
    $editorUploadUrl = $uploadUrl ?? '';
    $editorDisabled = (bool) ($disabled ?? false);
@endphp

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
(function () {
    const options = window.emailBroadcastEditorOptions || {};
    const uploadUrl = @json($editorUploadUrl ?: null) || options.uploadUrl || '';
    const disabled = @json($editorDisabled) || !!options.disabled;
    const csrfToken = @json(csrf_token());
    const bodyMaxLength = 50000;

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

    document.addEventListener('DOMContentLoaded', function () {
        const bodyField = document.getElementById('broadcast_body');
        const bodyCount = document.getElementById('body_char_count');
        const form = document.getElementById('email_broadcast_form');
        let broadcastEditor = null;

        function currentBodyLength() {
            const value = broadcastEditor ? broadcastEditor.getData() : (bodyField ? bodyField.value : '');
            return value.length;
        }

        function updateBodyCount() {
            if (!bodyCount) {
                return;
            }
            bodyCount.textContent = currentBodyLength();
        }

        if (!bodyField || !window.ClassicEditor) {
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

            editor.model.document.on('change:data', updateBodyCount);
            updateBodyCount();
        }).catch(function () {
            broadcastEditor = null;
            if (bodyField) {
                bodyField.addEventListener('input', updateBodyCount);
            }
            updateBodyCount();
        });

        if (form) {
            form.addEventListener('submit', function () {
                if (broadcastEditor && bodyField) {
                    bodyField.value = broadcastEditor.getData();
                }

                if (bodyField && bodyField.value.length > bodyMaxLength) {
                    bodyField.setCustomValidity('Email body must be ' + bodyMaxLength.toLocaleString() + ' characters or fewer.');
                } else if (bodyField) {
                    bodyField.setCustomValidity('');
                }
            });
        }
    });
})();
</script>
