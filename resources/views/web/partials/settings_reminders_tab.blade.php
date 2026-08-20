@php
    $selectedRemindersTo = \App\Models\PaymentReminderSetting::normalizeRemindersTo(
        optional($paymentReminderSetting)->reminders_to,
        optional($paymentReminderSetting)->email_to
    );
    $selectedPaymentEmailTo = \App\Models\PaymentReminderSetting::normalizeEmailTo(optional($paymentReminderSetting)->email_to);
    if (!in_array($selectedPaymentEmailTo, \App\Models\PaymentReminderSetting::allowedEmailToValuesForRemindersTo($selectedRemindersTo), true)) {
        $selectedPaymentEmailTo = \App\Models\PaymentReminderSetting::defaultEmailToForRemindersTo($selectedRemindersTo);
    }
    $selectedDocumentsEmailTo = \App\Models\PaymentReminderSetting::normalizeEmailTo(optional($documentsReminderSetting)->email_to);
    if (!in_array($selectedDocumentsEmailTo, \App\Models\PaymentReminderSetting::allowedEmailToValuesForRemindersTo(\App\Models\PaymentReminderSetting::REMINDERS_TO_CLIENTS), true)) {
        $selectedDocumentsEmailTo = \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY;
    }
@endphp

<div class="tab-pane fade" id="payment-reminder" role="tabpanel" aria-labelledby="payment-reminder-tab">
    <div class="row p-1 m-0">
        <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">Reminders</p>
    </div>

    <div class="row p-1 mb-3 align-items-center">
        <div class="col-6"><label for="reminder-type">Reminder Type</label></div>
        <div class="col-6">
            <select id="reminder-type" class="form-control form-select">
                <option value="payments">Payments</option>
                <option value="documents">Documents</option>
                <option value="application">Application</option>
            </select>
        </div>
    </div>

    <form id="payment-reminder-form" class="reminder-panel reminder-panel-payments">
        <input type="hidden" name="reminder_type" value="payments">
        @csrf
        <div class="row p-1 mb-3 align-items-start">
            <div class="col-6"><label>Reminders To</label></div>
            <div class="col-6">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="reminders_to" id="reminders-to-clients"
                        value="{{ \App\Models\PaymentReminderSetting::REMINDERS_TO_CLIENTS }}"
                        {{ $selectedRemindersTo === \App\Models\PaymentReminderSetting::REMINDERS_TO_CLIENTS ? 'checked' : '' }}>
                    <label class="form-check-label" for="reminders-to-clients">Clients</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="reminders_to" id="reminders-to-associates"
                        value="{{ \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES }}"
                        {{ $selectedRemindersTo === \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES ? 'checked' : '' }}>
                    <label class="form-check-label" for="reminders-to-associates">Associates</label>
                </div>
            </div>
        </div>
        <div class="row p-1 mb-3 align-items-center">
            <div class="col-6">
                <label id="reminder-group-label">{{ \App\Models\PaymentReminderSetting::groupFieldLabel($selectedRemindersTo) }}</label>
            </div>
            <div class="col-6">
                <select id="reminder-client-group" name="client_group" class="form-control form-select">
                    @foreach (\App\Models\PaymentReminderSetting::allowedClientGroups() as $groupValue)
                        <option value="{{ $groupValue }}" {{ optional($paymentReminderSetting)->client_group === $groupValue ? 'selected' : '' }}>
                            {{ \App\Models\PaymentReminderSetting::clientGroupLabel($groupValue) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row p-1 mb-3 align-items-center">
            <div class="col-6"><label>Select Email Frequency</label></div>
            <div class="col-6">
                <select id="reminder-frequency" name="email_frequency" class="form-control form-select">
                    <option value="daily" {{ optional($paymentReminderSetting)->email_frequency === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ optional($paymentReminderSetting)->email_frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ optional($paymentReminderSetting)->email_frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ optional($paymentReminderSetting)->email_frequency === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                </select>
            </div>
        </div>
        <div class="row p-1 mb-3 align-items-start">
            <div class="col-6"><label>Email To</label></div>
            <div class="col-6">
                <div id="reminder-email-to-clients" class="{{ $selectedRemindersTo === \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES ? 'd-none' : '' }}">
                    <div class="form-check mb-2">
                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-client-only"
                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY }}"
                            {{ $selectedPaymentEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY ? 'checked' : '' }}>
                        <label class="form-check-label" for="email-to-client-only">Client(s) Only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-client-bcc-subscriber"
                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER }}"
                            {{ $selectedPaymentEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER ? 'checked' : '' }}>
                        <label class="form-check-label" for="email-to-client-bcc-subscriber">Client(s) + Bcc (Subscriber)</label>
                    </div>
                </div>
                <div id="reminder-email-to-associates" class="{{ $selectedRemindersTo === \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES ? '' : 'd-none' }}">
                    <div class="form-check mb-2">
                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-associate-only"
                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_ONLY }}"
                            {{ $selectedPaymentEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_ONLY ? 'checked' : '' }}>
                        <label class="form-check-label" for="email-to-associate-only">Associate(s) Only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-associate-bcc-subscriber"
                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER }}"
                            {{ $selectedPaymentEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER ? 'checked' : '' }}>
                        <label class="form-check-label" for="email-to-associate-bcc-subscriber">Associate(s) + Bcc (Subscriber)</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row p-1 mb-3 align-items-center">
            <div class="col-6"></div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" id="save-payment-reminder">Apply</button>
                <button type="button" class="btn btn-outline-secondary" id="cancel-payment-reminder">Cancel</button>
            </div>
        </div>
    </form>

    <form id="documents-reminder-form" class="reminder-panel reminder-panel-documents d-none">
        <input type="hidden" name="reminder_type" value="documents">
        @csrf
        <p class="text-muted small px-1">Automatically email clients the missing documents required for their active applications.</p>
        <div class="row p-1 mb-3 align-items-center">
            <div class="col-6"><label>Select Email Frequency</label></div>
            <div class="col-6">
                <select name="email_frequency" class="form-control form-select">
                    <option value="daily" {{ optional($documentsReminderSetting)->email_frequency === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ optional($documentsReminderSetting)->email_frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ optional($documentsReminderSetting)->email_frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ optional($documentsReminderSetting)->email_frequency === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                </select>
            </div>
        </div>
        <div class="row p-1 mb-3 align-items-start">
            <div class="col-6"><label>Email To</label></div>
            <div class="col-6">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="email_to" id="doc-email-to-client-only"
                        value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY }}"
                        {{ $selectedDocumentsEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY ? 'checked' : '' }}>
                    <label class="form-check-label" for="doc-email-to-client-only">Client(s) Only</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_to" id="doc-email-to-client-bcc"
                        value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER }}"
                        {{ $selectedDocumentsEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER ? 'checked' : '' }}>
                    <label class="form-check-label" for="doc-email-to-client-bcc">Client(s) + Bcc (Subscriber)</label>
                </div>
            </div>
        </div>
        <div class="row p-1 mb-3 align-items-center">
            <div class="col-6"></div>
            <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" id="save-documents-reminder">Apply</button>
                <button type="button" class="btn btn-outline-secondary" id="cancel-documents-reminder">Cancel</button>
            </div>
        </div>
    </form>

    <div class="reminder-panel reminder-panel-application d-none">
        <form id="application-reminder-form">
            @csrf
            <div class="row p-1 mb-3 align-items-center">
                <div class="col-6"><label>Select Client <span class="text-danger">*</span></label></div>
                <div class="col-6">
                    <select id="app-reminder-client" name="client_id" class="form-control form-select" required>
                        <option value="">Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->id }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row p-1 mb-3 align-items-center">
                <div class="col-6"><label>Select Application <span class="text-danger">*</span></label></div>
                <div class="col-6">
                    <select id="app-reminder-application" name="application_id" class="form-control form-select" required>
                        <option value="">Select Application</option>
                    </select>
                </div>
            </div>
            <div class="row p-1 mb-3 align-items-center">
                <div class="col-6"><label>Subject <span class="text-danger">*</span></label></div>
                <div class="col-6"><input type="text" name="subject" class="form-control" maxlength="255" required></div>
            </div>
            <div class="row p-1 mb-3 align-items-center">
                <div class="col-6"><label>Deadline <span class="text-danger">*</span></label></div>
                <div class="col-6"><input type="date" name="deadline" class="form-control" required></div>
            </div>
            <div class="row p-1 mb-3 align-items-start">
                <div class="col-6"><label>Description</label></div>
                <div class="col-6"><textarea name="description" class="form-control" rows="3" maxlength="5000"></textarea></div>
            </div>
            <div class="row p-1 mb-3 align-items-center">
                <div class="col-6"><label>Select Email Frequency</label></div>
                <div class="col-6">
                    <select name="email_frequency" class="form-control form-select">
                        <option value="daily">Daily</option>
                        <option value="weekly" selected>Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                    </select>
                </div>
            </div>
            <div class="row p-1 mb-3 align-items-start">
                <div class="col-6"><label>Email To</label></div>
                <div class="col-6">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="email_to" id="app-reminder-user-only" value="user_only" checked>
                        <label class="form-check-label" for="app-reminder-user-only">User Only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="email_to" id="app-reminder-user-bcc" value="user_bcc_subscriber">
                        <label class="form-check-label" for="app-reminder-user-bcc">User + Bcc (Subscriber)</label>
                    </div>
                </div>
            </div>
            <div class="row p-1 mb-3 align-items-center">
                <div class="col-6"></div>
                <div class="col-6 text-right">
                    <button type="button" class="btn btn-primary" id="save-application-reminder">Add Reminder</button>
                </div>
            </div>
        </form>

        @if(isset($applicationReminders) && $applicationReminders->isNotEmpty())
            <div class="table-responsive mt-3">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Application</th>
                            <th>Subject</th>
                            <th>Deadline</th>
                            <th>Frequency</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="application-reminders-table-body">
                        @foreach($applicationReminders as $reminder)
                            <tr data-reminder-id="{{ $reminder->id }}">
                                <td>{{ optional($reminder->client)->name }}</td>
                                <td>{{ optional($reminder->application)->application_name }}</td>
                                <td>{{ $reminder->subject }}</td>
                                <td>{{ optional($reminder->deadline)->format('d-m-Y') }}</td>
                                <td>{{ ucfirst($reminder->email_frequency) }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-application-reminder" data-id="{{ $reminder->id }}">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
