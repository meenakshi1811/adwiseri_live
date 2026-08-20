@if(strtolower($user->user_type) !== 'admin')
<div class="tab-pane fade" id="documents-list-settings" role="tabpanel" aria-labelledby="documents-list-settings-tab">
    <div class="row p-1 m-0">
        <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">Documents List</p>
    </div>
    <p class="text-muted px-1 small">
        Standard document requirements by country and visa category. Use the builder under <strong>Countries &amp; Categories</strong> to add or edit lists.
    </p>
    <div class="mt-3">
        <button type="button" class="btn btn-sm btn-outline-primary" id="open-cc-doc-builder">
            <i class="fa fa-pen-to-square me-1"></i> Manage Document Lists
        </button>
    </div>
    @if(!empty($ccDocumentLists))
        <div class="table-responsive mt-3">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Visa Category</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ccDocumentLists as $entry)
                        <tr>
                            <td>{{ $entry['country'] ?? '' }}</td>
                            <td>{{ $entry['visa_category'] ?? '' }}</td>
                            <td>{{ count($entry['documents'] ?? []) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="cc-empty-state mt-3">
            <div><i class="fa fa-folder-open"></i></div>
            <div class="fw-semibold text-dark">No document lists yet</div>
        </div>
    @endif
</div>
@endif
