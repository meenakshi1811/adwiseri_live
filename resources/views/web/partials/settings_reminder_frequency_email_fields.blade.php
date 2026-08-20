@php
    $frequencyValue = $frequencyValue ?? 'weekly';
    $emailToClients = $emailToClients ?? \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY;
    $emailToAssociates = $emailToAssociates ?? \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_ONLY;
    $showRemindersToToggle = $showRemindersToToggle ?? false;
    $selectedRemindersTo = $selectedRemindersTo ?? \App\Models\PaymentReminderSetting::REMINDERS_TO_CLIENTS;
    $clientsOnly = $clientsOnly ?? false;
@endphp

<div class="row p-1 mb-3 align-items-center">
    <div class="col-6">
        <label>Select Email Frequency</label>
    </div>
    <div class="col-6">
        <select name="email_frequency" class="form-control form-select reminder-frequency-field">
            <option value="daily" {{ $frequencyValue === 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="weekly" {{ $frequencyValue === 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="monthly" {{ $frequencyValue === 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="quarterly" {{ $frequencyValue === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
        </select>
    </div>
</div>
<div class="row p-1 mb-3 align-items-start">
    <div class="col-6">
        <label>Email To</label>
    </div>
    <div class="col-6">
        <div id="reminder-email-to-clients" class="{{ (!$clientsOnly && $selectedRemindersTo === \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES) ? 'd-none' : '' }}">
            <div class="form-check mb-2">
                <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-client-only-{{ uniqid() }}"
                    value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY }}"
                    {{ $emailToClients === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY ? 'checked' : '' }}>
                <label class="form-check-label" for="email-to-client-only-{{ uniqid() }}">Client(s) Only</label>
            </div>
            <div class="form-check">
                <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-client-bcc-subscriber-{{ uniqid() }}"
                    value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER }}"
                    {{ $emailToClients === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER ? 'checked' : '' }}>
                <label class="form-check-label" for="email-to-client-bcc-subscriber-{{ uniqid() }}">Client(s) + Bcc (Subscriber)</label>
            </div>
        </div>
        @if(!$clientsOnly)
        <div id="reminder-email-to-associates" class="{{ $selectedRemindersTo === \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES ? '' : 'd-none' }}">
            <div class="form-check mb-2">
                <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-associate-only-{{ uniqid() }}"
                    value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_ONLY }}"
                    {{ $emailToAssociates === \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_ONLY ? 'checked' : '' }}>
                <label class="form-check-label" for="email-to-associate-only-{{ uniqid() }}">Associate(s) Only</label>
            </div>
            <div class="form-check">
                <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-associate-bcc-subscriber-{{ uniqid() }}"
                    value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER }}"
                    {{ $emailToAssociates === \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER ? 'checked' : '' }}>
                <label class="form-check-label" for="email-to-associate-bcc-subscriber-{{ uniqid() }}">Associate(s) + Bcc (Subscriber)</label>
            </div>
        </div>
        @endif
    </div>
</div>
