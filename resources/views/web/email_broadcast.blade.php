@extends('web.layout.main')

@section('main-section')
@php
use App\Models\UserRoles;
$communication_roles = UserRoles::where('user_id', $user->id)->where('module', 'Communication')->first();
$canSend = $user->user_type === 'Subscriber' || ($communication_roles && ($communication_roles->write_only == 1 || $communication_roles->read_write_only == 1));
$oldCommunicateType = old('communicate_type');
$staffCount = $staffMembers->count();
$clientCount = $clients->count();
$defaultType = null;
if (in_array($oldCommunicateType, ['internal', 'external'], true)) {
    $defaultType = $oldCommunicateType;
} elseif ($staffCount > 0) {
    $defaultType = 'internal';
} elseif ($clientCount > 0) {
    $defaultType = 'external';
}

$emailBroadcastUsage = $broadcastUsage ?? [
    'limit' => 0,
    'used' => 0,
    'remaining' => 0,
    'per_year' => 0,
    'plan_name' => '',
];

$emailBroadcastChunkSize = (int) data_get($broadcastLimits, 'chunk_size', config('mail.broadcast_chunk_size', 300));
$emailBroadcastChunkDelay = (int) data_get($broadcastLimits, 'chunk_delay_seconds', config('mail.broadcast_chunk_delay_seconds', 2));
$emailBroadcastMaxRecipients = (int) data_get($broadcastLimits, 'max_recipients', config('mail.broadcast_max_recipients', 0));

$emailBroadcastEditorOptionsJson = json_encode([
    'uploadUrl' => route('upload_email_broadcast_image'),
    'disabled' => !$canSend,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

$emailBroadcastLimitsJson = json_encode([
    'chunkSize' => $emailBroadcastChunkSize,
    'chunkDelaySeconds' => $emailBroadcastChunkDelay,
    'maxRecipients' => $emailBroadcastMaxRecipients,
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

$emailBroadcastUsageJson = json_encode($emailBroadcastUsage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

$broadcastSentMessage = session('broadcast_sent');
$broadcastErrorMessage = session('broadcast_error');
@endphp

@include('partials.email_broadcast_styles')

<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="mb-3 d-flex flex-wrap justify-content-between align-items-start gap-2 eb-page-header">
            <div>
                <h3 class="eb-page-title m-0">Email Broadcast</h3>
                <p class="eb-page-subtitle mb-0">Send bulk emails to your staff or clients. The footer shows your organisation address, website, and email.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 eb-page-actions">
                <button type="button"
                    class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#emailBroadcastLogModal"
                    onclick="if(typeof initEmailBroadcastLogModal === 'function'){ initEmailBroadcastLogModal(); }">
                    EB Log
                </button>
            </div>
        </div>

        @include('partials.communication_tabs', ['activeTab' => 'email_broadcast'])

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="eb-card">
                    <div class="eb-card-header">
                        <i class="fa-solid fa-envelope-open-text"></i>
                        Compose Broadcast
                    </div>
                    <div class="eb-card-body">
                        @if(!$canSend)
                        <div class="eb-permission-alert mb-3">
                            <i class="fa-solid fa-lock"></i>
                            You do not have permission to send email broadcasts.
                        </div>
                        @endif

                        <form id="email_broadcast_form" method="POST" action="{{ route('send_email_broadcast') }}">
                            @csrf
                            <input type="hidden" name="local_time" class="localtime" />
                            <input type="hidden" id="communicate_type" name="communicate_type" value="{{ $defaultType }}" />

                            <div class="eb-form-group">
                                <label class="eb-form-label">Communicate Type <span class="required">*</span></label>
                                <div class="eb-type-toggle" role="group" aria-label="Communicate type">
                                    <button type="button" class="eb-type-btn {{ $defaultType === 'internal' ? 'active' : '' }}" data-type="internal" @if(!$canSend) disabled @endif>
                                        <i class="fa-solid fa-users"></i> Internal
                                    </button>
                                    <button type="button" class="eb-type-btn {{ $defaultType === 'external' ? 'active' : '' }}" data-type="external" @if(!$canSend) disabled @endif>
                                        <i class="fa-solid fa-user-group"></i> External
                                    </button>
                                </div>
                                <div class="eb-form-hint">Internal sends to staff. External sends to clients.</div>
                                @error('communicate_type')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group recipient-section" style="display:none;">
                                <label class="eb-form-label recipient-label">Select Staff Member(s) <span class="required">*</span></label>

                                <div class="eb-recipient-wrap" id="staff_recipients_dropdown">
                                    <div class="eb-recipient-trigger" data-panel="staff-panel">
                                        <span class="eb-recipient-trigger-text">Choose recipients...</span>
                                        <span>
                                            <span class="eb-recipient-badge">0 selected</span>
                                            <i class="fa-solid fa-chevron-down ms-2 text-muted" style="font-size:0.75rem;"></i>
                                        </span>
                                    </div>
                                    <div class="eb-recipient-panel" id="staff-panel">
                                        <div class="eb-recipient-search-wrap">
                                            <input type="text" class="eb-recipient-search" placeholder="Search staff..." data-target="staff-panel">
                                        </div>
                                        <div class="eb-recipient-list">
                                            <label class="eb-recipient-option">
                                                <input type="checkbox" id="selectAllStaff">
                                                <strong>Select All</strong>
                                            </label>
                                            <div class="eb-recipient-group-title">Teams</div>
                                            <label class="eb-recipient-option"><input type="checkbox" class="staff-recipient" name="recipients[]" value="all"> All Staff</label>
                                            <label class="eb-recipient-option"><input type="checkbox" class="staff-recipient" name="recipients[]" value="group:branch_manager"> Branch Manager</label>
                                            <label class="eb-recipient-option"><input type="checkbox" class="staff-recipient" name="recipients[]" value="group:advisors"> Advisors</label>
                                            <label class="eb-recipient-option"><input type="checkbox" class="staff-recipient" name="recipients[]" value="group:sales_team"> Sales Team</label>
                                            <label class="eb-recipient-option"><input type="checkbox" class="staff-recipient" name="recipients[]" value="group:support_team"> Support Team</label>
                                            <label class="eb-recipient-option"><input type="checkbox" class="staff-recipient" name="recipients[]" value="group:hr_accountant"> HR / Accountant</label>
                                            @if($staffMembers->count())
                                            <div class="eb-recipient-group-title">Individual Staff</div>
                                            @foreach($staffMembers as $staff)
                                            <label class="eb-recipient-option" data-search="{{ strtolower($staff->name) }}">
                                                <input type="checkbox" class="staff-recipient" name="recipients[]" value="{{ $staff->id }}">
                                                {{ $staff->name }}
                                            </label>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @error('recipients')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group client-recipient-section" style="display:none;">
                                <label class="eb-form-label">Select Client(s) <span class="required">*</span></label>

                                <div class="eb-recipient-wrap" id="client_recipients_dropdown">
                                    <div class="eb-recipient-trigger" data-panel="client-panel">
                                        <span class="eb-recipient-trigger-text">Choose clients...</span>
                                        <span>
                                            <span class="eb-recipient-badge">0 selected</span>
                                            <i class="fa-solid fa-chevron-down ms-2 text-muted" style="font-size:0.75rem;"></i>
                                        </span>
                                    </div>
                                    <div class="eb-recipient-panel" id="client-panel">
                                        <div class="eb-recipient-search-wrap">
                                            <input type="text" class="eb-recipient-search" placeholder="Search clients..." data-target="client-panel">
                                        </div>
                                        <div class="eb-recipient-list">
                                            <label class="eb-recipient-option">
                                                <input type="checkbox" id="selectAllClients">
                                                <strong>Select All</strong>
                                            </label>
                                            <label class="eb-recipient-option"><input type="checkbox" class="client-recipient" name="recipients[]" value="all"> All Clients</label>
                                            @foreach($clients as $client)
                                            <label class="eb-recipient-option" data-search="{{ strtolower($client->name) }}">
                                                <input type="checkbox" class="client-recipient" name="recipients[]" value="{{ $client->id }}">
                                                {{ $client->name }}
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @error('recipients')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group">
                                <label class="eb-form-label" for="broadcast_subject">Email Subject <span class="required">*</span></label>
                                <input type="text" id="broadcast_subject" class="eb-input" name="subject" value="{{ old('subject') }}" maxlength="200" placeholder="Enter a clear, concise subject line" required @if(!$canSend) disabled @endif>
                                @error('subject')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group mb-0">
                                <label class="eb-form-label" for="broadcast_body">Email Body <span class="required">*</span></label>
                                <textarea id="broadcast_body" class="eb-textarea" name="body" placeholder="Write your message here. Use headings, colours, fonts, links, and banner images." @if(!$canSend) disabled @endif>{{ old('body') }}</textarea>
                                <div class="eb-form-hint">Rich HTML is supported: font styles, colours, headings, links, tables, and banner images (upload or paste a public image URL).</div>
                                <div class="eb-char-count"><span id="body_char_count">0</span> / 50000 characters</div>
                                @error('body')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-actions">
                                <span class="eb-form-hint mb-0"><i class="fa-solid fa-circle-info"></i> Large broadcasts are queued and sent in the background.</span>
                                @if($canSend)
                                <button type="submit" class="eb-send-btn">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    Send Email Broadcast
                                </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="eb-card">
                    <div class="eb-card-header">
                        <i class="fa-solid fa-circle-info"></i>
                        Delivery Details
                    </div>
                    <div class="eb-card-body">
                        @include('partials.email_broadcast_delivery_details')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.emailBroadcastEditorOptions = {!! $emailBroadcastEditorOptionsJson !!};
window.emailBroadcastLimits = {!! $emailBroadcastLimitsJson !!};
window.emailBroadcastUsage = {!! $emailBroadcastUsageJson !!};
</script>
@include('partials.email_broadcast_editor', [
    'uploadUrl' => route('upload_email_broadcast_image'),
    'disabled' => !$canSend,
    'bodyMaxLength' => 50000,
])

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeInput = document.getElementById('communicate_type');
    const typeButtons = document.querySelectorAll('.eb-type-btn');
    const staffSection = document.querySelector('.recipient-section');
    const clientSection = document.querySelector('.client-recipient-section');
    const bodyField = document.getElementById('broadcast_body');
    const bodyCount = document.getElementById('body_char_count');
    const staffCount = {{ (int) $staffCount }};
    const clientCount = {{ (int) $clientCount }};

    function showNoRecipientsAlert() {
        Swal.fire({
            icon: 'warning',
            customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No clients found in the system'
        });
    }

    function hasRecipientsForType(type) {
        if (type === 'internal') {
            return staffCount > 0;
        }
        if (type === 'external') {
            return clientCount > 0;
        }
        return false;
    }

    function setCommunicateType(type) {
        typeInput.value = type;
        typeButtons.forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.type === type);
        });

        document.querySelectorAll('.staff-recipient, .client-recipient').forEach(function (checkbox) {
            checkbox.checked = false;
            checkbox.disabled = true;
            checkbox.removeAttribute('name');
        });

        if (type === 'internal') {
            staffSection.style.display = 'block';
            clientSection.style.display = 'none';
            document.querySelectorAll('.staff-recipient').forEach(function (checkbox) {
                checkbox.disabled = false;
                checkbox.setAttribute('name', 'recipients[]');
            });
        } else if (type === 'external') {
            staffSection.style.display = 'none';
            clientSection.style.display = 'block';
            document.querySelectorAll('.client-recipient').forEach(function (checkbox) {
                checkbox.disabled = false;
                checkbox.setAttribute('name', 'recipients[]');
            });
        } else {
            staffSection.style.display = 'none';
            clientSection.style.display = 'none';
        }

        updateRecipientTriggers();
    }

    typeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (this.disabled) return;
            const type = this.dataset.type;
            if (!hasRecipientsForType(type)) {
                showNoRecipientsAlert();
                return;
            }
            setCommunicateType(type);
        });
    });

    function updateRecipientTriggers() {
        document.querySelectorAll('.eb-recipient-wrap').forEach(function (wrap) {
            const trigger = wrap.querySelector('.eb-recipient-trigger');
            const checkboxes = wrap.querySelectorAll('input[type="checkbox"]:not([id^="selectAll"])');
            const checked = Array.from(checkboxes).filter(function (cb) { return cb.checked && !cb.disabled; });
            const badge = trigger.querySelector('.eb-recipient-badge');
            const text = trigger.querySelector('.eb-recipient-trigger-text');

            if (checked.length > 0) {
                trigger.classList.add('has-selection');
                badge.textContent = checked.length + ' selected';
                text.textContent = checked.length === 1 ? '1 recipient selected' : checked.length + ' recipients selected';
            } else {
                trigger.classList.remove('has-selection');
                badge.textContent = '0 selected';
                text.textContent = wrap.id === 'staff_recipients_dropdown' ? 'Choose recipients...' : 'Choose clients...';
            }
        });
    }

    document.querySelectorAll('.eb-recipient-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const panelId = this.dataset.panel;
            const panel = document.getElementById(panelId);
            const isOpen = panel.classList.contains('open');

            document.querySelectorAll('.eb-recipient-panel').forEach(function (p) { p.classList.remove('open'); });
            document.querySelectorAll('.eb-recipient-trigger').forEach(function (t) { t.classList.remove('show'); });

            if (!isOpen) {
                panel.classList.add('open');
                this.classList.add('show');
            }
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.eb-recipient-panel').forEach(function (p) { p.classList.remove('open'); });
        document.querySelectorAll('.eb-recipient-trigger').forEach(function (t) { t.classList.remove('show'); });
    });

    document.querySelectorAll('.eb-recipient-panel').forEach(function (panel) {
        panel.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    document.querySelectorAll('.eb-recipient-search').forEach(function (input) {
        input.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            const panel = document.getElementById(this.dataset.target);
            panel.querySelectorAll('.eb-recipient-option[data-search]').forEach(function (option) {
                const match = !query || (option.dataset.search || '').includes(query);
                option.classList.toggle('hidden-by-search', !match);
            });
        });
    });

    function bindSelectAll(selectAllId, className) {
        const selectAll = document.getElementById(selectAllId);
        if (!selectAll) return;
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.' + className).forEach(function (checkbox) {
                if (!checkbox.disabled) checkbox.checked = this.checked;
            }, this);
            updateRecipientTriggers();
        });
    }

    bindSelectAll('selectAllStaff', 'staff-recipient');
    bindSelectAll('selectAllClients', 'client-recipient');

    document.querySelectorAll('.staff-recipient, .client-recipient').forEach(function (checkbox) {
        checkbox.addEventListener('change', updateRecipientTriggers);
    });

    if (bodyField && bodyCount && typeof window.jQuery === 'undefined') {
        bodyField.addEventListener('input', function () {
            bodyCount.textContent = bodyField.value.length;
        });
        bodyCount.textContent = bodyField.value.length;
    }

    function showPlanLimitAlert(message) {
        Swal.fire({
            icon: 'warning',
            customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Email Limit Reached',
            text: message
        });
    }

    function planLimitMessage(recipientCount) {
        const usage = window.emailBroadcastUsage || {};
        const limit = usage.limit || 0;
        const used = usage.used || 0;
        const remaining = usage.remaining || 0;
        const planName = usage.plan_name || 'your plan';

        if (limit <= 0) {
            return 'Email broadcasts are not included on ' + planName + '. Please upgrade your subscription plan to send bulk emails.';
        }

        return 'You have used ' + used.toLocaleString() + ' of ' + limit.toLocaleString()
            + ' email broadcasts allowed on your ' + planName + ' plan for the current subscription term. '
            + 'This broadcast would send to ' + recipientCount.toLocaleString() + ' recipient(s), '
            + 'but only ' + remaining.toLocaleString() + ' remain. Please upgrade your plan or reduce the number of recipients.';
    }

    function countSelectedRecipients() {
        const type = typeInput.value;
        const selector = type === 'internal' ? '.staff-recipient' : '.client-recipient';
        return document.querySelectorAll(selector + ':checked:not(:disabled)').length;
    }

    function showBatchConfirmAlert(recipientCount, onConfirm) {
        const limits = window.emailBroadcastLimits || {};
        const chunkSize = Math.max(1, limits.chunkSize || 300);
        const chunkDelay = limits.chunkDelaySeconds || 2;
        const batchCount = Math.ceil(recipientCount / chunkSize);
        let message = 'You selected ' + recipientCount.toLocaleString() + ' recipient(s).';

        if (batchCount > 1) {
            message += ' They will be sent in ' + batchCount.toLocaleString()
                + ' batches of ' + chunkSize + ', with a ' + chunkDelay + 's pause between batches.';
        }

        message += ' Continue?';

        Swal.fire({
            icon: 'question',
            title: 'Send Broadcast?',
            text: message,
            showCancelButton: true,
            confirmButtonText: 'Send Broadcast',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                onConfirm();
            }
        });
    }

    const form = document.getElementById('email_broadcast_form');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) {
                return;
            }

            const type = typeInput.value;
            if (!hasRecipientsForType(type)) {
                e.preventDefault();
                showNoRecipientsAlert();
                return;
            }

            const recipientCount = countSelectedRecipients();
            const limits = window.emailBroadcastLimits || {};
            const maxRecipients = limits.maxRecipients || 0;
            const usage = window.emailBroadcastUsage || {};

            if ((usage.limit || 0) <= 0) {
                e.preventDefault();
                showPlanLimitAlert(planLimitMessage(recipientCount));
                return;
            }

            if (recipientCount > (usage.remaining || 0)) {
                e.preventDefault();
                showPlanLimitAlert(planLimitMessage(recipientCount));
                return;
            }

            if (maxRecipients > 0 && recipientCount > maxRecipients) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Recipient Limit Exceeded',
                    text: 'This broadcast allows up to ' + maxRecipients.toLocaleString()
                        + ' recipients. Please reduce your selection or split it into smaller broadcasts.'
                });
                return;
            }

            if (form.dataset.batchConfirmed === '1') {
                form.dataset.batchConfirmed = '0';
                return;
            }

            e.preventDefault();
            showBatchConfirmAlert(recipientCount, function () {
                form.dataset.batchConfirmed = '1';
                form.requestSubmit();
            });
        });
    }

    if (staffCount < 1 && clientCount < 1) {
        showNoRecipientsAlert();
        typeButtons.forEach(function (btn) { btn.disabled = true; });
        typeInput.value = '';
        staffSection.style.display = 'none';
        clientSection.style.display = 'none';
    } else {
        const usage = window.emailBroadcastUsage || {};
        if ((usage.limit || 0) <= 0 || (usage.remaining || 0) <= 0) {
            showPlanLimitAlert(
                (usage.limit || 0) <= 0
                    ? planLimitMessage(0)
                    : 'You have used all ' + (usage.limit || 0).toLocaleString() + ' email broadcasts allowed on your '
                        + (usage.plan_name || 'plan') + ' plan for the current subscription term. Please upgrade your plan to send more.'
            );
        }

        const initialType = typeInput.value;
        if (initialType && hasRecipientsForType(initialType)) {
            setCommunicateType(initialType);
        } else {
            typeInput.value = '';
            staffSection.style.display = 'none';
            clientSection.style.display = 'none';
            typeButtons.forEach(function (btn) { btn.classList.remove('active'); });
        }
    }
});
</script>

@if(!empty($broadcastSentMessage))
<script>
Swal.fire({ icon: 'success', title: 'Broadcast Queued', text: {!! json_encode($broadcastSentMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!} });
</script>
@endif
@if(!empty($broadcastErrorMessage))
<script>
Swal.fire({ icon: 'error', title: 'Unable to Send', text: {!! json_encode($broadcastErrorMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!} });
</script>
@endif
@if(session()->has('no_broadcast_recipients'))
<script>
Swal.fire({
  icon: 'warning',
  customClass: { icon: 'adwiseri-oops-icon' },
  title: 'Oops!',
  text: 'No clients found in the system'
});
</script>
@endif

@include('partials.email_broadcast_log_modal', [
    'showSubscriberFilter' => false,
    'historyDataUrl' => route('email_broadcast_log_data'),
])
@endsection()
