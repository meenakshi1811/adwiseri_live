@php
    $documents = $documents ?? collect();
    $documentsByFolder = $documentsByFolder ?? ($documentsByType ?? collect());
    $documentsByType = $documentsByFolder;
    $documentFolders = $documentFolders ?? [];
    $canDownload = $canDownload ?? true;
    $showAddActions = $showAddActions ?? false;
    $showEditActions = $showEditActions ?? false;
    $showDeleteActions = $showDeleteActions ?? false;
    $uploadAction = $uploadAction ?? route('upload_client_document');
    $deleteRouteName = $deleteRouteName ?? 'delete_client_document';
    $clientId = $clientId ?? ($application->client_id ?? '');
    $applicationDbId = $applicationDbId ?? ($application->id ?? '');
    $returnApplicationId = $returnApplicationId ?? ($application->id ?? '');
    $panelId = 'appDocsPanel_' . ($application->id ?? '0');
    $modalId = $panelId . '_modal';
    $formId = $panelId . '_form';
    $showDocumentListActions = $showDocumentListActions ?? false;
    $documentListRoute = $documentListRoute ?? null;
    $documentListDownloadRoute = $documentListDownloadRoute ?? null;
    $documentListSendRoute = $documentListSendRoute ?? null;
    $documentListClientEmail = $documentListClientEmail ?? '';
    $documentListConfigured = $documentListConfigured ?? true;
    $sendModalId = $panelId . '_send_modal';
@endphp

<div class="app-documents-panel mt-4" id="{{ $panelId }}">
    <div class="app-documents-header">
        <div class="app-documents-title-wrap">
            <h5 class="app-documents-title mb-0">
                <i class="fas fa-folder-open"></i>
                <span>Application Documents</span>
            </h5>
            <span class="app-documents-count">{{ $documents->count() }} {{ $documents->count() === 1 ? 'file' : 'files' }}</span>
        </div>
        <div class="app-documents-toolbar">
            <div class="app-documents-search">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control app-doc-search-input" placeholder="Search documents..." aria-label="Search documents">
            </div>
            <div class="app-documents-view-toggle" role="group" aria-label="Document view mode">
                <button type="button" class="app-doc-view-btn" data-view="folder" title="Folder view">
                    <i class="fas fa-folder"></i>
                </button>
                <button type="button" class="app-doc-view-btn active" data-view="list" title="List view">
                    <i class="fas fa-list"></i>
                </button>
            </div>
            @if($showAddActions)
                <button type="button" class="app-doc-upload-btn app-doc-open-add" data-modal="{{ $modalId }}">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload</span>
                </button>
            @endif
        </div>
    </div>

    @if($showDocumentListActions && ($documentListRoute || $documentListDownloadRoute || $documentListSendRoute))
        <div class="app-documents-checklist-bar">
            <div class="app-documents-checklist-copy">
                <h6 class="app-documents-checklist-title mb-1">
                    <i class="fas fa-clipboard-list me-1"></i> Documents Checklist
                </h6>
                <p class="app-documents-checklist-subtitle mb-0">View, print, share, or download the documents list for this application.</p>
            </div>
            <div class="app-documents-checklist-actions">
                @if($documentListRoute)
                    <a href="{{ $documentListConfigured ? $documentListRoute : '#' }}"
                        class="btn btn-sm btn-outline-primary app-doc-list-action"
                        target="_blank" rel="noopener"
                        data-doc-list-action="view"
                        @if(!$documentListConfigured) data-doc-list-unconfigured="1" @endif>
                        <i class="fas fa-eye me-1"></i> View / Print
                    </a>
                @endif
                @if($documentListDownloadRoute)
                    <a href="{{ $documentListConfigured ? $documentListDownloadRoute : '#' }}"
                        class="btn btn-sm btn-primary app-doc-list-action"
                        data-doc-list-action="download"
                        @if(!$documentListConfigured) data-doc-list-unconfigured="1" @endif>
                        <i class="fas fa-download me-1"></i> Download PDF
                    </a>
                @endif
                @if($documentListSendRoute)
                    <button type="button"
                        class="btn btn-sm btn-success app-doc-list-action app-doc-open-send"
                        data-doc-list-action="send"
                        data-send-modal="{{ $sendModalId }}"
                        @if(!$documentListConfigured) data-doc-list-unconfigured="1" @endif>
                        <i class="fas fa-paper-plane me-1"></i> Send to Client
                    </button>
                @endif
            </div>
        </div>
    @endif

    @include('web.partials.application_documents_list_section', [
        'documentChecklistItems' => $documentChecklistItems ?? [],
    ])

    @if($documents->isEmpty())
        <div class="app-documents-empty">
            <div class="app-documents-empty-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h6>No documents uploaded yet</h6>
            <p class="text-muted mb-2">Documents linked to this application will appear here.</p>
            @if($showAddActions)
                <button type="button" class="btn btn-sm btn-primary app-doc-open-add" data-modal="{{ $modalId }}">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Upload Document
                </button>
            @endif
        </div>
    @endif

    @if($documents->isNotEmpty())
        @if($documentsByFolder->count() > 1)
            <div class="app-doc-section-nav" aria-label="Document sections">
                <button type="button" class="app-doc-section-chip active" data-section-filter="">All</button>
                @foreach($documentsByFolder as $folder => $folderDocs)
                    <button type="button" class="app-doc-section-chip" data-section-filter="{{ strtolower($folder) }}">
                        {{ $folder }} ({{ $folderDocs->count() }})
                    </button>
                @endforeach
            </div>
        @endif
        <div class="app-documents-folder-view app-doc-hidden">
            @foreach($documentsByFolder as $folder => $folderDocs)
                @php $folderId = $panelId . '_folder_' . $loop->index; @endphp
                <div class="app-doc-folder" data-folder-type="{{ strtolower($folder) }}">
                    <button class="app-doc-folder-header {{ $loop->first ? '' : 'collapsed' }}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#{{ $folderId }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        <span class="app-doc-folder-icon"><i class="fas fa-folder"></i></span>
                        <span class="app-doc-folder-name">{{ $folder }}</span>
                        <span class="app-doc-folder-badge">{{ $folderDocs->count() }}</span>
                        <i class="fas fa-chevron-down app-doc-folder-chevron"></i>
                    </button>
                    <div class="collapse {{ $loop->first ? 'show' : '' }}" id="{{ $folderId }}">
                        <div class="app-doc-folder-body">
                            <div class="row g-2">
                                @foreach($folderDocs as $doc)
                                    @include('web.partials.application_document_item', [
                                        'doc' => $doc,
                                        'canDownload' => $canDownload,
                                        'showEditActions' => $showEditActions,
                                        'showDeleteActions' => $showDeleteActions,
                                        'deleteRouteName' => $deleteRouteName,
                                        'returnApplicationId' => $returnApplicationId,
                                        'modalId' => $modalId,
                                        'viewMode' => 'grid',
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="app-documents-list-view is-active">
            <div class="app-documents-list-scroll table-responsive">
                <table class="table table-hover app-documents-table mb-0">
                    <thead>
                        <tr>
                            <th class="app-doc-col-document text-start">Document</th>
                            <th class="app-doc-col-section text-center">Section</th>
                            <th class="app-doc-col-type text-center">Type</th>
                            <th class="app-doc-col-uploaded text-center">Uploaded</th>
                            <th class="app-doc-col-actions text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentsByFolder as $folder => $folderDocs)
                            <tr class="app-doc-list-folder-row" data-doc-search="{{ strtolower($folder) }}" data-doc-section="{{ strtolower($folder) }}">
                                <td colspan="5">
                                    <div class="app-doc-list-folder-label">
                                        <i class="fas fa-folder"></i>
                                        <span>{{ $folder }}</span>
                                        <span class="app-doc-folder-badge">{{ $folderDocs->count() }}</span>
                                    </div>
                                </td>
                            </tr>
                            @foreach($folderDocs as $doc)
                                @include('web.partials.application_document_item', [
                                    'doc' => $doc,
                                    'canDownload' => $canDownload,
                                    'showEditActions' => $showEditActions,
                                    'showDeleteActions' => $showDeleteActions,
                                    'deleteRouteName' => $deleteRouteName,
                                    'returnApplicationId' => $returnApplicationId,
                                    'modalId' => $modalId,
                                    'viewMode' => 'list',
                                ])
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="app-doc-preview-pane app-doc-hidden" id="{{ $panelId }}_preview" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="app-doc-preview-toolbar">
            <button type="button" class="app-doc-preview-back" aria-label="Back to documents list">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Documents</span>
            </button>
            <span class="app-doc-preview-title"></span>
            <button type="button" class="app-doc-preview-close" aria-label="Close preview">
                <i class="fas fa-times" aria-hidden="true"></i>
                <span>Close</span>
            </button>
        </div>
        <div class="app-doc-preview-body">
            <iframe class="app-doc-preview-frame app-doc-hidden" title="Document preview"></iframe>
            <img class="app-doc-preview-image app-doc-hidden" alt="Document preview">
        </div>
    </div>
</div>

@if($showDocumentListActions && $documentListSendRoute)
    <div class="modal fade app-doc-modal" id="{{ $sendModalId }}" tabindex="-1" aria-labelledby="{{ $sendModalId }}Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $sendModalId }}Label">Send Documents List to Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ $documentListSendRoute }}">
                    @csrf
                    <input type="hidden" name="local_time" class="localtime" />
                    <div class="modal-body">
                        <p class="text-muted" style="font-size:13px;">
                            The checklist for this application is emailed to the client as a PDF attachment, with the
                            list also written out in the body of the email.
                        </p>
                        <div class="mb-3">
                            <label class="form-label" for="{{ $sendModalId }}_email">Send to <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="{{ $sendModalId }}_email" name="recipient_email"
                                value="{{ $documentListClientEmail }}" placeholder="client@example.com" required>
                            <div class="form-text">Defaults to the client's email on record. Change it to send elsewhere.</div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="{{ $sendModalId }}_message">Add a message <span class="text-muted">(optional)</span></label>
                            <textarea class="form-control" id="{{ $sendModalId }}_message" name="custom_message" rows="3"
                                maxlength="2000" placeholder="Anything you want the client to know before the list..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Send Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($showAddActions || $showEditActions)
    @include('web.partials.application_document_form', [
        'formId' => $formId,
        'modalId' => $modalId,
        'uploadAction' => $uploadAction,
        'clientId' => $clientId,
        'applicationDbId' => $applicationDbId,
        'returnApplicationId' => $returnApplicationId,
        'documentTypes' => $documentTypes ?? null,
        'documentFolders' => $documentFolders ?? null,
    ])
@endif

@push('scripts')
<script>
    (function () {
        var panel = document.getElementById(@json($panelId));
        if (!panel) return;

        var searchInput = panel.querySelector('.app-doc-search-input');
        var folderView = panel.querySelector('.app-documents-folder-view');
        var listView = panel.querySelector('.app-documents-list-view');
        var viewButtons = panel.querySelectorAll('.app-documents-view-toggle [data-view]');
        var modalEl = document.getElementById(@json($modalId));
        var formEl = document.getElementById(@json($formId));
        var previewPane = document.getElementById(@json($panelId) + '_preview');
        var previewFrame = previewPane ? previewPane.querySelector('.app-doc-preview-frame') : null;
        var previewImage = previewPane ? previewPane.querySelector('.app-doc-preview-image') : null;
        var previewTitle = previewPane ? previewPane.querySelector('.app-doc-preview-title') : null;
        var previewBackBtn = previewPane ? previewPane.querySelector('.app-doc-preview-back') : null;
        var previewCloseBtn = previewPane ? previewPane.querySelector('.app-doc-preview-close') : null;
        var activeSectionFilter = '';
        var sectionChips = panel.querySelectorAll('.app-doc-section-chip');

        function applyDocumentFilters() {
            var term = searchInput ? searchInput.value.trim().toLowerCase() : '';

            document.querySelectorAll('#' + @json($panelId) + ' tr[data-doc-section], #' + @json($panelId) + ' [data-doc-section].col-md-6').forEach(function (item) {
                var haystack = (item.getAttribute('data-doc-search') || '').toLowerCase();
                var section = (item.getAttribute('data-doc-section') || '').toLowerCase();
                var matchesSearch = term === '' || haystack.indexOf(term) !== -1;
                var matchesSection = activeSectionFilter === '' || section.indexOf(activeSectionFilter) !== -1;
                item.classList.toggle('app-doc-hidden', !(matchesSearch && matchesSection));
            });

            document.querySelectorAll('#' + @json($panelId) + ' .app-doc-list-folder-row').forEach(function (folderRow) {
                var section = (folderRow.getAttribute('data-doc-section') || '').toLowerCase();
                var matchesSection = activeSectionFilter === '' || section.indexOf(activeSectionFilter) !== -1;
                var nextRow = folderRow.nextElementSibling;
                var hasVisible = false;
                while (nextRow && !nextRow.classList.contains('app-doc-list-folder-row')) {
                    if (nextRow.hasAttribute('data-doc-search') && !nextRow.classList.contains('app-doc-hidden')) {
                        hasVisible = true;
                        break;
                    }
                    nextRow = nextRow.nextElementSibling;
                }
                folderRow.classList.toggle('app-doc-hidden', !matchesSection || !hasVisible);
            });

            document.querySelectorAll('#' + @json($panelId) + ' .app-doc-folder').forEach(function (folder) {
                var folderKey = (folder.getAttribute('data-folder-type') || '').toLowerCase();
                var matchesSection = activeSectionFilter === '' || folderKey === activeSectionFilter;
                var visibleItems = folder.querySelectorAll('[data-doc-search]:not(.app-doc-hidden)');
                folder.classList.toggle('app-doc-hidden', !matchesSection || visibleItems.length === 0);
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyDocumentFilters);
        }

        sectionChips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                sectionChips.forEach(function (btn) { btn.classList.remove('active'); });
                this.classList.add('active');
                activeSectionFilter = (this.getAttribute('data-section-filter') || '').toLowerCase();
                applyDocumentFilters();
            });
        });

        viewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var view = this.getAttribute('data-view');
                viewButtons.forEach(function (btn) { btn.classList.remove('active'); });
                this.classList.add('active');
                if (view === 'list') {
                    if (folderView) folderView.classList.add('app-doc-hidden');
                    if (listView) listView.classList.add('is-active');
                } else {
                    if (folderView) folderView.classList.remove('app-doc-hidden');
                    if (listView) listView.classList.remove('is-active');
                }
            });
        });

        function openPreview(url, title, ext) {
            if (!previewPane) return;
            ext = (ext || '').toLowerCase();
            if (previewTitle) previewTitle.textContent = title || 'Document';
            if (previewFrame) {
                previewFrame.classList.add('app-doc-hidden');
                previewFrame.removeAttribute('src');
            }
            if (previewImage) {
                previewImage.classList.add('app-doc-hidden');
                previewImage.removeAttribute('src');
            }
            if (ext === 'pdf' && previewFrame) {
                previewFrame.src = url;
                previewFrame.classList.remove('app-doc-hidden');
            } else if (previewImage) {
                previewImage.src = url;
                previewImage.classList.remove('app-doc-hidden');
            }
            panel.classList.add('is-preview-open');
            previewPane.classList.remove('app-doc-hidden');
            previewPane.setAttribute('aria-hidden', 'false');
            if (previewBackBtn) {
                previewBackBtn.focus();
            }
        }

        function closePreview() {
            if (!previewPane || previewPane.classList.contains('app-doc-hidden')) return;
            if (previewFrame) {
                previewFrame.classList.add('app-doc-hidden');
                previewFrame.src = 'about:blank';
            }
            if (previewImage) {
                previewImage.classList.add('app-doc-hidden');
                previewImage.removeAttribute('src');
            }
            previewPane.classList.add('app-doc-hidden');
            previewPane.setAttribute('aria-hidden', 'true');
            panel.classList.remove('is-preview-open');
        }

        if (previewBackBtn) {
            previewBackBtn.addEventListener('click', closePreview);
        }

        if (previewCloseBtn) {
            previewCloseBtn.addEventListener('click', closePreview);
        }

        panel.addEventListener('click', function (event) {
            var previewBtn = event.target.closest('.app-doc-open-preview');
            if (!previewBtn || !panel.contains(previewBtn)) return;
            openPreview(
                previewBtn.getAttribute('data-preview-url'),
                previewBtn.getAttribute('data-preview-name'),
                previewBtn.getAttribute('data-preview-ext')
            );
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && panel.classList.contains('is-preview-open')) {
                closePreview();
            }
        });

        function showModalElement(el) {
            if (!el) return;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(el).show();
                return;
            }
            if (typeof $ !== 'undefined' && $.fn && typeof $.fn.modal === 'function') {
                $(el).modal('show');
                return;
            }
            el.classList.add('show');
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
            el.setAttribute('aria-modal', 'true');
            document.body.classList.add('modal-open');
            if (!document.querySelector('[data-app-doc-backdrop="' + el.id + '"]')) {
                var backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.setAttribute('data-app-doc-backdrop', el.id);
                document.body.appendChild(backdrop);
            }
        }

        function showAppDocModal() {
            showModalElement(modalEl);
        }

        function setFormFieldValue(selector, value) {
            var field = formEl ? formEl.querySelector(selector) : null;
            if (field) {
                field.value = value;
            }
        }

        function setModalText(selector, value) {
            var el = modalEl ? modalEl.querySelector(selector) : null;
            if (el) {
                if (selector === '.app-doc-file-required') {
                    el.style.display = value;
                } else {
                    el.textContent = value;
                }
            }
        }

        function openAddModal() {
            if (!modalEl || !formEl) return;
            setFormFieldValue('[name="id"]', '');
            setFormFieldValue('[name="doc_type"]', '');
            setFormFieldValue('[name="doc_name"]', '');
            if (typeof formEl.clearDocFolders === 'function') {
                formEl.clearDocFolders();
            } else if (typeof formEl.syncDocFolderTypes === 'function') {
                formEl.syncDocFolderTypes();
            }
            var fileInput = formEl.querySelector('[name="doc_file"]');
            if (fileInput) {
                fileInput.value = '';
                fileInput.required = true;
            }
            setModalText('.modal-title', 'Add Document');
            setModalText('.app-doc-submit-btn', 'Upload');
            setModalText('.app-doc-file-required', '');
            showAppDocModal();
        }

        function openEditModal(docId, docType, docName, docFolder) {
            if (!modalEl || !formEl) return;
            setFormFieldValue('[name="id"]', docId);
            setFormFieldValue('[name="doc_type"]', docType);
            setFormFieldValue('[name="doc_name"]', docName);
            if (typeof formEl.setDocNameAutoBaseline === 'function') {
                formEl.setDocNameAutoBaseline(docName);
            }
            if (typeof formEl.setDocFolder === 'function') {
                formEl.setDocFolder(docFolder || '');
            }
            if (typeof formEl.syncDocFolderTypes === 'function') {
                formEl.syncDocFolderTypes(docType);
            }
            var fileInput = formEl.querySelector('[name="doc_file"]');
            if (fileInput) {
                fileInput.value = '';
                fileInput.required = false;
            }
            setModalText('.modal-title', 'Replace / Update Document');
            setModalText('.app-doc-submit-btn', 'Save Changes');
            setModalText('.app-doc-file-required', 'none');
            showAppDocModal();
        }

        panel.addEventListener('click', function (event) {
            var listAction = event.target.closest('.app-doc-list-action[data-doc-list-unconfigured="1"]');
            if (listAction && panel.contains(listAction)) {
                event.preventDefault();
                var message = 'No document list is configured for this application\'s country and visa category, and no uploaded documents were found. Add a list under Settings → Countries & Visa Categories, or upload documents to this application first.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Document List unavailable',
                        text: message
                    });
                } else {
                    alert(message);
                }
                return;
            }

            var sendBtn = event.target.closest('.app-doc-open-send');
            if (sendBtn && panel.contains(sendBtn)) {
                event.preventDefault();
                showModalElement(document.getElementById(sendBtn.getAttribute('data-send-modal')));
                return;
            }

            var addBtn = event.target.closest('.app-doc-open-add[data-modal="' + @json($modalId) + '"]');
            if (addBtn && panel.contains(addBtn)) {
                event.preventDefault();
                openAddModal();
            }
        });

        panel.querySelectorAll('.app-doc-open-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openEditModal(
                    this.getAttribute('data-doc-id'),
                    this.getAttribute('data-doc-type'),
                    this.getAttribute('data-doc-name'),
                    this.getAttribute('data-doc-folder')
                );
            });
        });

        panel.querySelectorAll('.app-doc-open-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var deleteUrl = this.getAttribute('data-delete-url');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#695EEE',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then(function (result) {
                        if (result.isConfirmed) window.location.href = deleteUrl;
                    });
                } else if (confirm('Are you sure you want to delete this document?')) {
                    window.location.href = deleteUrl;
                }
            });
        });

        if (formEl) {
            formEl.addEventListener('change', function (e) {
                if (e.target && e.target.name === 'doc_file' && e.target.files.length) {
                    var filepath = e.target.value;
                    var allowed = /(\.jpg|\.jpeg|\.png|\.pdf)$/i;
                    if (!allowed.exec(filepath)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: 'Oops!', icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, html: 'Please select a valid file format (jpg, jpeg, png, or pdf).' });
                        }
                        e.target.value = '';
                        return;
                    }
                    var size = (e.target.files[0].size / 1024 / 1024).toFixed(2);
                    if (size > 4) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: 'Oops!', icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, html: 'Please select a file up to 4 MB.' });
                        }
                        e.target.value = '';
                    }
                }
            });
        }
    })();
</script>
@endpush
