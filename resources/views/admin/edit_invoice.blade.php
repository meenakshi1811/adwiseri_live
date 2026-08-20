@extends('admin.layout.main')

@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="invoice-form-card">
                <div class="invoice-form-header">
                    <div>
                        <h3 class="text-primary">Edit Invoice</h3>
                        <p class="text-muted mb-0">Update subscriber invoice details</p>
                    </div>
                    <span class="invoice-ref-badge">Invoice #{{ $invoice->invoice_no }}</span>
                </div>

                @include('web.partials.invoice_audit_bar')

                <form id="registration_form" class="invoice-edit-form" method="POST"
                    action="{{ route('admin_update_invoice', $invoice->id) }}"
                    onsubmit="document.getElementById('invoice_submit').setAttribute('disabled','true');">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 p-1">
                            <label>Subscriber<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="subscriber" required class="form-control form-select @error('subscriber') is-invalid @enderror">
                                <option value="">Select Subscriber</option>
                                @foreach ($subscribers as $subs)
                                    <option value="{{ $subs->id }}" {{ old('subscriber', $invoice->subscriber_id) == $subs->id ? 'selected' : '' }}>
                                        {{ $subs->name . '(' . $subs->id . ')' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subscriber')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        @include('web.partials.invoice_service_rows', [
                            'showApplication' => false,
                            'invoice' => $invoice,
                            'detailLabel' => 'Service Description',
                            'detailPlaceholder' => 'Service Description',
                            'amountLabel' => 'Amount',
                            'amountPlaceholder' => 'Amount',
                        ])

                        @include('web.partials.invoice_note_form_field', [
                            'invoiceNote' => $invoiceNote ?? '',
                            'isLocked' => $isLocked ?? true,
                        ])

                        <div class="col-md-4 p-1">
                            <label>Invoice Status<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="status" class="form-control form-select @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                @php
                                $statusLabels = ['PartiallyPaid' => 'Partially Paid', 'UnPaid' => 'Unpaid', 'Paid' => 'Paid', 'Cancelled' => 'Cancelled'];
                                @endphp
                                @foreach ($statusLabels as $statusOption => $statusLabel)
                                    <option value="{{ $statusOption }}" {{ old('status', $invoice->status) == $statusOption ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Payment Due Date<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="due_date" type="text" required
                                class="form-control datepicker @error('due_date') is-invalid @enderror"
                                value="{{ old('due_date', $invoice->due_date ? date('d-m-Y', strtotime($invoice->due_date)) : date('d-m-Y')) }}"
                                placeholder="dd-mm-yyyy">
                            @error('due_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-4 p-1"></div>
                        <div class="col-md-8 p-1">
                            <div class="invoice-form-actions">
                                <button type="submit" id="invoice_submit" class="invoice-btn invoice-btn-primary">
                                    <i class="fa-solid fa-check"></i> Save Changes
                                </button>
                                <a href="{{ route('invoice_detail', $invoice->id) }}" class="invoice-btn invoice-btn-cancel">
                                    <i class="fa-solid fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            flatpickr(".datepicker", {
                dateFormat: "d-m-Y",
                defaultDate: document.querySelector('input[name="due_date"]').value || "today",
                allowInput: true,
                clickOpens: true
            });
        });
        $(document).ready(function () {
            initInvoiceServiceRows({
                showApplication: false,
                subscriberSelector: '#subscriber'
            });
        });
    </script>
@endsection
