@extends('web.layout.main')

@section('main-section')
    <div class="col-lg-10 column-client">
        <div class="client-dashboard">
            <div class="invoice-form-card">
                <div class="invoice-form-header">
                    <div>
                        <h3 class="text-primary">Edit Invoice (AR)</h3>
                        <p class="text-muted mb-0">Update payment received invoice details. Discount, tax, and service fees are applied from Invoice Settings.</p>
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

                        @php
                            $serviceRow = app(\App\Services\InvoiceItemService::class)->rowsForForm($invoice)[0] ?? [
                                'application_id' => null,
                                'detail' => '',
                                'amount' => '',
                            ];
                        @endphp

                        <div class="col-md-4 p-1">
                            <label>Service Offered (Application/Other)<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="application_id" id="application_id"
                                class="form-control form-select @error('application_id') is-invalid @enderror" required>
                                <option value="">Select Service Offered (Application/Other)</option>
                                @if (!empty($serviceRow['application_id']))
                                    <option value="{{ $serviceRow['application_id'] }}" selected>{{ $serviceRow['detail'] }}</option>
                                @endif
                            </select>
                            @error('application_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Service Description<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="detail" type="text" minlength="2" maxlength="200"
                                class="form-control @error('detail') is-invalid @enderror" id="service_description"
                                value="{{ old('detail', $serviceRow['detail']) }}" required placeholder="Service Description"
                                autocomplete="detail">
                            @error('detail')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>Amount To Pay<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="amount" type="number" min="0" step="0.01" required
                                class="form-control @error('amount') is-invalid @enderror" id="invoice_amount"
                                value="{{ old('amount', $serviceRow['amount']) }}" placeholder="Amount" autocomplete="amount">
                            @error('amount')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="col-md-4 p-1">
                            <label>{{ $taxLabel ?? 'Tax' }} (%)<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <input name="tax" type="number" min="0" max="100" step="0.01" required
                                class="form-control @error('tax') is-invalid @enderror"
                                value="{{ old('tax', $invoice->tax ?? 0) }}" placeholder="Tax (%)">
                            @error('tax')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

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
                                placeholder="dd-mm-yyyy" autocomplete="due_date">
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
            var selectedApplicationId = @json(old('application_id', $serviceRow['application_id'] ?? null));

            function lookupInvoiceServiceFee(serviceName, country) {
                const name = (serviceName || '').toString().trim();
                if (!name) {
                    return;
                }

                $.ajax({
                    url: "{{ route('get_service_fee') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        application_type: name,
                        visa_country: (country || '').toString().trim()
                    },
                    success: function (response) {
                        if (response.fee !== null && response.fee !== undefined && response.fee !== '') {
                            $("#invoice_amount").val(response.fee);
                        }
                    }
                });
            }

            function applySelectedService(option) {
                const service = option.data('name');
                const serviceType = option.data('type');
                const serviceCountry = option.data('country');
                const fee = option.data('fee');

                if (option.val() === 'Other') {
                    $("#service_description").val('');
                    $("#invoice_amount").val('');
                    return;
                }

                if (service) {
                    $("#service_description").val(service);
                }

                if (fee !== undefined && fee !== null && fee !== '') {
                    $("#invoice_amount").val(fee);
                } else if (serviceType || service) {
                    lookupInvoiceServiceFee(serviceType || service, serviceCountry || '');
                }
            }

            $("#client_id").change(function () {
                var id = $(this).val();
                $.ajax({
                    url: "{{ route('get_application') }}",
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        id: id,
                        comm: "invoice",
                        ignore_invoice_id: {{ (int) $invoice->id }},
                    },
                    cache: false,
                    success: function (data) {
                        $("#application_id").html(data);
                        if (selectedApplicationId) {
                            $("#application_id").val(selectedApplicationId);
                        }
                    }
                });
            });

            $("#application_id").change(function () {
                applySelectedService($(this).find('option:selected'));
            });

            $("#service_description").on('change blur', function () {
                const detail = ($(this).val() || '').toString().trim();
                if (!detail) {
                    return;
                }

                const selectedOption = $("#application_id").find('option:selected');
                lookupInvoiceServiceFee(detail, selectedOption.data('country') || '');
            });

            @if(old('client', $selectedClientId))
            $("#client_id").trigger('change');
            @endif
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
