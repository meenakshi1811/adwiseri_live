@if(strtolower($user->user_type) !== 'admin')
<div class="tab-pane fade" id="documents-list-settings" role="tabpanel" aria-labelledby="documents-list-settings-tab">
    <div class="row p-1 m-0">
        <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">Documents Checklist</p>
    </div>
    <p class="text-muted px-1 small">
        Standard document requirements by country and visa category. Use the builder under <strong>Countries &amp; Categories</strong> to add or edit lists.
    </p>
    <div class="px-1 mt-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="auto-send-document-checklist"
                {{ !empty($autoSendDocumentChecklist) ? 'checked' : '' }}>
            <label class="form-check-label" for="auto-send-document-checklist">
                Send Documents Checklist automatically upon application creation
            </label>
        </div>
    </div>
    <div class="mt-3 px-1">
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
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ccDocumentLists as $index => $entry)
                        @php
                            $documentCount = 0;
                            if (!empty($entry['sections']) && is_array($entry['sections'])) {
                                foreach ($entry['sections'] as $section) {
                                    $documentCount += count($section['documents'] ?? []);
                                }
                            } else {
                                $documentCount = count($entry['documents'] ?? []);
                            }
                        @endphp
                        <tr>
                            <td>{{ $entry['country'] ?? '' }}</td>
                            <td>{{ $entry['visa_category'] ?? '' }}</td>
                            <td>{{ $documentCount }}</td>
                            <td class="text-center action-icon">
                                <button type="button"
                                        class="btn btn-link p-0 m-0 text-dark cc-doc-list-view"
                                        data-country="{{ $entry['country'] ?? '' }}"
                                        data-visa-category="{{ $entry['visa_category'] ?? '' }}"
                                        title="View documents list">
                                    <i class="fa-solid fa-eye btn text-info p-1 m-0"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-link p-0 m-0 text-dark cc-doc-list-edit"
                                        data-country="{{ $entry['country'] ?? '' }}"
                                        data-visa-category="{{ $entry['visa_category'] ?? '' }}"
                                        title="Edit documents list">
                                    <i class="fa-solid fa-edit btn text-primary p-1 m-0"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-link p-0 m-0 text-dark cc-doc-list-share"
                                        data-country="{{ $entry['country'] ?? '' }}"
                                        data-visa-category="{{ $entry['visa_category'] ?? '' }}"
                                        title="Share documents list with staff">
                                    <i class="fa-solid fa-share-nodes btn text-success p-1 m-0"></i>
                                </button>
                            </td>
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

<div class="modal fade" id="ccDocumentListViewModal" tabindex="-1" aria-labelledby="ccDocumentListViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ccDocumentListViewModalLabel">Documents List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4 text-end">Country</dt>
                    <dd class="col-sm-8 text-start mb-2" id="cc-doc-list-view-country">—</dd>
                    <dt class="col-sm-4 text-end">Visa Category</dt>
                    <dd class="col-sm-8 text-start mb-0" id="cc-doc-list-view-category">—</dd>
                </dl>
                <div id="cc-doc-list-view-content"></div>
            </div>
        </div>
    </div>
</div>

@php
    $dcStaffMembers = ($staffMembers ?? collect())->filter();
    $dcStaffByDesignation = $dcStaffMembers->groupBy(function ($staff) {
        $designation = trim((string) ($staff->designation ?? ''));
        return $designation !== '' ? $designation : 'Other';
    });
@endphp
<link rel="stylesheet" href="{{ asset('web_assets/css/report-share.css') }}">
<div id="documentListShareModal" class="report-share-modal" style="display:none;" aria-hidden="true">
    <div class="report-share-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="documentListShareModalTitle">
        <div class="report-share-modal__header">
            <h4 id="documentListShareModalTitle" class="mb-0">Share Documents Checklist</h4>
            <button type="button" class="btn-close" id="documentListShareModalClose" aria-label="Close"></button>
        </div>
        <div class="report-share-modal__body">
            <p class="text-muted small mb-3" id="documentListShareFileName"></p>
            <label class="form-label fw-bold">Select Recipient(s)</label>
            <div class="report-share-recipient-wrap" id="documentListShareRecipients">
                @if($dcStaffMembers->isEmpty())
                    <p class="text-danger mb-0">No staff members found in your consultancy.</p>
                @else
                    <div class="report-share-recipient-list">
                        <label class="report-share-recipient-option">
                            <input type="checkbox" id="documentListShareSelectAll">
                            <strong>Select All</strong>
                        </label>
                        @foreach($dcStaffByDesignation as $designation => $members)
                            <div class="report-share-group-title">{{ $designation }}</div>
                            @foreach($members as $staff)
                                <label class="report-share-recipient-option" data-search="{{ strtolower($staff->name . ' ' . $designation) }}">
                                    <input type="checkbox" class="document-list-share-recipient" name="document_list_share_recipients[]" value="{{ $staff->id }}">
                                    {{ $staff->name }}
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="report-share-modal__footer">
            <button type="button" class="btn btn-primary" id="documentListShareSendBtn" @if($dcStaffMembers->isEmpty()) disabled data-share-disabled="true" @endif>Send Email</button>
            <button type="button" class="btn btn-secondary" id="documentListShareCancelBtn">Cancel</button>
        </div>
    </div>
</div>
@endif
