@php
    $notificationTypes = $notificationTypes ?? [];
    $notificationPreferences = $notificationPreferences ?? [];
@endphp
<div class="adw-notif-prefs">
    <div class="adw-notif-prefs-hero">
        <h5><i class="fas fa-bell me-2 text-primary"></i>Notification Preferences</h5>
        <p>Choose which alerts you want to receive. The bell icon in the top bar shows your unread count for the types you enable.</p>
    </div>

    <form id="notification-preferences-form">
        @csrf
        <div class="adw-notif-pref-grid">
            @foreach ($notificationTypes as $key => $label)
                <label class="adw-notif-pref-card {{ !empty($notificationPreferences[$key]) ? 'is-checked' : '' }}" data-pref-key="{{ $key }}">
                    <input type="checkbox"
                           name="{{ $key }}"
                           value="1"
                           {{ !empty($notificationPreferences[$key]) ? 'checked' : '' }}>
                    <span class="adw-notif-pref-label">{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div class="adw-notif-pref-actions">
            <button type="button" class="btn btn-outline-secondary btn-select-all" id="notif-select-all">Select all</button>
            <button type="button" class="btn btn-outline-secondary btn-clear-all" id="notif-clear-all">Clear all</button>
            <button type="submit" class="btn btn-primary" id="save-notification-preferences">
                <i class="fas fa-save me-1"></i> Save preferences
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('notification-preferences-form');
    if (!form) return;

    const cards = form.querySelectorAll('.adw-notif-pref-card');
    cards.forEach(function (card) {
        const input = card.querySelector('input[type="checkbox"]');
        input.addEventListener('change', function () {
            card.classList.toggle('is-checked', input.checked);
        });
    });

    const selectAll = document.getElementById('notif-select-all');
    const clearAll = document.getElementById('notif-clear-all');

    if (selectAll) {
        selectAll.addEventListener('click', function () {
            cards.forEach(function (card) {
                const input = card.querySelector('input[type="checkbox"]');
                input.checked = true;
                card.classList.add('is-checked');
            });
        });
    }

    if (clearAll) {
        clearAll.addEventListener('click', function () {
            cards.forEach(function (card) {
                const input = card.querySelector('input[type="checkbox"]');
                input.checked = false;
                card.classList.remove('is-checked');
            });
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('save-notification-preferences');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        fetch(@json(route('save_notification_preferences')), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || @json(csrf_token()),
                'Accept': 'application/json',
            },
            body: new FormData(form),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && typeof showAdwAlert === 'function') {
                showAdwAlert('success', data.message || 'Preferences saved.');
            } else if (data.success) {
                alert(data.message || 'Preferences saved.');
            }
        })
        .catch(function () {
            if (typeof showAdwAlert === 'function') {
                showAdwAlert('error', 'Unable to save notification preferences.');
            } else {
                alert('Unable to save notification preferences.');
            }
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });

    if (window.location.hash === '#notifications') {
        const tab = document.getElementById('notifications-tab');
        if (tab) {
            tab.click();
        }
    }
});
</script>
