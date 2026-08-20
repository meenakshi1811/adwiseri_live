@extends('admin.layout.main')
<style>
    .nav-item {
        margin: 0px !important;
        --bs-nav-tabs-border-radius: 0px !important;
    }

    /* Discounts & Offers tab */
    .offers-panel {
        background: #fff;
        border: 1px solid #e8ebf3;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1rem;
    }

    .offers-panel__header {
        border-bottom: 1px solid #eef1f7;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
    }

    .offers-panel__title {
        color: #1f2937;
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 .35rem;
    }

    .offers-panel__subtitle {
        color: #6b7280;
        font-size: .875rem;
        margin: 0;
    }

    .offers-step {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        margin-bottom: 1rem;
        padding: 1.1rem 1.15rem;
    }

    .offers-step__label {
        align-items: center;
        color: #695EEE;
        display: flex;
        font-size: .75rem;
        font-weight: 700;
        gap: .5rem;
        letter-spacing: .04em;
        margin-bottom: .85rem;
        text-transform: uppercase;
    }

    .offers-step__number {
        align-items: center;
        background: #695EEE;
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        font-size: .7rem;
        height: 1.35rem;
        justify-content: center;
        width: 1.35rem;
    }

    .offers-field-label {
        color: #374151;
        font-size: .875rem;
        font-weight: 600;
        margin-bottom: .35rem;
    }

    .offers-field-hint {
        color: #9ca3af;
        display: block;
        font-size: .78rem;
        margin-top: .35rem;
    }

    .offers-mode-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media (max-width: 767px) {
        .offers-mode-grid {
            grid-template-columns: 1fr;
        }
    }

    .offers-mode-card {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        padding: .95rem 1rem;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .offers-mode-card:hover {
        border-color: #c4b5fd;
    }

    .offers-mode-card.is-active {
        background: #F3F2FF;
        border-color: #695EEE;
        box-shadow: 0 0 0 3px rgba(105, 94, 238, .12);
    }

    .offers-mode-card input {
        display: none;
    }

    .offers-mode-card__title {
        color: #111827;
        font-size: .95rem;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    .offers-mode-card__text {
        color: #6b7280;
        font-size: .8rem;
        line-height: 1.45;
        margin: 0;
    }

    .offers-date-grid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media (max-width: 767px) {
        .offers-date-grid {
            grid-template-columns: 1fr;
        }
    }

    .offers-subscriber-picker {
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        max-height: 320px;
        overflow: hidden;
    }

    .offers-subscriber-toolbar {
        align-items: center;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        flex-shrink: 0;
        justify-content: space-between;
        padding: .75rem 1rem;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .offers-subscriber-toolbar .offers-subscriber-item {
        margin: 0;
        padding: 0;
    }

    #offers-settings-form .offers-subscriber-item {
        align-items: flex-start !important;
        box-sizing: border-box;
        display: flex !important;
        flex-direction: row !important;
        height: auto !important;
        min-height: 48px;
    }

    #offers-settings-form .offers-subscriber-item.offers-section-hidden {
        display: none !important;
    }

    #offers-settings-form .offers-subscriber-item__body,
    #offers-settings-form .offers-subscriber-item__name,
    #offers-settings-form .offers-subscriber-item__meta {
        margin-top: 0 !important;
    }

    .offers-subscriber-list {
        overflow-y: auto;
        padding: .35rem .5rem .65rem;
    }

    .offers-subscriber-item {
        align-items: flex-start;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        gap: .75rem;
        margin: 0;
        padding: .65rem .75rem;
        transition: background-color .15s ease;
        width: 100%;
    }

    .offers-subscriber-item:hover {
        background: #F3F2FF;
    }

    .offers-subscriber-item input[type="checkbox"] {
        accent-color: #695EEE;
        cursor: pointer;
        flex-shrink: 0;
        height: 18px;
        margin: .15rem 0 0;
        width: 18px;
    }

    .offers-subscriber-item__body {
        display: block;
        flex: 1;
        min-width: 0;
    }

    .offers-subscriber-item__name {
        color: #111827;
        display: block;
        font-size: .875rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0 0 .2rem;
        word-break: break-word;
    }

    .offers-subscriber-item__meta {
        color: #6b7280;
        display: block;
        font-size: .78rem;
        line-height: 1.35;
        margin: 0;
    }

    .offers-subscriber-item__meta strong {
        color: #695EEE;
        font-weight: 600;
    }

    .offers-subscriber-item--select-all .offers-subscriber-item__name {
        font-size: .875rem;
    }

    .offers-selected-count {
        background: #ede9fe;
        border-radius: 999px;
        color: #5b21b6;
        flex-shrink: 0;
        font-size: .75rem;
        font-weight: 600;
        padding: .35rem .75rem;
        white-space: nowrap;
    }

    .offers-subscriber-empty {
        color: #9ca3af;
        font-size: .875rem;
        margin: 0;
        padding: 1rem .75rem;
        text-align: center;
    }

    #subscriber-picker-row {
        margin-top: .25rem;
    }

    .offers-subscriber-list::-webkit-scrollbar {
        width: 8px;
    }

    .offers-subscriber-list::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 999px;
    }

    .offers-subscriber-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .offers-info-banner {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        color: #1e40af;
        font-size: .82rem;
        line-height: 1.5;
        margin-top: .75rem;
        padding: .65rem .8rem;
    }

    .offers-actions {
        align-items: center;
        border-top: 1px solid #eef1f7;
        display: flex;
        gap: .75rem;
        justify-content: center;
        margin-top: .5rem;
        padding-top: 1.15rem;
    }

    .offers-section-hidden {
        display: none !important;
    }

    #subscriber-picker-row.offers-section-hidden {
        display: none !important;
    }

    .offers-history {
        border-top: 1px solid #eef1f7;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
    }

    .offers-history__title {
        font-size: 1.05rem;
        font-weight: 600;
        margin-bottom: .35rem;
    }

    .offers-history__subtitle {
        color: #6b7280;
        font-size: .875rem;
        margin-bottom: 1rem;
    }

    .offers-history-table-wrap {
        overflow-x: auto;
    }

    .offers-history-table-wrap .dataTables_wrapper .row:first-child,
    .offers-history-table-wrap .dataTables_wrapper .row:last-child {
        align-items: center;
        margin-bottom: .75rem;
        margin-top: .5rem;
    }

    .offers-history-table-wrap .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        margin-left: .5rem;
        padding: .35rem .65rem;
    }

    .offers-history-table-wrap .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        margin: 0 .35rem;
        padding: .25rem .5rem;
    }

    .offers-history-table-wrap .dataTables_info,
    .offers-history-table-wrap .dataTables_paginate {
        font-size: .8125rem;
    }

    .offers-history-table {
        font-size: .875rem;
        margin-bottom: 0;
        width: 100%;
    }

    .offers-history-table th {
        background: #f8fafc;
        font-weight: 600;
        white-space: nowrap;
    }

    .offers-history-table td {
        vertical-align: top;
    }

    .offers-history-table .offers-history-desc {
        max-width: 280px;
        white-space: pre-line;
    }

    #offers-settings-form .form-control,
    #offers-settings-form .form-select {
        border-color: #d1d5db;
        border-radius: 8px;
        font-size: .875rem;
        min-height: 42px;
    }

    #offers-settings-form .form-control:focus,
    #offers-settings-form .form-select:focus {
        border-color: #695EEE;
        box-shadow: 0 0 0 .2rem rgba(105, 94, 238, .15);
    }
</style>

@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="client-btn d-flex justify-content-center mb-2">
                <h3 class="text-primary text-center">Settings</h3>
            </div>

            <ul class="nav nav-tabs border" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab"
                        aria-controls="general" aria-selected="true">General</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="invoice-tab" data-bs-toggle="tab" href="#invoice" role="tab"
                        aria-controls="invoice" aria-selected="false">Invoice</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="service-tab" data-bs-toggle="tab" href="#service" role="tab"
                        aria-controls="service" aria-selected="false">Discounts & Offers</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="reports-tab" data-bs-toggle="tab" href="#reports" role="tab"
                        aria-controls="reports" aria-selected="false">Reports</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="email-template-tab" data-bs-toggle="tab" href="#email-template" role="tab"
                        aria-controls="email-template" aria-selected="false">Email Templates</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" href="#notifications" role="tab"
                        aria-controls="notifications" aria-selected="false">Notifications</button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="settingsTabContent">
                <!-- General Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div style="overflow:hidden;" class="table-wrapper">

                        <div class="row p-1 m-0">
                            <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">General</p>
                        </div>
                        <form id="general-settings-form">
                            @csrf
                            <!-- Time Zone -->
                            <div class="row p-1 mb-3 align-items-center">
                                <div class="col-6">
                                    <label for="timezon" class="form-label">Time Zone</label>
                                </div>
                                <div class="col-6">
                                    <select id="timezon" name="timezone" class="form-control form-select">
                                        @foreach ($tzlist as $time)
                                            <option {{ $user->timezone == $time ? 'selected' : '' }}>{{ $time }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- Currency -->
                            <div class="row p-1 mb-3 align-items-center">
                                <div class="col-6">
                                    <label for="currenc" class="form-label">Currency</label>
                                </div>
                                <div class="col-6">
                                    <select id="currenc" name="currency" class="form-control form-select">
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option
                                                {{ $user->currency == $currency->currency_code . '(' . $currency->currency_symbol . ')' ? 'selected' : '' }}
                                                value="{{ $currency->currency_code }}({{ $currency->currency_symbol }})">
                                                {{ $currency->currency_code }} - {{ $currency->currency_symbol }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- Save Button -->
                            <div class="row p-1 m-0">
                                <div class="col-md-6">

                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" id="save-general-settings"class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- Invoice Tab -->
                <div class="tab-pane fade" id="invoice" role="tabpanel" aria-labelledby="invoice-tab">
                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">Invoice</p>
                    </div>
                    <form id="invoice-settings-form">
                        @csrf
                        <input type="hidden" name="id" value="1" />

                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Tax (%)</label>
                            </div>
                            <div class="col-6">
                                <input type="number" min="0" max="100" value="{{ $inv_setting->tax }}"
                                    id="tax" name="tax" class="form-control" placeholder="Tax (%)">
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Tax Label</label>
                            </div>
                            <div class="col-6">
                                <select name="tax_label" id="tax_label" class="form-control form-select">
                                    @foreach(\App\Models\Invoice_settings::taxLabelOptions() as $taxLabelOption)
                                        <option value="{{ $taxLabelOption }}" {{ ((!empty($inv_setting) ? ($inv_setting->tax_label ?? 'Tax') : 'Tax') === $taxLabelOption) ? 'selected' : '' }}>
                                            {{ $taxLabelOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Discount(%)</label>
                            </div>
                            <div class="col-6">
                                <input type="number" min="0" max="100" value="{{ $inv_setting->discount }}"
                                    id="discount" name="discount" class="form-control" placeholder="Discount (%)">
                            </div>
                        </div>

                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Payment Link URL</label>
                            </div>
                            <div class="col-6">
                                <input type="url" value="{{ $inv_setting->payment_link ?? '' }}" id="payment_link" name="payment_link" class="form-control" placeholder="https://example.com/pay">
                            </div>
                        </div>

                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Payment QR Code</label>
                            </div>
                            <div class="col-6">
                                <input type="file" id="payment_qr_code" name="payment_qr_code" class="form-control"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="text-muted d-block mt-1">Upload a square UPI/payment QR image (JPG, PNG, max 2MB).</small>
                                @if(!empty($inv_setting) && !empty($inv_setting->payment_qr_code))
                                    <div class="mt-2" id="payment-qr-preview">
                                        <img src="{{ asset('web_assets/users/user' . $user->id . '/' . $inv_setting->payment_qr_code) }}"
                                            alt="Payment QR code preview"
                                            style="width: 100px; height: 100px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px; padding: 4px;">
                                        <div class="mt-1">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="remove_payment_qr" value="1" id="remove_payment_qr"> Remove current QR code
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row p-1 mb-3 align-items-start">
                            <div class="col-6">
                                <label>Note</label>
                            </div>
                            <div class="col-6">
                                <textarea id="invoice_note" name="invoice_note" class="form-control" rows="4"
                                    placeholder="Optional note to appear on invoices">{{ $inv_setting->invoice_note ?? '' }}</textarea>
                                <small class="text-muted d-block mt-1">This note appears under the Note section on new invoices only.</small>
                            </div>
                        </div>

                        <div class="row p-1 m-0">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-primary" id="save-invoice-settings">Save</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Discounts & Offers Tab -->
                <div class="tab-pane fade" id="service" role="tabpanel" aria-labelledby="service-tab">
                    <div class="offers-panel">
                        <div class="offers-panel__header">
                            <h4 class="offers-panel__title">Discounts &amp; Offers</h4>
                            <p class="offers-panel__subtitle">Apply one-time wallet discounts or automated subscription benefits to selected subscribers. Confirmation emails are sent after each successful apply.</p>
                        </div>

                        <form id="offers-settings-form" method="post" novalidate>
                            @csrf
                            <input type="hidden" id="offer_mode" name="offer_mode" value="{{ old('offer_mode') }}">

                            <div class="offers-step">
                                <div class="offers-step__label">
                                    <span class="offers-step__number">1</span>
                                    Discount / Offer Type
                                </div>
                                <div class="offers-mode-grid">
                                    <label class="offers-mode-card {{ old('offer_mode', '') === 'manual' ? 'is-active' : '' }}" data-offer-mode="manual">
                                        <input type="radio" name="offer_mode_choice" value="manual" {{ old('offer_mode') == 'manual' ? 'checked' : '' }}>
                                        <div class="offers-mode-card__title">Manual / One-time Discount</div>
                                        <p class="offers-mode-card__text">Cashback, one-time wallet credit, or double subscription term applied immediately.</p>
                                    </label>
                                    <label class="offers-mode-card {{ old('offer_mode', '') === 'automated' ? 'is-active' : '' }}" data-offer-mode="automated">
                                        <input type="radio" name="offer_mode_choice" value="automated" {{ old('offer_mode') == 'automated' ? 'checked' : '' }}>
                                        <div class="offers-mode-card__title">Automated Offers</div>
                                        <p class="offers-mode-card__text">Extended term, doubled limits, or Analytics access for a defined offer period.</p>
                                    </label>
                                </div>
                            </div>

                            <div id="offer-details-section" class="offers-step offers-section-hidden">
                                <div class="offers-step__label">
                                    <span class="offers-step__number">2</span>
                                    Offer Detail
                                </div>

                                <div id="offer-subtype-row" class="mb-3 offers-section-hidden">
                                    <label class="offers-field-label" id="offer-subtype-label" for="discount_type">Offer Type</label>
                                    <select id="discount_type" name="discount_type" class="form-control form-select">
                                        <option value="">Select an option</option>
                                        <option value="cashback" {{ old('discount_type') == 'cashback' ? 'selected' : '' }}>Cashback</option>
                                        @foreach (\App\Services\OfferBenefitService::oneOffCreditTypeOptions() as $oneOffType => $oneOffLabel)
                                            <option value="{{ $oneOffType }}" {{ old('discount_type') == $oneOffType ? 'selected' : '' }}>{{ $oneOffLabel }}</option>
                                        @endforeach
                                        <option value="double_term" {{ old('discount_type') == 'double_term' ? 'selected' : '' }}>Double the subscription term</option>
                                        <option value="3_months_extra" {{ old('discount_type') == '3_months_extra' ? 'selected' : '' }}>3 Months Extra</option>
                                        <option value="6_months_extra" {{ old('discount_type') == '6_months_extra' ? 'selected' : '' }}>6 Months Extra</option>
                                        <option value="double_features" {{ old('discount_type') == 'double_features' ? 'selected' : '' }}>Double Features</option>
                                        <option value="double_clients" {{ old('discount_type') == 'double_clients' ? 'selected' : '' }}>Double Clients</option>
                                        <option value="double_users" {{ old('discount_type') == 'double_users' ? 'selected' : '' }}>Double Users</option>
                                        <option value="double_messages" {{ old('discount_type') == 'double_messages' ? 'selected' : '' }}>Double Messages</option>
                                        <option value="analytics_on" {{ old('discount_type') == 'analytics_on' ? 'selected' : '' }}>Analytics ON</option>
                                    </select>
                                    @error('discount_type')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div id="offer-date-range" class="offers-section-hidden">
                                    <label class="offers-field-label">Offer Duration</label>
                                    <div class="offers-date-grid">
                                        <div>
                                            <label class="offers-field-hint mb-1" for="offer_start_date">Start Date</label>
                                            <input type="text" id="offer_start_date" name="offer_start_date" class="form-control offer-date-input" value="{{ old('offer_start_date') }}" placeholder="dd-mm-yyyy" autocomplete="off" />
                                        </div>
                                        <div>
                                            <label class="offers-field-hint mb-1" for="offer_end_date">End Date</label>
                                            <input type="text" id="offer_end_date" name="offer_end_date" class="form-control offer-date-input" value="{{ old('offer_end_date') }}" placeholder="dd-mm-yyyy" autocomplete="off" />
                                        </div>
                                    </div>
                                    <span class="offers-field-hint">Benefits will be applied for selected subscriber(s) between these dates.</span>
                                </div>

                                <div id="dynamic-field" class="offers-section-hidden">
                                    <label class="offers-field-label" for="discount_value" id="discount_label">Discount Amount</label>
                                    <input type="number" id="discount_value" name="discount_value" class="form-control"
                                        placeholder="Enter value" min="1" step="any" value="{{ old('discount_value') }}" />
                                    @error('discount_value')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div id="offer-audience-section" class="offers-step offers-section-hidden">
                                <div class="offers-step__label">
                                    <span class="offers-step__number">3</span>
                                    <span id="offer-audience-step-title">Select Subscriber(s)</span>
                                </div>

                                <div class="mb-3">
                                    <label class="offers-field-label" for="subscriber_type">Subscriber(s)</label>
                                    <select id="subscriber_type" name="subscriber_type" class="form-control form-select" required>
                                        <option value="existing" {{ old('subscriber_type', 'existing') == 'existing' ? 'selected' : '' }}>Existing Subscribers</option>
                                        <option value="loyal" {{ old('subscriber_type') == 'loyal' ? 'selected' : '' }}>Loyal Subscribers (Over 5 Years)</option>
                                        <option value="new" {{ old('subscriber_type') == 'new' ? 'selected' : '' }}>New Subscribers</option>
                                    </select>
                                    <span class="offers-field-hint mb-3" id="subscriber-type-hint">Select one or more subscribers from the checklist below.</span>
                                </div>

                                <div id="subscriber-picker-row">
                                    <label class="offers-field-label d-block mb-2">Select Subscriber(s)</label>
                                    <div class="offers-subscriber-picker">
                                        <div class="offers-subscriber-toolbar" id="offers-subscriber-toolbar">
                                            <label class="offers-subscriber-item offers-subscriber-item--select-all mb-0">
                                                <input type="checkbox" id="selectAll">
                                                <div class="offers-subscriber-item__body">
                                                    <div class="offers-subscriber-item__name">Select All</div>
                                                </div>
                                            </label>
                                            <span class="offers-selected-count" id="manual-selected-count">0 selected</span>
                                        </div>
                                        <div class="offers-subscriber-list">
                                            @if ($subscribers)
                                                @foreach ($subscribers as $suser)
                                                    @php
                                                        $loyalSince = !empty($suser->membership_start_date)
                                                            ? \Carbon\Carbon::parse($suser->membership_start_date)
                                                            : \Carbon\Carbon::parse($suser->created_at);
                                                    @endphp
                                                    <label class="offers-subscriber-item" data-subscriber-item>
                                                        <input type="checkbox" class="subscriber-checkbox" name="subscribers[]" value="{{ $suser->id }}"
                                                            data-created="{{ \Carbon\Carbon::parse($suser->created_at)->format('Y-m-d') }}"
                                                            data-loyal-since="{{ $loyalSince->format('Y-m-d') }}">
                                                        <div class="offers-subscriber-item__body">
                                                            <div class="offers-subscriber-item__name">{{ $suser->name }}</div>
                                                            <div class="offers-subscriber-item__meta">ID <strong>{{ $suser->id }}</strong> &middot; {{ $suser->membership }}</div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                                <p id="offers-audience-empty" class="offers-subscriber-empty offers-section-hidden">No subscribers match the selected audience.</p>
                                            @else
                                                <p class="offers-subscriber-empty">No subscribers available.</p>
                                            @endif
                                        </div>
                                    </div>
                                    @error('subscribers')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div id="offer-audience-info" class="offers-info-banner offers-section-hidden"></div>
                            </div>

                            <div id="offer-actions-section" class="offers-actions offers-section-hidden">
                                <button type="submit" class="btn btn-primary px-4" id="save-offers-settings">Apply &amp; Save</button>
                            </div>
                        </form>

                        <div class="offers-history">
                            <h5 class="offers-history__title">Applied Discounts &amp; Offers</h5>
                            <p class="offers-history__subtitle">This table will have D &amp; O applied successfully. Newest first. Use this list to plan future D &amp; O.</p>
                            <div class="offers-history-table-wrap">
                                <table class="table table-bordered table-striped offers-history-table display" id="offers-history-table" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Given To</th>
                                            <th>D/O Type</th>
                                            <th>Description</th>
                                            <th>Date</th>
                                            <th>Given By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($offerApplications as $application)
                                            @php
                                                $offer = $application->offer;
                                                $offerType = (string) ($application->type ?? $offer?->discount_type ?? '');
                                                $description = $offer
                                                    ? $offerBenefitService->offerDescriptionForRecord($offer, $application->user)
                                                    : $offerBenefitService->offerTypeLabel($offerType);
                                            @endphp
                                            <tr>
                                                <td>{{ $application->id }}</td>
                                                <td>{{ $offerBenefitService->offerModeLabelForRecord($offer, $offerType) }}</td>
                                                <td>{{ $application->user_name ?? optional($application->user)->name ?? '—' }}</td>
                                                <td>{{ $offerBenefitService->offerTypeLabel($offerType) }}</td>
                                                <td class="offers-history-desc">{{ $description }}</td>
                                                <td data-order="{{ $application->created_at ? $application->created_at->timestamp : 0 }}">
                                                    {{ $application->created_at ? $application->created_at->format('d-m-Y H:i') : '—' }}
                                                </td>
                                                <td>{{ $offer?->applied_by_name ?? $application->applied_by_name ?? 'Admin' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">
                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" style="font-size:18px;font-weight:550;">Reports Settings</p>
                        <small class="text-muted px-2">A single PDF will be generated for the selected modules and sent on the selected frequency.</small>
                    </div>

                    <form id="reports-settings-form">
                        @csrf
                    <div class="row p-1 mb-3 align-items-center">
                        <div class="col-6">
                                    <label>Select Module(s)</label>
                                </div>
                                <div class="col-6">
                                    @php
                                        $selectedModules = old('modules', optional($reportSetting)->modules ?? []);
                                        $reportDefaultEmail = trim((string) (optional($reportSetting)->emails ?? $user->email ?? ''));
                                    @endphp
                                    @foreach ($reportModules as $moduleKey => $moduleLabel)
                                        <div class="form-check">
                                            <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" class="form-check-input report-module-checkbox"
                                                {{ in_array($moduleKey, $selectedModules) ? 'checked' : '' }}>
                                            <label class="form-check-label">{{ $moduleLabel }}</label>
                                        </div>
                                    @endforeach
                                    @error('modules')
                                        <span style="color: red;">{{ $message }}</span>
                                    @enderror
                                    <div class="invalid-feedback d-block" id="reports-modules-error" style="display:none;"></div>
                                </div>
                    </div>

                    <div class="row p-1 mb-3 align-items-center">
                        <div class="col-6">
                            <label>Select Frequency</label>
                        </div>
                        <div class="col-6">
                            <select name="frequency" class="form-control form-select">
                                @php
                                    $selectedFrequency = old('frequency', optional($reportSetting)->frequency ?? 'daily');
                                @endphp
                                <option value="daily" {{ $selectedFrequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $selectedFrequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ $selectedFrequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ $selectedFrequency == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            </select>
                        </div>
                    </div>

                    <div class="row p-1 mb-3 align-items-center">
                        <div class="col-6">
                            <label>Delivery Mode</label>
                        </div>
                        <div class="col-6">
                            @php
                                $selectedDeliveryMode = old('delivery_mode', optional($reportSetting)->delivery_mode ?? 'attachment');
                            @endphp
                            <select name="delivery_mode" class="form-control form-select">
                                <option value="attachment" {{ $selectedDeliveryMode == 'attachment' ? 'selected' : '' }}>Reports as PDF in Email Attachment</option>
                                <option value="link" {{ $selectedDeliveryMode == 'link' ? 'selected' : '' }}>Links to View / Download Reports</option>
                            </select>
                        </div>
                    </div>

                    <div class="row p-1 mb-3 align-items-center">
                        <div class="col-6">
                            <label>Send To</label>
                        </div>
                        <div class="col-6">
                            <textarea name="emails" class="form-control"
                                placeholder="Enter upto 5 emails separated by comma">{{ old('emails', $reportDefaultEmail) }}</textarea>
                            <div class="invalid-feedback" id="reports-emails-error"></div>
                            <small class="text-muted">Example: test1@gmail.com, test2@gmail.com</small>
                        </div>
                    </div>

                    <div class="row p-1 m-0">
                       <div class="col-md-6">

                                </div>
                                <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary" id="save-reports-settings">Apply</button>
                            <button type="reset" class="btn btn-secondary">Cancel</button>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="email-template" role="tabpanel" aria-labelledby="email-template-tab">
                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">Email Template</p>
                    </div>
                    <form id="email-template-form">
                        @csrf
                        <input type="hidden" id="emailAudience" name="audience" value="{{ $emailTemplateAudience }}">
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6"><label>Template Audience</label></div>
                            <div class="col-6">
                                <input type="text" class="form-control" value="{{ $emailTemplateAudience === 'admin' ? 'Admin Mail Templates' : 'Subscriber Mail Templates' }}" readonly>
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6"><label>Select Email Template</label></div>
                            <div class="col-6"><select id="emailTemplateKey" class="form-control form-select" name="template_key"></select></div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center" id="otherTemplateRow" style="display:none;">
                            <div class="col-6"><label>Other (text input)</label></div>
                            <div class="col-6"><input type="text" id="otherTemplateName" class="form-control" name="custom_name" placeholder="Template name"></div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6"><label>Subject</label></div>
                            <div class="col-6"><input type="text" id="emailTemplateSubject" class="form-control" name="subject" placeholder="Email subject"></div>
                        </div>
                        <div class="row p-1 mb-3 align-items-start">
                            <div class="col-6"><label>Email Body</label></div>
                            <div class="col-6"><textarea id="emailTemplateBody" name="body" class="form-control" rows="8"></textarea></div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Payment Link URL</label>
                            </div>
                            <div class="col-6">
                                <input type="url" value="{{ $inv_setting->payment_link ?? '' }}" id="payment_link" name="payment_link" class="form-control" placeholder="https://example.com/pay">
                            </div>
                        </div>

                        <div class="row p-1 m-0">
                           <div class="col-md-6">

                                </div>
                                <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-primary" id="save-email-template">Save</button>
                                <button type="button" class="btn btn-outline-secondary" id="reset-email-template">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">Notifications</p>
                    </div>
                    @php
                        $adminNotifTypes = $recipientNotificationTypes
                            ?? (class_exists(\App\Services\NotificationService::class)
                                ? \App\Services\NotificationService::adminSendableNotificationTypes()
                                : []);
                    @endphp
                    @include('partials.admin_notification_settings', [
                        'recipientNotificationTypes' => $adminNotifTypes,
                        'subscribers' => $notificationSubscribers ?? collect(),
                        'staffUsers' => $staffUsers ?? collect(),
                        'subscriberLookup' => $subscriberLookup ?? collect(),
                    ])
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>


    <script>
        const emailTemplateAudience = @json($emailTemplateAudience);
        const emailTemplatesData = @json(($emailTemplates[$emailTemplateAudience] ?? collect()->values()));
        const oneOffCreditTypes = @json(array_keys(\App\Services\OfferBenefitService::oneOffCreditTypeOptions()));
        let emailTemplateEditor = null;

        function isOneOffCreditType(type) {
            return oneOffCreditTypes.includes(type);
        }

        const automatedOfferTypes = @json(\App\Services\OfferBenefitService::automatedOfferTypeKeys());

        function parseDisplayDate(value) {
            if (!value) {
                return null;
            }

            if (/^\d{2}-\d{2}-\d{4}$/.test(value)) {
                const parts = value.split('-');
                return new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
            }

            if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                const parts = value.split('-');
                return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            }

            return null;
        }

        function validateAutomatedOfferDates(startDate, endDate) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (!startDate || !endDate) {
                return 'Please select offer start and end dates.';
            }

            const start = parseDisplayDate(startDate);
            const end = parseDisplayDate(endDate);

            if (!start || !end) {
                return 'Please enter valid dates in dd-mm-yyyy format.';
            }

            if (start < today) {
                return 'Start date cannot be before today.';
            }

            if (end < today) {
                return 'End date cannot be before today.';
            }

            if (end < start) {
                return 'End date cannot be earlier than start date.';
            }

            return '';
        }

        function getTemplatesMap() {
            const rows = (emailTemplatesData || []);
            return rows.reduce((acc, row) => {
                acc[row.template_key] = row;
                return acc;
            }, {});
        }
        function renderEmailTemplateOptions() {
            const map = getTemplatesMap();
            const select = $('#emailTemplateKey');
            select.html('');
            Object.keys(map).forEach((key) => {
                select.append(`<option value="${key}">${map[key].template_name}</option>`);
            });
            renderEmailTemplateDetails();
        }
        function renderEmailTemplateDetails() {
            const key = $('#emailTemplateKey').val();
            const template = getTemplatesMap()[key] || {};
            $('#otherTemplateRow').toggle(key === 'other');
            $('#otherTemplateName').val(template.custom_name || '');
            $('#emailTemplateSubject').val(template.subject || '');
            const body = template.body || '';
            if (emailTemplateEditor) {
                emailTemplateEditor.setData(body);
            } else {
                $('#emailTemplateBody').val(body);
            }
        }
    </script>

    <script>
        function clearReportSettingsInlineErrors() {
            const emailField = $('#reports-settings-form textarea[name="emails"]');
            emailField.removeClass('is-invalid');
            $('#reports-emails-error').text('');
            $('#reports-modules-error').text('').hide();
        }

        function setReportSettingsInlineError(field, message) {
            if (field === 'modules') {
                $('#reports-modules-error').text(message).show();
                return;
            }

            if (field === 'emails') {
                const emailField = $('#reports-settings-form textarea[name="emails"]');
                emailField.addClass('is-invalid');
                $('#reports-emails-error').text(message);
            }
        }

        $('#save-reports-settings').click(function() {
            const $saveReportsButton = $('#save-reports-settings');
            const defaultButtonText = ($saveReportsButton.data('default-text') || $.trim($saveReportsButton.text()) || 'Apply');
            clearReportSettingsInlineErrors();

            $saveReportsButton
                .data('default-text', defaultButtonText)
                .prop('disabled', true)
                .text('Submitting...');

            const emailField = $('#reports-settings-form textarea[name="emails"]');
            const emails = $.trim(emailField.val());
            const selectedModulesCount = $('#reports-settings-form input[name="modules[]"]:checked').length;

            if (!selectedModulesCount) {
                setReportSettingsInlineError('modules', 'Please select at least one module.');
                $saveReportsButton
                    .prop('disabled', false)
                    .text(defaultButtonText);
                return;
            }

            if (!emails) {
                setReportSettingsInlineError('emails', 'Please enter at least one recipient email.');
                $saveReportsButton
                    .prop('disabled', false)
                    .text(defaultButtonText);
                return;
            }

            let formData = $('#reports-settings-form').serialize();

            $.ajax({
                url: "{{ route('save_report_settings') }}",
                method: "POST",
                data: formData,

                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });

                },

                error: function(xhr) {
                    let message = 'Failed to save report settings';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        if (xhr.responseJSON.errors) {
                            Object.entries(xhr.responseJSON.errors).forEach(function([field, messages]) {
                                const inlineField = field === 'modules.0' ? 'modules' : field;
                                if (messages && messages[0]) {
                                    setReportSettingsInlineError(inlineField, messages[0]);
                                }
                            });
                            const firstErrorKey = Object.keys(xhr.responseJSON.errors)[0];
                            if (firstErrorKey && xhr.responseJSON.errors[firstErrorKey][0]) {
                                message = xhr.responseJSON.errors[firstErrorKey][0];
                            }
                        }
                    }

                    if (xhr.status !== 422) {
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: message
                        });
                    }
                },

                complete: function() {
                    $saveReportsButton
                        .prop('disabled', false)
                        .text(defaultButtonText);
                }
            });
        });

        function deleteapplication(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#695EEE',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "delete_application/" + id + "";
                }
            })
        }

        function updateapplication(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to update this record?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#695EEE',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, continue'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "application_update/" + id + "";
                }
            })
        }


        document.addEventListener('DOMContentLoaded', function() {
            const subscriberOfferEligibility = @json($subscriberOfferEligibility ?? []);
            const offerModeInput = document.getElementById('offer_mode');
            const offerModeCards = document.querySelectorAll('[data-offer-mode]');
            const offerDetailsSection = document.getElementById('offer-details-section');
            const offerAudienceSection = document.getElementById('offer-audience-section');
            const offerActionsSection = document.getElementById('offer-actions-section');
            const offerSubtypeRow = document.getElementById('offer-subtype-row');
            const offerSubtypeLabel = document.getElementById('offer-subtype-label');
            const discountType = document.getElementById('discount_type');
            const dynamicField = document.getElementById('dynamic-field');
            const discountLabel = document.getElementById('discount_label');
            const discountValue = document.getElementById('discount_value');
            const subscriberType = document.getElementById('subscriber_type');
            const subscriberTypeHint = document.getElementById('subscriber-type-hint');
            const offerDateRange = document.getElementById('offer-date-range');
            const offerAudienceInfo = document.getElementById('offer-audience-info');
            const subscriberPickerRow = document.getElementById('subscriber-picker-row');
            const offerAudienceStepTitle = document.getElementById('offer-audience-step-title');
            const saveOffersButton = document.getElementById('save-offers-settings');
            const subscriberToolbar = document.getElementById('offers-subscriber-toolbar');
            const audienceEmptyMessage = document.getElementById('offers-audience-empty');
            const manualSelectedCount = document.getElementById('manual-selected-count');
            const selectAllCheckbox = document.getElementById('selectAll');
            const subscriberCheckboxes = document.querySelectorAll('.subscriber-checkbox');
            const offerStartDateInput = document.getElementById('offer_start_date');
            const offerEndDateInput = document.getElementById('offer_end_date');
            let previousOfferMode = '';
            let offerStartDatePicker = null;
            let offerEndDatePicker = null;

            function formatDisplayDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

            function addMonthsToDate(date, months) {
                const copy = new Date(date.getTime());
                copy.setMonth(copy.getMonth() + months);
                return copy;
            }

            function initOfferDatePickers() {
                if (typeof flatpickr === 'undefined' || !offerStartDateInput || !offerEndDateInput) {
                    return;
                }

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (!offerStartDatePicker) {
                    offerStartDatePicker = flatpickr(offerStartDateInput, {
                        dateFormat: 'd-m-Y',
                        allowInput: true,
                        disableMobile: true,
                        minDate: today,
                        onChange: function(selectedDates) {
                            if (offerEndDatePicker && selectedDates[0]) {
                                offerEndDatePicker.set('minDate', selectedDates[0]);
                                const endDate = parseDisplayDate(offerEndDateInput.value);
                                if (endDate && endDate < selectedDates[0]) {
                                    offerEndDatePicker.setDate(selectedDates[0], true);
                                }
                            }
                            filterSubscriberCheckboxes();
                        },
                    });
                }

                if (!offerEndDatePicker) {
                    offerEndDatePicker = flatpickr(offerEndDateInput, {
                        dateFormat: 'd-m-Y',
                        allowInput: true,
                        disableMobile: true,
                        minDate: today,
                        onChange: function() {
                            filterSubscriberCheckboxes();
                        },
                    });
                }
            }

            function applyAutomatedOfferDateRules(resetDefaults) {
                if (!offerStartDateInput || !offerEndDateInput) {
                    return;
                }

                initOfferDatePickers();

                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const endDefault = addMonthsToDate(today, 1);

                offerStartDatePicker?.set('minDate', today);
                offerEndDatePicker?.set('minDate', today);

                if (resetDefaults || !offerStartDateInput.value.trim()) {
                    offerStartDatePicker?.setDate(today, true);
                }

                const startDate = parseDisplayDate(offerStartDateInput.value) || today;
                const endMinDate = startDate < today ? today : startDate;
                offerEndDatePicker?.set('minDate', endMinDate);

                if (resetDefaults || !offerEndDateInput.value.trim()) {
                    offerEndDatePicker?.setDate(endDefault, true);
                } else {
                    const endDate = parseDisplayDate(offerEndDateInput.value);
                    if (endDate && endDate < endMinDate) {
                        offerEndDatePicker?.setDate(endMinDate, true);
                    }
                }
            }

            initOfferDatePickers();

            function showSection(el) {
                if (el) el.classList.remove('offers-section-hidden');
            }

            function hideSection(el) {
                if (el) el.classList.add('offers-section-hidden');
            }

            function setOfferMode(mode) {
                offerModeInput.value = mode || '';
                offerModeCards.forEach(function(card) {
                    card.classList.toggle('is-active', card.dataset.offerMode === mode);
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio) radio.checked = card.dataset.offerMode === mode;
                });
            }

            function isSubscriberItemVisible(item) {
                return item && !item.classList.contains('offers-section-hidden');
            }

            function showNoLoyalSubscribersAlert() {
                Swal.fire({
                    icon: 'warning',
                    customClass: { icon: 'adwiseri-oops-icon' },
                    title: 'Oops!',
                    text: 'There is no subscriber who meets criteria to be termed as "Loyal Subscriber".',
                });
            }

            function updateDiscountTypeOptions(mode) {
                if (!discountType) {
                    return;
                }

                const isAutomated = mode === 'automated';
                const currentValue = discountType.value;
                let currentStillValid = false;

                Array.from(discountType.options).forEach(function(option) {
                    if (!option.value) {
                        return;
                    }

                    const isAutomatedType = automatedOfferTypes.includes(option.value);
                    const showOption = isAutomated ? isAutomatedType : true;

                    option.hidden = !showOption;
                    option.disabled = !showOption;

                    if (option.value === currentValue && showOption) {
                        currentStillValid = true;
                    }
                });

                if (!currentStillValid) {
                    discountType.value = '';
                    updateDiscountField('');
                }
            }

            function filterSubscriberCheckboxes(options) {
                const showLoyalAlert = options && options.showLoyalAlert;
                const audience = subscriberType.value;
                const mode = offerModeInput.value;
                const isAutomated = mode === 'automated';

                if (isAutomated) {
                    subscriberCheckboxes.forEach(function(box) {
                        box.checked = false;
                    });
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                    }
                    if (subscriberToolbar) {
                        hideSection(subscriberToolbar);
                    }
                    if (audienceEmptyMessage) {
                        hideSection(audienceEmptyMessage);
                    }
                    updateSelectedCount();
                    return 0;
                }

                const startDate = offerStartDateInput ? offerStartDateInput.value : '';
                const endDate = offerEndDateInput ? offerEndDateInput.value : '';
                const selectedOfferType = discountType ? discountType.value : '';
                const now = new Date();
                const loyalCutoff = new Date(now);
                loyalCutoff.setFullYear(loyalCutoff.getFullYear() - 5);
                let visibleCount = 0;

                subscriberCheckboxes.forEach(function(box) {
                    const item = box.closest('[data-subscriber-item]');
                    const created = new Date(box.dataset.created + 'T00:00:00');
                    const loyalSince = new Date((box.dataset.loyalSince || box.dataset.created) + 'T00:00:00');
                    let visible = true;

                    if (audience === 'loyal') {
                        visible = loyalSince <= loyalCutoff;
                    } else if (audience === 'new') {
                        if (startDate && endDate) {
                            const start = parseDisplayDate(startDate);
                            const end = parseDisplayDate(endDate);
                            if (start && end) {
                                end.setHours(23, 59, 59, 999);
                                visible = created >= start && created <= end;
                            }
                        } else {
                            const recentCutoff = new Date(now);
                            recentCutoff.setDate(recentCutoff.getDate() - 90);
                            visible = created >= recentCutoff;
                        }
                    }

                    if (selectedOfferType) {
                        const eligibility = subscriberOfferEligibility[box.value];
                        visible = visible && eligibility && eligibility[selectedOfferType] === true;
                    }

                    item.classList.toggle('offers-section-hidden', !visible);
                    if (!visible) {
                        box.checked = false;
                    } else {
                        visibleCount++;
                    }
                });

                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                }
                updateSelectedCount();

                if (visibleCount === 0) {
                    if (subscriberToolbar) {
                        hideSection(subscriberToolbar);
                    }
                    if (audienceEmptyMessage) {
                        showSection(audienceEmptyMessage);
                    }
                    if (audience === 'loyal') {
                        hideSection(offerAudienceInfo);
                        if (showLoyalAlert) {
                            showNoLoyalSubscribersAlert();
                        }
                    } else if (offerAudienceInfo) {
                        if (selectedOfferType) {
                            offerAudienceInfo.textContent = 'No subscribers are currently eligible for the selected offer type.';
                        } else {
                            offerAudienceInfo.textContent = 'Select an offer type to see eligible subscribers for manual assignment.';
                        }
                        showSection(offerAudienceInfo);
                    }
                } else {
                    if (subscriberToolbar) {
                        showSection(subscriberToolbar);
                    }
                    if (audienceEmptyMessage) {
                        hideSection(audienceEmptyMessage);
                    }
                    updateAudienceInfo(audience, offerModeInput.value);
                }

                return visibleCount;
            }

            function updateSelectedCount() {
                if (!manualSelectedCount) return;
                let count = 0;
                subscriberCheckboxes.forEach(function(box) {
                    const item = box.closest('[data-subscriber-item]');
                    if (box.checked && isSubscriberItemVisible(item)) {
                        count++;
                    }
                });
                manualSelectedCount.textContent = count + ' selected';
            }
            function updateDiscountField(type) {
                if (type === 'cashback') {
                    showSection(dynamicField);
                    discountLabel.textContent = 'Cashback (%)';
                    discountValue.placeholder = 'Enter percentage (e.g. 20)';
                    discountValue.setAttribute('max', '100');
                    discountValue.setAttribute('required', 'required');
                } else if (isOneOffCreditType(type)) {
                    showSection(dynamicField);
                    discountLabel.textContent = 'Credit Amount (USD)';
                    discountValue.placeholder = 'Enter amount (e.g. 100)';
                    discountValue.removeAttribute('max');
                    discountValue.setAttribute('required', 'required');
                } else {
                    hideSection(dynamicField);
                    discountValue.removeAttribute('required');
                    discountValue.removeAttribute('max');
                    discountValue.value = '';
                }
            }

            function updateAudienceHint(audience, mode) {
                if (mode === 'automated') {
                    const automatedHints = {
                        existing: 'This offer will apply automatically to all existing paid subscribers during the offer period. No manual selection is required.',
                        loyal: 'This offer will apply automatically to loyal subscribers (5+ years) during the offer period. No manual selection is required.',
                        new: 'This offer will apply automatically to new subscribers who register during the offer period. No manual selection is required.',
                    };
                    subscriberTypeHint.textContent = automatedHints[audience] || '';
                    return;
                }

                const hints = {
                    existing: 'All existing paid subscribers are listed. Select one or more from the checklist.',
                    loyal: 'Only subscribers with accounts over 5 years old are shown. Select from the checklist.',
                    new: 'Subscribers registered in the last 90 days are shown. Select from the checklist.',
                };
                subscriberTypeHint.textContent = hints[audience] || '';
            }

            function updateAudienceInfo(audience, mode) {
                hideSection(offerAudienceInfo);
                if (mode === 'automated') {
                    const automatedInfo = {
                        existing: 'Automated offer for all existing paid subscribers between the offer start and end dates in Step 2.',
                        loyal: 'Automated offer for loyal subscribers (5+ years) between the offer start and end dates in Step 2.',
                        new: 'Automated offer for new subscribers who register between the offer start and end dates in Step 2.',
                    };
                    offerAudienceInfo.textContent = automatedInfo[audience] || 'Automated offers are scheduled in advance. Subscriber checklist selection is not used.';
                    showSection(offerAudienceInfo);
                } else if (audience === 'loyal') {
                    offerAudienceInfo.textContent = 'The checklist is filtered to loyal subscribers (account age over 5 years).';
                    showSection(offerAudienceInfo);
                }
            }

            function updateOfferForm() {
                const mode = offerModeInput.value;
                const audience = subscriberType.value;

                if (!mode) {
                    hideSection(offerDetailsSection);
                    hideSection(offerAudienceSection);
                    hideSection(offerActionsSection);
                    discountType.removeAttribute('required');
                    return;
                }

                showSection(offerDetailsSection);
                showSection(offerAudienceSection);
                showSection(offerActionsSection);
                showSection(offerSubtypeRow);
                discountType.setAttribute('required', 'required');
                offerSubtypeLabel.textContent = 'Offer Type';
                updateDiscountTypeOptions(mode);
                updateDiscountField(discountType.value);
                updateAudienceHint(audience, mode);
                updateAudienceInfo(audience, mode);

                const isAutomated = mode === 'automated';

                if (offerAudienceStepTitle) {
                    offerAudienceStepTitle.textContent = isAutomated ? 'Select Audience' : 'Select Subscriber(s)';
                }

                if (saveOffersButton) {
                    saveOffersButton.textContent = isAutomated ? 'Save Offer' : 'Apply & Save';
                }

                if (isAutomated) {
                    hideSection(subscriberPickerRow);
                } else {
                    showSection(subscriberPickerRow);
                }

                if (isAutomated) {
                    showSection(offerDateRange);
                    offerStartDateInput.setAttribute('required', 'required');
                    offerEndDateInput.setAttribute('required', 'required');
                    applyAutomatedOfferDateRules(previousOfferMode !== 'automated');
                } else {
                    hideSection(offerDateRange);
                    offerStartDateInput.removeAttribute('required');
                    offerEndDateInput.removeAttribute('required');
                    offerStartDatePicker?.clear();
                    offerEndDatePicker?.clear();
                }

                previousOfferMode = mode;

                if (isAutomated && audience === 'loyal') {
                    const loyalCutoff = new Date();
                    loyalCutoff.setFullYear(loyalCutoff.getFullYear() - 5);
                    let loyalCount = 0;
                    subscriberCheckboxes.forEach(function(box) {
                        const loyalSince = new Date((box.dataset.loyalSince || box.dataset.created) + 'T00:00:00');
                        if (loyalSince <= loyalCutoff) {
                            loyalCount++;
                        }
                    });
                    if (loyalCount === 0) {
                        showNoLoyalSubscribersAlert();
                    }
                }

                filterSubscriberCheckboxes({ showLoyalAlert: !isAutomated && audience === 'loyal' });
            }

            offerModeCards.forEach(function(card) {
                card.addEventListener('click', function() {
                    setOfferMode(card.dataset.offerMode);
                    updateOfferForm();
                });
            });

            subscriberType.addEventListener('change', function() {
                updateOfferForm();
            });
            discountType.addEventListener('change', function() {
                updateDiscountField(this.value);
                filterSubscriberCheckboxes();
            });

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    subscriberCheckboxes.forEach(function(box) {
                        const item = box.closest('[data-subscriber-item]');
                        if (isSubscriberItemVisible(item)) {
                            box.checked = selectAllCheckbox.checked;
                        }
                    });
                    updateSelectedCount();
                });
            }

            subscriberCheckboxes.forEach(function(box) {
                box.addEventListener('change', function() {
                    if (selectAllCheckbox && !box.checked) {
                        selectAllCheckbox.checked = false;
                    }
                    updateSelectedCount();
                });
            });

            if (offerModeInput.value) {
                setOfferMode(offerModeInput.value);
            }
            updateOfferForm();
        });

        $(document).ready(() => {
            $("#country").change(function() {
                var country = $(this).val();
                // console.log(counrty);
                $.ajax({
                    url: 'get_states',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        country: country,
                    },
                    cache: false,
                    success: function(data) {
                        console.log(data);
                        $("#state").html(data);
                    }
                });
            });
            $("#currency").change(function() {
                var currency = $(this).val();
                // console.log(counrty);
                $.ajax({
                    url: 'update_currency',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        currency: currency,
                    },
                    cache: false,
                    success: function(data) {
                        if (data = "currency_updated") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Currency updated successfully.'
                            })
                        }
                    }
                });
            });
            $("#timezone").change(function() {
                var timezone = $(this).val();
                // console.log(counrty);
                $.ajax({
                    url: 'update_timezone',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        timezone: timezone,
                    },
                    cache: false,
                    success: function(data) {
                        if (data = "timezone_updated") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Timezone updated successfully.'
                            })
                        }
                    }
                });
            });
            $('#save-general-settings').click(function() {
                const timezone = $('#timezon').val();
                const currency = $('#currenc').val();

                if (!timezone) {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please select a timezone.' });
                    return;
                }

                if (!currency) {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please select a currency.' });
                    return;
                }

                let formData = $('#general-settings-form').serialize();

                $.ajax({
                    url: "{{ route('update_currency') }}",
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Settings updated successfully.',
                        });
                    },
                    error: function(xhr) {
                        const errorText = xhr?.responseJSON?.message || 'Failed to update settings!';
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText,
                        });
                    },
                });
            });
            $('#save-invoice-settings').click(function() {
                const tax = $.trim($('#tax').val());
                const discount = $.trim($('#discount').val());

                if (tax !== '' && (isNaN(tax) || Number(tax) < 0 || Number(tax) > 100)) {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Tax must be between 0 and 100.' });
                    return;
                }

                if (discount !== '' && (isNaN(discount) || Number(discount) < 0 || Number(discount) > 100)) {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Discount must be between 0 and 100.' });
                    return;
                }

                let formData = new FormData($('#invoice-settings-form')[0]);

                $.ajax({
                    url: "{{ route('invoice_settings') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        });
                    },
                    error: function(xhr) {
                        const errorText = xhr?.responseJSON?.message || 'Failed to update invoice settings!';
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText,
                        });
                    },
                });
            });
            // const discountInput = document.getElementById('discount_value');


            // discountInput.addEventListener('input', function () {
            //     if (this.value === '' || parseFloat(this.value) <= 0) {
            //         this.value = ''; // Clear the input
            //         this.placeholder = 'Error: Must be greater than 0'; // Show the error message
            //     } else {
            //         this.placeholder = ''; // Reset the placeholder
            //     }
            // });

            // $('#offers-settings-form').on('submit', function(e) {
            //     e.preventDefault(); // Prevent default form submission

            //     const discountValue = $('#discount_value').val();

            //     if (discountValue === '' || parseFloat(discountValue) < 1) {
            //         Swal.fire({
            //             icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
            //             title: 'Oops!',
            //             text: 'Discount value must be greater than or equal to 1!',
            //         });
            //         return; // Stop form submission
            //     }

            //     // Proceed with AJAX call if validation passes
            //     const formData = $(this).serialize();
            //     $.ajax({
            //         url: "{{ url('offers_store') }}",
            //         method: 'POST',
            //         data: formData,
            //         success: function(response) {
            //             Swal.fire({
            //                 icon: 'success',
            //                 title: 'Success',
            //                 text: 'Discount applied successfully!',
            //             });
            //         },
            //         error: function(xhr) {
            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Oops!',
            //                 text: 'Failed to apply discount!',
            //             });
            //         },
            //     });
            // });

            $('#selectAll').on('change', function () {
                const isChecked = $(this).is(':checked');
                $('.subscriber-checkbox').prop('checked', isChecked);
            });

            if (window.ClassicEditor) {
                ClassicEditor.create(document.querySelector('#emailTemplateBody'))
                    .then(editor => {
                        emailTemplateEditor = editor;
                        renderEmailTemplateOptions();
                    })
                    .catch(() => {
                        renderEmailTemplateOptions();
                    });
            } else {
                renderEmailTemplateOptions();
            }
            $('#emailTemplateKey').on('change', renderEmailTemplateDetails);
            $('#save-email-template').on('click', function () {
                const templateKey = $('#emailTemplateKey').val();
                const body = emailTemplateEditor ? emailTemplateEditor.getData() : $('#emailTemplateBody').val();
                $.ajax({
                    url: "{{ route('save_email_template') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                                                template_key: templateKey,
                        template_name: $('#emailTemplateKey option:selected').text(),
                        custom_name: templateKey === 'other' ? $('#otherTemplateName').val() : '',
                        subject: $('#emailTemplateSubject').val(),
                        body: body,
                    },
                    success: function (response) {
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message });
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Oops!', text: 'Failed to save email template.' });
                    }
                });
            });
            $('#reset-email-template').on('click', function () {
                renderEmailTemplateDetails();
            });

            function formatOfferSwalMessage(message) {
                return String(message || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n\n/g, '<br><br>')
                    .replace(/\n/g, '<br>');
            }

            $('#offers-settings-form').on('submit', function (e) {
                e.preventDefault();

                const offerMode = $('#offer_mode').val();
                const discountValue = $('#discount_value').val();
                const discountType = $('#discount_type').val();
                const subscriberType = $('#subscriber_type').val();
                const offerStartDate = $('#offer_start_date').val();
                const offerEndDate = $('#offer_end_date').val();
                const isAutomated = offerMode === 'automated';

                if (!offerMode) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Please choose Manual / One-time Discount or Automated Offers.',
                    });
                    return;
                }

                if (!discountType) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Please select a discount or offer.',
                    });
                    return;
                }

                if ((discountType === 'cashback' || isOneOffCreditType(discountType)) && (discountValue === '' || parseFloat(discountValue) < 1)) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Discount value must be greater than or equal to 1!',
                    });
                    return;
                }

                if (isAutomated) {
                    const dateValidationMessage = validateAutomatedOfferDates(offerStartDate, offerEndDate);
                    if (dateValidationMessage) {
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: dateValidationMessage,
                        });
                        return;
                    }

                    if (!automatedOfferTypes.includes(discountType)) {
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: 'Please select an automated offer type (for example, Double Users, Analytics ON, or 3 Months Extra).',
                        });
                        return;
                    }
                }

                let selectedSubscribers = [];
                if (!isAutomated) {
                    let visibleSubscriberCount = 0;
                    $('.subscriber-checkbox').each(function () {
                        if (!$(this).closest('[data-subscriber-item]').hasClass('offers-section-hidden')) {
                            visibleSubscriberCount++;
                        }
                    });

                    if (subscriberType === 'loyal' && visibleSubscriberCount === 0) {
                        Swal.fire({
                            icon: 'warning',
                            customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: 'There is no subscriber who meets criteria to be termed as "Loyal Subscriber".',
                        });
                        return;
                    }

                    $('.subscriber-checkbox:checked').each(function () {
                        const item = $(this).closest('[data-subscriber-item]');
                        if (!item.hasClass('offers-section-hidden')) {
                            selectedSubscribers.push($(this).val());
                        }
                    });

                    if (selectedSubscribers.length === 0) {
                        if (subscriberType === 'loyal') {
                            Swal.fire({
                                icon: 'warning',
                                customClass: { icon: 'adwiseri-oops-icon' },
                                title: 'Oops!',
                                text: 'There is no subscriber who meets criteria to be termed as "Loyal Subscriber".',
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                                title: 'Oops!',
                                text: 'Please select at least one subscriber from the checklist.',
                            });
                        }
                        return;
                    }
                }

                const formData = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    offer_mode: offerMode,
                    discount_value: discountValue,
                    discount_type: discountType,
                    subscriber_type: subscriberType,
                };

                selectedSubscribers.forEach(function (subscriberId, index) {
                    formData['subscribers[' + index + ']'] = subscriberId;
                });

                if (isAutomated) {
                    formData.offer_start_date = offerStartDate;
                    formData.offer_end_date = offerEndDate;
                }

                $.ajax({
                    url: "{{ url('offers_store') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: formData,
                    headers: {
                        'Accept': 'application/json',
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            html: formatOfferSwalMessage(response.message || 'Discounts & offers applied successfully!'),
                        }).then(function () {
                            window.location.reload();
                        });
                    },
                    error: function (xhr) {
                        let message = xhr?.responseJSON?.message
                            || xhr?.responseJSON?.error?.message
                            || 'Failed to apply discount!';
                        if (xhr?.responseJSON?.errors) {
                            const firstError = Object.values(xhr.responseJSON.errors)[0];
                            if (firstError && firstError[0]) {
                                message = firstError[0];
                            }
                        }
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            html: formatOfferSwalMessage(message),
                        });
                    },
                });
            });


        });

        let offersHistoryTable = null;

        function initOffersHistoryTable() {
            const tableEl = $('#offers-history-table');
            if (!tableEl.length || $.fn.DataTable.isDataTable(tableEl)) {
                return;
            }

            offersHistoryTable = tableEl.DataTable({
                order: [[5, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                autoWidth: false,
                columnDefs: [
                    { targets: [4], orderable: true },
                    { targets: '_all', className: 'align-top' },
                ],
                language: {
                    emptyTable: 'No discounts or offers have been applied yet.',
                    zeroRecords: 'No matching discounts or offers found.',
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                },
            });
        }

        $('#service-tab').on('shown.bs.tab', function () {
            initOffersHistoryTable();
            if (offersHistoryTable) {
                offersHistoryTable.columns.adjust().draw(false);
            }
        });

        if ($('#service').hasClass('active') || window.location.hash === '#service') {
            initOffersHistoryTable();
        }

          document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('selectAll');
        const subscriberCheckboxes = document.querySelectorAll('.subscriber-checkbox');

        // Add event listener for "All" checkbox
        selectAllCheckbox.addEventListener('change', function () {
            if (this.checked) {
                // Uncheck and disable other checkboxes
                subscriberCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    checkbox.disabled = true;
                });
            } else {
                // Enable other checkboxes
                subscriberCheckboxes.forEach(checkbox => {
                    checkbox.disabled = false;
                    checkbox.checked = false;
                });
            }
        });

        // Add event listeners for individual subscriber checkboxes
        subscriberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    // Uncheck the "All" checkbox if any subscriber is selected
                    selectAllCheckbox.checked = false;
                }
            });
        });
    });
    </script>

    @if (session()->has('deleted'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Application deleted successfully.'
            })
        </script>
    @endif
    @if (session()->has('setting_general'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'General settings updated successfully.'
            })
        </script>
    @endif
    @if (session()->has('setting_saved'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Invoice settings updated successfully.'
            })
        </script>
    @endif
    @if (session()->has('application_updated'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Service updated successfully.'
            })
        </script>
    @endif
    @if (session()->has('offer_apply'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Discounts and offers applied successfully.'
            })
        </script>
    @endif
    @if (session()->has('notification_sent'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Notification sent',
                text: @json(session('notification_sent'))
            });
        </script>
    @endif
@endpush
