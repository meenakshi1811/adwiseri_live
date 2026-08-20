@php
    if (!isset($unreadMessageCount) && isset($notificationService, $user)) {
        $unreadMessageCount = $notificationService->envelopeCount($user);
    }
    $unreadMessageCount = (int) ($unreadMessageCount ?? 0);

    if (!isset($messageStatusCounts) && isset($notificationService, $user)) {
        $messageStatusCounts = $notificationService->messageStatusCountsForUser($user);
    }
    $messageStatusCounts = array_merge(
        ['unread' => 0, 'read' => 0, 'sent' => 0],
        $messageStatusCounts ?? []
    );
@endphp
<div class="comm-inbox-toolbar">
    <div class="comm-inbox-toolbar__header">
        <div class="comm-inbox-toolbar__title-group">
            <span class="comm-inbox-toolbar__icon"><i class="fas fa-inbox"></i></span>
            <div>
                <p class="comm-inbox-toolbar__title">Inbox overview</p>
                <p class="comm-inbox-toolbar__subtitle">Unread messages are highlighted in the list below</p>
            </div>
        </div>

        @if($unreadMessageCount > 0)
            <div class="comm-inbox-toolbar__header-actions">
                <span class="comm-inbox-toolbar__count">{{ $unreadMessageCount }} unread</span>
                <button type="button" class="comm-mark-all-read-btn" id="mark-all-messages-read">
                    <i class="fas fa-check-double"></i>
                    <span>Mark all as read</span>
                </button>
            </div>
        @endif
    </div>

    <div class="comm-inbox-toolbar__legend table-filter-toolbar__items"
         data-table-filter-toolbar
         data-table-id="{{ $communicationsTableId ?? 'clientTable' }}"
         data-filter-attr="data-message-status">
        <button type="button"
                class="table-filter-btn table-filter-btn--tone-0 comm-legend-item is-filterable is-active"
                data-filter-value=""
                aria-pressed="true">
            <span class="comm-legend-item__text">All Messages</span>
            <span class="comm-legend-item__count">{{ array_sum($messageStatusCounts) }}</span>
        </button>
        <button type="button"
                class="table-filter-btn table-filter-btn--tone-1 comm-legend-item is-filterable"
                data-filter-value="unread"
                aria-pressed="false">
            @include('partials.communication_status_badge', ['messageStatus' => 'unread'])
            <span class="comm-legend-item__count">{{ $messageStatusCounts['unread'] }}</span>
            <span class="comm-legend-item__text">Not opened yet</span>
        </button>
        <button type="button"
                class="table-filter-btn table-filter-btn--tone-2 comm-legend-item is-filterable"
                data-filter-value="read"
                aria-pressed="false">
            @include('partials.communication_status_badge', ['messageStatus' => 'read'])
            <span class="comm-legend-item__count">{{ $messageStatusCounts['read'] }}</span>
            <span class="comm-legend-item__text">Already viewed</span>
        </button>
        <button type="button"
                class="table-filter-btn table-filter-btn--tone-3 comm-legend-item is-filterable"
                data-filter-value="sent"
                aria-pressed="false">
            @include('partials.communication_status_badge', ['messageStatus' => 'sent'])
            <span class="comm-legend-item__count">{{ $messageStatusCounts['sent'] }}</span>
            <span class="comm-legend-item__text">Sent by you</span>
        </button>
    </div>
</div>
<link rel="stylesheet" href="{{ asset('web_assets/css/table-filter-toolbar.css') }}">
@push('scripts')
@include('partials.table_filter_scripts', ['tableId' => $communicationsTableId ?? 'clientTable', 'filterAttribute' => 'data-message-status'])
@endpush

<script>
document.addEventListener('DOMContentLoaded', function () {
    const markAllMessages = document.getElementById('mark-all-messages-read');
    if (!markAllMessages) {
        return;
    }

    markAllMessages.addEventListener('click', function () {
        const original = markAllMessages.innerHTML;
        markAllMessages.disabled = true;
        markAllMessages.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Updating...</span>';

        fetch(@json(route('messages_mark_all_read')), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || @json(csrf_token()),
                'Accept': 'application/json',
            },
        })
        .then(function () { window.location.reload(); })
        .catch(function () {
            markAllMessages.disabled = false;
            markAllMessages.innerHTML = original;
        });
    });
});
</script>
