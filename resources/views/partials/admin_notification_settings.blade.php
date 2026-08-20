@php
    $recipientNotificationTypes = $recipientNotificationTypes ?? [];
    $subscribers = $subscribers ?? collect();
    $staffUsers = $staffUsers ?? collect();
    $subscriberLookup = $subscriberLookup ?? collect();
@endphp
<link rel="stylesheet" href="{{ asset('web_assets/css/topbar-notifications.css') }}">

<div class="adw-notif-prefs">
    <div class="adw-notif-prefs-hero">
        <h5><i class="fas fa-bell me-2 text-primary"></i>Send Notifications</h5>
        <p>As an admin you send platform notifications to system users. Recipients control which notification types they receive in their own settings. You do not need personal notification filters here.</p>
    </div>

    <form id="admin-send-notification-form" method="POST" action="{{ route('admin_send_notification') }}">
        @csrf
        <input type="hidden" name="local_time" class="localtime" />

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="notification_type">Notification Type</label>
                <select class="form-select" id="notification_type" name="notification_type" required>
                    <option value="">Select type</option>
                    @foreach($recipientNotificationTypes as $key => $label)
                        <option value="{{ $key }}" {{ old('notification_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('notification_type')
                    <span class="text-danger small d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold" for="recipient_group">Send To</label>
                <select class="form-select" id="recipient_group" name="recipient_group" required>
                    <option value="">Select recipient group</option>
                    <option value="Subscribers" {{ old('recipient_group') === 'Subscribers' ? 'selected' : '' }}>Subscribers</option>
                    <option value="Users" {{ old('recipient_group') === 'Users' ? 'selected' : '' }}>Staff (Users)</option>
                </select>
                @error('recipient_group')
                    <span class="text-danger small d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-12 admin-notif-recipient-group subscribers-group">
                <label class="form-label fw-semibold mb-2">Select Subscriber(s)</label>
                <div class="admin-notif-recipient-list border rounded p-2 bg-white">
                    <label class="d-block mb-2">
                        <input type="checkbox" id="notifSelectAllSubscribers" class="me-2" />
                        <strong>Select All</strong>
                    </label>
                    @forelse($subscribers as $subscriber)
                        <label class="d-block mb-1 admin-notif-recipient-option">
                            <input type="checkbox" class="notif-subscriber-checkbox me-2" name="sendto[]" value="{{ $subscriber->id }}" {{ in_array((string) $subscriber->id, array_map('strval', old('sendto', [])), true) ? 'checked' : '' }} />
                            {{ $subscriber->name }}({{ $subscriber->id }})
                        </label>
                    @empty
                        <p class="text-muted small mb-0">No subscribers found.</p>
                    @endforelse
                </div>
                @error('sendto')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-12 admin-notif-recipient-group users-group">
                <label class="form-label fw-semibold mb-2">Select User(s)</label>
                <div class="admin-notif-recipient-list border rounded p-2 bg-white">
                    <label class="d-block mb-2">
                        <input type="checkbox" id="notifSelectAllUsers" class="me-2" />
                        <strong>Select All</strong>
                    </label>
                    @forelse($staffUsers as $staffUser)
                        @php
                            $parentSubscriber = $subscriberLookup->get($staffUser->added_by);
                            $subscriberLabel = $parentSubscriber
                                ? $parentSubscriber->name . '(' . $parentSubscriber->id . ')'
                                : 'N/A';
                        @endphp
                        <label class="d-block mb-1 admin-notif-recipient-option">
                            <input type="checkbox" class="notif-user-checkbox me-2" name="sendto[]" value="{{ $staffUser->id }}" {{ in_array((string) $staffUser->id, array_map('strval', old('sendto', [])), true) ? 'checked' : '' }} />
                            {{ $subscriberLabel }} - {{ $staffUser->name }}({{ $staffUser->id }})
                        </label>
                    @empty
                        <p class="text-muted small mb-0">No users found.</p>
                    @endforelse
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold" for="notification_title">Title</label>
                <input type="text" class="form-control" id="notification_title" name="title" maxlength="200" value="{{ old('title') }}" placeholder="Notification title" required>
                @error('title')
                    <span class="text-danger small d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold" for="notification_body">Message</label>
                <textarea class="form-control" id="notification_body" name="body" rows="6" maxlength="2000" placeholder="Notification message (line breaks are kept, e.g. one list item per line)">{{ old('body') }}</textarea>
                @error('body')
                    <span class="text-danger small d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold" for="notification_link">Link (optional)</label>
                <input type="text" class="form-control" id="notification_link" name="link" maxlength="500" value="{{ old('link') }}" placeholder="https://example.com/page">
                @error('link')
                    <span class="text-danger small d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary" id="admin-send-notification-btn">
                    <i class="fas fa-paper-plane me-1"></i> Send Notification
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.admin-notif-recipient-group {
    display: none !important;
}
.admin-notif-recipient-group.is-visible {
    display: block !important;
}
.admin-notif-recipient-list {
    max-height: 240px;
    overflow-y: auto;
}
.admin-notif-recipient-option {
    cursor: pointer;
    margin-bottom: 0.35rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('admin-send-notification-form');
    const groupSelect = document.getElementById('recipient_group');
    const subscribersGroup = document.querySelector('.subscribers-group');
    const usersGroup = document.querySelector('.users-group');
    const selectAllSubscribers = document.getElementById('notifSelectAllSubscribers');
    const selectAllUsers = document.getElementById('notifSelectAllUsers');

    function setCheckboxGroupEnabled(selector, enabled) {
        document.querySelectorAll(selector).forEach(function (checkbox) {
            checkbox.disabled = !enabled;
            if (!enabled) {
                checkbox.checked = false;
            }
        });
    }

    function setGroupVisibility(type) {
        if (subscribersGroup) {
            subscribersGroup.classList.toggle('is-visible', type === 'Subscribers');
        }
        if (usersGroup) {
            usersGroup.classList.toggle('is-visible', type === 'Users');
        }

        setCheckboxGroupEnabled('.notif-subscriber-checkbox', type === 'Subscribers');
        setCheckboxGroupEnabled('#notifSelectAllSubscribers', type === 'Subscribers');
        setCheckboxGroupEnabled('.notif-user-checkbox', type === 'Users');
        setCheckboxGroupEnabled('#notifSelectAllUsers', type === 'Users');
    }

    function clearChecks() {
        document.querySelectorAll('.notif-subscriber-checkbox, .notif-user-checkbox, #notifSelectAllSubscribers, #notifSelectAllUsers').forEach(function (input) {
            input.checked = false;
        });
    }

    function syncSelectAll(master, itemsSelector) {
        const items = Array.from(document.querySelectorAll(itemsSelector)).filter(function (item) {
            return !item.disabled;
        });
        if (!master || !items.length) {
            if (master) {
                master.checked = false;
            }
            return;
        }
        master.checked = items.every(function (item) {
            return item.checked;
        });
    }

    if (groupSelect) {
        groupSelect.addEventListener('change', function () {
            clearChecks();
            setGroupVisibility(this.value);
        });
        setGroupVisibility(groupSelect.value);
        syncSelectAll(selectAllSubscribers, '.notif-subscriber-checkbox');
        syncSelectAll(selectAllUsers, '.notif-user-checkbox');
    }

    if (selectAllSubscribers) {
        selectAllSubscribers.addEventListener('change', function () {
            document.querySelectorAll('.notif-subscriber-checkbox').forEach(function (checkbox) {
                if (!checkbox.disabled) {
                    checkbox.checked = selectAllSubscribers.checked;
                }
            });
        });
    }

    if (selectAllUsers) {
        selectAllUsers.addEventListener('change', function () {
            document.querySelectorAll('.notif-user-checkbox').forEach(function (checkbox) {
                if (!checkbox.disabled) {
                    checkbox.checked = selectAllUsers.checked;
                }
            });
        });
    }

    document.querySelectorAll('.notif-subscriber-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            syncSelectAll(selectAllSubscribers, '.notif-subscriber-checkbox');
        });
    });

    document.querySelectorAll('.notif-user-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            syncSelectAll(selectAllUsers, '.notif-user-checkbox');
        });
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            const group = groupSelect ? groupSelect.value : '';
            const selector = group === 'Subscribers'
                ? '.notif-subscriber-checkbox:checked'
                : (group === 'Users' ? '.notif-user-checkbox:checked' : null);

            if (!selector || document.querySelectorAll(selector).length === 0) {
                event.preventDefault();
                alert('Please select at least one recipient.');
            }
        });
    }

    if (window.location.hash === '#notifications') {
        const tab = document.getElementById('notifications-tab');
        if (tab) {
            tab.click();
        }
    }
});
</script>
