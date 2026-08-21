@extends('web.layout.main')

@push('css')
<link rel="stylesheet" href="{{ asset('web_assets/css/settings-module.css') }}">
@endpush

<style>
    .nav-item {
        margin: 0px !important;
        --bs-nav-tabs-border-radius: 0px !important;
    }

    .error {
        border: 2px red solid !important;
    }

    #serviceTable th,
    #serviceTable td {
        text-align: center;
        vertical-align: middle;
    }

    #serviceTable th {
        font-weight: bold;
    }

    #appointmentRecordsTable th,
    #appointmentRecordsTable td {
        text-align: center !important;
        vertical-align: middle;
    }

    #appointmentRecordsTable th {
        font-weight: 600;
    }

    #appointmentRecordsTable .appointment-purpose-col {
        min-width: 240px;
        width: 30%;
        white-space: normal;
        word-break: break-word;
        text-align: center !important;
    }

    .service-status-switch {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        padding: 0;
        cursor: pointer;
        vertical-align: middle;
    }

    .service-status-switch:focus-visible {
        outline: 2px solid #695EEE;
        outline-offset: 3px;
        border-radius: 999px;
    }

    .service-status-track {
        position: relative;
        width: 46px;
        height: 24px;
        border-radius: 999px;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
        flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08);
    }

    .service-status-thumb {
        position: absolute;
        top: 3px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        transition: left 0.2s ease;
    }

    .service-status-switch.is-active .service-status-track {
        background: #198754;
    }

    .service-status-switch.is-active .service-status-thumb {
        left: 25px;
    }

    .service-status-switch.is-deactivated .service-status-track {
        background: #dc3545;
    }

    .service-status-switch.is-deactivated .service-status-thumb {
        left: 3px;
    }

    .service-status-switch:hover .service-status-track {
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.12), 0 0 0 3px rgba(105, 94, 238, 0.12);
    }

    .cc-checkbox-dropdown .dropdown-menu {
        max-height: 280px;
        overflow-y: auto;
        width: 100%;
    }

    .cc-checkbox-dropdown .dropdown-item {
        cursor: pointer;
        white-space: normal;
    }

    .cc-checkbox-dropdown .dropdown-item input {
        margin-right: 8px;
    }

    .cc-settings-shell {
        padding: 0.25rem 0.5rem 0.5rem;
    }

    .cc-settings-hero {
        border: 1px solid #e0e7ff;
        border-radius: 14px;
        background: linear-gradient(135deg, #F7F8FF 0%, #E5E8FF 100%);
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    .cc-settings-hero h5 {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--adwiseri-primary, #695EEE);
        margin-bottom: 0.35rem;
        text-align: center;
    }

    .cc-settings-hero p {
        color: #4b5563;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
        text-align: center;
    }

    .cc-stat-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        justify-content: center;
    }

    .dash-pref-label {
        display: block;
        width: 100%;
        text-align: center;
        font-weight: 600;
        font-size: 0.82rem;
        color: #374151;
    }

    .dash-chart-slot-title {
        text-align: center;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 0.35rem;
        color: #111827;
    }

    .dash-pref-columns {
        align-items: stretch;
    }

    .dash-pref-columns .cc-picker-card {
        min-height: 100%;
    }

    .dash-pref-columns .cc-picker-header {
        flex-wrap: wrap;
        align-items: flex-start;
    }

    .dash-pref-columns .cc-picker-header .dash-section-actions {
        margin-left: auto;
        flex-shrink: 0;
    }

    .dash-chart-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    @media (min-width: 768px) {
        .dash-chart-fields {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .dash-chart-slot {
        padding: 0.45rem 0.65rem !important;
        margin-bottom: 0.45rem !important;
    }

    .dash-chart-slot-title {
        margin-bottom: 0.35rem;
    }

    .cc-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #dbeafe;
        color: #4C3BB7;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .cc-stat-pill i {
        opacity: 0.85;
    }

    .cc-defaults-notice {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-top: 0.85rem;
        padding: 0.75rem 0.9rem;
        border-radius: 10px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .cc-defaults-notice i {
        margin-top: 0.1rem;
        color: #d97706;
    }

    .cc-defaults-notice strong {
        color: #78350f;
    }

    .cc-picker-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .cc-picker-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        padding: 0.95rem 1rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
        background: #fafafa;
    }

    .cc-picker-header h6 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
    }

    .cc-picker-header h6 i {
        color: #8B75EE;
        margin-right: 0.35rem;
    }

    .cc-picker-count {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        background: #E5E8FF;
        color: #4C3BB7;
        font-size: 0.72rem;
        font-weight: 700;
        margin-top: 0.25rem;
    }

    .cc-picker-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        padding: 0.65rem 0.75rem 0.5rem;
        border-bottom: 1px solid #f1f5f9;
        background: #fcfcfd;
    }

    .cc-tool-btn {
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 0.28rem 0.55rem;
        border-radius: 8px;
        transition: all 0.15s ease;
    }

    .cc-tool-btn:hover {
        border-color: #C4B5FD;
        background: #F3F2FF;
        color: #4C3BB7;
    }

    .cc-tool-btn.cc-tool-primary {
        border-color: #C4B5FD;
        background: #E5E8FF;
        color: #4C3BB7;
    }

    .cc-picker-search-wrap {
        position: relative;
        padding: 0.65rem 0.75rem 0.35rem;
    }

    .cc-picker-search-wrap i {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-35%);
        color: #9ca3af;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .cc-picker-search {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.45rem 0.75rem 0.45rem 2rem;
        font-size: 0.84rem;
        background: #fff;
    }

    .cc-picker-search:focus {
        outline: none;
        border-color: #a5b4fc;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .cc-picker-list {
        max-height: 220px;
        overflow-y: auto;
        padding: 0.35rem 0.75rem 0.75rem;
    }

    .cc-picker-list.is-compact {
        max-height: 180px;
    }

    .cc-picker-view-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0 0.75rem 0.45rem;
    }

    .cc-list-hint {
        font-size: 0.74rem;
        color: #6b7280;
        flex: 1 1 180px;
    }

    .cc-view-toggle {
        display: inline-flex;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .cc-view-btn {
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.65rem;
    }

    .cc-view-btn.active {
        background: #E5E8FF;
        color: #4C3BB7;
    }

    .cc-pagination {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .cc-page-btn {
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        color: #374151;
    }

    .cc-page-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .cc-page-info {
        font-size: 0.72rem;
        color: #6b7280;
        min-width: 88px;
        text-align: center;
    }

    .cc-picker-item {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.45rem 0.55rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.84rem;
        color: #374151;
        transition: background 0.12s ease;
        margin-bottom: 0.15rem;
    }

    .cc-picker-item:hover {
        background: #f8fafc;
    }

    .cc-picker-item.is-hidden {
        display: none;
    }

    .cc-picker-item input {
        flex-shrink: 0;
        margin: 0;
    }

    .cc-selected-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        min-height: 2rem;
        max-height: 92px;
        overflow-y: auto;
        padding: 0.65rem 0.75rem 0.85rem;
        border-top: 1px dashed #e5e7eb;
        background: #fafbff;
    }

    .cc-selected-chips.is-expanded {
        max-height: 180px;
    }

    .cc-chips-more-btn {
        border: none;
        background: transparent;
        color: #4C3BB7;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 0.15rem 0.35rem;
        cursor: pointer;
    }

    .cc-chip.is-collapsed-hidden {
        display: none;
    }

    .cc-selected-chips:empty::before {
        content: 'No items selected yet';
        color: #9ca3af;
        font-size: 0.78rem;
        font-style: italic;
    }

    .cc-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: #E5E8FF;
        border: 1px solid #e0e7ff;
        color: #4C3BB7;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .cc-chip button {
        border: none;
        background: transparent;
        color: #8B75EE;
        padding: 0;
        line-height: 1;
        font-size: 0.85rem;
        cursor: pointer;
    }

    .cc-chip button:hover {
        color: #4C3BB7;
    }

    .cc-settings-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }

    .cc-settings-actions--center {
        justify-content: center;
    }

    .cc-no-results {
        text-align: center;
        color: #9ca3af;
        font-size: 0.82rem;
        padding: 1rem 0.5rem;
        display: none;
    }

    .cc-no-results.is-visible {
        display: block;
    }

    .cc-doc-builder {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #f8fafc;
        padding: 1rem;
    }

    .cc-doc-builder .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }

    .cc-doc-builder-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid #e2e8f0;
    }

    .cc-doc-builder-footer .btn {
        min-height: 38px;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .cc-documents-list-wrap {
        max-height: 360px;
        overflow-y: auto;
        padding-right: 0.15rem;
    }

    .cc-documents-list-wrap::-webkit-scrollbar,
    .cc-picker-list::-webkit-scrollbar,
    .cc-selected-chips::-webkit-scrollbar {
        width: 6px;
    }

    .cc-documents-list-wrap::-webkit-scrollbar-thumb,
    .cc-picker-list::-webkit-scrollbar-thumb,
    .cc-selected-chips::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .cc-documents-panel {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: linear-gradient(180deg, #fafbff 0%, #ffffff 100%);
        padding: 1.25rem;
        margin-top: 0.5rem;
    }

    .cc-documents-panel .panel-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--adwiseri-primary, #695EEE);
        margin-bottom: 0.25rem;
        text-align: center;
    }

    .cc-documents-panel .panel-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .cc-combo-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 1rem 1.1rem;
        margin-bottom: 0.75rem;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .cc-combo-card:hover {
        border-color: #C4B5FD;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.08);
    }

    .cc-combo-card .combo-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .cc-combo-card .combo-meta {
        font-weight: 600;
        color: #111827;
        line-height: 1.4;
    }

    .cc-combo-card .combo-meta small {
        display: block;
        color: #6b7280;
        font-weight: 500;
        margin-top: 0.15rem;
    }

    .cc-doc-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        margin: 0.15rem 0.35rem 0.15rem 0;
        border-radius: 999px;
        background: #E5E8FF;
        color: #4C3BB7;
        font-size: 0.78rem;
        font-weight: 500;
        border: 1px solid #e0e7ff;
    }

    .cc-doc-section-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 0.85rem;
        margin-bottom: 0.75rem;
    }

    .cc-doc-section-card .section-head {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.65rem;
    }

    .cc-doc-section-card .section-docs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        min-height: 28px;
        margin-bottom: 0.5rem;
    }

    .cc-doc-section-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 0.15rem 0.55rem;
        font-size: 0.78rem;
    }

    .cc-doc-section-chip button {
        border: 0;
        background: transparent;
        color: #9ca3af;
        padding: 0;
        line-height: 1;
    }

    .cc-empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: #6b7280;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        background: #fff;
    }

    .cc-empty-state i {
        font-size: 1.75rem;
        color: #9ca3af;
        margin-bottom: 0.5rem;
    }

    .cc-doc-count-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        background: #F5F6FA;
        color: #047857;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.35rem;
    }
</style>

@section('main-section')

    <div class="col-lg-10 column-client">
        <div class="client-dashboard settings-module">
            <div class="client-btn d-flex justify-content-center mb-2">
                <h3 class="text-primary text-center">Settings</h3>
            </div>

            <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab"
                        aria-controls="general" aria-selected="true">General</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="invoice-tab" data-bs-toggle="tab" href="#invoice" role="tab"
                        aria-controls="invoice" aria-selected="false">Invoice</button>
                </li>
                <li class="nav-item"> 
                    <button class="nav-link" id="reports-tab" data-bs-toggle="tab" href="#reports" role="tab" aria-controls="reports" aria-selected="false"> Reports 
                    </button> 
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="service-tab" data-bs-toggle="tab" href="#service" role="tab"
                        aria-controls="service" aria-selected="false">Services</button>
                </li>
                @if(strtolower($user->user_type) !== 'admin')
                <li class="nav-item">
                    <button class="nav-link" id="cc-tab" data-bs-toggle="tab" href="#cc-settings" role="tab"
                        aria-controls="cc-settings" aria-selected="false">Countries &amp; Categories</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="dashboard-settings-tab" data-bs-toggle="tab" href="#dashboard-settings" role="tab"
                        aria-controls="dashboard-settings" aria-selected="false">Dashboard</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="enquiry-form-settings-tab" data-bs-toggle="tab" href="#enquiry-form-settings" role="tab"
                        aria-controls="enquiry-form-settings" aria-selected="false">Enquiry Form</button>
                </li>
                @endif
                <li class="nav-item">
                    <button class="nav-link" id="payment-reminder-tab" data-bs-toggle="tab" href="#payment-reminder" role="tab"
                        aria-controls="payment-reminder" aria-selected="false">Reminders</button>
                </li>
                @if(strtolower($user->user_type) !== 'admin')
                <li class="nav-item">
                    <button class="nav-link" id="documents-list-settings-tab" data-bs-toggle="tab" href="#documents-list-settings" role="tab"
                        aria-controls="documents-list-settings" aria-selected="false">Documents List</button>
                </li>
                @endif
                <li class="nav-item">
                    <button class="nav-link" id="email-template-tab" data-bs-toggle="tab" href="#email-template" role="tab"
                        aria-controls="email-template" aria-selected="false">Email Templates</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="appointment-tab" data-bs-toggle="tab"
                        href="#appointment" role="tab" aria-controls="appointment"
                        aria-selected="false">
                        Appointment Scheduler
                    </button>
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
                    <div style="overflow: hidden!important;" class="table-wrapper">
                        <div class="row p-1 m-0">
                            <p class="m-0 p-1" style="font-size:18px;font-weight: 550;">General</p>
                        </div>
                        <form  id="general-settings-form">
                            @csrf
                            <div class="row p-1 mb-3 align-items-center">
                                <div class="col-6">
                                    <label>Time Zone</label>
                                </div>
                                <div class="col-6">
                                    <select id="timezone1" name="timezone" class="form-control form-select">
                                        @foreach ($tzlist as $time)
                                            <option {{ $user->timezone == $time ? 'selected' : '' }}>{{ $time }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row p-1 mb-3 align-items-center">
                                <div class="col-6">
                                    <label>Currency</label>
                                </div>
                                <div class="col-6">
                                    <select id="currenc" name="currency" class="form-control form-select">
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option
                                                {{ $user->currency == $currency->currency_code . '(' . $currency->currency_symbol . ')' ? 'selected' : '' }}
                                                value="{{ $currency->currency_code }}({{ $currency->currency_symbol }})">
                                                {{ $currency->currency_code }} - {{ $currency->currency_symbol }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row p-1 m-0">
                                <div class="col-6"></div>
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-primary" id="save-general-settings">Save</button>
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
                        <input type="hidden" name="recipient_type" id="invoice_recipient_type" value="clients">

                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Invoice Settings For</label>
                            </div>
                            <div class="col-6 d-flex align-items-center" style="gap:20px;">
                                <label class="m-0">
                                    <input type="radio" name="invoice_settings_audience" value="clients" checked>
                                    Clients
                                </label>
                                <label class="m-0">
                                    <input type="radio" name="invoice_settings_audience" value="associates">
                                    Associates
                                </label>
                            </div>
                        </div>
                        <div class="row p-1 mb-2">
                            <div class="col-12">
                                <p class="text-muted mb-0" id="invoice-settings-audience-hint" style="font-size:13px;">
                                    These defaults apply to invoices issued to your end-clients.
                                </p>
                            </div>
                        </div>

                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Tax (%)</label>
                            </div>
                            <div class="col-6">
                                <input type="number" min="0" max="100" value="{{ !empty($inv_setting) ? $inv_setting->tax : '' }}"
                                    name="tax" id="invoice_tax" class="form-control" placeholder="Tax(%)">
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Tax Label</label>
                            </div>
                            <div class="col-6">
                                <select name="tax_label" id="tax_label" class="form-control form-select">
                                    @foreach(\App\Models\Invoice_settings::taxLabelOptions() as $taxLabelOption)
                                        <option value="{{ $taxLabelOption }}" {{ (($inv_setting->tax_label ?? 'Tax') === $taxLabelOption) ? 'selected' : '' }}>
                                            {{ $taxLabelOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Discount (%)</label>
                            </div>
                            <div class="col-6">
                                <input type="number" min="0" max="100" value="{{  !empty($inv_setting) ? $inv_setting->discount : ''; }}"
                                    name="discount" id="invoice_discount" class="form-control" placeholder="Discount(%)">
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Payment Link</label>
                            </div>
                            <div class="col-6">
                                <input type="url"   value="{{  !empty($inv_setting) ? $inv_setting->payment_link : '' }}"
                                    id="payment_link" name="payment_link" class="form-control" placeholder="Payment Link" >
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
                                <div class="mt-2" id="payment-qr-preview" style="{{ (!empty($inv_setting) && !empty($inv_setting->payment_qr_code)) ? '' : 'display:none;' }}">
                                    <img src="{{ (!empty($inv_setting) && !empty($inv_setting->payment_qr_code)) ? asset('web_assets/users/user' . $user->id . '/' . $inv_setting->payment_qr_code) : '' }}"
                                        alt="Payment QR code preview" id="payment-qr-preview-img"
                                        style="width: 100px; height: 100px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px; padding: 4px;{{ (!empty($inv_setting) && !empty($inv_setting->payment_qr_code)) ? '' : 'display:none;' }}">
                                    <div class="mt-1" id="payment-qr-remove-wrap" style="{{ (!empty($inv_setting) && !empty($inv_setting->payment_qr_code)) ? '' : 'display:none;' }}">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="remove_payment_qr" value="1" id="remove_payment_qr"> Remove current QR code
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row p-1 mb-3 align-items-start">
                            <div class="col-6">
                                <label>Note</label>
                            </div>
                            <div class="col-6">
                                <textarea id="invoice_note" name="invoice_note" class="form-control" rows="4"
                                    placeholder="Optional note to appear on invoices">{{ !empty($inv_setting) ? ($inv_setting->invoice_note ?? '') : '' }}</textarea>
                                <small class="text-muted d-block mt-1">This note appears under the Note section on new invoices only.</small>
                            </div>
                        </div>
                        <div class="row p-1 m-0">
                            <div class="col-6"></div>
                            <div class="col-6 text-right">
                                <button type="button" class="btn btn-primary" id="save-invoice-settings">Save</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Services Tab -->
                <div class="tab-pane fade" id="service" role="tabpanel" aria-labelledby="service-tab">
                    @php
                        $serviceCcPrefs = $serviceCcPreferences ?? ['countries' => collect(), 'visa_categories' => collect(), 'has_saved' => false];
                        $serviceCcCountries = collect($serviceCcPrefs['countries'] ?? []);
                        $serviceCcCategories = collect($serviceCcPrefs['visa_categories'] ?? []);
                        $serviceCcHasSaved = !empty($serviceCcPrefs['has_saved']);
                    @endphp
                    <div class="cc-settings-shell px-1">
                        <div class="cc-settings-hero mb-3" id="service-cc-preferences">
                            <h5><i class="fa fa-sliders me-1"></i> Country &amp; Visa Category Preferences</h5>
                            <p class="mb-2">
                                Add New Service only lists countries and visa categories you have saved under
                                <strong>Countries &amp; Categories</strong>. It never shows the full world list.
                            </p>
                            <div class="cc-stat-row mt-2 mb-2">
                                <span class="cc-stat-pill"><i class="fa fa-globe"></i> <span id="serviceCcCountriesCount">{{ $serviceCcCountries->count() }}</span> preferred countries</span>
                                <span class="cc-stat-pill"><i class="fa fa-passport"></i> <span id="serviceCcCategoriesCount">{{ $serviceCcCategories->count() }}</span> preferred categories</span>
                            </div>
                            <div id="serviceCcPreferencesBody">
                                @if($serviceCcHasSaved)
                                    <div class="text-start mb-2">
                                        <div class="small text-muted mb-1">Preferred countries</div>
                                        <div class="cc-selected-chips" id="serviceCcCountryChips">
                                            @foreach($serviceCcCountries as $countryName)
                                                <span class="cc-chip">{{ $countryName }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="text-start mb-2">
                                        <div class="small text-muted mb-1">Preferred visa categories</div>
                                        <div class="cc-selected-chips" id="serviceCcCategoryChips">
                                            @foreach($serviceCcCategories as $categoryName)
                                                <span class="cc-chip">{{ $categoryName }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="cc-defaults-notice mb-2" id="serviceCcEmptyNotice">
                                        <i class="fa fa-circle-info"></i>
                                        <div>
                                            <strong>No Countries &amp; Categories preferences saved yet.</strong>
                                            Configure your preferred countries and visa categories in the
                                            <strong>Countries &amp; Categories</strong> tab, then return here to add services for those combinations only.
                                        </div>
                                    </div>
                                    <div class="cc-selected-chips d-none" id="serviceCcCountryChips"></div>
                                    <div class="cc-selected-chips d-none" id="serviceCcCategoryChips"></div>
                                @endif
                            </div>
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="open-cc-from-services">
                                    Manage Countries &amp; Categories Preferences
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" id="serviceSectionTitle" style="font-size:18px;font-weight: 550;">Services</p>
                    </div>
                    <form  id="add-service">
                        <input type="hidden" name="id" value=""  id="serviceId"/>
                        @csrf
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label for="serviceCountry">Country</label>
                            </div>
                            <div class="col-6">
                                <select id="serviceCountry" name="country" class="form-control form-select">
                                    <option value="NA">NA</option>
                                    @foreach(($serviceCountryOptions ?? collect()) as $countryName)
                                        <option value="{{ $countryName }}">{{ $countryName }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">From your saved Countries &amp; Categories countries (plus NA).</small>
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Service Name</label>
                            </div>
                            <div class="col-6">
                                <select id="serviceName" name="service_name" class="form-control form-select">
                                    <option value="">Select Service Name</option>
                                    @foreach(($serviceNameOptions ?? collect()) as $serviceNameOption)
                                        <option value="{{ $serviceNameOption }}">{{ $serviceNameOption }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Consultation plus your saved Countries &amp; Categories visa categories.</small>
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Fees (Amount)</label>
                            </div>
                            <div class="col-6">
                                <input type="number" min="0" step="0.01" max="9999999999.99" id="serviceFee" name="fees" class="form-control"
                                    placeholder="Fees (Amount)">
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6"></div>
                            <div class="col-6 text-right">
                                <button type="button" class="btn btn-primary" id="save-add-service">Add New Service</button>
                            </div>
                        </div>
                    </form>
                    <div class="row p-1 m-0">
                        <div class="col-12">
                            <div class="table-wrapper">
                                <table class="fl-table table table-hover table-responsive p-0 m-0" style="width:100%"
                                    id="serviceTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Sr No.</th>
                                            <th class="text-center">Sub_Name(Sub_ID)</th>
                                            <th class="text-center">User_Name(User_ID)</th>
                                            <th class="text-center">Country</th>
                                            <th class="text-center">Service Name</th>
                                            <th class="text-center">Fees</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @if(strtolower($user->user_type) !== 'admin')
                <div class="tab-pane fade" id="cc-settings" role="tabpanel" aria-labelledby="cc-tab">
                    <form id="cc-settings-form">
                        @csrf
                        <div class="cc-settings-shell">
                            <div class="cc-settings-hero">
                                <h5><i class="fa fa-sliders me-1"></i> Countries &amp; Visa Categories</h5>
                                <p class="mb-0">
                                    Configure which destinations and visa types appear across your application forms, client modals, and enquiry screens.
                                    Defaults follow your sub-category profile — adjust anytime and save.
                                </p>
                                <div class="cc-stat-row mt-3">
                                    <span class="cc-stat-pill"><i class="fa fa-globe"></i> <span id="ccHeroCountriesCount">{{ $selectedCountries->count() }}</span> countries</span>
                                    <span class="cc-stat-pill"><i class="fa fa-passport"></i> <span id="ccHeroCategoriesCount">{{ $selectedVisaCategories->count() }}</span> visa categories</span>
                                    <span class="cc-stat-pill"><i class="fa fa-folder-open"></i> <span id="ccHeroDocCombosCount">{{ count($ccDocumentLists) }}</span> document lists</span>
                                </div>
                                @if($ccUsingDefaults)
                                    <div class="cc-defaults-notice" id="ccDefaultsNotice">
                                        <i class="fa fa-circle-info"></i>
                                        <div>
                                            <strong>Profile defaults are pre-selected.</strong>
                                            Countries and visa categories matching your sub-category are shown as checked checkboxes below.
                                            Entry forms already use these defaults — click <strong>Save Countries &amp; Categories</strong> when you want to lock in your own selection.
                                        </div>
                                    </div>
                                @else
                                    <div class="cc-defaults-notice d-none" id="ccDefaultsNotice" aria-hidden="true"></div>
                                @endif
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="cc-picker-card" data-picker="countries">
                                        <div class="cc-picker-header">
                                            <div>
                                                <h6><i class="fa fa-globe"></i>Countries</h6>
                                                <span class="cc-picker-count" id="ccCountriesCount">{{ $selectedCountries->count() }} selected</span>
                                            </div>
                                        </div>
                                        <div class="cc-picker-toolbar">
                                            <button type="button" class="cc-tool-btn cc-countries-select-all">Select all</button>
                                            <button type="button" class="cc-tool-btn cc-countries-unselect-all">Unselect all</button>
                                            <button type="button" class="cc-tool-btn cc-tool-primary cc-countries-use-defaults">Use defaults</button>
                                            <button type="button" class="cc-tool-btn cc-countries-invert">Invert</button>
                                            <button type="button" class="cc-tool-btn cc-countries-select-visible">Select visible</button>
                                        </div>
                                        <div class="cc-picker-search-wrap">
                                            <i class="fa fa-search"></i>
                                            <input type="search" class="cc-picker-search cc-countries-search" placeholder="Search countries..." autocomplete="off">
                                        </div>
                                        <div class="cc-picker-view-bar cc-countries-view-bar">
                                            <span class="cc-list-hint" id="ccCountriesListHint">Type to search or switch to Browse all.</span>
                                            <div class="cc-view-toggle">
                                                <button type="button" class="cc-view-btn cc-countries-view-btn active" data-view="selected">Selected</button>
                                                <button type="button" class="cc-view-btn cc-countries-view-btn" data-view="browse">Browse all</button>
                                            </div>
                                            <div class="cc-pagination d-none" id="ccCountriesPagination">
                                                <button type="button" class="cc-page-btn cc-countries-page-prev" disabled>Prev</button>
                                                <span class="cc-page-info" id="ccCountriesPageInfo">Page 1</span>
                                                <button type="button" class="cc-page-btn cc-countries-page-next">Next</button>
                                            </div>
                                        </div>
                                        <div class="cc-picker-list is-compact" id="ccCountriesList">
                                            @foreach ($allCountries as $country)
                                                <label class="cc-picker-item" data-label="{{ strtolower($country->country_name) }}">
                                                    <input type="checkbox" class="cc-country-checkbox" name="countries[]"
                                                        value="{{ $country->country_name }}"
                                                        {{ $selectedCountries->contains($country->country_name) ? 'checked' : '' }}>
                                                    <span>{{ $country->country_name }}</span>
                                                </label>
                                            @endforeach
                                            <div class="cc-no-results cc-countries-no-results">No countries match your search.</div>
                                        </div>
                                        <div class="cc-selected-chips" id="ccCountriesChips"></div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="cc-picker-card" data-picker="categories">
                                        <div class="cc-picker-header">
                                            <div>
                                                <h6><i class="fa fa-id-card"></i>Visa Categories</h6>
                                                <span class="cc-picker-count" id="ccCategoriesCount">{{ $selectedVisaCategories->count() }} selected</span>
                                            </div>
                                        </div>
                                        <div class="cc-picker-toolbar">
                                            <button type="button" class="cc-tool-btn cc-categories-select-all">Select all</button>
                                            <button type="button" class="cc-tool-btn cc-categories-unselect-all">Unselect all</button>
                                            <button type="button" class="cc-tool-btn cc-tool-primary cc-categories-use-defaults">Use defaults</button>
                                            <button type="button" class="cc-tool-btn cc-categories-invert">Invert</button>
                                            <button type="button" class="cc-tool-btn cc-categories-select-visible">Select visible</button>
                                        </div>
                                        <div class="cc-picker-search-wrap">
                                            <i class="fa fa-search"></i>
                                            <input type="search" class="cc-picker-search cc-categories-search" placeholder="Search visa categories..." autocomplete="off">
                                        </div>
                                        <div class="cc-picker-list" id="ccCategoriesList">
                                            @forelse ($allVisaCategories as $category)
                                                <label class="cc-picker-item" data-label="{{ strtolower($category) }}">
                                                    <input type="checkbox" class="cc-category-checkbox" name="visa_categories[]"
                                                        value="{{ $category }}"
                                                        {{ $selectedVisaCategories->contains($category) ? 'checked' : '' }}>
                                                    <span>{{ $category }}</span>
                                                </label>
                                            @empty
                                                <div class="text-muted small px-2 py-3">No visa categories found for your sub-category.</div>
                                            @endforelse
                                            <div class="cc-no-results cc-categories-no-results">No categories match your search.</div>
                                        </div>
                                        <div class="cc-selected-chips" id="ccCategoriesChips"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="cc-settings-actions">
                                <button type="button" class="btn btn-outline-secondary" id="reset-cc-settings">
                                    <i class="fa fa-rotate-left me-1"></i> Reset to Defaults
                                </button>
                                <button type="button" class="btn btn-primary" id="save-cc-settings">
                                    <i class="fa fa-save me-1"></i> Save Countries &amp; Categories
                                </button>
                            </div>

                            <div class="cc-documents-panel mt-4">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <div class="panel-title">
                                                Required Documents by Country &amp; Visa Category
                                                <span class="cc-doc-count-pill" id="ccDocComboCount">{{ count($ccDocumentLists) }} combinations</span>
                                            </div>
                                            <div class="panel-subtitle mb-0">
                                                Build checklists for each country and visa category pair. These lists help your team track which documents clients must submit.
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="generate-cc-doc-combinations">
                                            <i class="fa fa-wand-magic-sparkles me-1"></i> Generate common lists
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary" id="save-cc-document-lists">
                                            <i class="fa fa-save me-1"></i> Save Document Lists
                                        </button>
                                    </div>

                                    <div class="cc-doc-builder mb-3">
                                        <div class="row g-3 align-items-start">
                                            <div class="col-md-6">
                                                <label class="form-label" for="ccDocCountry">Country</label>
                                                <select id="ccDocCountry" class="form-control form-select">
                                                    <option value="">Select country</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="ccDocCategory">Visa Category</label>
                                                <select id="ccDocCategory" class="form-control form-select">
                                                    <option value="">Select visa category</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                                    <label class="form-label mb-0">Document Sections</label>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-cc-doc-section">
                                                        <i class="fa fa-plus me-1"></i> Add Section
                                                    </button>
                                                </div>
                                                <div id="ccDocSectionsContainer"></div>
                                                <div class="form-text">Group documents under section titles (e.g. Personal, Application, Educational). These headings appear on the printable Document List PDF.</div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Quick Pick Documents</label>
                                                <div class="cc-picker-card">
                                                    <div class="cc-picker-toolbar border-0 pt-2 pb-2">
                                                        <button type="button" class="cc-tool-btn cc-docs-select-all">Select all</button>
                                                        <button type="button" class="cc-tool-btn cc-docs-unselect-all">Unselect all</button>
                                                        <button type="button" class="cc-tool-btn cc-tool-primary cc-docs-common">Common set</button>
                                                    </div>
                                                    <div class="cc-picker-search-wrap pt-0">
                                                        <i class="fa fa-search"></i>
                                                        <input type="search" class="cc-picker-search cc-docs-search" placeholder="Search documents..." autocomplete="off">
                                                    </div>
                                                    <div class="cc-picker-list is-compact" id="ccDocTypesList">
                                                        @foreach ($documentTypes as $docType)
                                                            <label class="cc-picker-item cc-doc-picker-item" data-label="{{ strtolower($docType) }}">
                                                                <input type="checkbox" class="cc-doc-type-checkbox" value="{{ $docType }}">
                                                                <span>{{ $docType }}</span>
                                                            </label>
                                                        @endforeach
                                                        <div class="cc-no-results cc-docs-no-results">No documents match your search.</div>
                                                    </div>
                                                    <div class="cc-selected-chips" id="ccDocTypesChips"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cc-doc-builder-footer">
                                            <button type="button" class="btn btn-outline-secondary" id="clear-cc-doc-builder">Clear</button>
                                            <button type="button" class="btn btn-primary" id="add-cc-doc-combination">
                                                <i class="fa fa-plus me-1"></i> Add / Update Combination
                                            </button>
                                        </div>
                                    </div>

                                    <div class="cc-documents-list-wrap">
                                    <div id="ccDocumentListsContainer"></div>
                                    </div>
                                    <div id="ccDocumentListsEmpty" class="cc-empty-state">
                                        <div><i class="fa fa-folder-open"></i></div>
                                        <div class="fw-semibold text-dark">No document lists yet</div>
                                        <div class="small mt-1">Select a country and visa category above, choose documents, then click <strong>Add / Update Combination</strong>.</div>
                                    </div>
                                </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="dashboard-settings" role="tabpanel" aria-labelledby="dashboard-settings-tab">
                    <form id="dashboard-settings-form">
                        @csrf
                        <div class="cc-settings-shell">
                            <div class="cc-settings-hero">
                                <h5><i class="fa fa-gauge-high me-1"></i> Dashboard Preferences</h5>
                                <p class="mb-0">
                                    Choose which headers and which charts appear on your dashboard, and how each chart is drawn.
                                    Leave any position blank to hide it.
                                </p>
                                <div class="cc-stat-row mt-3">
                                    <span class="cc-stat-pill"><i class="fa fa-table-columns"></i> <span id="dashHeroHeadersCount">0</span> of {{ $dashboardHeaderSlots }} headers</span>
                                    <span class="cc-stat-pill"><i class="fa fa-chart-pie"></i> <span id="dashHeroChartsCount">0</span> of <span id="dashHeroChartsMax">{{ $dashboardChartCount }}</span> charts</span>
                                </div>
                                @if($dashboardUsingDefaults)
                                    <div class="cc-defaults-notice" id="dashDefaultsNotice">
                                        <i class="fa fa-circle-info"></i>
                                        <div>
                                            <strong>Standard dashboard defaults are pre-selected.</strong>
                                            Adjust the dropdowns below and click <strong>Save Dashboard Preferences</strong> to lock in your own layout.
                                        </div>
                                    </div>
                                @else
                                    <div class="cc-defaults-notice d-none" id="dashDefaultsNotice" aria-hidden="true"></div>
                                @endif
                            </div>

                            <div class="row g-3 dash-pref-columns">
                                <div class="col-12 col-xl-5">
                                    <div class="cc-picker-card">
                                        <div class="cc-picker-header">
                                            <div>
                                                <h6><i class="fa fa-table-columns"></i>Set Header Preferences</h6>
                                                <span class="cc-picker-count">Pick up to {{ $dashboardHeaderSlots }} stat cards, in the order you want them shown.</span>
                                            </div>
                                            <div class="dash-section-actions">
                                                <button type="button" class="btn btn-primary btn-sm" id="reset-dashboard-headers">
                                                    Reset to Default
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row g-2 p-2">
                                            @for($i = 0; $i < $dashboardHeaderSlots; $i++)
                                                <div class="col-12 col-sm-6">
                                                    <label class="form-label dash-pref-label mb-1" for="dashHeader{{ $i }}">
                                                        {{ $i + 1 }}{{ [1=>'st',2=>'nd',3=>'rd'][$i + 1] ?? 'th' }} Header
                                                    </label>
                                                    <select class="form-select dash-header-select" id="dashHeader{{ $i }}" name="headers[]">
                                                        <option value="">— None —</option>
                                                        @foreach($dashboardHeaderOptions as $key => $opt)
                                                            <option value="{{ $key }}" {{ ($dashboardHeaders[$i] ?? '') === $key ? 'selected' : '' }}>
                                                                {{ $opt['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-xl-7">
                                    <div class="cc-picker-card">
                                        <div class="cc-picker-header">
                                            <div>
                                                <h6><i class="fa fa-chart-pie"></i>Set Chart Preferences</h6>
                                                <span class="cc-picker-count">Each position takes a module, a filter, a duration and a chart type. A module + filter pair can only be used once.</span>
                                            </div>
                                            <div class="dash-section-actions">
                                                <button type="button" class="btn btn-primary btn-sm" id="reset-dashboard-charts">
                                                    Reset to Default
                                                </button>
                                            </div>
                                        </div>
                                        <div class="p-2" id="dashChartSlots">
                                            @for($i = 0; $i < $dashboardChartSlots; $i++)
                                                @php $slot = $dashboardCharts[$i] ?? null; @endphp
                                                <div class="dash-chart-slot border rounded"
                                                    data-slot-index="{{ $i }}">
                                                    <div class="dash-chart-slot-title">
                                                        Chart Position {{ $i + 1 }}
                                                    </div>
                                                    <div class="dash-chart-fields">
                                                        <div>
                                                            <label class="form-label dash-pref-label mb-1" for="dashChartModule{{ $i }}">Module</label>
                                                            <select class="form-select dash-chart-module" id="dashChartModule{{ $i }}"
                                                                name="charts[{{ $i }}][module]" data-slot-index="{{ $i }}">
                                                                <option value="">— None —</option>
                                                                @foreach($dashboardChartModules as $key => $mod)
                                                                    <option value="{{ $key }}" {{ ($slot['module'] ?? '') === $key ? 'selected' : '' }}>
                                                                        {{ $mod['label'] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="form-label dash-pref-label mb-1" for="dashChartFilter{{ $i }}">Filter</label>
                                                            <select class="form-select dash-chart-filter" id="dashChartFilter{{ $i }}"
                                                                name="charts[{{ $i }}][filter]" data-slot-index="{{ $i }}"
                                                                data-selected="{{ $slot['filter'] ?? '' }}">
                                                                <option value="">— Select Module first —</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="form-label dash-pref-label mb-1" for="dashChartDuration{{ $i }}">Duration</label>
                                                            <select class="form-select dash-chart-duration" id="dashChartDuration{{ $i }}"
                                                                name="charts[{{ $i }}][duration]">
                                                                @foreach($dashboardDurations as $key => $label)
                                                                    <option value="{{ $key }}" {{ ($slot['duration'] ?? 'since_inception') === $key ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="form-label dash-pref-label mb-1" for="dashChartType{{ $i }}">Chart Type</label>
                                                            <select class="form-select dash-chart-type" id="dashChartType{{ $i }}"
                                                                name="charts[{{ $i }}][chart_type]">
                                                                @foreach($dashboardChartTypes as $key => $label)
                                                                    <option value="{{ $key }}" {{ ($slot['chart_type'] ?? 'doughnut') === $key ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cc-settings-actions cc-settings-actions--center">
                                <button type="button" class="btn btn-primary" id="save-dashboard-settings">
                                    <i class="fa fa-save me-1"></i> Save Dashboard Preferences
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="enquiry-form-settings" role="tabpanel" aria-labelledby="enquiry-form-settings-tab">
                    <form id="enquiry-form-settings-form">
                        @csrf
                        <div class="cc-settings-shell">
                            <div class="cc-settings-hero">
                                <h5><i class="fa fa-file-lines me-1"></i> Enquiry Form</h5>
                                <p class="mb-0">
                                    Choose which sections appear on your public enquiry form. Keep <strong>Default</strong> checked for the full enquiry form, or uncheck it to pick individual sections.
                                </p>
                                @if($enquiryFormUsingDefaults)
                                    <div class="cc-defaults-notice mt-3" id="enquiryFormDefaultsNotice">
                                        <i class="fa fa-circle-info"></i>
                                        <div>
                                            <strong>All sections are enabled by default.</strong>
                                            Adjust the checkboxes below and click <strong>Save Enquiry Form Settings</strong> to customize your form.
                                        </div>
                                    </div>
                                @else
                                    <div class="cc-defaults-notice d-none mt-3" id="enquiryFormDefaultsNotice" aria-hidden="true"></div>
                                @endif
                            </div>

                            <div class="cc-picker-card">
                                <div class="cc-picker-header">
                                    <div>
                                        <h6><i class="fa fa-list-check"></i> Form Sections</h6>
                                        <span class="cc-picker-count">Toggle the enquiry form blocks shown to leads.</span>
                                    </div>
                                    <div class="dash-section-actions">
                                        <button type="button" class="btn btn-primary btn-sm" id="reset-enquiry-form-settings">
                                            Reset to Default
                                        </button>
                                    </div>
                                </div>
                                <div class="cc-picker-list p-3">
                                    @foreach($enquiryFormSectionOptions as $key => $option)
                                        <label class="cc-picker-item d-flex align-items-start gap-2 mb-2">
                                            <input
                                                type="checkbox"
                                                class="enquiry-form-section-checkbox mt-1"
                                                name="sections[{{ $key }}]"
                                                value="1"
                                                data-section-key="{{ $key }}"
                                                {{ !empty($enquiryFormSections[$key]) ? 'checked' : '' }}
                                            >
                                            <span>{{ $option['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="cc-settings-actions cc-settings-actions--center">
                                <button type="button" class="btn btn-primary" id="save-enquiry-form-settings">
                                    <i class="fa fa-save me-1"></i> Save Enquiry Form Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

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
                    <div class="reminder-panel reminder-panel-payments">
                    <form id="payment-reminder-form">
                        <input type="hidden" name="reminder_type" value="payments">
                        @csrf
                        @php
                            $selectedRemindersTo = $selectedRemindersTo ?? \App\Models\PaymentReminderSetting::REMINDERS_TO_CLIENTS;
                            $selectedEmailTo = $selectedEmailTo ?? \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY;
                        @endphp
                        <div class="row p-1 mb-3 align-items-start">
                            <div class="col-6">
                                <label>Reminders To</label>
                            </div>
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
                            <div class="col-6">
                                <label>Select Email Frequency</label>
                            </div>
                            <div class="col-6">
                                <select id="reminder-frequency" name="email_frequency" class="form-control form-select">
                                    <option value="daily" {{ optional($paymentReminderSetting)->email_frequency === "daily" ? "selected" : "" }}>Daily</option>
                                    <option value="weekly" {{ optional($paymentReminderSetting)->email_frequency === "weekly" ? "selected" : "" }}>Weekly</option>
                                    <option value="monthly" {{ optional($paymentReminderSetting)->email_frequency === "monthly" ? "selected" : "" }}>Monthly</option>
                                    <option value="quarterly" {{ optional($paymentReminderSetting)->email_frequency === "quarterly" ? "selected" : "" }}>Quarterly</option>
                                </select>
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-start">
                            <div class="col-6">
                                <label>Email To</label>
                            </div>
                            <div class="col-6">
                                <div id="reminder-email-to-clients" class="{{ ($selectedRemindersTo ?? \App\Models\PaymentReminderSetting::REMINDERS_TO_CLIENTS) === \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES ? 'd-none' : '' }}">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-client-only"
                                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY }}"
                                            {{ ($selectedEmailTo ?? \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY) === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email-to-client-only">Client(s) Only</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-client-bcc-subscriber"
                                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER }}"
                                            {{ $selectedEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_BCC_SUBSCRIBER ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email-to-client-bcc-subscriber">Client(s) + Bcc (Subscriber)</label>
                                    </div>
                                </div>
                                <div id="reminder-email-to-associates" class="{{ $selectedRemindersTo === \App\Models\PaymentReminderSetting::REMINDERS_TO_ASSOCIATES ? '' : 'd-none' }}">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-associate-only"
                                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_ONLY }}"
                                            {{ $selectedEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_ONLY ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email-to-associate-only">Associate(s) Only</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input reminder-email-to-option" type="radio" name="email_to" id="email-to-associate-bcc-subscriber"
                                            value="{{ \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER }}"
                                            {{ $selectedEmailTo === \App\Models\PaymentReminderSetting::EMAIL_TO_ASSOCIATE_BCC_SUBSCRIBER ? 'checked' : '' }}>
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
                    </div>

                    @php
                        $selectedDocumentsEmailTo = $selectedDocumentsEmailTo ?? \App\Models\PaymentReminderSetting::EMAIL_TO_CLIENT_ONLY;
                    @endphp
                    <form id="documents-reminder-form" class="reminder-panel reminder-panel-documents d-none">
                        <input type="hidden" name="reminder_type" value="documents">
                        @csrf
                        <p class="text-muted small px-1">Automatically email clients the missing documents required for their active applications.</p>
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6"><label>Select Email Frequency</label></div>
                            <div class="col-6">
                                <select name="email_frequency" class="form-control form-select">
                                    <option value="daily" {{ optional($documentsReminderSetting ?? null)->email_frequency === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ optional($documentsReminderSetting ?? null)->email_frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ optional($documentsReminderSetting ?? null)->email_frequency === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ optional($documentsReminderSetting ?? null)->email_frequency === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
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
                                    <tbody>
                                        @foreach($applicationReminders as $reminder)
                                            <tr>
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


                @include('web.partials.settings_document_lists_tab')

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
                            <div class="col-6">
                                <select id="emailTemplateKey" class="form-control form-select" name="template_key"></select>
                            </div>
                        </div>
                        <div class="row p-1 mb-3 align-items-center d-none" id="otherTemplateRow">
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
                        <div class="row p-1 m-0">
                            <div class="col-6">

                            </div>
                            <div class="col-6 text-right">
                                <button type="button" class="btn btn-primary" id="save-email-template">Save</button>
                                <button type="button" class="btn btn-outline-secondary" id="reset-email-template">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="reports" role="tabpanel" aria-labelledby="reports-tab">

                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" style="font-size:18px;font-weight:550;">
                        Reports Settings
                        </p>
                        <small class="text-muted px-2">A single PDF will be generated for the selected modules and sent on the selected frequency.</small>
                    </div>

                    <form id="reports-settings-form">
                        @csrf

                        <!-- Select Modules -->
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Select Module(s)</label>
                            </div>

                            <div class="col-6">
                                @php
                                    $selectedModules = old('modules', optional($reportSetting)->modules ?? []);
                                    $selectedModules = is_array($selectedModules) ? $selectedModules : [];
                                    $reportDefaultEmail = trim((string) (optional($reportSetting)->emails ?? $user->email ?? ''));
                                @endphp
                                @foreach ($reportModules as $moduleKey => $moduleLabel)
                                    <div class="form-check">
                                        <input type="checkbox" name="modules[]" value="{{ $moduleKey }}" class="form-check-input report-module-checkbox"
                                            {{ in_array($moduleKey, $selectedModules) ? 'checked' : '' }}>
                                        <label class="form-check-label">{{ $moduleLabel }}</label>
                                    </div>
                                @endforeach
                                <div class="invalid-feedback d-block" id="reports-modules-error" style="display:none;"></div>
                            </div>
                        </div>

                        <!-- Frequency -->
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

                        <!-- Delivery Mode -->
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

                        <!-- Send To Emails -->
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


                        <!-- Buttons -->
                        <div class="row p-1 m-0">
                        <div class="col-6"></div>

                        <div class="col-6 text-right">

                        <button type="button" class="btn btn-primary"
                        id="save-reports-settings">
                        Apply
                        </button>

                        <button type="reset" class="btn btn-secondary">
                        Cancel
                        </button>

                        </div>
                        </div>

                    </form>

                </div>

                <div class="tab-pane fade" id="appointment" role="tabpanel" aria-labelledby="appointment-tab">

                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" style="font-size:18px;font-weight:550;">
                            Appointment Scheduler
                        </p>
                    </div>

                    <form id="appointment-form">
                        @csrf

                        <!-- Select Client -->
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Select Client</label>
                            </div>
                            <div class="col-6">
                                <select name="client_id" id="appointment-client" class="form-control form-select">
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" data-email="{{ $client->email }}" data-phone="{{ $client->phone }}">
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Client Email</label>
                            </div>
                            <div class="col-6">
                                <input type="email" name="client_email" id="appointment-client-email" class="form-control" placeholder="client@example.com">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Description (Meeting Purpose)</label>
                            </div>
                            <div class="col-6">
                                <input type="text" name="remarks" class="form-control"
                                    placeholder="Meeting Purpose">
                            </div>
                        </div>

                        <!-- Select Date -->
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Select Date</label>
                            </div>
                            <div class="col-6">
                                <input type="date" name="appointment_date" class="form-control" min="{{ now()->toDateString() }}">
                            </div>
                        </div>

                        <!-- Select Time -->
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Select Time</label>
                            </div>
                            <div class="col-6">
                                <input type="time" name="appointment_time" class="form-control">
                            </div>
                        </div>

                        <!-- Send Link Via -->
                        <div class="row p-1 mb-3 align-items-center">
                            <div class="col-6">
                                <label>Send Link Via</label>
                            </div>
                            <div class="col-6">

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="send_via"
                                        value="email" checked>
                                    <label class="form-check-label">Email</label>
                                </div>

                            </div>
                        </div>

                    <!-- Buttons -->
                    <div class="row p-1 m-0">
                        <div class="col-6"></div>
                        <div class="col-6 text-right">

                            <button type="button" class="btn btn-primary"
                                id="save-appointment">
                                Apply
                            </button>

                            <button type="reset" class="btn btn-secondary">
                                Cancel
                            </button>

                        </div>
                    </div>

                </form>

                @if(isset($appointments))
                    <div class="row p-1 m-0" id="appointment-records-section">
                        <p class="m-0 p-1" style="font-size:18px;font-weight:550;">Appointment Records</p>
                    </div>
                    <div class="table-wrapper mt-2" id="appointment-records-table-wrap">
                        <table class="fl-table table table-hover table-responsive p-0 m-0" style="width:100%"
                            id="appointmentRecordsTable">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Client</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Time</th>
                                    <th class="text-center appointment-purpose-col">Purpose</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Sent By</th>
                                    <th class="text-center">Sent On</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="modal fade" id="appointmentRecordViewModal" tabindex="-1" aria-labelledby="appointmentRecordViewModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="appointmentRecordViewModalLabel">Appointment Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <dl class="row mb-0 appointment-record-view-list">
                                        <dt class="col-sm-4 text-end">ID</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-id">—</dd>
                                        <dt class="col-sm-4 text-end">Client</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-client">—</dd>
                                        <dt class="col-sm-4 text-end">Date</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-date">—</dd>
                                        <dt class="col-sm-4 text-end">Time</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-time">—</dd>
                                        <dt class="col-sm-4 text-end">Purpose</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-purpose">—</dd>
                                        <dt class="col-sm-4 text-end">Status</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-status">—</dd>
                                        <dt class="col-sm-4 text-end">Sent By</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-sent-by">—</dd>
                                        <dt class="col-sm-4 text-end">Sent On</dt>
                                        <dd class="col-sm-8 text-start" id="appointment-view-sent-on">—</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

                <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                    <div class="row p-1 m-0">
                        <p class="m-0 p-1" style="font-size:18px;font-weight:550;">Notifications</p>
                    </div>
                    @include('partials.notification_settings', [
                        'notificationTypes' => $notificationTypes,
                        'notificationPreferences' => $notificationPreferences,
                    ])
                </div>
            </div>
        </div>

    </div>

    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

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

        $('#save-reports-settings').click(function () {

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

                success: function(response){

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                },

                error:function(xhr){
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


        const paymentReminderDefaults = {
            reminders_to: @json($selectedRemindersTo),
            client_group: @json(optional($paymentReminderSetting)->client_group ?? 'all'),
            email_frequency: @json(optional($paymentReminderSetting)->email_frequency ?? 'weekly'),
            email_to: @json($selectedEmailTo)
        };

        function syncPaymentReminderAudience(preserveEmailTo) {
            const remindersTo = $('input[name="reminders_to"]:checked').val() || 'clients';
            const isAssociates = remindersTo === 'associates';

            $('#reminder-group-label').text(isAssociates ? 'Select Associate Group(s)' : 'Select Client Group(s)');
            $('#reminder-email-to-clients').toggleClass('d-none', isAssociates);
            $('#reminder-email-to-associates').toggleClass('d-none', !isAssociates);

            $('#reminder-email-to-clients .reminder-email-to-option').prop('disabled', isAssociates);
            $('#reminder-email-to-associates .reminder-email-to-option').prop('disabled', !isAssociates);

            const $visibleOptions = isAssociates
                ? $('#reminder-email-to-associates .reminder-email-to-option')
                : $('#reminder-email-to-clients .reminder-email-to-option');

            if (!preserveEmailTo || !$visibleOptions.filter(':checked').length) {
                $visibleOptions.first().prop('checked', true);
            }
        }

        syncPaymentReminderAudience(true);
        $('input[name="reminders_to"]').on('change', function () {
            syncPaymentReminderAudience(false);
        });

        $('#save-payment-reminder').click(function () {
            let formData = $('#payment-reminder-form').serialize();

            $.ajax({
                url: "{{ route('save_payment_reminder_settings') }}",
                method: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });

                    paymentReminderDefaults.reminders_to = $('input[name="reminders_to"]:checked').val();
                    paymentReminderDefaults.client_group = $('#reminder-client-group').val();
                    paymentReminderDefaults.email_frequency = $('#reminder-frequency').val();
                    paymentReminderDefaults.email_to = $('input[name="email_to"]:checked').val();
                },
                error: function (xhr) {
                    let message = 'Failed to save payment reminder settings.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: message
                    });
                }
            });
        });

        $('#cancel-payment-reminder').click(function () {
            $('input[name="reminders_to"][value="' + paymentReminderDefaults.reminders_to + '"]').prop('checked', true);
            syncPaymentReminderAudience(true);
            $('#reminder-client-group').val(paymentReminderDefaults.client_group);
            $('#reminder-frequency').val(paymentReminderDefaults.email_frequency);
            $('input[name="email_to"][value="' + paymentReminderDefaults.email_to + '"]').prop('checked', true);
        });

        function switchReminderPanel(type) {
            $('.reminder-panel-payments, .reminder-panel-documents, .reminder-panel-application').addClass('d-none');
            if (type === 'documents') {
                $('.reminder-panel-documents').removeClass('d-none');
            } else if (type === 'application') {
                $('.reminder-panel-application').removeClass('d-none');
            } else {
                $('.reminder-panel-payments').removeClass('d-none');
            }
        }

        $('#reminder-type').on('change', function () {
            switchReminderPanel($(this).val());
        });

        $('#save-documents-reminder').click(function () {
            $.ajax({
                url: "{{ route('save_payment_reminder_settings') }}",
                method: 'POST',
                data: $('#documents-reminder-form').serialize(),
                success: function (response) {
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: xhr.responseJSON?.message || 'Failed to save documents reminder settings.' });
                }
            });
        });

        $('#app-reminder-client').on('change', function () {
            const clientId = $(this).val();
            const $applicationSelect = $('#app-reminder-application');
            $applicationSelect.html('<option value="">Select Application</option>');
            if (!clientId) return;
            $.get("{{ url('/get-applications-by-client') }}/" + clientId, function (applications) {
                applications.forEach(function (application) {
                    $applicationSelect.append('<option value="' + application.id + '">' + application.application_name + '</option>');
                });
            });
        });

        $('#save-application-reminder').click(function () {
            $.ajax({
                url: "{{ route('save_application_reminder') }}",
                method: 'POST',
                data: $('#application-reminder-form').serialize(),
                success: function (response) {
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message }).then(function () {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: xhr.responseJSON?.message || 'Failed to save application reminder.' });
                }
            });
        });

        $(document).on('click', '.delete-application-reminder', function () {
            const id = $(this).data('id');
            $.ajax({
                url: "{{ url('/application-reminders') }}/" + id,
                method: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function (response) {
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message }).then(function () {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: xhr.responseJSON?.message || 'Failed to delete application reminder.' });
                }
            });
        });

        $('#open-cc-doc-builder').on('click', function () {
            const tab = document.querySelector('#cc-tab');
            if (tab) {
                bootstrap.Tab.getOrCreateInstance(tab).show();
                document.querySelector('.cc-documents-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });


        const appointmentDateField = $('#appointment-form input[name="appointment_date"]');
        const appointmentTimeField = $('#appointment-form input[name="appointment_time"]');
        const todayIso = new Date().toISOString().split('T')[0];
        appointmentDateField.attr('min', todayIso);

        function syncAppointmentTimeMin() {
            if (!appointmentDateField.length || !appointmentTimeField.length) {
                return;
            }

            if (appointmentDateField.val() === todayIso) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                appointmentTimeField.attr('min', `${hours}:${minutes}`);
            } else {
                appointmentTimeField.removeAttr('min');
            }
        }

        appointmentDateField.on('change input', syncAppointmentTimeMin);
        syncAppointmentTimeMin();

        let appointmentRecordsPollTimer = null;

        function initAppointmentRecordsTable() {
            if (!$('#appointmentRecordsTable').length || $.fn.DataTable.isDataTable('#appointmentRecordsTable')) {
                return;
            }

            $('#appointmentRecordsTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[7, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ajax: {
                    url: "{{ route('get_appointment_records') }}",
                    type: 'GET',
                },
                columnDefs: [
                    { targets: '_all', className: 'text-center align-middle' },
                    { targets: 4, className: 'text-center align-middle appointment-purpose-col' },
                    { targets: 8, orderable: false, searchable: false },
                ],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'client_name', name: 'client_name' },
                    { data: 'appointment_date', name: 'appointment_date' },
                    { data: 'appointment_time', name: 'appointment_time' },
                    { data: 'remarks', name: 'remarks' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'sent_by', name: 'sent_by' },
                    { data: 'sent_on', name: 'sent_on' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                drawCallback: function () {
                    document.querySelectorAll('#appointmentRecordsTable [data-bs-toggle="tooltip"]').forEach(function (el) {
                        bootstrap.Tooltip.getOrCreateInstance(el);
                    });
                },
            });
        }

        function showAppointmentRecordModal(rowData) {
            $('#appointment-view-id').text(rowData.id ?? '—');
            $('#appointment-view-client').text(rowData.client_name ?? '—');
            $('#appointment-view-date').text(rowData.appointment_date ?? '—');
            $('#appointment-view-time').text(rowData.appointment_time ?? '—');
            $('#appointment-view-purpose').text(rowData.remarks ?? '—');
            $('#appointment-view-status').html(rowData.status ?? '—');
            $('#appointment-view-sent-by').text(rowData.sent_by ?? '—');
            $('#appointment-view-sent-on').text(rowData.sent_on ?? '—');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('appointmentRecordViewModal')).show();
        }

        $(document).on('click', '#appointmentRecordsTable .appointment-record-view-btn', function (event) {
            event.preventDefault();

            const table = $('#appointmentRecordsTable').DataTable();
            const rowData = table.row($(this).closest('tr')).data();

            if (rowData) {
                showAppointmentRecordModal(rowData);
            }
        });

        function reloadAppointmentRecordsTable(resetPaging) {
            if (!$.fn.DataTable.isDataTable('#appointmentRecordsTable')) {
                return;
            }

            $('#appointmentRecordsTable').DataTable().ajax.reload(null, resetPaging === true);
        }

        function isAppointmentTabActive() {
            return $('#appointment').hasClass('active') || $('#appointment').hasClass('show');
        }

        function startAppointmentRecordsPolling() {
            initAppointmentRecordsTable();
            reloadAppointmentRecordsTable(false);

            if (appointmentRecordsPollTimer) {
                return;
            }

            appointmentRecordsPollTimer = setInterval(function () {
                reloadAppointmentRecordsTable(false);
            }, 5000);
        }

        function stopAppointmentRecordsPolling() {
            if (!appointmentRecordsPollTimer) {
                return;
            }

            clearInterval(appointmentRecordsPollTimer);
            appointmentRecordsPollTimer = null;
        }

        $('#appointment-tab').on('shown.bs.tab', startAppointmentRecordsPolling);

        document.querySelectorAll('[data-bs-toggle="tab"][href="#appointment"], [data-bs-toggle="tab"][data-bs-target="#appointment"]')
            .forEach(function (tabTrigger) {
                tabTrigger.addEventListener('shown.bs.tab', startAppointmentRecordsPolling);
            });

        document.querySelectorAll('[data-bs-toggle="tab"]:not([href="#appointment"]):not([data-bs-target="#appointment"])')
            .forEach(function (tabTrigger) {
                tabTrigger.addEventListener('shown.bs.tab', stopAppointmentRecordsPolling);
            });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && isAppointmentTabActive()) {
                reloadAppointmentRecordsTable(false);
            }
        });

        window.addEventListener('focus', function () {
            if (isAppointmentTabActive()) {
                reloadAppointmentRecordsTable(false);
            }
        });

        if (window.location.hash === '#appointment' || isAppointmentTabActive()) {
            startAppointmentRecordsPolling();
        }

        $('#save-appointment').click(function () {
            const clientId = $('#appointment-client').val();
            const clientEmail = $.trim($('#appointment-client-email').val());
            const remarks = $.trim($('#appointment-form input[name="remarks"]').val());
            const appointmentDateField = $('#appointment-form input[name="appointment_date"]');
            const appointmentDate = appointmentDateField.val();
            const appointmentTime = $('#appointment-form input[name="appointment_time"]').val();

            if (!clientId) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please select a client.' });
                return;
            }

            if (!clientEmail) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please enter the client email.' });
                return;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(clientEmail)) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please enter a valid client email address.' });
                return;
            }

            if (!remarks) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please enter the meeting purpose.' });
                return;
            }

            if (!appointmentDate) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please select an appointment date.' });
                return;
            }

            const selectedDate = new Date(`${appointmentDate}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'The appointment date cannot be in the past.' });
                return;
            }

            if (!appointmentTime) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please select an appointment time.' });
                return;
            }

            const scheduledAt = new Date(`${appointmentDate}T${appointmentTime}:00`);
            const now = new Date();
            if (scheduledAt <= now) {
                Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Appointment must be scheduled for a future date and time.' });
                return;
            }

            let formData = $('#appointment-form').serialize();

            $.ajax({
                url: "{{ route('save_appointment') }}",
                method: 'POST',
                data: formData,
                success: function (response) {
                    reloadAppointmentRecordsTable(true);
                    $('#appointment-form')[0].reset();
                    $('#appointment-client-email').val('');

                    Swal.fire({
                        icon: response.email_sent === false ? 'warning' : 'success',
                        title: response.email_sent === false ? 'Saved with warning' : 'Success',
                        text: response.message
                    });
                },
                error: function (xhr) {

                    let message = 'Failed to schedule appointment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    }

                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: message
                    });

                }
            });

        });

        $('#appointment-client').on('change', function () {
            const selected = $(this).find(':selected');
            $('#appointment-client-email').val(selected.data('email') || '');
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
    </script>
    <script>
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
                    url: "{{ route('update_my_currency') }}",
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
            $("#tax").change(function() {
                var tax = $(this).val();
                // console.log(counrty);
                $.ajax({
                    url: "{{ route('invoice_settings') }}",
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        tax: tax,
                    },
                    cache: false,
                    success: function(data) {
                        if (data = "setting_saved") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Settings updated successfully.'
                            })
                        }
                    }
                });
            });
            $("#discount").change(function() {
                var discount = $(this).val();
                // console.log(counrty);
                $.ajax({
                    url: "{{ route('invoice_settings') }}",
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        discount: discount,
                    },
                    cache: false,
                    success: function(data) {
                        if (data = "setting_saved") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Settings updated successfully.'
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



            $('#serviceTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('get_subscriber_service') }}",
                    type: 'GET',
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'subscriber',
                        name: 'subscriber'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'country',
                        name: 'country'
                    },
                    {
                        data: 'service_name',
                        name: 'service_name'
                    },
                    {
                        data: 'fees',
                        name: 'fees'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });



        });
        function ensureServiceCountryOption(country) {
            const value = ((country || 'NA').toString().trim()) || 'NA';
            const $select = $('#serviceCountry');
            if ($select.find('option').filter(function () { return $(this).val() === value; }).length === 0) {
                $select.append($('<option></option>').attr('value', value).text(value));
            }
            $select.val(value);
        }

        function ensureServiceNameOption(name) {
            const value = (name || '').toString().trim();
            if (!value) {
                $('#serviceName').val('');
                return;
            }
            const $select = $('#serviceName');
            if ($select.find('option').filter(function () { return $(this).val() === value; }).length === 0) {
                $select.append($('<option></option>').attr('value', value).text(value));
            }
            $select.val(value);
        }

        function updateServiceCcPreferencesSummary(serviceCountries, serviceNames, preferences) {
            let countries = [];
            let names = [];

            if (preferences && typeof preferences === 'object') {
                countries = (preferences.countries || []).map(function (c) { return (c || '').toString().trim(); }).filter(Boolean);
                names = (preferences.visa_categories || []).map(function (n) { return (n || '').toString().trim(); }).filter(Boolean);
            } else {
                countries = (serviceCountries || []).map(function (c) { return (c || '').toString().trim(); }).filter(Boolean);
                names = (serviceNames || []).map(function (n) { return (n || '').toString().trim(); }).filter(Boolean)
                    .filter(function (n) { return n.toLowerCase() !== 'consultation'; });
            }

            $('#serviceCcCountriesCount').text(countries.length);
            $('#serviceCcCategoriesCount').text(names.length);

            const $countryChips = $('#serviceCcCountryChips').empty().removeClass('d-none');
            const $categoryChips = $('#serviceCcCategoryChips').empty().removeClass('d-none');
            countries.forEach(function (country) {
                $countryChips.append($('<span class="cc-chip"></span>').text(country));
            });
            names.forEach(function (name) {
                $categoryChips.append($('<span class="cc-chip"></span>').text(name));
            });

            if (countries.length && names.length) {
                $('#serviceCcEmptyNotice').addClass('d-none');
            } else if ($('#serviceCcEmptyNotice').length === 0) {
                $('#serviceCcPreferencesBody').prepend(
                    '<div class="cc-defaults-notice mb-2" id="serviceCcEmptyNotice">' +
                    '<i class="fa fa-circle-info"></i><div><strong>No Countries &amp; Categories preferences saved yet.</strong> ' +
                    'Configure preferred countries and visa categories in the <strong>Countries &amp; Categories</strong> tab.</div></div>'
                );
            } else {
                $('#serviceCcEmptyNotice').removeClass('d-none');
            }
        }

        function updateServiceDropdowns(serviceCountries, serviceNames, preferences) {
            const $countrySelect = $('#serviceCountry');
            const currentCountry = $countrySelect.val();
            $countrySelect.find('option:not([value="NA"])').remove();
            (serviceCountries || []).forEach(function (country) {
                const value = (country || '').toString().trim();
                if (!value) {
                    return;
                }
                $countrySelect.append($('<option></option>').attr('value', value).text(value));
            });
            ensureServiceCountryOption(currentCountry || 'NA');

            const $nameSelect = $('#serviceName');
            const currentName = $nameSelect.val();
            $nameSelect.find('option:not([value=""])').remove();
            $nameSelect.append($('<option></option>').attr('value', '').text('Select Service Name'));
            (serviceNames || []).forEach(function (name) {
                const value = (name || '').toString().trim();
                if (!value) {
                    return;
                }
                $nameSelect.append($('<option></option>').attr('value', value).text(value));
            });
            if (currentName) {
                ensureServiceNameOption(currentName);
            } else {
                $nameSelect.val('');
            }

            updateServiceCcPreferencesSummary(serviceCountries, serviceNames, preferences);
        }

        function ccSettingsErrorMessage(xhr, fallback) {
            if (xhr?.responseJSON?.message) {
                return xhr.responseJSON.message;
            }
            if (xhr?.responseJSON?.errors) {
                const first = Object.values(xhr.responseJSON.errors).flat()[0];
                if (first) {
                    return first;
                }
            }
            return fallback;
        }

        function resetServiceFormMode() {
            $('#serviceSectionTitle').text('Services');
            $('#save-add-service').text('Add New Service');
            $('#add-service #serviceId').val('');
            ensureServiceCountryOption('NA');
            $('#serviceName').val('');
        }

        $('#open-cc-from-services').on('click', function () {
            const tabTrigger = document.querySelector('#cc-tab');
            if (tabTrigger && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
            } else {
                $('#cc-tab').tab('show');
            }
            $('html, body').animate({ scrollTop: $('#cc-settings').offset().top - 80 }, 300);
        });

        $(document).on('click', '.editService', function() {
            const id = $(this).data('id');
            const country = $(this).data('country');
            const name = $(this).data('name');
            const fee = $(this).data('fee');

            $('#add-service #serviceId').val(id);
            ensureServiceCountryOption(country);
            ensureServiceNameOption(name);
            $('#add-service #serviceFee').val(fee);
            $('#serviceSectionTitle').text('Update Service');
            $('#save-add-service').text('Update');

            $('html, body').animate({
                scrollTop: $('#serviceSectionTitle').offset().top - 80
            }, 300);
        });

        $(document).on('click', '.service-status-switch', function() {
            const serviceId = $(this).data('id');
            const nextStatus = Number($(this).data('status'));
            const nextLabel = nextStatus === 1 ? 'Active' : 'Deactivated';
            const currentLabel = nextStatus === 1 ? 'Deactivated' : 'Active';

            Swal.fire({
                title: 'Change status?',
                html: 'This service is currently <strong>' + currentLabel + '</strong>.<br>Do you want to mark it as <strong>' + nextLabel + '</strong>?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: nextStatus === 1 ? '#198754' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, set to ' + nextLabel
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: "{{ route('add_service') }}",
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: serviceId,
                        status: nextStatus
                    },
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message });
                        $('#serviceTable').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        const errorText = serviceAjaxErrorMessage(xhr, 'An error occurred while updating the service status.');
                        Swal.fire({ icon: 'error', title: 'Oops!', text: errorText });
                    }
                });
            });
        });

        function deleteService(serviceId) {
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
                    // Send an AJAX request to delete the service
                    $.ajax({
                        url: '/services_delete/' + serviceId, // Update with your route
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}" // Include CSRF token
                        },
                        success: function(response) {
                            Swal.fire({ icon: 'success', title: 'Success', text: response.message });
                            // Refresh the DataTable or remove the row
                            $('#serviceTable').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Oops!', text: 'An error occurred while deleting the service.' });
                        }
                    });
                }
            });
        }
        $('#save-general-settings').click(function () {
                const timezone = $('#timezone1').val();
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
                    url: "{{ route('update_my_currency') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'General settings updated successfully.',
                        });
                    },
                    error: function (xhr) {
                        const errorText = xhr?.responseJSON?.message || 'Failed to update settings!';
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText,
                        });
                    },
                });
            });
            $('#save-invoice-settings').click(function () {
                    const tax = $('input[name="tax"]').val();
                    const discount = $('input[name="discount"]').val();
                    const paymentLink = $.trim($('#payment_link').val());

                    if (tax !== '' && (isNaN(tax) || Number(tax) < 0 || Number(tax) > 100)) {
                        Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Tax must be between 0 and 100.' });
                        return;
                    }

                    if (discount !== '' && (isNaN(discount) || Number(discount) < 0 || Number(discount) > 100)) {
                        Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Discount must be between 0 and 100.' });
                        return;
                    }

                    if (paymentLink) {
                        try {
                            new URL(paymentLink);
                        } catch (e) {
                            Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Please enter a valid payment link URL.' });
                            return;
                        }
                    }

                    let formData = new FormData($('#invoice-settings-form')[0]);
                    formData.set('recipient_type', $('#invoice_recipient_type').val() || 'clients');

                    $.ajax({
                        url: "{{ route('invoice_settings') }}",
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.settings && response.recipient_type) {
                                invoiceSettingsByRecipient[response.recipient_type] = response.settings;
                                applyInvoiceSettingsAudience(response.recipient_type);
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            });
                        },
                        error: function (xhr) {
                            const errorText = xhr?.responseJSON?.message || 'Failed to update invoice settings!';
                            Swal.fire({
                                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                                title: 'Oops!',
                                text: errorText,
                            });
                        },
                    });
            });

            var invoiceSettingsByRecipient = @json($invoiceSettingsByRecipient ?? ['clients' => [], 'associates' => []]);

            $('#payment_qr_code').on('change', function () {
                var file = this.files && this.files[0] ? this.files[0] : null;
                if (!file) {
                    return;
                }

                if (!file.type || file.type.indexOf('image/') !== 0) {
                    Swal.fire({
                        icon: 'warning',
                        customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Please select a valid image file (JPG, PNG).',
                    });
                    $(this).val('');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'warning',
                        customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'QR image must be 2MB or smaller.',
                    });
                    $(this).val('');
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#payment-qr-preview').show();
                    $('#payment-qr-preview-img').attr('src', e.target.result).show();
                    $('#payment-qr-remove-wrap').show();
                    $('#remove_payment_qr').prop('checked', false);
                };
                reader.readAsDataURL(file);
            });

            function applyInvoiceSettingsAudience(audience) {
                audience = audience === 'associates' ? 'associates' : 'clients';
                $('#invoice_recipient_type').val(audience);
                var settings = invoiceSettingsByRecipient[audience] || {};
                $('#invoice_tax').val(settings.tax ?? '');
                $('#invoice_discount').val(settings.discount ?? '');
                $('#tax_label').val(settings.tax_label || 'Tax');
                $('#payment_link').val(settings.payment_link || '');
                $('#invoice_note').val(settings.invoice_note || '');
                $('#payment_qr_code').val('');
                $('#remove_payment_qr').prop('checked', false);

                if (settings.payment_qr_url) {
                    $('#payment-qr-preview').show();
                    $('#payment-qr-preview-img').attr('src', settings.payment_qr_url).show();
                    $('#payment-qr-remove-wrap').show();
                } else {
                    $('#payment-qr-preview-img').attr('src', '').hide();
                    $('#payment-qr-remove-wrap').hide();
                    $('#payment-qr-preview').hide();
                }

                $('#invoice-settings-audience-hint').text(
                    audience === 'associates'
                        ? 'These defaults apply to invoices issued to your associates.'
                        : 'These defaults apply to invoices issued to your end-clients.'
                );
            }

            $('input[name="invoice_settings_audience"]').on('change', function () {
                applyInvoiceSettingsAudience($(this).val());
            });
            applyInvoiceSettingsAudience($('input[name="invoice_settings_audience"]:checked').val() || 'clients');

            function serviceAjaxErrorMessage(xhr, fallback) {
                const json = xhr?.responseJSON;
                if (!json) {
                    return fallback;
                }
                if (json.message) {
                    return json.message;
                }
                if (json.error && typeof json.error === 'object' && json.error.message) {
                    return json.error.message;
                }
                if (json.errors) {
                    const first = Object.values(json.errors).flat()[0];
                    if (first) {
                        return first;
                    }
                }
                return fallback;
            }

            $('#save-add-service').click(function () {
                    const serviceCountry = $.trim($('#serviceCountry').val());
                    const serviceName = $.trim($('#serviceName').val());
                    const serviceFee = $('#serviceFee').val();

                    if (!serviceCountry) {
                        Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Country is required.' });
                        return;
                    }

                    if (!serviceName) {
                        Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Service name is required.' });
                        return;
                    }

                    if (serviceFee === '' || isNaN(serviceFee) || Number(serviceFee) < 0) {
                        Swal.fire({ icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' }, title: 'Oops!', text: 'Fees are required and must be 0 or greater.' });
                        return;
                    }

                    let formData = $('#add-service').serialize();

                    $.ajax({
                        url: "{{ route('add_service') }}",
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            });
                            $('#add-service')[0].reset();
                            resetServiceFormMode();
                            $('#serviceTable').DataTable().ajax.reload(null, true);
                        },
                        error: function (xhr) {
                            const errorText = serviceAjaxErrorMessage(xhr, 'Failed to save service. Please try again.');
                            Swal.fire({
                                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                                title: 'Oops!',
                                text: errorText,
                            });
                        },
                    });
            });
    </script>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        const emailTemplateAudience = @json($emailTemplateAudience);
        const emailTemplatesData = @json(($emailTemplates[$emailTemplateAudience] ?? collect())->values());
        const emailTemplateState = { editor: null };

        function mapTemplatesByAudience() {
            const items = (emailTemplatesData || []);
            return items.reduce((acc, item) => {
                acc[item.template_key] = item;
                return acc;
            }, {});
        }

        function loadEmailTemplateOptions() {
            const templates = mapTemplatesByAudience();
            const select = $('#emailTemplateKey');
            select.html('');
            Object.keys(templates).forEach((key) => {
                select.append(`<option value="${key}">${templates[key].template_name}</option>`);
            });
            loadEmailTemplateDetails();
        }

        function loadEmailTemplateDetails() {
            const key = $('#emailTemplateKey').val();
            const template = mapTemplatesByAudience()[key] || {};
            $('#otherTemplateRow').toggleClass('d-none', key !== 'other');
            $('#otherTemplateName').val(template.custom_name || '');
            $('#emailTemplateSubject').val(template.subject || '');
            if (emailTemplateState.editor) {
                emailTemplateState.editor.setData(template.body || '');
            } else {
                $('#emailTemplateBody').val(template.body || '');
            }
        }

        $(document).ready(function () {
            const editorElement = document.querySelector('#emailTemplateBody');
            const editorInit = window.ClassicEditor
                ? window.ClassicEditor.create(editorElement)
                    .then((editor) => {
                        emailTemplateState.editor = editor;
                    })
                    .catch(() => {
                        emailTemplateState.editor = null;
                    })
                : Promise.resolve();

            editorInit.finally(() => {
                loadEmailTemplateOptions();
            });
                        $('#emailTemplateKey').on('change', loadEmailTemplateDetails);

            $('#save-email-template').on('click', function () {
                const key = $('#emailTemplateKey').val();
                const selectedText = $('#emailTemplateKey option:selected').text();
                const body = emailTemplateState.editor ? emailTemplateState.editor.getData() : $('#emailTemplateBody').val();
                $.ajax({
                    url: "{{ route('save_email_template') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                                                template_key: key,
                        template_name: selectedText,
                        custom_name: key === 'other' ? $('#otherTemplateName').val() : '',
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
                loadEmailTemplateDetails();
            });

            @if(strtolower($user->user_type) !== 'admin')
            const ccDefaults = {
                countries: @json($defaultCountries->values()),
                visaCategories: @json($defaultVisaCategories->values()),
            };

            const ccCommonDocuments = @json($ccCommonDocuments);

            const ccDocumentListsState = {
                items: @json($ccDocumentLists),
                editingKey: null,
            };
            const ccDocSectionsState = {
                sections: [],
            };

            function flattenEntryDocuments(entry) {
                if (Array.isArray(entry.sections) && entry.sections.length) {
                    return entry.sections.reduce(function (all, section) {
                        return all.concat(section.documents || []);
                    }, []);
                }

                return entry.documents || [];
            }

            function renderCcDocSections() {
                const container = $('#ccDocSectionsContainer');
                container.empty();

                ccDocSectionsState.sections.forEach(function (section, sectionIndex) {
                    const chips = (section.documents || []).map(function (doc, docIndex) {
                        return `
                            <span class="cc-doc-section-chip">
                                ${escapeHtml(doc)}
                                <button type="button" class="cc-remove-section-doc" data-section-index="${sectionIndex}" data-doc-index="${docIndex}" aria-label="Remove">&times;</button>
                            </span>
                        `;
                    }).join('');

                    container.append(`
                        <div class="cc-doc-section-card" data-section-index="${sectionIndex}">
                            <div class="section-head">
                                <input type="text" class="form-control form-control-sm cc-doc-section-title" value="${escapeHtml(section.title || '')}" placeholder="Section title (e.g. Personal)">
                                <button type="button" class="btn btn-sm btn-outline-primary cc-add-checked-to-section" data-section-index="${sectionIndex}">Add checked</button>
                                <button type="button" class="btn btn-sm btn-outline-danger cc-remove-doc-section" data-section-index="${sectionIndex}"><i class="fa fa-trash"></i></button>
                            </div>
                            <div class="section-docs">${chips || '<span class="text-muted small">No documents in this section yet.</span>'}</div>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control cc-doc-custom-input" data-section-index="${sectionIndex}" placeholder="Add custom document name">
                                <button type="button" class="btn btn-outline-secondary cc-add-custom-doc" data-section-index="${sectionIndex}">Add</button>
                            </div>
                        </div>
                    `);
                });
            }

            function ensureCcDocSections() {
                if (!ccDocSectionsState.sections.length) {
                    ccDocSectionsState.sections.push({ title: 'Personal', documents: [] });
                }
            }

            function loadCcDocSectionsFromEntry(entry) {
                if (Array.isArray(entry.sections) && entry.sections.length) {
                    ccDocSectionsState.sections = entry.sections.map(function (section) {
                        return {
                            title: section.title || '',
                            documents: (section.documents || []).slice(),
                        };
                    });
                } else {
                    ccDocSectionsState.sections = [{
                        title: 'Required Documents',
                        documents: (entry.documents || []).slice(),
                    }];
                }

                renderCcDocSections();
            }

            function collectCcDocSectionsPayload() {
                return ccDocSectionsState.sections
                    .map(function (section) {
                        const title = $.trim(section.title || '');
                        const documents = (section.documents || [])
                            .map(function (doc) { return $.trim(doc); })
                            .filter(function (doc, index, list) {
                                return doc !== '' && list.indexOf(doc) === index;
                            });

                        if (!title || !documents.length) {
                            return null;
                        }

                        return { title: title, documents: documents };
                    })
                    .filter(Boolean);
            }

            function getCcSelectedCountries() {
                return $('.cc-country-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();
            }

            function getCcSelectedCategories() {
                return $('.cc-category-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();
            }

            function comboKey(country, visaCategory) {
                return country + '||' + visaCategory;
            }

            function escapeHtml(value) {
                return $('<div>').text(value || '').html();
            }

            function refreshCcHeroDocCount() {
                $('#ccHeroDocCombosCount').text(ccDocumentListsState.items.length);
            }

            function refreshCcDocCombinationSelectors() {
                const countries = getCcSelectedCountries();
                const categories = getCcSelectedCategories();
                const countrySelect = $('#ccDocCountry');
                const categorySelect = $('#ccDocCategory');
                const currentCountry = countrySelect.val();
                const currentCategory = categorySelect.val();

                countrySelect.html('<option value="">Select country</option>');
                countries.forEach(function (country) {
                    countrySelect.append(`<option value="${escapeHtml(country)}">${escapeHtml(country)}</option>`);
                });

                categorySelect.html('<option value="">Select visa category</option>');
                categories.forEach(function (category) {
                    categorySelect.append(`<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`);
                });

                if (countries.includes(currentCountry)) {
                    countrySelect.val(currentCountry);
                }
                if (categories.includes(currentCategory)) {
                    categorySelect.val(currentCategory);
                }
            }

            function initCcPicker(config) {
                const $checkboxes = $(config.checkboxSelector);
                const $items = $(config.itemSelector);
                const $search = $(config.searchSelector);
                const $noResults = $(config.noResultsSelector);
                const $chips = $(config.chipsSelector);
                const $count = $(config.countSelector);
                const chipLimit = config.chipCollapseLimit || 8;
                const pageSize = config.pageSize || 50;
                const isLargeList = config.largeListThreshold && $items.length > config.largeListThreshold;
                let listView = isLargeList ? 'selected' : 'browse';
                let currentPage = 1;
                let chipsExpanded = false;

                function getSearchQuery() {
                    return $.trim($search.val()).toLowerCase();
                }

                function itemMatchesSearch($item, query) {
                    const label = String($item.data('label') || '').toLowerCase();
                    return query === '' || label.indexOf(query) !== -1;
                }

                function getEligibleItems() {
                    const query = getSearchQuery();
                    return $items.filter(function () {
                        const $item = $(this);
                        if (!itemMatchesSearch($item, query)) {
                            return false;
                        }
                        if (query !== '') {
                            return true;
                        }
                        if (!isLargeList || listView === 'browse') {
                            return true;
                        }
                        return $item.find('input[type="checkbox"]').is(':checked');
                    });
                }

                function updateListHint(eligibleCount) {
                    if (!config.listHintSelector) {
                        return;
                    }
                    const query = getSearchQuery();
                    const selectedCount = $checkboxes.filter(':checked').length;
                    let hint = '';

                    if (query !== '') {
                        hint = 'Showing ' + eligibleCount + ' match' + (eligibleCount === 1 ? '' : 'es') + ' for "' + $search.val() + '"';
                    } else if (isLargeList && listView === 'selected') {
                        hint = selectedCount + ' selected shown · switch to Browse all or search 200+ countries';
                    } else if (isLargeList) {
                        hint = 'Browsing ' + $items.length + ' countries · ' + pageSize + ' per page';
                    } else {
                        hint = eligibleCount + ' item' + (eligibleCount === 1 ? '' : 's') + ' available';
                    }

                    $(config.listHintSelector).text(hint);
                }

                function applyListVisibility() {
                    const query = getSearchQuery();
                    const eligible = getEligibleItems();
                    const totalPages = Math.max(1, Math.ceil(eligible.length / pageSize));

                    if (currentPage > totalPages) {
                        currentPage = totalPages;
                    }

                    $items.addClass('is-hidden');

                    if (query !== '' || !isLargeList || listView === 'browse') {
                        const start = (currentPage - 1) * pageSize;
                        eligible.slice(start, start + pageSize).removeClass('is-hidden');
                    } else {
                        eligible.removeClass('is-hidden');
                    }

                    if (config.paginationSelector) {
                        const showPagination = isLargeList && listView === 'browse' && query === '' && eligible.length > pageSize;
                        $(config.paginationSelector).toggleClass('d-none', !showPagination);
                        if (showPagination && config.pageInfoSelector) {
                            $(config.pageInfoSelector).text('Page ' + currentPage + ' of ' + totalPages);
                        }
                        if (config.pagePrevSelector) {
                            $(config.pagePrevSelector).prop('disabled', currentPage <= 1);
                        }
                        if (config.pageNextSelector) {
                            $(config.pageNextSelector).prop('disabled', currentPage >= totalPages);
                        }
                    }

                    $noResults.toggleClass('is-visible', eligible.length === 0);
                    updateListHint(eligible.length);
                }

                function renderChips() {
                    const selected = $checkboxes.filter(':checked').map(function () {
                        return $(this).val();
                    }).get();

                    $chips.empty().removeClass('is-expanded');

                    selected.forEach(function (value, index) {
                        const $chip = $('<span class="cc-chip"></span>')
                            .append($('<span></span>').text(value))
                            .append(
                                $('<button type="button" aria-label="Remove">&times;</button>').on('click', function () {
                                    $checkboxes.filter(function () {
                                        return $(this).val() === value;
                                    }).prop('checked', false).trigger('change');
                                })
                            );

                        if (!chipsExpanded && index >= chipLimit) {
                            $chip.addClass('is-collapsed-hidden');
                        }

                        $chips.append($chip);
                    });

                    if (selected.length > chipLimit) {
                        const hiddenCount = selected.length - chipLimit;
                        $('<button type="button" class="cc-chips-more-btn"></button>')
                            .text(chipsExpanded ? 'Show less' : ('+' + hiddenCount + ' more'))
                            .on('click', function () {
                                chipsExpanded = !chipsExpanded;
                                $chips.toggleClass('is-expanded', chipsExpanded);
                                renderChips();
                            })
                            .appendTo($chips);
                    }

                    const count = selected.length;
                    if ($count && $count.length) {
                        $count.text(count + ' selected');
                    }
                    if (config.heroCountSelector) {
                        $(config.heroCountSelector).text(count);
                    }
                }

                function setCheckedValues(values) {
                    const lookup = {};
                    (values || []).forEach(function (value) {
                        lookup[value] = true;
                    });
                    $checkboxes.each(function () {
                        $(this).prop('checked', !!lookup[$(this).val()]);
                    });
                    currentPage = 1;
                    applyListVisibility();
                    renderChips();
                    if (typeof config.onChange === 'function') {
                        config.onChange();
                    }
                }

                function notifyChange() {
                    applyListVisibility();
                    renderChips();
                    if (typeof config.onChange === 'function') {
                        config.onChange();
                    }
                }

                $search.on('input', function () {
                    currentPage = 1;
                    notifyChange();
                });

                $checkboxes.on('change', notifyChange);

                $(config.selectAllSelector).on('click', function () {
                    if (isLargeList && listView === 'browse' && getSearchQuery() === '') {
                        getEligibleItems().find('input[type="checkbox"]').prop('checked', true);
                    } else {
                        $checkboxes.prop('checked', true);
                    }
                    notifyChange();
                });

                $(config.unselectAllSelector).on('click', function () {
                    if (isLargeList && listView === 'browse' && getSearchQuery() === '') {
                        getEligibleItems().find('input[type="checkbox"]').prop('checked', false);
                    } else {
                        $checkboxes.prop('checked', false);
                    }
                    notifyChange();
                });

                $(config.useDefaultsSelector).on('click', function () {
                    setCheckedValues(config.defaults || []);
                });

                if (config.invertSelector) {
                    $(config.invertSelector).on('click', function () {
                        getEligibleItems().find('input[type="checkbox"]').each(function () {
                            $(this).prop('checked', !$(this).prop('checked'));
                        });
                        notifyChange();
                    });
                }

                if (config.selectVisibleSelector) {
                    $(config.selectVisibleSelector).on('click', function () {
                        $items.not('.is-hidden').find('input[type="checkbox"]').prop('checked', true);
                        notifyChange();
                    });
                }

                if (config.viewBtnSelector) {
                    $(config.viewBtnSelector).on('click', function () {
                        listView = $(this).data('view');
                        $(config.viewBtnSelector).removeClass('active');
                        $(this).addClass('active');
                        currentPage = 1;
                        notifyChange();
                    });
                }

                if (config.pagePrevSelector) {
                    $(config.pagePrevSelector).on('click', function () {
                        if (currentPage > 1) {
                            currentPage--;
                            applyListVisibility();
                        }
                    });
                }

                if (config.pageNextSelector) {
                    $(config.pageNextSelector).on('click', function () {
                        const totalPages = Math.max(1, Math.ceil(getEligibleItems().length / pageSize));
                        if (currentPage < totalPages) {
                            currentPage++;
                            applyListVisibility();
                        }
                    });
                }

                applyListVisibility();
                renderChips();

                return { setCheckedValues, renderChips };
            }

            const ccCountriesPicker = initCcPicker({
                checkboxSelector: '.cc-country-checkbox',
                itemSelector: '#ccCountriesList .cc-picker-item',
                searchSelector: '.cc-countries-search',
                noResultsSelector: '.cc-countries-no-results',
                chipsSelector: '#ccCountriesChips',
                countSelector: '#ccCountriesCount',
                heroCountSelector: '#ccHeroCountriesCount',
                selectAllSelector: '.cc-countries-select-all',
                unselectAllSelector: '.cc-countries-unselect-all',
                useDefaultsSelector: '.cc-countries-use-defaults',
                invertSelector: '.cc-countries-invert',
                selectVisibleSelector: '.cc-countries-select-visible',
                defaults: ccDefaults.countries,
                largeListThreshold: 50,
                pageSize: 50,
                chipCollapseLimit: 10,
                listHintSelector: '#ccCountriesListHint',
                paginationSelector: '#ccCountriesPagination',
                pageInfoSelector: '#ccCountriesPageInfo',
                pagePrevSelector: '.cc-countries-page-prev',
                pageNextSelector: '.cc-countries-page-next',
                viewBtnSelector: '.cc-countries-view-btn',
                onChange: refreshCcDocCombinationSelectors,
            });

            const ccCategoriesPicker = initCcPicker({
                checkboxSelector: '.cc-category-checkbox',
                itemSelector: '#ccCategoriesList .cc-picker-item',
                searchSelector: '.cc-categories-search',
                noResultsSelector: '.cc-categories-no-results',
                chipsSelector: '#ccCategoriesChips',
                countSelector: '#ccCategoriesCount',
                heroCountSelector: '#ccHeroCategoriesCount',
                selectAllSelector: '.cc-categories-select-all',
                unselectAllSelector: '.cc-categories-unselect-all',
                useDefaultsSelector: '.cc-categories-use-defaults',
                invertSelector: '.cc-categories-invert',
                selectVisibleSelector: '.cc-categories-select-visible',
                defaults: ccDefaults.visaCategories,
                chipCollapseLimit: 10,
                onChange: refreshCcDocCombinationSelectors,
            });

            const ccDocsPicker = initCcPicker({
                checkboxSelector: '.cc-doc-type-checkbox',
                itemSelector: '#ccDocTypesList .cc-doc-picker-item',
                searchSelector: '.cc-docs-search',
                noResultsSelector: '.cc-docs-no-results',
                chipsSelector: '#ccDocTypesChips',
                countSelector: null,
                selectAllSelector: '.cc-docs-select-all',
                unselectAllSelector: '.cc-docs-unselect-all',
                useDefaultsSelector: '.cc-docs-common',
                invertSelector: null,
                selectVisibleSelector: null,
                defaults: ccCommonDocuments,
                chipCollapseLimit: 6,
            });

            function renderCcDocumentLists() {
                const container = $('#ccDocumentListsContainer');
                container.empty();

                if (!ccDocumentListsState.items.length) {
                    $('#ccDocumentListsEmpty').show();
                    $('#ccDocComboCount').text('0 combinations');
                    refreshCcHeroDocCount();
                    return;
                }

                $('#ccDocumentListsEmpty').hide();
                $('#ccDocComboCount').text(ccDocumentListsState.items.length + ' combination' + (ccDocumentListsState.items.length === 1 ? '' : 's'));
                refreshCcHeroDocCount();

                ccDocumentListsState.items.forEach(function (entry, index) {
                    const badges = flattenEntryDocuments(entry).map(function (doc) {
                        return `<span class="cc-doc-badge">${escapeHtml(doc)}</span>`;
                    }).join('');
                    const sectionSummary = (entry.sections || []).map(function (section) {
                        return escapeHtml(section.title || 'Section');
                    }).join(', ');

                    container.append(`
                        <div class="cc-combo-card" data-index="${index}">
                            <div class="combo-heading">
                                <div class="combo-meta">
                                    ${escapeHtml(entry.country)}
                                    <small>${escapeHtml(entry.visa_category)}</small>
                                    ${sectionSummary ? `<small class="d-block text-muted">${sectionSummary}</small>` : ''}
                                </div>
                                <div class="d-flex gap-2 flex-shrink-0">
                                    <button type="button" class="btn btn-sm btn-outline-primary cc-edit-doc-combo" data-index="${index}">
                                        <i class="fa fa-pen"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger cc-delete-doc-combo" data-index="${index}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div>${badges}</div>
                        </div>
                    `);
                });
            }

            function clearCcDocBuilder() {
                ccDocumentListsState.editingKey = null;
                $('#ccDocCountry').val('');
                $('#ccDocCategory').val('');
                ccDocsPicker.setCheckedValues([]);
                ccDocSectionsState.sections = [];
                renderCcDocSections();
            }

            function setCcDocBuilder(entry) {
                $('#ccDocCountry').val(entry.country);
                $('#ccDocCategory').val(entry.visa_category);
                ccDocsPicker.setCheckedValues(flattenEntryDocuments(entry));
                loadCcDocSectionsFromEntry(entry);
            }

            function upsertCcDocumentCombination() {
                const country = $.trim($('#ccDocCountry').val());
                const visaCategory = $.trim($('#ccDocCategory').val());
                const sections = collectCcDocSectionsPayload();

                if (!country || !visaCategory) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Please select both a country and a visa category.'
                    });
                    return;
                }

                if (!sections.length) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Please add at least one section with a title and one document.'
                    });
                    return;
                }

                const documents = sections.reduce(function (all, section) {
                    return all.concat(section.documents || []);
                }, []);

                const key = comboKey(country, visaCategory);
                const existingIndex = ccDocumentListsState.items.findIndex(function (item) {
                    return comboKey(item.country, item.visa_category) === key;
                });

                const payload = {
                    country: country,
                    visa_category: visaCategory,
                    sections: sections,
                    documents: documents,
                };

                if (existingIndex >= 0) {
                    ccDocumentListsState.items[existingIndex] = payload;
                } else {
                    ccDocumentListsState.items.push(payload);
                }

                ccDocumentListsState.items.sort(function (a, b) {
                    return (a.country + a.visa_category).localeCompare(b.country + b.visa_category);
                });

                renderCcDocumentLists();
                clearCcDocBuilder();
            }

            function saveCcDocumentLists(showSuccessAlert) {
                return $.ajax({
                    url: "{{ route('save_cc_document_lists') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        document_lists: ccDocumentListsState.items
                    }
                }).done(function (response) {
                    ccDocumentListsState.items = response.document_lists || ccDocumentListsState.items;
                    renderCcDocumentLists();
                    if (showSuccessAlert !== false) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    }
                }).fail(function (xhr) {
                    const errorText = xhr?.responseJSON?.message || 'Failed to save document lists.';
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: errorText
                    });
                });
            }

            function generateCcDocumentCombinations() {
                const countries = getCcSelectedCountries();
                const categories = getCcSelectedCategories();

                if (!countries.length || !categories.length) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Select at least one country and one visa category first.'
                    });
                    return;
                }

                const pairCount = countries.length * categories.length;
                if (pairCount > 100) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Too many combinations',
                        html: 'This would create <strong>' + pairCount + '</strong> country × category pairs.<br>Narrow your selection first (recommended under 100 pairs).'
                    });
                    return;
                }

                let added = 0;
                countries.forEach(function (country) {
                    categories.forEach(function (visaCategory) {
                        const key = comboKey(country, visaCategory);
                        const exists = ccDocumentListsState.items.some(function (item) {
                            return comboKey(item.country, item.visa_category) === key;
                        });

                        if (!exists) {
                            ccDocumentListsState.items.push({
                                country: country,
                                visa_category: visaCategory,
                                sections: [{
                                    title: 'Required Documents',
                                    documents: ccCommonDocuments.slice(),
                                }],
                                documents: ccCommonDocuments.slice(),
                            });
                            added++;
                        }
                    });
                });

                ccDocumentListsState.items.sort(function (a, b) {
                    return (a.country + a.visa_category).localeCompare(b.country + b.visa_category);
                });

                renderCcDocumentLists();

                Swal.fire({
                    icon: added ? 'success' : 'info',
                    title: added ? 'Common lists generated' : 'Nothing to add',
                    text: added
                        ? ('Added ' + added + ' combination(s) with the common document set. Review them, then click Save Document Lists.')
                        : 'Every selected country × visa category pair already has a document list.'
                });
            }

            refreshCcDocCombinationSelectors();
            ensureCcDocSections();
            renderCcDocSections();
            renderCcDocumentLists();

            $('#add-cc-doc-section').on('click', function () {
                ccDocSectionsState.sections.push({ title: '', documents: [] });
                renderCcDocSections();
            });

            $(document).on('click', '.cc-remove-doc-section', function () {
                const sectionIndex = Number($(this).data('section-index'));
                ccDocSectionsState.sections.splice(sectionIndex, 1);
                ensureCcDocSections();
                renderCcDocSections();
            });

            $(document).on('click', '.cc-add-checked-to-section', function () {
                const sectionIndex = Number($(this).data('section-index'));
                const section = ccDocSectionsState.sections[sectionIndex];
                if (!section) {
                    return;
                }

                $('.cc-doc-type-checkbox:checked').each(function () {
                    const value = $.trim($(this).val());
                    if (value && section.documents.indexOf(value) === -1) {
                        section.documents.push(value);
                    }
                });

                renderCcDocSections();
            });

            $(document).on('click', '.cc-add-custom-doc', function () {
                const sectionIndex = Number($(this).data('section-index'));
                const section = ccDocSectionsState.sections[sectionIndex];
                const input = $('.cc-doc-custom-input[data-section-index="' + sectionIndex + '"]');
                const value = $.trim(input.val());

                if (!section || !value) {
                    return;
                }

                if (section.documents.indexOf(value) === -1) {
                    section.documents.push(value);
                }

                input.val('');
                renderCcDocSections();
            });

            $(document).on('keydown', '.cc-doc-custom-input', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    $('.cc-add-custom-doc[data-section-index="' + $(this).data('section-index') + '"]').trigger('click');
                }
            });

            $(document).on('click', '.cc-remove-section-doc', function () {
                const sectionIndex = Number($(this).data('section-index'));
                const docIndex = Number($(this).data('doc-index'));
                const section = ccDocSectionsState.sections[sectionIndex];

                if (!section) {
                    return;
                }

                section.documents.splice(docIndex, 1);
                renderCcDocSections();
            });

            $(document).on('input', '.cc-doc-section-title', function () {
                const sectionIndex = Number($(this).closest('.cc-doc-section-card').data('section-index'));
                const section = ccDocSectionsState.sections[sectionIndex];
                if (section) {
                    section.title = $(this).val();
                }
            });

            $('#add-cc-doc-combination').on('click', upsertCcDocumentCombination);
            $('#clear-cc-doc-builder').on('click', clearCcDocBuilder);

            $(document).on('click', '.cc-edit-doc-combo', function () {
                const index = Number($(this).data('index'));
                const entry = ccDocumentListsState.items[index];
                if (!entry) {
                    return;
                }
                ccDocumentListsState.editingKey = comboKey(entry.country, entry.visa_category);
                setCcDocBuilder(entry);
                $('html, body').animate({ scrollTop: $('.cc-doc-builder').offset().top - 120 }, 250);
            });

            $(document).on('click', '.cc-delete-doc-combo', function () {
                const index = Number($(this).data('index'));
                const entry = ccDocumentListsState.items[index];
                if (!entry) {
                    return;
                }

                Swal.fire({
                    title: 'Remove this combination?',
                    text: entry.country + ' · ' + entry.visa_category,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it'
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }
                    ccDocumentListsState.items.splice(index, 1);
                    renderCcDocumentLists();
                });
            });

            $('#save-cc-document-lists').on('click', function () {
                saveCcDocumentLists(true);
            });

            $('#generate-cc-doc-combinations').on('click', generateCcDocumentCombinations);

            $('#save-cc-settings').on('click', function () {
                const countries = $('.cc-country-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();
                const visaCategories = $('.cc-category-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();

                if (!countries.length || !visaCategories.length) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Please select at least one country and one visa category.'
                    });
                    return;
                }

                $.ajax({
                    url: "{{ route('save_cc_settings') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        countries: countries,
                        visa_categories: visaCategories
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                        $('#ccDefaultsNotice').addClass('d-none');
                        if (response.service_countries || response.service_names) {
                            updateServiceDropdowns(
                                response.service_countries || [],
                                response.service_names || [],
                                response.service_cc_preferences || null
                            );
                        }
                        $('#ccHeroCountriesCount').text((response.countries || countries).length);
                        $('#ccHeroCategoriesCount').text((response.visa_categories || visaCategories).length);
                    },
                    error: function (xhr) {
                        const errorText = ccSettingsErrorMessage(xhr, 'Failed to save Countries & Categories settings.');
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText
                        });
                    }
                });
            });

            $('#reset-cc-settings').on('click', function () {
                $.ajax({
                    url: "{{ route('save_cc_settings') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        reset_defaults: 1
                    },
                    success: function (response) {
                        ccCountriesPicker.setCheckedValues(response.countries || []);
                        ccCategoriesPicker.setCheckedValues(response.visa_categories || []);

                        ccDocumentListsState.items = response.document_lists || [];
                        refreshCcDocCombinationSelectors();
                        renderCcDocumentLists();
                        clearCcDocBuilder();
                        $('#ccDefaultsNotice').removeClass('d-none').html(
                            '<i class="fa fa-circle-info"></i><div><strong>Profile defaults restored.</strong> Pre-selected countries and visa categories are shown as checked checkboxes. Document lists were cleared.</div>'
                        );

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                        if (response.service_countries || response.service_names) {
                            updateServiceDropdowns(
                                response.service_countries || [],
                                response.service_names || [],
                                response.service_cc_preferences || null
                            );
                        }
                    },
                    error: function (xhr) {
                        const errorText = ccSettingsErrorMessage(xhr, 'Failed to reset Countries & Categories settings.');
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText
                        });
                    }
                });
            });

            /* ---------------- Dashboard preferences ---------------- */

            const dashChartModules = @json($dashboardChartModules);
            const dashChartAvailability = @json($dashboardChartAvailability ?? []);
            const dashHeaderSlots = {{ $dashboardHeaderSlots }};
            const dashChartSlots = {{ $dashboardChartSlots }};
            const dashActiveChartSlots = dashChartSlots;

            function dashGetActiveChartSlots() {
                return dashActiveChartSlots;
            }

            function dashApplyChartSlotVisibility() {
                $('#dashHeroChartsMax').text(dashActiveChartSlots);
                dashRefreshAllFilters();
            }

            /* A module+filter pair may only be charted once, so each slot's options
               exclude pairs already taken by the other slots. */
            function dashPairKey(module, filter) {
                return module + '|' + filter;
            }

            function dashFilterHasData(module, filter) {
                if (!module || !filter) return true;
                const key = dashPairKey(module, filter);
                if (!Object.prototype.hasOwnProperty.call(dashChartAvailability, key)) {
                    return true;
                }
                return !!dashChartAvailability[key];
            }

            function dashTakenPairs(exceptIndex) {
                const taken = [];
                const activeSlots = dashGetActiveChartSlots();
                for (let i = 0; i < activeSlots; i++) {
                    if (i === exceptIndex) continue;
                    const module = $('#dashChartModule' + i).val();
                    const filter = $('#dashChartFilter' + i).val();
                    if (module && filter) taken.push(dashPairKey(module, filter));
                }
                return taken;
            }

            function dashPopulateFilters(index, keepValue) {
                const $module = $('#dashChartModule' + index);
                const $filter = $('#dashChartFilter' + index);
                const module = $module.val();
                const desired = keepValue !== undefined ? keepValue : ($filter.attr('data-selected') || '');

                $filter.empty();

                if (!module || !dashChartModules[module]) {
                    $filter.append('<option value="">— Select Module first —</option>');
                    return;
                }

                const taken = dashTakenPairs(index);
                const filters = dashChartModules[module].filters || {};

                $filter.append('<option value="">— Select Filter —</option>');

                Object.keys(filters).forEach(function (key) {
                    if (taken.indexOf(dashPairKey(module, key)) !== -1) return;

                    const hasData = dashFilterHasData(module, key);
                    const selected = key === desired && hasData ? ' selected' : '';
                    const disabled = !hasData ? ' disabled' : '';
                    const suffix = !hasData ? ' (no data)' : '';

                    $filter.append(
                        '<option value="' + key + '"' + selected + disabled + '>' +
                        filters[key] + suffix +
                        '</option>'
                    );
                });

                /* If the previously saved filter has no data, clear the selection. */
                if (desired && !dashFilterHasData(module, desired)) {
                    $filter.val('');
                    $filter.attr('data-selected', '');
                }
            }

            /* Pass the live value (never undefined) so clearing a filter stays cleared —
               undefined would fall back to data-selected and silently restore it. */
            function dashRefreshAllFilters() {
                const activeSlots = dashGetActiveChartSlots();
                for (let i = 0; i < activeSlots; i++) {
                    const current = $('#dashChartFilter' + i).val();
                    dashPopulateFilters(i, current == null ? '' : current);
                }
                dashUpdateCounts();
            }

            /* Headers can't repeat either — grey out anything another slot already took. */
            function dashRefreshHeaderOptions() {
                const chosen = [];
                $('.dash-header-select').each(function () {
                    const val = $(this).val();
                    if (val) chosen.push(val);
                });

                $('.dash-header-select').each(function () {
                    const current = $(this).val();
                    $(this).find('option').each(function () {
                        const val = $(this).val();
                        if (!val) return;
                        $(this).prop('disabled', val !== current && chosen.indexOf(val) !== -1);
                    });
                });

                dashUpdateCounts();
            }

            function dashUpdateCounts() {
                let headers = 0;
                $('.dash-header-select').each(function () {
                    if ($(this).val()) headers++;
                });

                let charts = 0;
                const activeSlots = dashGetActiveChartSlots();
                for (let i = 0; i < activeSlots; i++) {
                    if ($('#dashChartModule' + i).val() && $('#dashChartFilter' + i).val()) charts++;
                }

                $('#dashHeroHeadersCount').text(headers);
                $('#dashHeroChartsCount').text(charts);
                $('#dashHeroChartsMax').text(activeSlots);
            }

            $(document).on('change', '.dash-chart-module', function () {
                const index = parseInt($(this).attr('data-slot-index'), 10);
                $('#dashChartFilter' + index).attr('data-selected', '');
                dashPopulateFilters(index, '');
                dashRefreshAllFilters();
            });

            $(document).on('change', '.dash-chart-filter', function () {
                $(this).attr('data-selected', $(this).val() || '');
                dashRefreshAllFilters();
            });

            $(document).on('change', '.dash-header-select', function () {
                dashRefreshHeaderOptions();
            });

            function dashCollectPayload() {
                const headers = [];
                $('.dash-header-select').each(function () {
                    headers.push($(this).val() || '');
                });

                const charts = [];
                const activeSlots = dashGetActiveChartSlots();
                for (let i = 0; i < activeSlots; i++) {
                    const module = $('#dashChartModule' + i).val();
                    const filter = $('#dashChartFilter' + i).val();
                    if (!module || !filter) continue;
                    charts.push({
                        module: module,
                        filter: filter,
                        duration: $('#dashChartDuration' + i).val() || 'since_inception',
                        chart_type: $('#dashChartType' + i).val() || 'doughnut'
                    });
                }

                return {
                    headers: headers,
                    charts: charts,
                    chart_count: activeSlots
                };
            }

            function dashApplyResponse(response) {
                const headers = response.headers || [];
                for (let i = 0; i < dashHeaderSlots; i++) {
                    $('#dashHeader' + i).val(headers[i] || '');
                }

                const charts = response.charts || [];
                for (let i = 0; i < dashChartSlots; i++) {
                    const slot = charts[i];
                    $('#dashChartModule' + i).val(slot ? slot.module : '');
                    $('#dashChartFilter' + i).attr('data-selected', slot ? slot.filter : '');
                    $('#dashChartDuration' + i).val(slot ? slot.duration : 'since_inception');
                    $('#dashChartType' + i).val(slot ? slot.chart_type : 'doughnut');
                    dashPopulateFilters(i, slot ? slot.filter : '');
                }

                dashApplyChartSlotVisibility();
                dashRefreshHeaderOptions();
                dashUpdateCounts();
            }

            $('#save-dashboard-settings').on('click', function () {
                const payload = dashCollectPayload();

                if (!payload.headers.filter(Boolean).length && !payload.charts.length) {
                    Swal.fire({
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        title: 'Oops!',
                        text: 'Select at least one header or one chart before saving.'
                    });
                    return;
                }

                $.ajax({
                    url: "{{ route('save_dashboard_settings') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        headers: payload.headers,
                        charts: payload.charts,
                        chart_count: payload.chart_count
                    },
                    success: function (response) {
                        dashApplyResponse(response);
                        $('#dashDefaultsNotice').addClass('d-none');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    },
                    error: function (xhr) {
                        const errorText = xhr?.responseJSON?.message || 'Failed to save dashboard preferences.';
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText
                        });
                    }
                });
            });

            $('#reset-dashboard-headers').on('click', function () {
                $.ajax({
                    url: "{{ route('save_dashboard_settings') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        reset_headers: 1
                    },
                    success: function (response) {
                        dashApplyResponse(response);
                        $('#dashDefaultsNotice').removeClass('d-none').html(
                            '<i class="fa fa-circle-info"></i><div><strong>Header preferences restored to defaults.</strong> Adjust the dropdowns and save to set your own layout again.</div>'
                        );
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    },
                    error: function (xhr) {
                        const errorText = xhr?.responseJSON?.message || 'Failed to reset header preferences.';
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText
                        });
                    }
                });
            });

            $('#reset-dashboard-charts').on('click', function () {
                $.ajax({
                    url: "{{ route('save_dashboard_settings') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        reset_charts: 1
                    },
                    success: function (response) {
                        dashApplyResponse(response);
                        $('#dashDefaultsNotice').removeClass('d-none').html(
                            '<i class="fa fa-circle-info"></i><div><strong>Chart preferences restored to defaults.</strong> Adjust the dropdowns and save to set your own layout again.</div>'
                        );
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    },
                    error: function (xhr) {
                        const errorText = xhr?.responseJSON?.message || 'Failed to reset chart preferences.';
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText
                        });
                    }
                });
            });

            for (let i = 0; i < dashChartSlots; i++) {
                dashPopulateFilters(i);
            }
            dashApplyChartSlotVisibility();
            dashRefreshHeaderOptions();
            dashUpdateCounts();

            function enquiryFormDefaultCheckbox() {
                return $('.enquiry-form-section-checkbox[data-section-key="default"]');
            }

            function enquiryFormOtherCheckboxes() {
                return $('.enquiry-form-section-checkbox').not('[data-section-key="default"]');
            }

            function enquiryFormAllCheckboxesChecked() {
                let allChecked = true;
                $('.enquiry-form-section-checkbox').each(function () {
                    if (!$(this).is(':checked')) {
                        allChecked = false;
                    }
                });
                return allChecked;
            }

            function enquiryFormSyncDefaultCheckbox() {
                enquiryFormDefaultCheckbox().prop('checked', enquiryFormAllCheckboxesChecked());
            }

            function enquiryFormCollectSections() {
                const sections = {};
                $('.enquiry-form-section-checkbox').each(function () {
                    const key = $(this).data('section-key');
                    if (!key) {
                        return;
                    }
                    sections[key] = $(this).is(':checked') ? 1 : 0;
                });
                return sections;
            }

            function enquiryFormApplySections(sections) {
                if (!sections) {
                    return;
                }
                $('.enquiry-form-section-checkbox').each(function () {
                    const key = $(this).data('section-key');
                    if (!key) {
                        return;
                    }
                    $(this).prop('checked', !!sections[key]);
                });
                enquiryFormSyncDefaultCheckbox();
            }

            enquiryFormDefaultCheckbox().on('change', function () {
                if ($(this).is(':checked')) {
                    enquiryFormOtherCheckboxes().prop('checked', true);
                    return;
                }
                enquiryFormOtherCheckboxes().prop('checked', false);
            });

            enquiryFormOtherCheckboxes().on('change', function () {
                enquiryFormSyncDefaultCheckbox();
            });

            function enquiryFormAjaxErrorMessage(xhr, fallback) {
                const response = xhr?.responseJSON;
                if (response?.message) {
                    return response.message;
                }
                if (response?.errors) {
                    return Object.values(response.errors).flat().join(' ');
                }
                return fallback;
            }

            enquiryFormSyncDefaultCheckbox();

            $('#save-enquiry-form-settings').on('click', function () {
                $.ajax({
                    url: "{{ route('save_enquiry_form_settings') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        sections: enquiryFormCollectSections()
                    },
                    success: function (response) {
                        enquiryFormApplySections(response.sections || {});
                        if (enquiryFormAllCheckboxesChecked()) {
                            $('#enquiryFormDefaultsNotice').removeClass('d-none').html(
                                '<i class="fa fa-circle-info"></i><div><strong>All sections are enabled by default.</strong> Adjust the checkboxes below and click <strong>Save Enquiry Form Settings</strong> to customize your form.</div>'
                            );
                        } else {
                            $('#enquiryFormDefaultsNotice').addClass('d-none');
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    },
                    error: function (xhr) {
                        const errorText = enquiryFormAjaxErrorMessage(xhr, 'Failed to save enquiry form settings.');
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText
                        });
                    }
                });
            });

            $('#reset-enquiry-form-settings').on('click', function () {
                $.ajax({
                    url: "{{ route('save_enquiry_form_settings') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        reset_defaults: 1
                    },
                    success: function (response) {
                        enquiryFormApplySections(response.sections || {});
                        $('#enquiryFormDefaultsNotice').removeClass('d-none').html(
                            '<i class="fa fa-circle-info"></i><div><strong>All enquiry form sections restored.</strong> Adjust the checkboxes and save to customize again.</div>'
                        );
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    },
                    error: function (xhr) {
                        const errorText = enquiryFormAjaxErrorMessage(xhr, 'Failed to reset enquiry form settings.');
                        Swal.fire({
                            icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                            title: 'Oops!',
                            text: errorText
                        });
                    }
                });
            });
            @endif
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
    @if (session()->has('setting_saved'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Invoice settings updated successfully.'
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
    @if (session()->has('application_updated'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Service updated successfully.'
            })
        </script>
    @endif
    @if (session()->has('success_services'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success_services') }}'
            })
        </script>
    @endif
    @include('web.partials.settings_tab_row_lines_script')
@endsection()
