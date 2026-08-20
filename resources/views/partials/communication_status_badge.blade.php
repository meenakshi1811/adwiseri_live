@php
    $messageStatus = $messageStatus ?? (isset($notificationService, $user, $message)
        ? $notificationService->messageStatusForUser($user, $message)
        : 'other');
@endphp
@if ($messageStatus === 'unread')
    <span class="comm-status-badge comm-status-unread">
        <span class="comm-status-dot" aria-hidden="true"></span>
        <i class="fas fa-envelope" aria-hidden="true"></i>
        <strong>Unread</strong>
    </span>
@elseif ($messageStatus === 'read')
    <span class="comm-status-badge comm-status-read">
        <i class="fas fa-envelope-open" aria-hidden="true"></i>
        <strong>Read</strong>
    </span>
@elseif ($messageStatus === 'sent')
    <span class="comm-status-badge comm-status-sent">
        <i class="fas fa-paper-plane" aria-hidden="true"></i>
        <strong>Sent</strong>
    </span>
@else
    <span class="comm-status-badge comm-status-muted"></span>
@endif
