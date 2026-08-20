@extends('web.layout.main')

@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="invoice-form-card">
                <div class="invoice-form-header">
                    <div>
                        <h3 class="text-primary">Edit Invoice (AP)</h3>
                        <p class="text-muted mb-0">Update payment made invoice details</p>
                    </div>
                    <span class="invoice-ref-badge">Invoice #{{ $invoice->invoice_no }}</span>
                </div>

                @include('web.partials.invoice_audit_bar')

                <form id="registration_form" class="invoice-edit-form" method="POST" enctype="multipart/form-data"
                    action="{{ route('update_invoice_ap', $invoice->id) }}"
                    onsubmit="document.getElementById('invoice_submit').setAttribute('disabled','true');">
                    @csrf
                    <input type="hidden" name="local_time" class="localtime" />
                    <div class="row">
                        <div class="col-md-4 p-1">
                            <label>Invoice ID<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="invoice_vendor_id" type="text" minlength="2" maxlength="100" required
                                class="form-control @error('invoice_vendor_id') is-invalid @enderror"
                                value="{{ old('invoice_vendor_id', $invoice->invoice_no) }}" placeholder="Invoice ID">
                            @error('invoice_vendor_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Vendor Name<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="vendor_name" type="text" minlength="2" maxlength="150" required
                                class="form-control @error('vendor_name') is-invalid @enderror"
                                value="{{ old('vendor_name', $invoice->to_name) }}" placeholder="Vendor Name">
                            @error('vendor_name')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        @include('web.partials.invoice_service_rows', [
                            'showApplication' => false,
                            'invoice' => $invoice,
                            'detailLabel' => 'Product/Service Taken',
                            'detailPlaceholder' => 'Product/Service Taken',
                            'amountLabel' => 'Amount',
                            'amountPlaceholder' => 'Amount',
                        ])

                        <div class="col-md-4 p-1">
                            <label>Subtotal<span class="text-danger required-star">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="amount" required type="number" min="0" step="0.01" readonly
                                class="form-control invoice-subtotal-readonly @error('amount') is-invalid @enderror" id="invoice_subtotal"
                                value="{{ old('amount', $invoice->amount) }}" placeholder="Subtotal">
                            @error('amount')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Discount (%)<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="discount" id="discount_percent" type="number" min="0" max="100" step="0.01" required
                                class="form-control @error('discount') is-invalid @enderror"
                                value="{{ old('discount', $invoice->discount) }}" placeholder="Discount (%)">
                            @error('discount')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Tax (%)<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="tax" id="tax_percent" type="number" min="0" max="100" step="0.01" required
                                class="form-control @error('tax') is-invalid @enderror"
                                value="{{ old('tax', $invoice->tax) }}" placeholder="Tax (%)">
                            @error('tax')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Total To Pay<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="total_to_pay" id="total_to_pay" type="number" min="0" step="0.01" required
                                class="form-control @error('total_to_pay') is-invalid @enderror"
                                value="{{ old('total_to_pay', $invoice->total) }}" placeholder="Total To Pay">
                            @error('total_to_pay')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Upload Invoice (PDF)</label>
                        </div>
                        <div class="col-md-8 p-1">
                            <div class="invoice-upload-panel">
                                @if(!empty($invoice->uploaded_invoice))
                                    <div class="invoice-upload-current">
                                        <div class="invoice-upload-current-icon">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div class="invoice-upload-current-body">
                                            <span class="invoice-upload-current-label">Current invoice file</span>
                                            <a href="{{ asset('web_assets/users/' . $invoice->uploaded_invoice) }}" target="_blank" rel="noopener noreferrer" class="invoice-pdf-view-btn">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open PDF
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <label class="invoice-upload-replace-label">Upload replacement (optional)</label>
                                <input name="upload_invoice" type="file" accept="application/pdf,.pdf"
                                    class="form-control invoice-file-input @error('upload_invoice') is-invalid @enderror">
                                <small class="invoice-upload-hint">Leave blank to keep the existing file.</small>
                                @error('upload_invoice')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

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

                        @include('web.partials.invoice_note_form_field', [
                            'invoiceNote' => $invoiceNote ?? '',
                            'isLocked' => $isLocked ?? true,
                        ])

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
                                <a href="{{ route('view_invoice', $invoice->id) }}" class="invoice-btn invoice-btn-cancel">
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
            function updateInvoiceSubtotal() {
                let subtotal = 0;
                $('.invoice-service-amount').each(function () {
                    subtotal += parseFloat($(this).val()) || 0;
                });
                $('#invoice_subtotal').val(subtotal.toFixed(2));
                calculateTotalToPay();
            }

            function calculateTotalToPay() {
                const subtotal = parseFloat($("#invoice_subtotal").val()) || 0;
                const discountPercent = parseFloat($("#discount_percent").val()) || 0;
                const taxPercent = parseFloat($("#tax_percent").val()) || 0;
                const discountedSubtotal = subtotal - (subtotal * (discountPercent / 100));
                const calculatedTotal = discountedSubtotal + (discountedSubtotal * (taxPercent / 100));
                $("#total_to_pay").val(calculatedTotal.toFixed(2));
            }

            initInvoiceServiceRows({
                showApplication: false,
                onAmountChange: updateInvoiceSubtotal
            });

            $("#discount_percent, #tax_percent").on("input", calculateTotalToPay);
            updateInvoiceSubtotal();
        });
    </script>
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Could not save invoice',
                html: {!! json_encode(implode('<br>', $errors->all())) !!}
            });
        </script>
    @endif
@endsection
