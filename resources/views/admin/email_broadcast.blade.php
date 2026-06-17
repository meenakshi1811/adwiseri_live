@extends('admin.layout.main')

@section('main-section')

@include('partials.email_broadcast_styles')

<div class="col-lg-10 column-client">
    <div class="client-dashboard">
        <div class="mb-3">
            <h3 class="eb-page-title m-0">Email Broadcast</h3>
            <p class="eb-page-subtitle mb-0">Send bulk emails to subscribers using the standard adwiseri email format.</p>
        </div>

        @include('partials.admin_communication_tabs', ['activeTab' => 'email_broadcast'])

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="eb-card">
                    <div class="eb-card-header">
                        <i class="fa-solid fa-envelope-open-text"></i>
                        Compose Broadcast
                    </div>
                    <div class="eb-card-body">
                        <form id="email_broadcast_form" method="POST" action="{{ route('admin_send_email_broadcast') }}">
                            @csrf
                            <input type="hidden" name="local_time" class="localtime" />

                            <div class="eb-form-group">
                                <label class="eb-form-label">Select Subscriber(s) <span class="required">*</span></label>
                                <div class="eb-recipient-wrap" id="subscriber_recipients_dropdown">
                                    <div class="eb-recipient-trigger" data-panel="subscriber-panel">
                                        <span class="eb-recipient-trigger-text">Choose subscribers...</span>
                                        <span>
                                            <span class="eb-recipient-badge">0 selected</span>
                                            <i class="fa-solid fa-chevron-down ms-2 text-muted" style="font-size:0.75rem;"></i>
                                        </span>
                                    </div>
                                    <div class="eb-recipient-panel" id="subscriber-panel">
                                        <div class="eb-recipient-search-wrap">
                                            <input type="text" class="eb-recipient-search" placeholder="Search subscribers..." data-target="subscriber-panel">
                                        </div>
                                        <div class="eb-recipient-list">
                                            <label class="eb-recipient-option">
                                                <input type="checkbox" id="selectAllSubscribers">
                                                <strong>Select All</strong>
                                            </label>
                                            <label class="eb-recipient-option"><input type="checkbox" class="subscriber-recipient" name="recipients[]" value="all"> All Subscribers</label>
                                            @foreach($subscribers as $subscriber)
                                            <label class="eb-recipient-option" data-search="{{ strtolower($subscriber->name . ' ' . $subscriber->email) }}">
                                                <input type="checkbox" class="subscriber-recipient" name="recipients[]" value="{{ $subscriber->id }}">
                                                <span>{{ $subscriber->name }} <small class="text-muted">({{ $subscriber->email }})</small></span>
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
                                <input type="text" id="broadcast_subject" class="eb-input" name="subject" value="{{ old('subject') }}" maxlength="200" placeholder="Enter a clear, concise subject line" required>
                                @error('subject')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-form-group mb-0">
                                <label class="eb-form-label" for="broadcast_body">Email Body <span class="required">*</span></label>
                                <textarea id="broadcast_body" class="eb-textarea" name="body" minlength="3" maxlength="5000" placeholder="Write your message here. It will be sent inside the standard adwiseri email template." required>{{ old('body') }}</textarea>
                                <div class="eb-char-count"><span id="body_char_count">0</span> / 5000 characters</div>
                                @error('body')
                                <span class="eb-error-text">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="eb-actions">
                                <span class="eb-form-hint mb-0"><i class="fa-solid fa-circle-info"></i> Large broadcasts are queued and sent in the background.</span>
                                <button type="submit" class="eb-send-btn">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    Send Email Broadcast
                                </button>
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
                            <div class="eb-info-icon"><i class="fa-solid fa-users"></i></div>
                            <div>
                                <div class="eb-info-label">Audience</div>
                                <div class="eb-info-value">Subscribers only</div>
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
                            <p><i class="fa-solid fa-lightbulb"></i><strong>Tip:</strong> Broadcasts are processed in the background in batches, so you can queue thousands of subscribers without waiting on this page.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bodyField = document.getElementById('broadcast_body');
    const bodyCount = document.getElementById('body_char_count');

    function updateRecipientTrigger() {
        const wrap = document.getElementById('subscriber_recipients_dropdown');
        const trigger = wrap.querySelector('.eb-recipient-trigger');
        const checkboxes = wrap.querySelectorAll('.subscriber-recipient');
        const checked = Array.from(checkboxes).filter(function (cb) { return cb.checked; });
        const badge = trigger.querySelector('.eb-recipient-badge');
        const text = trigger.querySelector('.eb-recipient-trigger-text');

        if (checked.length > 0) {
            trigger.classList.add('has-selection');
            badge.textContent = checked.length + ' selected';
            text.textContent = checked.length === 1 ? '1 subscriber selected' : checked.length + ' subscribers selected';
        } else {
            trigger.classList.remove('has-selection');
            badge.textContent = '0 selected';
            text.textContent = 'Choose subscribers...';
        }
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

    document.getElementById('selectAllSubscribers').addEventListener('change', function () {
        document.querySelectorAll('.subscriber-recipient').forEach(function (checkbox) {
            checkbox.checked = this.checked;
        }, this);
        updateRecipientTrigger();
    });

    document.querySelectorAll('.subscriber-recipient').forEach(function (checkbox) {
        checkbox.addEventListener('change', updateRecipientTrigger);
    });

    if (bodyField && bodyCount) {
        const updateCount = function () {
            bodyCount.textContent = bodyField.value.length;
        };
        bodyField.addEventListener('input', updateCount);
        updateCount();
    }
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
