@php
    $extension = strtolower(pathinfo($doc->doc_file ?? '', PATHINFO_EXTENSION));
    $filePath = public_path('web_assets/users/client' . $doc->client_id . '/docs/' . $doc->doc_file);
    $fileExists = !empty($doc->doc_file) && file_exists($filePath);
    $fileUrl = asset('web_assets/users/client' . $doc->client_id . '/docs/' . $doc->doc_file);
    $fileSize = $fileExists ? number_format(filesize($filePath) / 1024, 1) . ' KB' : '';
    $iconClass = 'is-file';
    $icon = 'fa-file-alt';

    if ($extension === 'pdf') {
        $iconClass = 'is-pdf';
        $icon = 'fa-file-pdf';
    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $iconClass = 'is-image';
        $icon = 'fa-file-image';
    }

    $rawName = trim($doc->doc_name ?? '');
    $displayName = $rawName !== '' ? $rawName : ($doc->doc_file ?: 'Untitled document');
    if (strlen($displayName) <= 3 || strtolower($displayName) === 'xxx') {
        $displayName = preg_replace('/^\d+/', '', (string) ($doc->doc_file ?: $displayName));
        $displayName = trim($displayName) ?: ($doc->doc_file ?: 'Untitled document');
    }
    $showFileName = !empty($doc->doc_file)
        && strcasecmp(trim($displayName), trim($doc->doc_file)) !== 0;
    $searchText = trim(($doc->doc_name ?? '') . ' ' . ($doc->doc_type ?? '') . ' ' . ($doc->doc_file ?? ''));
    $canPreview = $fileExists && in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
@endphp

@if(($viewMode ?? 'grid') === 'list')
    <tr data-doc-search="{{ $searchText }}">
        <td>
            <div class="app-doc-list-name">
                <span class="app-doc-file-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></span>
                <div class="app-doc-list-text">
                    <strong>{{ $displayName }}</strong>
                    @if($showFileName)
                        <span class="app-doc-list-sub">{{ $doc->doc_file }}</span>
                    @endif
                </div>
            </div>
        </td>
        <td>{{ $doc->doc_type ?: 'Other' }}</td>
        <td>
            <span>{{ date('d M Y', strtotime($doc->created_at)) }}</span>
            @if($fileSize)
                <span class="app-doc-list-sub">{{ $fileSize }}</span>
            @endif
        </td>
        <td class="text-end">
            <div class="app-doc-card-actions app-doc-card-actions-inline">
                @if($fileExists && $canPreview && $canDownload)
                    <a href="{{ $fileUrl }}" target="_blank" class="app-doc-action-icon" title="View" aria-label="View">
                        <i class="fas fa-eye"></i>
                    </a>
                @endif
                @if($fileExists && $canDownload)
                    <a href="{{ $fileUrl }}" download="{{ $doc->doc_file }}" class="app-doc-action-icon" title="Download" aria-label="Download">
                        <i class="fas fa-download"></i>
                    </a>
                @endif
                @if($showEditActions)
                    <a href="{{ route($editRoute, $doc->id) }}" class="app-doc-action-icon" title="Edit" aria-label="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@else
    <div class="col-md-6 col-xl-4" data-doc-search="{{ $searchText }}">
        <div class="app-doc-card">
            <div class="app-doc-card-top">
                <span class="app-doc-file-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></span>
                <div class="app-doc-card-content">
                    <div class="app-doc-card-name" title="{{ $displayName }}">{{ $displayName }}</div>
                    <div class="app-doc-card-meta">
                        <span>{{ date('d M Y', strtotime($doc->created_at)) }}</span>
                        @if($fileSize)
                            <span class="app-doc-meta-sep">{{ $fileSize }}</span>
                        @endif
                    </div>
                    @if($showFileName)
                        <div class="app-doc-card-filename" title="{{ $doc->doc_file }}">{{ $doc->doc_file }}</div>
                    @endif
                </div>
            </div>
            <div class="app-doc-card-actions">
                @if($fileExists && $canPreview && $canDownload)
                    <a href="{{ $fileUrl }}" target="_blank" class="app-doc-action-icon" title="View" aria-label="View">
                        <i class="fas fa-eye"></i>
                    </a>
                @endif
                @if($fileExists && $canDownload)
                    <a href="{{ $fileUrl }}" download="{{ $doc->doc_file }}" class="app-doc-action-icon" title="Download" aria-label="Download">
                        <i class="fas fa-download"></i>
                    </a>
                @endif
                @if($showEditActions)
                    <a href="{{ route($editRoute, $doc->id) }}" class="app-doc-action-icon" title="Edit" aria-label="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
