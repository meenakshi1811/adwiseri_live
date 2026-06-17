@extends('web.layout.main')

@section('main-section')
@php
use App\Models\UserRoles;
$communication_roles = UserRoles::where('user_id', $user->id)->where('module', 'Communication')->first();
$canSend = $user->user_type === 'Subscriber' || ($communication_roles && ($communication_roles->write_only == 1 || $communication_roles->read_write_only == 1));
@endphp

@include('partials.email_broadcast_styles')

<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="mb-3">
            <h3 class="eb-page-title m-0">Email Broadcast</h3>
            <p class="eb-page-subtitle mb-0">Send bulk emails to your staff or clients using the standard adwiseri email format.</p>
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
                            <input type="hidden" id="communicate_type" name="communicate_type" value="{{ old('communicate_type') }}" />

                            <div class="eb-form-group">
                                <label class="eb-form-label">Communicate Type <span class="required">*</span></label>
                                <div class="eb-type-toggle" role="group" aria-label="Communicate type">
                                    <button type="button" class="eb-type-btn {{ old('communicate_type', 'internal') === 'internal' ? 'active' : '' }}" data-type="internal" @if(!$canSend) disabled @endif>
                                        <i class="fa-solid fa-users"></i> Internal
                                    </button>
                                    <button type="button" class="eb-type-btn {{ old('communicate_type') === 'external' ? 'active' : '' }}" data-type="external" @if(!$canSend) disabled @endif>
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
                                <textarea id="broadcast_body" class="eb-textarea" name="body" minlength="3" maxlength="5000" placeholder="Write your message here. It will be sent inside the standard adwiseri email template." required @if(!$canSend) disabled @endif>{{ old('body') }}</textarea>
                                <div class="eb-char-count"><span id="body_char_count">0</span> / 5000 characters</div>
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
                        <div class="eb-info-item">
                            <div class="eb-info-icon"><i class="fa-solid fa-at"></i></div>
                            <div>
                                <div class="eb-info-label">Sender</div>
                                <div class="eb-info-value">{{ $user->email ?: 'Not configured' }}</div>
                            </div>
                        </div>
                        <div class="eb-info-item">
                            <div class="eb-info-icon"><i class="fa-solid fa-reply"></i></div>
                            <div>
                                <div class="eb-info-label">Reply To</div>
                                <div class="eb-info-value">{{ $user->email ?: 'Not configured' }}</div>
                            </div>
                        </div>
                        <div class="eb-info-item">
                            <div class="eb-info-icon"><i class="fa-solid fa-file-lines"></i></div>
                            <div>
                                <div class="eb-info-label">Email Format</div>
                                <div class="eb-info-value">Standard adwiseri template with header &amp; footer</div>
                            </div>
                        </div>

                        <div class="eb-tip-box">
                            <p><i class="fa-solid fa-lightbulb"></i><strong>Tip:</strong> Broadcasts are processed in the background in batches, so you can queue thousands of recipients without waiting on this page.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeInput = document.getElementById('communicate_type');
    const typeButtons = document.querySelectorAll('.eb-type-btn');
    const staffSection = document.querySelector('.recipient-section');
    const clientSection = document.querySelector('.client-recipient-section');
    const bodyField = document.getElementById('broadcast_body');
    const bodyCount = document.getElementById('body_char_count');

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
            setCommunicateType(this.dataset.type);
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

    if (bodyField && bodyCount) {
        const updateCount = function () {
            bodyCount.textContent = bodyField.value.length;
        };
        bodyField.addEventListener('input', updateCount);
        updateCount();
    }

    const initialType = typeInput.value || 'internal';
    setCommunicateType(initialType);
});
</script>

@if(session()->has('broadcast_sent'))
<script>
Swal.fire({ icon: 'success', title: 'Broadcast Queued', text: @json(session('broadcast_sent')) });
</script>
@endif
@if(session()->has('broadcast_error'))
<script>
Swal.fire({ icon: 'error', title: 'Unable to Send', text: @json(session('broadcast_error')) });
</script>
@endif
@endsection()
