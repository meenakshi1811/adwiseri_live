@php
    $formId = $formId ?? 'appDocForm';
    $modalId = $modalId ?? 'appDocModal';
    $uploadAction = $uploadAction ?? route('upload_client_document');
    $clientId = $clientId ?? ($application->client_id ?? '');
    $applicationDbId = $applicationDbId ?? ($application->id ?? '');
    $returnApplicationId = $returnApplicationId ?? ($application->id ?? '');
    $ccService = app(\App\Services\CountryCategorySettingsService::class);
    $defaultDocTypes = $ccService->getDocumentTypes();
    $defaultDocumentFolders = $ccService->getDocumentFolders();
    $documentTypes = $documentTypes ?? $defaultDocTypes;
    $documentFolders = $documentFolders ?? $defaultDocumentFolders;
@endphp

<div class="modal fade app-doc-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">Add Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="{{ $formId }}" method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data"
                data-doc-folders='@json($documentFolders)'>
                @csrf
                <input type="hidden" name="local_time" class="localtime" />
                <input type="hidden" name="id" id="{{ $formId }}_doc_id" value="" />
                <input type="hidden" name="client_id" value="{{ $clientId }}" />
                <input type="hidden" name="application_id" value="{{ $applicationDbId }}" />
                <input type="hidden" name="return_application_id" value="{{ $returnApplicationId }}" />
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label d-block">Folder / Section <span class="text-danger">*</span></label>
                        <div class="app-doc-folder-radios" id="{{ $formId }}_doc_folders">
                            @foreach($documentFolders as $folder => $types)
                                <label class="app-doc-folder-radio">
                                    <input type="radio" name="doc_folder" value="{{ $folder }}" class="form-check-input">
                                    <span>{{ $folder }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="form-text">Select one section for this document.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="{{ $formId }}_doc_type">Document Type <span class="text-danger">*</span></label>
                        <select name="doc_type" id="{{ $formId }}_doc_type" class="form-select" required disabled>
                            <option value="">Select section first</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="{{ $formId }}_doc_name">Document Name <span class="text-danger">*</span></label>
                        <input name="doc_name" type="text" id="{{ $formId }}_doc_name" minlength="3" maxlength="100"
                            class="form-control" required placeholder="Document Name" autocomplete="off" />
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="{{ $formId }}_doc_file">
                            Document File <span class="text-danger app-doc-file-required">*</span>
                        </label>
                        <input name="doc_file" type="file" id="{{ $formId }}_doc_file" class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf" />
                        <div class="form-text">jpg, jpeg, png, or pdf — max 4 MB</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary app-doc-submit-btn">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var formEl = document.getElementById(@json($formId));
        if (!formEl) return;

        var folderRadios = formEl.querySelectorAll('[name="doc_folder"]');
        var typeSelect = formEl.querySelector('[name="doc_type"]');
        var nameInput = formEl.querySelector('[name="doc_name"]');
        var folderMap = {};
        var lastAutoDocName = '';

        try {
            folderMap = JSON.parse(formEl.getAttribute('data-doc-folders') || '{}');
        } catch (e) {
            folderMap = {};
        }

        function syncDocNameFromType(force) {
            if (!typeSelect || !nameInput) return;

            var type = (typeSelect.value || '').trim();
            if (type === '') return;

            var currentName = (nameInput.value || '').trim();
            if (force || currentName === '' || currentName === lastAutoDocName) {
                nameInput.value = type;
                lastAutoDocName = type;
            }
        }

        function getSelectedFolder() {
            var selected = formEl.querySelector('[name="doc_folder"]:checked');
            return selected ? selected.value : '';
        }

        function populateDocTypes(selectedType) {
            var folder = getSelectedFolder();
            var types = folderMap[folder] || [];
            var previous = selectedType || (typeSelect ? typeSelect.value : '');

            if (!typeSelect) return;

            typeSelect.innerHTML = '';
            typeSelect.disabled = types.length === 0;

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = types.length ? 'Select Document Type' : 'Select section first';
            typeSelect.appendChild(placeholder);

            types.forEach(function (type) {
                var option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                if (previous && previous === type) {
                    option.selected = true;
                }
                typeSelect.appendChild(option);
            });

            if (typeSelect.value) {
                syncDocNameFromType(false);
            }
        }

        formEl.syncDocFolderTypes = populateDocTypes;

        formEl.setDocFolder = function (folder) {
            folderRadios.forEach(function (input) {
                input.checked = input.value === folder;
            });
            populateDocTypes(typeSelect ? typeSelect.value : '');
        };

        formEl.clearDocFolders = function () {
            folderRadios.forEach(function (input) { input.checked = false; });
            lastAutoDocName = '';
            populateDocTypes('');
        };

        formEl.setDocNameAutoBaseline = function (name) {
            lastAutoDocName = (name || '').trim();
        };

        folderRadios.forEach(function (input) {
            input.addEventListener('change', function () {
                if (nameInput) {
                    nameInput.value = '';
                }
                lastAutoDocName = '';
                populateDocTypes('');
            });
        });

        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                syncDocNameFromType(true);
            });
        }

        formEl.addEventListener('submit', function (e) {
            if (!getSelectedFolder()) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Select a section', text: 'Please choose one folder / section.' });
                } else {
                    AdwiseriAlert.oops('Please choose one folder / section.');
                }
            }
        });
    })();
</script>
@endpush
