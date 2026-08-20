@php
    $extension = strtolower(pathinfo($doc->doc_file ?? '', PATHINFO_EXTENSION));
    $filePath = public_path('web_assets/users/client' . $doc->client_id . '/docs/' . $doc->doc_file);
    $fileExists = !empty($doc->doc_file) && file_exists($filePath);
    $fileUrl = asset('web_assets/users/client' . $doc->client_id . '/docs/' . $doc->doc_file);
    $fileSize = $fileExists ? number_format(filesize($filePath) / 1024, 1) . ' KB' : '';
    $iconClass = 'is-file';
    $icon = 'fa-file-alt';
    $permittedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

    if ($extension === 'pdf') {
        $iconClass = 'is-pdf';
        $icon = 'fa-file-pdf';
    } elseif (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        $iconClass = 'is-image';
        $icon = 'fa-file-image';
    }

    $rawName = trim($doc->doc_name ?? '');
    $displayName = \App\Support\DocumentFileName::forTable($doc->doc_file ?? '', $rawName !== '' ? $rawName : null, 28);
    $ccService = app(\App\Services\CountryCategorySettingsService::class);
    $docFolders = \App\Support\ApplicationDocumentFolders::resolveForDoc($ccService, $doc);
    $docFolder = $docFolders[0] ?? 'Other';
    $searchText = trim(($doc->doc_name ?? '') . ' ' . ($doc->doc_type ?? '') . ' ' . $docFolder . ' ' . ($doc->doc_file ?? ''));
    $docSectionKeys = strtolower($docFolder);
    $canPreview = $fileExists && in_array($extension, $permittedExtensions, true) && ($canDownload ?? true);
    $deleteUrl = route($deleteRouteName ?? 'delete_client_document', $doc->id)
        . '?return_application_id=' . ($returnApplicationId ?? '');
@endphp

@if(($viewMode ?? 'grid') === 'list')
    <tr data-doc-search="{{ $searchText }}" data-doc-section="{{ $docSectionKeys }}">
        <td class="app-doc-name-cell">
            <div class="app-doc-list-name">
                <span class="app-doc-file-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></span>
                <div class="app-doc-list-text">
                    <strong>{{ $displayName }}</strong>
                </div>
            </div>
        </td>
        <td class="app-doc-section-cell text-center">
            <span class="app-doc-section-badge" title="{{ $docFolder }}">{{ $docFolder }}</span>
        </td>
        <td class="app-doc-type-cell text-center">{{ $doc->doc_type ?: 'Other' }}</td>
        <td class="app-doc-date-cell text-center">
            <span>{{ date('d M Y', strtotime($doc->created_at)) }}</span>
            @if($fileSize)
                <span class="app-doc-list-sub">{{ $fileSize }}</span>
            @endif
        </td>
        <td class="app-doc-actions-cell text-center">
            <div class="app-doc-card-actions app-doc-card-actions-inline">
                <span class="app-doc-action-slot">
                @if($canPreview)
                    <button type="button" class="app-doc-action-icon app-doc-open-preview" title="View" aria-label="View"
                        data-preview-url="{{ $fileUrl }}"
                        data-preview-name="{{ $displayName }}"
                        data-preview-ext="{{ $extension }}">
                        <i class="fas fa-eye"></i>
                    </button>
                @else
                    <span class="app-doc-action-icon app-doc-action-placeholder" aria-hidden="true"></span>
                @endif
                </span>
                <span class="app-doc-action-slot">
                @if($fileExists && $canDownload)
                    <a href="{{ $fileUrl }}" download="{{ $doc->doc_file }}" class="app-doc-action-icon" title="Download" aria-label="Download">
                        <i class="fas fa-download"></i>
                    </a>
                @else
                    <span class="app-doc-action-icon app-doc-action-placeholder" aria-hidden="true"></span>
                @endif
                </span>
                <span class="app-doc-action-slot">
                @if($showEditActions)
                    <button type="button" class="app-doc-action-icon app-doc-open-edit" title="Replace / Update" aria-label="Replace"
                        data-doc-id="{{ $doc->id }}" data-doc-type="{{ $doc->doc_type }}" data-doc-name="{{ $doc->doc_name }}"
                        data-doc-folder="{{ $docFolder }}">
                        <i class="fas fa-edit"></i>
                    </button>
                @else
                    <span class="app-doc-action-icon app-doc-action-placeholder" aria-hidden="true"></span>
                @endif
                </span>
                <span class="app-doc-action-slot">
                @if($showDeleteActions)
                    <button type="button" class="app-doc-action-icon app-doc-action-danger app-doc-open-delete" title="Delete" aria-label="Delete"
                        data-delete-url="{{ $deleteUrl }}">
                        <i class="fas fa-trash"></i>
                    </button>
                @else
                    <span class="app-doc-action-icon app-doc-action-placeholder" aria-hidden="true"></span>
                @endif
                </span>
            </div>
        </td>
    </tr>
@else
    <div class="col-md-6 col-xl-4" data-doc-search="{{ $searchText }}" data-doc-section="{{ $docSectionKeys }}">
        <div class="app-doc-card">
            <div class="app-doc-card-top">
                <span class="app-doc-file-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></span>
                <div class="app-doc-card-content">
                    <span class="app-doc-section-badge app-doc-section-badge-card">{{ $docFolder }}</span>
                    <div class="app-doc-card-name" title="{{ $displayName }}">{{ $displayName }}</div>
                    <div class="app-doc-card-meta">
                        <span>{{ date('d M Y', strtotime($doc->created_at)) }}</span>
                        @if($fileSize)
                            <span class="app-doc-meta-sep">{{ $fileSize }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="app-doc-card-actions">
                @if($canPreview)
                    <button type="button" class="app-doc-action-icon app-doc-open-preview" title="View" aria-label="View"
                        data-preview-url="{{ $fileUrl }}"
                        data-preview-name="{{ $displayName }}"
                        data-preview-ext="{{ $extension }}">
                        <i class="fas fa-eye"></i>
                    </button>
                @endif
                @if($fileExists && $canDownload)
                    <a href="{{ $fileUrl }}" download="{{ $doc->doc_file }}" class="app-doc-action-icon" title="Download" aria-label="Download">
                        <i class="fas fa-download"></i>
                    </a>
                @endif
                @if($showEditActions)
                    <button type="button" class="app-doc-action-icon app-doc-open-edit" title="Replace / Update" aria-label="Replace"
                        data-doc-id="{{ $doc->id }}" data-doc-type="{{ $doc->doc_type }}" data-doc-name="{{ $doc->doc_name }}"
                        data-doc-folder="{{ $docFolder }}">
                        <i class="fas fa-edit"></i>
                    </button>
                @endif
                @if($showDeleteActions)
                    <button type="button" class="app-doc-action-icon app-doc-action-danger app-doc-open-delete" title="Delete" aria-label="Delete"
                        data-delete-url="{{ $deleteUrl }}">
                        <i class="fas fa-trash"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
@endif
