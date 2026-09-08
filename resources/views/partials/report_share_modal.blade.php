@php
    $staffMembers = ($staffMembers ?? collect())->filter();
    $staffByDesignation = $staffMembers->groupBy(function ($staff) {
        $designation = trim((string) ($staff->designation ?? ''));
        return $designation !== '' ? $designation : 'Other';
    });
@endphp
<link rel="stylesheet" href="{{ asset('web_assets/css/report-share.css') }}">

<div id="reportShareModal" class="report-share-modal" style="display:none;" aria-hidden="true">
    <div class="report-share-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="reportShareModalTitle">
        <div class="report-share-modal__header">
            <h4 id="reportShareModalTitle" class="mb-0">Share Report / Chart</h4>
            <button type="button" class="btn-close" id="reportShareModalClose" aria-label="Close"></button>
        </div>
        <div class="report-share-modal__body">
            <p class="text-muted small mb-3" id="reportShareFileName"></p>
            <label class="form-label fw-bold">Select Recipient(s)</label>
            <div class="report-share-recipient-wrap" id="reportShareRecipients">
                @if($staffMembers->isEmpty())
                    <p class="text-danger mb-0">No staff members found in your consultancy.</p>
                @else
                    <div class="report-share-recipient-list">
                        <label class="report-share-recipient-option">
                            <input type="checkbox" id="reportShareSelectAll">
                            <strong>Select All</strong>
                        </label>
                        @foreach($staffByDesignation as $designation => $members)
                            <div class="report-share-group-title">{{ $designation }}</div>
                            @foreach($members as $staff)
                                <label class="report-share-recipient-option" data-search="{{ strtolower($staff->name . ' ' . $designation) }}">
                                    <input type="checkbox" class="report-share-recipient" name="report_share_recipients[]" value="{{ $staff->id }}">
                                    {{ $staff->name }}
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="report-share-modal__footer">
            <button type="button" class="btn btn-secondary" id="reportShareCancelBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="reportShareSendBtn" @if($staffMembers->isEmpty()) disabled @endif>Send Email</button>
        </div>
    </div>
</div>

<script>
    window.ReportShareConfig = {
        shareUrl: @json(route('share_report_chart')),
        csrfToken: @json(csrf_token()),
    };
</script>
@push('scripts')
<script src="{{ asset('web_assets/js/report-share.js') }}?v=20260908"></script>
@endpush
