@php
    $documents = $documents ?? collect();
    $documentsByType = $documentsByType ?? collect();
    $canDownload = $canDownload ?? true;
    $showEditActions = $showEditActions ?? false;
    $editRoute = $editRoute ?? 'client_document_update';
    $uploadRoute = $uploadRoute ?? route('client_documents');
    $panelId = 'appDocsPanel_' . ($application->id ?? '0');
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
                <button type="button" class="app-doc-view-btn active" data-view="folder" title="Folder view">
                    <i class="fas fa-folder"></i>
                </button>
                <button type="button" class="app-doc-view-btn" data-view="list" title="List view">
                    <i class="fas fa-list"></i>
                </button>
            </div>
            @if($showEditActions)
                <a href="{{ $uploadRoute }}" class="app-doc-upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Upload</span>
                </a>
            @endif
        </div>
    </div>

    @if($documents->isEmpty())
        <div class="app-documents-empty">
            <div class="app-documents-empty-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h6>No documents uploaded yet</h6>
            <p class="text-muted mb-0">Documents linked to this application will appear here, grouped by type.</p>
        </div>
    @else
        <div class="app-documents-folder-view">
            @foreach($documentsByType as $type => $typeDocs)
                @php
                    $folderId = $panelId . '_folder_' . $loop->index;
                @endphp
                <div class="app-doc-folder" data-folder-type="{{ strtolower($type) }}">
                    <button class="app-doc-folder-header {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $folderId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        <span class="app-doc-folder-icon"><i class="fas fa-folder"></i></span>
                        <span class="app-doc-folder-name">{{ $type }}</span>
                        <span class="app-doc-folder-badge">{{ $typeDocs->count() }}</span>
                        <i class="fas fa-chevron-down app-doc-folder-chevron"></i>
                    </button>
                    <div class="collapse {{ $loop->first ? 'show' : '' }}" id="{{ $folderId }}">
                        <div class="app-doc-folder-body">
                            <div class="row g-3">
                                @foreach($typeDocs as $doc)
                                    @include('web.partials.application_document_item', [
                                        'doc' => $doc,
                                        'canDownload' => $canDownload,
                                        'showEditActions' => $showEditActions,
                                        'editRoute' => $editRoute,
                                        'viewMode' => 'grid',
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="app-documents-list-view">
            <div class="table-responsive">
                <table class="table table-hover app-documents-table mb-0">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Type</th>
                            <th>Uploaded</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                            @include('web.partials.application_document_item', [
                                'doc' => $doc,
                                'canDownload' => $canDownload,
                                'showEditActions' => $showEditActions,
                                'editRoute' => $editRoute,
                                'viewMode' => 'list',
                            ])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var panel = document.getElementById(@json($panelId));
        if (!panel) return;

        var searchInput = panel.querySelector('.app-doc-search-input');
        var folderView = panel.querySelector('.app-documents-folder-view');
        var listView = panel.querySelector('.app-documents-list-view');
        var viewButtons = panel.querySelectorAll('.app-documents-view-toggle [data-view]');

        function filterDocuments(term) {
            term = (term || '').trim().toLowerCase();
            panel.querySelectorAll('[data-doc-search]').forEach(function (item) {
                var haystack = (item.getAttribute('data-doc-search') || '').toLowerCase();
                item.classList.toggle('app-doc-hidden', term !== '' && haystack.indexOf(term) === -1);
            });

            panel.querySelectorAll('.app-doc-folder').forEach(function (folder) {
                var visibleItems = folder.querySelectorAll('[data-doc-search]:not(.app-doc-hidden)');
                folder.classList.toggle('app-doc-hidden', visibleItems.length === 0);
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                filterDocuments(this.value);
            });
        }

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
    });
</script>
