<link rel="stylesheet" href="{{ asset('web_assets/css/topbar-notifications.css') }}">
@php
    $canClearNotifications = $canClearNotifications ?? false;
@endphp
<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="client-btn d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h3 class="text-primary mb-1">Notifications</h3>
                <p class="text-muted mb-0" style="font-size:0.88rem;">{{ $notificationsSubtitle ?? 'System alerts and updates based on your preferences' }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($notifications->whereNull('read_at')->count() > 0)
                    <button type="button" class="btn btn-outline-primary btn-sm" id="mark-all-notifications-read">
                        <i class="fas fa-check-double me-1"></i> Mark all as read
                    </button>
                @endif
                @if($canClearNotifications && $notifications->count() > 0)
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clear-selected-notifications" disabled>
                        <i class="fas fa-trash-alt me-1"></i> Clear selected
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clear-all-notifications">
                        <i class="fas fa-trash me-1"></i> Clear all
                    </button>
                @endif
            </div>
        </div>

        <div class="adw-notifications-shell">
            <div class="adw-notifications-header">
                <div>
                    <h4><i class="fas fa-bell me-2"></i>Your notifications</h4>
                    <p>{{ $bellCount }} unread &middot; {{ $notifications->count() }} total shown</p>
                </div>
                <a href="{{ $messagesRoute }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-envelope me-1"></i> {{ $messagesLabel ?? 'Messages' }}
                    @if($envelopeCount > 0)
                        <span class="badge bg-danger ms-1">{{ $envelopeCount }}</span>
                    @endif
                </a>
            </div>

            @if($canClearNotifications && $notifications->count() > 0)
                <div class="adw-notifications-select-all">
                    <label class="adw-notif-select-all-label">
                        <input type="checkbox" id="select-all-notifications">
                        <span>Select all on this page</span>
                    </label>
                </div>
            @endif

            @forelse($notifications as $notification)
                <div class="adw-notification-item {{ $notification->read_at ? '' : 'is-unread' }}"
                     data-notification-id="{{ $notification->id }}">
                    @if($canClearNotifications)
                        <label class="adw-notif-checkbox-wrap" title="Select notification">
                            <input type="checkbox" class="notif-select" value="{{ $notification->id }}">
                        </label>
                    @endif
                    <div class="adw-notification-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="adw-notification-body">
                        <p class="adw-notification-title">{{ $notification->title }}</p>
                        @if($notification->body)
                            <p class="adw-notification-text">{!! nl2br(e($notification->body)) !!}</p>
                        @endif
                        <div class="adw-notification-meta">
                            <span>{{ $notification->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        @if($notification->link)
                            <a href="{{ $notification->link }}" class="btn btn-link btn-sm ps-0 mt-1 mark-notif-read-link"
                               data-id="{{ $notification->id }}">View details</a>
                        @elseif(!$notification->read_at)
                            <button type="button" class="btn btn-link btn-sm ps-0 mt-1 mark-notif-read-btn"
                                    data-id="{{ $notification->id }}">Mark as read</button>
                        @endif
                    </div>
                    @if($canClearNotifications)
                        <button type="button"
                                class="notif-clear-btn js-clear-notification"
                                data-id="{{ $notification->id }}"
                                title="Clear notification"
                                aria-label="Clear notification">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            @empty
                <div class="adw-notification-empty">
                    <div><i class="fas fa-bell-slash d-block"></i></div>
                    <p class="mb-1 fw-semibold">No notifications yet</p>
                    <p class="mb-0" style="font-size:0.85rem;">When there are updates matching your preferences, they will appear here.</p>
                    @if(!empty($settingsRoute))
                        <a href="{{ $settingsRoute }}" class="btn btn-sm btn-outline-primary mt-3">Manage preferences</a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>

@php
    $notificationsReadBaseUrl = url('/notifications');
    $notificationsMarkAllReadUrl = route('notifications_mark_all_read');
    $notificationsDeleteSelectedUrl = route('notifications_delete_selected');
    $notificationsClearAllUrl = route('notifications_clear_all');
    $notificationsCsrfToken = csrf_token();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const notificationsReadBaseUrl = @json($notificationsReadBaseUrl);
    const notificationsMarkAllReadUrl = @json($notificationsMarkAllReadUrl);
    const notificationsDeleteSelectedUrl = @json($notificationsDeleteSelectedUrl);
    const notificationsClearAllUrl = @json($notificationsClearAllUrl);
    const notificationsCsrfToken = @json($notificationsCsrfToken);
    const canClearNotifications = @json($canClearNotifications);

    function csrfHeaders() {
        return {
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || notificationsCsrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };
    }

    function showError(message) {
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: 'Error', text: message });
        } else {
            alert(message);
        }
    }

    function postNotificationAction(url, body, onSuccess) {
        fetch(url, {
            method: 'POST',
            headers: csrfHeaders(),
            body: body ? JSON.stringify(body) : null,
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return response.json();
        })
        .then(function (data) {
            if (data && data.success) {
                onSuccess(data);
            } else {
                showError('Unable to complete the action. Please try again.');
            }
        })
        .catch(function () {
            showError('Unable to complete the action. Please try again.');
        });
    }

    function markRead(id, callback) {
        fetch(notificationsReadBaseUrl + '/' + id + '/read', {
            method: 'POST',
            headers: csrfHeaders(),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (callback) callback(data);
        });
    }

    document.querySelectorAll('.mark-notif-read-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.dataset.id;
            const row = btn.closest('.adw-notification-item');
            markRead(id, function () {
                row.classList.remove('is-unread');
                btn.remove();
            });
        });
    });

    document.querySelectorAll('.mark-notif-read-link').forEach(function (link) {
        link.addEventListener('click', function () {
            markRead(link.dataset.id);
        });
    });

    const markAll = document.getElementById('mark-all-notifications-read');
    if (markAll) {
        markAll.addEventListener('click', function () {
            fetch(notificationsMarkAllReadUrl, {
                method: 'POST',
                headers: csrfHeaders(),
            }).then(function () { window.location.reload(); });
        });
    }

    if (!canClearNotifications) {
        return;
    }

    const selectAll = document.getElementById('select-all-notifications');
    const clearSelectedBtn = document.getElementById('clear-selected-notifications');
    const clearAllBtn = document.getElementById('clear-all-notifications');
    const checkboxes = Array.from(document.querySelectorAll('.notif-select'));

    function selectedIds() {
        return checkboxes.filter(function (checkbox) { return checkbox.checked; }).map(function (checkbox) {
            return parseInt(checkbox.value, 10);
        });
    }

    function updateClearSelectedState() {
        if (!clearSelectedBtn) {
            return;
        }

        const count = selectedIds().length;
        clearSelectedBtn.disabled = count === 0;
        clearSelectedBtn.innerHTML = count > 0
            ? '<i class="fas fa-trash-alt me-1"></i> Clear selected (' + count + ')'
            : '<i class="fas fa-trash-alt me-1"></i> Clear selected';
    }

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            if (selectAll) {
                selectAll.checked = checkboxes.length > 0 && checkboxes.every(function (item) { return item.checked; });
            }
            updateClearSelectedState();
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
            updateClearSelectedState();
        });
    }

    document.querySelectorAll('.js-clear-notification').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = button.getAttribute('data-id');
            if (!id || !window.confirm('Clear this notification?')) {
                return;
            }

            button.disabled = true;
            postNotificationAction(notificationsReadBaseUrl + '/' + id + '/delete', null, function () {
                window.location.reload();
            });
        });
    });

    if (clearSelectedBtn) {
        clearSelectedBtn.addEventListener('click', function () {
            const ids = selectedIds();
            if (ids.length === 0) {
                return;
            }

            if (!window.confirm('Clear ' + ids.length + ' selected notification(s)?')) {
                return;
            }

            clearSelectedBtn.disabled = true;
            postNotificationAction(notificationsDeleteSelectedUrl, { ids: ids }, function () {
                window.location.reload();
            });
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            if (!window.confirm('Clear all notifications on this page?')) {
                return;
            }

            clearAllBtn.disabled = true;
            postNotificationAction(notificationsClearAllUrl, null, function () {
                window.location.reload();
            });
        });
    }

    updateClearSelectedState();
});
</script>
