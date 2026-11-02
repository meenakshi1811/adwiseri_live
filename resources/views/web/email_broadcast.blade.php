@extends('web.layout.main')

@section('main-section')
@php
use App\Models\UserRoles;
$communication_roles = UserRoles::where('user_id', $user->id)->where('module', 'Communication')->first();
$canSend = $user->user_type === 'Subscriber' || ($communication_roles && ($communication_roles->write_only == 1 || $communication_roles->read_write_only == 1));
$oldCommunicateType = old('communicate_type');
$staffCount = $staffMembers->count();
$clientCount = $clients->count();
$associates = $associates ?? collect();
$associateCount = $associates->count();
// External broadcasts go to one group at a time: clients, associates, or a pasted list of potential clients
// ("marketing", always available, up to the daily limit).
$externalGroups = array_filter(['clients' => $clientCount, 'associates' => $associateCount]) + ['marketing' => 1];
$defaultExternalGroup = isset($externalGroups[old('external_group')]) ? old('external_group') : array_key_first($externalGroups);
$marketingUsage = $marketingUsage ?? ['limit' => 0, 'used' => 0, 'remaining' => 0];
// "All Staff" and the teams only tick their members. Counts are distinct email addresses, as the broadcast
// emails each address once and skips people without one.
$emailCount = fn ($people) => $people->map(fn ($person) => strtolower(trim((string) $person->email)))->filter()->unique()->count();
$staffTeamsById = $staffMembers->mapWithKeys(fn ($staff) => [$staff->id => \App\Services\EmailBroadcastService::staffTeamsFor($staff->designation)]);
$staffTeams = collect([
    'branch_manager' => 'Branch Manager',
    'advisors' => 'Advisors',
    'sales_team' => 'Sales Team',
    'support_team' => 'Support Team',
    'hr_accountant' => 'HR / Accountant',
])->map(fn ($label, $key) => [
    'label' => $label,
    'count' => $emailCount($staffMembers->filter(fn ($staff) => in_array($key, $staffTeamsById[$staff->id], true))),
]);
$reachableStaff = $emailCount($staffMembers);
$reachableClients = $emailCount($clients);
$reachableAssociates = $emailCount($associates);
$defaultType = null;
if (in_array($oldCommunicateType, ['internal', 'external'], true)) {
    $defaultType = $oldCommunicateType;
} else {
    $defaultType = $staffCount > 0 ? 'internal' : 'external';
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
        <div class="client-btn d-flex justify-content-between align-items-center mb-2">
            <h3 class="text-primary text-center flex-grow-1 text-center m-0">Email Broadcast</h3>
            <div class="module-header-actions d-flex flex-wrap gap-2 eb-page-actions">
                <button type="button"
                    class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#emailBroadcastLogModal"
                    onclick="if(typeof initEmailBroadcastLogModal === 'function'){ initEmailBroadcastLogModal(); }">
                    EB Log
                </button>
            </div>
        </div>
        <p class="eb-page-subtitle text-center mb-3">Send bulk emails to your staff, clients, associates or potential clients. The footer shows your organisation address, website, and email.</p>

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
                                <div class="eb-form-hint">Internal sends to staff. External sends to clients, associates or potential clients.</div>
                                @error('communicate_type')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group recipient-section" style="display:none;">
                                <label class="eb-form-label recipient-label">Select Staff Member(s) <span class="required">*</span></label>

                                <div class="eb-recipient-wrap" id="staff_recipients_dropdown">
                                    <div class="eb-recipient-trigger" data-panel="staff-panel">
                                        <span class="eb-recipient-trigger-text" data-placeholder="Choose recipients...">Choose recipients...</span>
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
                                                <input type="checkbox" id="selectAllStaff" class="eb-select-all">
                                                <strong>Select All</strong>
                                            </label>
                                            <div class="eb-recipient-group-title">Teams</div>
                                            <label class="eb-recipient-option{{ $reachableStaff ? '' : ' is-unavailable' }}"><input type="checkbox" class="staff-recipient eb-shortcut{{ $reachableStaff ? '' : ' eb-unavailable' }}" name="recipients[]" value="all" data-shortcut="all"> All Staff <span class="eb-recipient-count">({{ $reachableStaff }})</span></label>
                                            @foreach($staffTeams as $teamKey => $team)
                                            <label class="eb-recipient-option{{ $team['count'] ? '' : ' is-unavailable' }}"><input type="checkbox" class="staff-recipient eb-shortcut{{ $team['count'] ? '' : ' eb-unavailable' }}" name="recipients[]" value="group:{{ $teamKey }}" data-shortcut="{{ $teamKey }}"> {{ $team['label'] }} <span class="eb-recipient-count">({{ $team['count'] }})</span></label>
                                            @endforeach
                                            @if($staffMembers->count())
                                            <div class="eb-recipient-group-title">Individual Staff</div>
                                            @foreach($staffMembers as $staff)
                                            @include('partials.email_broadcast_recipient_option', ['class' => 'staff-recipient', 'value' => $staff->id, 'email' => $staff->email, 'label' => $staff->name, 'groups' => implode(' ', $staffTeamsById[$staff->id])])
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @error('recipients')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group external-group-section" style="display:none;">
                                <label class="eb-form-label" for="external_group">Select Group <span class="required">*</span></label>
                                <select id="external_group" name="external_group" class="eb-select" @if(!$canSend) disabled @endif>
                                    @if($clientCount > 0)
                                    <option value="clients" {{ $defaultExternalGroup === 'clients' ? 'selected' : '' }}>Clients ({{ $reachableClients }})</option>
                                    @endif
                                    @if($associateCount > 0)
                                    <option value="associates" {{ $defaultExternalGroup === 'associates' ? 'selected' : '' }}>Associates ({{ $reachableAssociates }})</option>
                                    @endif
                                    <option value="marketing" {{ $defaultExternalGroup === 'marketing' ? 'selected' : '' }}>Marketing - Potential Clients</option>
                                </select>
                                <div class="eb-form-hint">Each group is emailed separately: one group per broadcast.</div>
                                @error('external_group')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group client-recipient-section" style="display:none;">
                                <label class="eb-form-label">Select Client(s) <span class="required">*</span></label>

                                <div class="eb-recipient-wrap" id="client_recipients_dropdown">
                                    <div class="eb-recipient-trigger" data-panel="client-panel">
                                        <span class="eb-recipient-trigger-text" data-placeholder="Choose clients...">Choose clients...</span>
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
                                                <input type="checkbox" id="selectAllClients" class="eb-select-all">
                                                <strong>Select All</strong>
                                            </label>
                                            <label class="eb-recipient-option{{ $reachableClients ? '' : ' is-unavailable' }}"><input type="checkbox" class="client-recipient eb-shortcut{{ $reachableClients ? '' : ' eb-unavailable' }}" name="recipients[]" value="all" data-shortcut="all"> All Clients <span class="eb-recipient-count">({{ $reachableClients }})</span></label>
                                            @foreach($clients as $client)
                                            @include('partials.email_broadcast_recipient_option', ['class' => 'client-recipient', 'value' => $client->id, 'email' => $client->email, 'label' => $client->name])
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @error('recipients')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            @if($associateCount > 0)
                            <div class="eb-form-group associate-recipient-section" style="display:none;">
                                <label class="eb-form-label">Select Associate(s) <span class="required">*</span></label>

                                <div class="eb-recipient-wrap" id="associate_recipients_dropdown">
                                    <div class="eb-recipient-trigger" data-panel="associate-panel">
                                        <span class="eb-recipient-trigger-text" data-placeholder="Choose associates...">Choose associates...</span>
                                        <span>
                                            <span class="eb-recipient-badge">0 selected</span>
                                            <i class="fa-solid fa-chevron-down ms-2 text-muted" style="font-size:0.75rem;"></i>
                                        </span>
                                    </div>
                                    <div class="eb-recipient-panel" id="associate-panel">
                                        <div class="eb-recipient-search-wrap">
                                            <input type="text" class="eb-recipient-search" placeholder="Search associates..." data-target="associate-panel">
                                        </div>
                                        <div class="eb-recipient-list">
                                            <label class="eb-recipient-option">
                                                <input type="checkbox" id="selectAllAssociates" class="eb-select-all">
                                                <strong>Select All</strong>
                                            </label>
                                            <label class="eb-recipient-option{{ $reachableAssociates ? '' : ' is-unavailable' }}"><input type="checkbox" class="associate-recipient eb-shortcut{{ $reachableAssociates ? '' : ' eb-unavailable' }}" name="recipients[]" value="associate:all" data-shortcut="all"> All Associates <span class="eb-recipient-count">({{ $reachableAssociates }})</span></label>
                                            @foreach($associates as $associate)
                                            @include('partials.email_broadcast_recipient_option', ['class' => 'associate-recipient', 'value' => 'associate:' . $associate->id, 'email' => $associate->email, 'label' => $associate->name, 'note' => $associate->organization])
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="eb-form-group marketing-recipient-section" style="display:none;">
                                <label class="eb-form-label" for="marketing_emails">Potential Clients' Email Addresses <span class="required">*</span></label>
                                <textarea id="marketing_emails" class="eb-textarea eb-marketing-emails" name="marketing_emails" rows="5" placeholder="e.g. name@example.com, another@example.com" disabled>{{ old('marketing_emails') }}</textarea>
                                <div class="eb-form-hint">
                                    Separate addresses with commas (or put one per line). Duplicates are removed.
                                    Up to {{ number_format($marketingUsage['limit']) }} emails per day; <span id="marketing_remaining" data-remaining="{{ (int) $marketingUsage['remaining'] }}">{{ number_format($marketingUsage['remaining']) }}</span> left today.
                                </div>
                                <div class="eb-marketing-summary" id="marketing_summary" aria-live="polite"></div>
                                @error('marketing_emails')
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
    const groupSection = document.querySelector('.external-group-section');
    const groupSelect = document.getElementById('external_group');
    const clientSection = document.querySelector('.client-recipient-section');
    const associateSection = document.querySelector('.associate-recipient-section');
    const marketingSection = document.querySelector('.marketing-recipient-section');
    const marketingField = document.getElementById('marketing_emails');
    const marketingSummary = document.getElementById('marketing_summary');
    const marketingRemaining = parseInt(document.getElementById('marketing_remaining').dataset.remaining, 10) || 0;
    const bodyField = document.getElementById('broadcast_body');
    const bodyCount = document.getElementById('body_char_count');
    const staffCount = {{ (int) $staffCount }};
    const canSend = @json($canSend);

    // Only Internal can run out of recipients; External always has the marketing list.
    function showNoRecipientsAlert() {
        Swal.fire({
            icon: 'warning',
            customClass: { icon: 'adwiseri-oops-icon' },
            title: 'Oops!',
            text: 'No active staff found in the system'
        });
    }

    function hasRecipientsForType(type) {
        if (type === 'internal') {
            return staffCount > 0;
        }
        // External always has "Marketing - Potential Clients", even without clients or associates.
        return type === 'external';
    }

    function externalGroup() {
        return groupSelect ? groupSelect.value : '';
    }

    // The list being used: staff for Internal, otherwise the chosen External group.
    function activeSection() {
        if (typeInput.value === 'internal') {
            return staffSection;
        }
        if (typeInput.value === 'external') {
            return { associates: associateSection, marketing: marketingSection }[externalGroup()] || clientSection;
        }
        return null;
    }

    // Same rules as EmailBroadcastService::parseEmailList: commas, semicolons, new lines or spaces separate
    // entries, "Name <email>" is accepted, and addresses are counted once whatever their case.
    function parseEmailList(text) {
        const valid = new Map();
        const invalid = new Set();
        // The site-wide email check (form-validation.js), so the list agrees with every other email field.
        const isValidEmail = window.AdwiseriFormValidation && window.AdwiseriFormValidation.isValidEmail
            ? window.AdwiseriFormValidation.isValidEmail
            : function (value) { return /^[^\s@,;]+@[^\s@,;]+\.[A-Za-z]{2,}$/.test(value); };

        String(text || '').split(/[,;\r\n]+/).forEach(function (entry) {
            entry = entry.trim();
            if (!entry) return;
            const bracketed = entry.match(/<([^<>]+)>/);
            (bracketed ? [bracketed[1]] : entry.split(/\s+/)).forEach(function (candidate) {
                candidate = candidate.replace(/^[\s"'<>]+|[\s"'<>]+$/g, '');
                if (!candidate) return;
                if (isValidEmail(candidate)) {
                    if (!valid.has(candidate.toLowerCase())) valid.set(candidate.toLowerCase(), candidate);
                } else {
                    invalid.add(candidate);
                }
            });
        });

        return { valid: Array.from(valid.values()), invalid: Array.from(invalid) };
    }

    function escapeText(value) {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }

    function updateMarketingSummary() {
        if (!marketingSummary) return;
        const list = parseEmailList(marketingField.value);
        if (!list.valid.length && !list.invalid.length) {
            marketingSummary.innerHTML = '';
            return;
        }

        const parts = [];
        const validText = list.valid.length.toLocaleString() + ' valid email' + (list.valid.length === 1 ? '' : 's');
        parts.push(list.valid.length > marketingRemaining
            ? '<span class="eb-marketing-over">' + validText + ': over today\'s limit by ' + (list.valid.length - marketingRemaining).toLocaleString() + '</span>'
            : '<span class="eb-marketing-ok">' + validText + '</span>');
        if (list.invalid.length) {
            const shown = list.invalid.slice(0, 5).map(escapeText).join(', ');
            parts.push('<span class="eb-marketing-warn">' + list.invalid.length.toLocaleString() + ' not valid (will be skipped): '
                + shown + (list.invalid.length > 5 ? ', …' : '') + '</span>');
        }
        marketingSummary.innerHTML = parts.join(' · ');
    }

    if (marketingField) {
        marketingField.addEventListener('input', updateMarketingSummary);
        updateMarketingSummary();
    }

    // Shows only the list in use; the others are cleared and left out of the form.
    function showRecipientSections() {
        const active = activeSection();

        if (marketingSection) {
            const isMarketing = marketingSection === active;
            marketingSection.style.display = isMarketing ? 'block' : 'none';
            // A disabled field isn't submitted, so the pasted list only goes with a marketing broadcast.
            marketingField.disabled = !isMarketing || !canSend;
        }

        [staffSection, clientSection, associateSection].forEach(function (section) {
            if (!section) return;
            const isActive = section === active;
            section.style.display = isActive ? 'block' : 'none';

            section.querySelectorAll('.eb-person, .eb-shortcut').forEach(function (checkbox) {
                checkbox.checked = false;
                checkbox.disabled = !isActive || checkbox.classList.contains('eb-unavailable');
                if (checkbox.disabled) {
                    checkbox.removeAttribute('name');
                } else {
                    checkbox.setAttribute('name', 'recipients[]');
                }
            });

            const selectAll = section.querySelector('.eb-select-all');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.disabled = !isActive || !section.querySelector('.eb-person:not(.eb-unavailable)');
            }
        });

        if (groupSection) {
            const isExternal = typeInput.value === 'external';
            groupSection.style.display = isExternal ? 'block' : 'none';
            groupSelect.disabled = !isExternal || !canSend;
        }

        updateRecipientTriggers();
    }

    function setCommunicateType(type) {
        typeInput.value = type;
        typeButtons.forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.type === type);
        });

        showRecipientSections();
    }

    if (groupSelect) {
        groupSelect.addEventListener('change', showRecipientSections);
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

    // People ticked in a list, once per email address (as the server sends them). "Select All", "All ..." and
    // the teams only tick people, so they never count themselves.
    function countPeople(scope) {
        const emails = new Set();
        scope.querySelectorAll('.eb-person').forEach(function (checkbox) {
            if (checkbox.checked && !checkbox.disabled) {
                emails.add(checkbox.dataset.email);
            }
        });
        return emails.size;
    }

    function updateRecipientTriggers() {
        document.querySelectorAll('.eb-recipient-wrap').forEach(function (wrap) {
            const trigger = wrap.querySelector('.eb-recipient-trigger');
            const count = countPeople(wrap);
            const badge = trigger.querySelector('.eb-recipient-badge');
            const text = trigger.querySelector('.eb-recipient-trigger-text');

            if (count > 0) {
                trigger.classList.add('has-selection');
                badge.textContent = count + ' selected';
                text.textContent = count === 1 ? '1 recipient selected' : count + ' recipients selected';
            } else {
                trigger.classList.remove('has-selection');
                badge.textContent = '0 selected';
                text.textContent = text.dataset.placeholder || 'Choose recipients...';
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

    function coversPerson(shortcut, person) {
        const key = shortcut.dataset.shortcut;
        return key === 'all' || (person.dataset.groups || '').split(' ').indexOf(key) !== -1;
    }

    // "Select All", "All ..." and the teams tick or untick their people. Unticking someone also unticks every
    // option that covers them, so the options sent with the form always match the people ticked.
    document.querySelectorAll('.eb-recipient-panel').forEach(function (panel) {
        const people = Array.from(panel.querySelectorAll('.eb-person'));
        const shortcuts = Array.from(panel.querySelectorAll('.eb-shortcut'));
        const selectAll = panel.querySelector('.eb-select-all');

        function sync() {
            const usable = people.filter(function (person) { return !person.disabled; });
            shortcuts.forEach(function (shortcut) {
                if (shortcut.checked && usable.some(function (person) { return coversPerson(shortcut, person) && !person.checked; })) {
                    shortcut.checked = false;
                }
            });
            if (selectAll) {
                selectAll.checked = usable.length > 0 && usable.every(function (person) { return person.checked; });
            }
            updateRecipientTriggers();
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                people.concat(shortcuts).forEach(function (checkbox) {
                    if (!checkbox.disabled) checkbox.checked = selectAll.checked;
                });
                sync();
            });
        }

        shortcuts.forEach(function (shortcut) {
            shortcut.addEventListener('change', function () {
                people.forEach(function (person) {
                    if (!person.disabled && coversPerson(shortcut, person)) person.checked = shortcut.checked;
                });
                sync();
            });
        });

        people.forEach(function (person) {
            person.addEventListener('change', sync);
        });
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
        const section = activeSection();
        if (section && section === marketingSection) {
            return parseEmailList(marketingField.value).valid.length;
        }
        return section ? countPeople(section) : 0;
    }

    function showBatchConfirmAlert(recipientCount, onConfirm) {
        const limits = window.emailBroadcastLimits || {};
        const chunkSize = Math.max(1, limits.chunkSize || 300);
        const chunkDelay = limits.chunkDelaySeconds || 2;
        const batchCount = Math.ceil(recipientCount / chunkSize);
        let message = 'You selected ' + recipientCount.toLocaleString() + ' recipient(s).';

        if (activeSection() === marketingSection) {
            const skipped = parseEmailList(marketingField.value).invalid.length;
            if (skipped > 0) {
                message += ' ' + skipped.toLocaleString() + ' entr' + (skipped === 1 ? 'y is not a valid email address and will be skipped.' : 'ies are not valid email addresses and will be skipped.');
            }
        }

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
            if (recipientCount === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: type === 'internal'
                        ? 'Please select at least one staff member.'
                        : ({
                            associates: 'Please select at least one associate.',
                            marketing: 'Please enter at least one valid email address.'
                        }[externalGroup()] || 'Please select at least one client.')
                });
                return;
            }

            if (activeSection() === marketingSection && recipientCount > marketingRemaining) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Daily Limit Reached',
                    text: 'Marketing emails are limited to {{ number_format($marketingUsage['limit']) }} per day. You have '
                        + marketingRemaining.toLocaleString() + ' left today, but this list has ' + recipientCount.toLocaleString()
                        + ' email addresses. Please shorten the list or send the rest tomorrow.'
                });
                return;
            }
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

    const usage = window.emailBroadcastUsage || {};
    if ((usage.limit || 0) <= 0 || (usage.remaining || 0) <= 0) {
        showPlanLimitAlert(
            (usage.limit || 0) <= 0
                ? planLimitMessage(0)
                : 'You have used all ' + (usage.limit || 0).toLocaleString() + ' email broadcasts allowed on your '
                    + (usage.plan_name || 'plan') + ' plan for the current subscription term. Please upgrade your plan to send more.'
        );
    }

    // There is always someone to write to: External offers "Marketing - Potential Clients" even with no
    // staff, clients or associates.
    const initialType = typeInput.value;
    if (initialType && hasRecipientsForType(initialType)) {
        setCommunicateType(initialType);
    } else {
        typeInput.value = '';
        showRecipientSections();
        typeButtons.forEach(function (btn) { btn.classList.remove('active'); });
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
  text: @json(session('no_broadcast_recipients'))
});
</script>
@endif

@include('partials.email_broadcast_log_modal', [
    'showSubscriberFilter' => false,
    'historyDataUrl' => route('email_broadcast_log_data'),
])
@endsection()
