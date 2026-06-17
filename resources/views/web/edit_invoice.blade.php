@extends('web.layout.main')

@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="invoice-form-card">
                <div class="invoice-form-header">
                    <div>
                        <h3 class="text-primary">Edit Invoice (AR)</h3>
                        <p class="text-muted mb-0">Update payment received invoice details</p>
                    </div>
                    <span class="invoice-ref-badge">Invoice #{{ $invoice->invoice_no }}</span>
                </div>

                @include('web.partials.invoice_audit_bar')

                <form id="registration_form" class="invoice-edit-form" method="POST"
                    action="{{ route('update_invoice', $invoice->id) }}"
                    onsubmit="document.getElementById('invoice_submit').setAttribute('disabled','true');">
                    @csrf
                    <input type="hidden" name="local_time" class="localtime" />
                    <div class="row">
                        <div class="col-md-4 p-1">
                            <label>Client Name<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="client" id="client_id" required
                                class="form-control form-select @error('client') is-invalid @enderror">
                                <option value="">Select client</option>
                                @foreach ($clients as $clint)
                                    <option {{ (old('client', $selectedClientId) == $clint->id) ? 'selected' : '' }}
                                        value="{{ $clint->id }}">{{ $clint->name . '(' . $clint->id . ')' }}</option>
                                @endforeach
                            </select>
                            @error('client')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Service Description<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="detail" type="text" minlength="3" maxlength="200"
                                class="form-control @error('detail') is-invalid @enderror" id="service_description"
                                value="{{ old('detail', $invoice->detail) }}" required placeholder="Service Description">
                            @error('detail')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Amount To Pay<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="amount" required type="number" min="0" step="0.01"
                                class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount', $invoice->amount) }}" placeholder="Amount">
                            @error('amount')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Invoice Status<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="status" class="form-control form-select @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                @foreach (['PartiallyPaid', 'UnPaid', 'Paid', 'Cancelled'] as $statusOption)
                                    <option value="{{ $statusOption }}" {{ old('status', $invoice->status) == $statusOption ? 'selected' : '' }}>
                                        {{ $statusOption }}
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
                                placeholder="dd-mm-yyyy" autocomplete="due_date">
                            @error('due_date')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
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
    </script>
@endsection
