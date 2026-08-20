@php
    $bell = (int) ($bellCount ?? 0);
    $envelope = (int) ($envelopeCount ?? 0);
    $notifRoute = $notificationsRoute ?? '#';
    $msgRoute = $messagesRoute ?? '#';
@endphp
<link rel="stylesheet" href="{{ asset('web_assets/css/topbar-notifications.css') }}">
<div class="adw-topbar-actions d-inline-flex align-items-center">
    <a href="{{ $notifRoute }}"
       class="adw-topbar-icon {{ $bell > 0 ? 'has-count' : '' }}"
       title="Notifications"
       aria-label="Notifications{{ $bell > 0 ? ', ' . $bell . ' unread' : '' }}">
        <i class="fas fa-bell" aria-hidden="true"></i>
        <span class="adw-topbar-badge {{ $bell > 9 ? 'is-wide' : '' }}" data-count-type="bell">{{ $bell > 99 ? '99+' : $bell }}</span>
    </a>
    <a href="{{ $msgRoute }}"
       class="adw-topbar-icon {{ $envelope > 0 ? 'has-count' : '' }}"
       title="Messages"
       aria-label="Messages{{ $envelope > 0 ? ', ' . $envelope . ' unread' : '' }}">
        <i class="fas fa-envelope" aria-hidden="true"></i>
        <span class="adw-topbar-badge {{ $envelope > 9 ? 'is-wide' : '' }}" data-count-type="envelope">{{ $envelope > 99 ? '99+' : $envelope }}</span>
    </a>
</div>
