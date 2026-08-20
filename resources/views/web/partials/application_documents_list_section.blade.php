@if(!empty($documentChecklistItems))
<div class="app-documents-list-section mt-3">
    <div class="app-documents-checklist-bar mb-0">
        <div class="app-documents-checklist-copy">
            <h6 class="app-documents-checklist-title mb-1">
                <i class="fas fa-list-check me-1"></i> Documents List
            </h6>
            <p class="app-documents-checklist-subtitle mb-0">Standard document requirements for this application with received / missing status.</p>
        </div>
    </div>
    <div class="table-responsive mt-2">
        <table class="table table-sm table-hover mb-0 app-documents-list-table">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Section</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documentChecklistItems as $item)
                    <tr>
                        <td>{{ $item['label'] }}</td>
                        <td>{{ $item['section'] ?: '—' }}</td>
                        <td class="text-center">
                            @if($item['status'] === 'received')
                                <span class="badge bg-success">Received</span>
                            @else
                                <span class="badge bg-warning text-dark">Missing</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
