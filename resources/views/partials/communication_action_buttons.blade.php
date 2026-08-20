@php
    $messageStatus = $messageStatus ?? (isset($notificationService, $user, $message)
        ? $notificationService->messageStatusForUser($user, $message)
        : 'other');

    $canView = $canView ?? true;
    $canDelete = $canDelete ?? false;
    $viewRoute = $viewRoute ?? '#';
    $isUnread = $messageStatus === 'unread';
@endphp
<div class="comm-action-btns">
    @if($canView)
        <a href="{{ $viewRoute }}" class="comm-action-btn comm-view-btn" title="View">
            <i class="fa-solid fa-eye"></i>
        </a>
    @else
        <span class="comm-action-btn comm-action-btn--disabled" title="View not allowed">
            <i class="fa-solid fa-eye"></i>
        </span>
    @endif

    @if($isUnread)
        <button
            type="button"
            class="comm-action-btn comm-mark-read-btn js-mark-message-read"
            title="Mark as read"
            data-id="{{ $message->id }}"
        >
            <i class="fa-solid fa-envelope-open-text"></i>
        </button>
    @else
        <span class="comm-action-btn comm-action-btn--muted" title="Already read / sent">
            <i class="fa-solid fa-envelope-open-text"></i>
        </span>
    @endif

    @if($canDelete)
        <button
            type="button"
            class="comm-action-btn comm-delete-btn js-delete-message"
            title="Delete"
            data-id="{{ $message->id }}"
        >
            <i class="fa-solid fa-trash"></i>
        </button>
    @endif
</div>
