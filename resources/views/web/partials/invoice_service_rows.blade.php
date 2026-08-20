@php
    $serviceRows = app(\App\Services\InvoiceItemService::class)->rowsForForm($invoice ?? null);
    $showApplication = $showApplication ?? true;
    $detailLabel = $detailLabel ?? 'Service Description';
    $amountLabel = $amountLabel ?? 'Amount To Pay';
    $detailPlaceholder = $detailPlaceholder ?? 'Service Description';
    $amountPlaceholder = $amountPlaceholder ?? 'Amount';
@endphp

<div class="col-md-4 p-1 invoice-services-label-col">
    <label class="mb-0">Services<span class="text-danger required-star">*</span></label>
</div>

<div class="col-md-8 p-1">
    <div class="invoice-services-panel">
        <div class="invoice-services-toolbar">
            <div class="invoice-services-intro">
                <span class="invoice-services-heading">Service line items</span>
                <p class="invoice-services-hint mb-0">
                    @if ($showApplication)
                        Add each application or service to include on this invoice.
                    @else
                        Add each product or service included on this invoice.
                    @endif
                </p>
            </div>
            <button type="button" class="invoice-service-add-btn add-invoice-service" title="Add another service">
                <i class="fa-solid fa-plus"></i>
                <span>Add Service</span>
            </button>
        </div>

        <div class="invoice-services-grid {{ $showApplication ? 'has-application' : 'no-application' }}">
            <div class="invoice-services-head row g-2 gx-2">
                @if ($showApplication)
                    <div class="col-md-5 col-head">Application / Service Type</div>
                    <div class="col-md-4 col-head">{{ $detailLabel }}</div>
                    <div class="col-md-2 col-head text-end">{{ $amountLabel }}</div>
                @else
                    <div class="col-md-8 col-head">{{ $detailLabel }}</div>
                    <div class="col-md-3 col-head text-end">{{ $amountLabel }}</div>
                @endif
                <div class="col-md-1 col-head text-center">Action</div>
            </div>

            <div id="invoice_services" class="invoice-services-body">
                @foreach ($serviceRows as $idx => $row)
                    <div class="invoice-service-row row g-2 gx-2 align-items-start" data-row-index="{{ $idx }}">
                        @if ($showApplication)
                            <div class="col-md-5">
                                <select name="application_id[]"
                                    class="form-control form-select invoice-application-select @error('application_id.' . $idx) is-invalid @enderror"
                                    {{ $idx === 0 ? 'required' : '' }}>
                                    <option value="">Select application / service type</option>
                                    @if (!empty($row['application_id']))
                                        <option value="{{ $row['application_id'] }}" selected>{{ $row['detail'] }}</option>
                                    @endif
                                </select>
                                @error('application_id.' . $idx)
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <input name="detail[]" type="text" minlength="2" maxlength="200"
                                    class="form-control invoice-service-detail @error('detail.' . $idx) is-invalid @enderror"
                                    value="{{ $row['detail'] }}" required placeholder="{{ $detailPlaceholder }}" autocomplete="off">
                                @error('detail.' . $idx)
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <input name="amount[]" type="number" min="0" step="0.01"
                                    class="form-control invoice-service-amount text-end @error('amount.' . $idx) is-invalid @enderror"
                                    value="{{ $row['amount'] }}" required placeholder="{{ $amountPlaceholder }}" autocomplete="off">
                                @error('amount.' . $idx)
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @else
                            <div class="col-md-8">
                                <input name="detail[]" type="text" minlength="2" maxlength="200"
                                    class="form-control invoice-service-detail @error('detail.' . $idx) is-invalid @enderror"
                                    value="{{ $row['detail'] }}" required placeholder="{{ $detailPlaceholder }}" autocomplete="off">
                                @error('detail.' . $idx)
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <input name="amount[]" type="number" min="0" step="0.01"
                                    class="form-control invoice-service-amount text-end @error('amount.' . $idx) is-invalid @enderror"
                                    value="{{ $row['amount'] }}" required placeholder="{{ $amountPlaceholder }}" autocomplete="off">
                                @error('amount.' . $idx)
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        @endif
                        <div class="col-md-1 d-flex justify-content-center">
                            <button type="button"
                                class="invoice-service-remove-btn remove-invoice-service"
                                title="Remove service"
                                @if(count($serviceRows) <= 1) style="visibility:hidden;" @endif>
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@once
    @push('css')
    <style>
        .invoice-form-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        }

        .invoice-edit-form {
            padding: 0;
            border: none;
            box-shadow: none;
            background: transparent;
        }

        .invoice-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #edf2f7;
        }

        .invoice-form-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .invoice-form-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .invoice-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .invoice-btn-primary {
            background: #695EEE;
            color: #fff;
            border-color: #695EEE;
        }

        .invoice-btn-primary:hover {
            background: #695EEE;
            border-color: #695EEE;
            color: #fff;
        }

        .required-star {
            font-size: 1rem;
            margin-left: 2px;
        }

        .invoice-services-label-col {
            padding-top: 1.15rem !important;
        }

        .invoice-services-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
        }

        .invoice-services-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-services-heading {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .invoice-services-hint {
            font-size: 12px;
            color: #64748b;
            line-height: 1.45;
        }

        .invoice-service-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 14px;
            border: 1px solid #695EEE;
            border-radius: 8px;
            background: #fff;
            color: #695EEE;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .invoice-service-add-btn:hover {
            background: #eef4ff;
            color: #695EEE;
            border-color: #695EEE;
        }

        .invoice-services-head {
            margin-bottom: 8px;
        }

        .invoice-services-head .col-head {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            padding: 0 6px;
        }

        .invoice-services-body .invoice-service-row {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 8px;
            margin-bottom: 8px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .invoice-services-body .invoice-service-row:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .invoice-services-body .invoice-service-row:last-child {
            margin-bottom: 0;
        }

        .invoice-services-body .form-control,
        .invoice-services-body .form-select {
            min-height: 40px;
            border-color: #d0d7de;
            border-radius: 8px;
            font-size: 14px;
        }

        .invoice-services-body .form-control:focus,
        .invoice-services-body .form-select:focus {
            border-color: #695EEE;
            box-shadow: 0 0 0 3px rgba(0, 97, 242, 0.12);
        }

        .invoice-service-remove-btn {
            width: 38px;
            height: 38px;
            border: 1px solid #fecaca;
            border-radius: 8px;
            background: #fff5f5;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .invoice-service-remove-btn:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        .invoice-subtotal-readonly {
            background: #f8fafc;
            color: #0f172a;
            font-weight: 600;
            border-color: #dbe3ee;
        }

        @media (max-width: 767.98px) {
            .invoice-services-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .invoice-service-add-btn {
                justify-content: center;
                width: 100%;
            }

            .invoice-services-head {
                display: none;
            }

            .invoice-services-body .invoice-service-row {
                padding: 12px;
            }

            .invoice-services-body .invoice-service-row > [class*="col-"] {
                margin-bottom: 8px;
            }

            .invoice-services-body .invoice-service-row > [class*="col-"]:last-child {
                margin-bottom: 0;
            }

            .invoice-services-label-col {
                padding-top: 0.5rem !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        (function ($) {
            const defaultGetApplicationUrl = @json(route('get_application'));
            const defaultGetServiceFeeUrl = @json(route('get_service_fee'));
            const defaultCsrfToken = @json(csrf_token());

            let invoiceApplicationOptionsHtml = '<option value="">Select application / service type</option>';
            const showApplication = {{ $showApplication ? 'true' : 'false' }};
            const detailPlaceholder = @json($detailPlaceholder);
            const amountPlaceholder = @json($amountPlaceholder);

            function resolveSubscriberId(options) {
                const selector = (options && options.subscriberSelector) ? options.subscriberSelector : '#subscriber';
                const $subscriber = $(selector);

                if ($subscriber.length && $subscriber.val()) {
                    return $subscriber.val();
                }

                return '';
            }

            function refreshRemoveButtons() {
                const $rows = $('#invoice_services .invoice-service-row');
                const hideRemove = $rows.length <= 1;
                $rows.find('.remove-invoice-service').css('visibility', hideRemove ? 'hidden' : 'visible');
            }

            function populateApplicationSelects(html, selectedValues) {
                $('#invoice_services .invoice-application-select').each(function (index) {
                    const current = selectedValues && selectedValues[index] !== undefined
                        ? selectedValues[index]
                        : $(this).val();
                    $(this).html(html);
                    if (current) {
                        $(this).val(current);
                    }
                });
            }

            function lookupServiceFee(serviceName, $row, country, options) {
                const name = (serviceName || '').toString().trim();
                if (!name || !window.invoiceServiceFeeUrl) {
                    return;
                }

                $.ajax({
                    url: window.invoiceServiceFeeUrl,
                    method: 'POST',
                    data: {
                        _token: window.invoiceServiceFeeToken || '',
                        application_type: name,
                        visa_country: (country || '').toString().trim(),
                        subscriber_id: resolveSubscriberId(options)
                    },
                    success: function (response) {
                        if (response.fee !== null && response.fee !== undefined && response.fee !== '') {
                            $row.find('.invoice-service-amount').val(response.fee).trigger('input');
                        }
                    }
                });
            }

            function bindServiceDetailLookup($row, options) {
                $row.find('.invoice-service-detail').off('change.invoiceServiceFee blur.invoiceServiceFee')
                    .on('change.invoiceServiceFee blur.invoiceServiceFee', function () {
                        const detail = ($(this).val() || '').toString().trim();
                        const $parent = $(this).closest('.invoice-service-row');
                        if (!detail) {
                            return;
                        }
                        const selectedOption = $parent.find('.invoice-application-select option:selected');
                        const serviceCountry = selectedOption.data('country') || '';
                        lookupServiceFee(detail, $parent, serviceCountry, options);
                    });
            }

            function bindApplicationChange($row, options) {
                $row.find('.invoice-application-select').off('change.invoiceService').on('change.invoiceService', function () {
                    const option = $(this).find('option:selected');
                    const service = option.data('name');
                    const serviceType = option.data('type');
                    const serviceCountry = option.data('country');
                    const fee = option.data('fee');
                    const $parent = $(this).closest('.invoice-service-row');

                    if (option.val() === 'Other') {
                        $parent.find('.invoice-service-detail').val('');
                        $parent.find('.invoice-service-amount').val('').trigger('input');
                        return;
                    }

                    $parent.find('.invoice-service-detail').val(service || '');
                    if (fee !== undefined && fee !== null && fee !== '') {
                        $parent.find('.invoice-service-amount').val(fee).trigger('input');
                    } else if (serviceType || service) {
                        lookupServiceFee(serviceType || service, $parent, serviceCountry || '', options);
                    }
                });
            }

            function bindAllApplicationChanges(options) {
                $('#invoice_services .invoice-service-row').each(function () {
                    const $row = $(this);
                    bindApplicationChange($row, options);
                    bindServiceDetailLookup($row, options);
                });
            }

            function buildServiceRowHtml() {
                let rowHtml = '<div class="invoice-service-row row g-2 gx-2 align-items-start">';

                if (showApplication) {
                    rowHtml += '<div class="col-md-5"><select name="application_id[]" class="form-control form-select invoice-application-select"><option value="">Select application / service type</option></select></div>';
                    rowHtml += '<div class="col-md-4"><input name="detail[]" type="text" minlength="2" maxlength="200" class="form-control invoice-service-detail" required placeholder="' + detailPlaceholder + '" autocomplete="off"></div>';
                    rowHtml += '<div class="col-md-2"><input name="amount[]" type="number" min="0" step="0.01" class="form-control invoice-service-amount text-end" required placeholder="' + amountPlaceholder + '" autocomplete="off"></div>';
                } else {
                    rowHtml += '<div class="col-md-8"><input name="detail[]" type="text" minlength="2" maxlength="200" class="form-control invoice-service-detail" required placeholder="' + detailPlaceholder + '" autocomplete="off"></div>';
                    rowHtml += '<div class="col-md-3"><input name="amount[]" type="number" min="0" step="0.01" class="form-control invoice-service-amount text-end" required placeholder="' + amountPlaceholder + '" autocomplete="off"></div>';
                }

                rowHtml += '<div class="col-md-1 d-flex justify-content-center"><button type="button" class="invoice-service-remove-btn remove-invoice-service" title="Remove service"><i class="fa-solid fa-trash-can"></i></button></div>';
                rowHtml += '</div>';

                return rowHtml;
            }

            function addInvoiceServiceRow(options) {
                const $row = $(buildServiceRowHtml());

                $('#invoice_services').append($row);

                if (showApplication) {
                    $row.find('.invoice-application-select').html(invoiceApplicationOptionsHtml);
                    bindApplicationChange($row, options);
                }
                bindServiceDetailLookup($row, options);

                refreshRemoveButtons();
            }

            window.initInvoiceServiceRows = function (options) {
                options = options || {};
                const clientSelector = options.clientSelector || '#client_id';
                const getApplicationUrl = options.getApplicationUrl || defaultGetApplicationUrl;
                const csrfToken = options.csrfToken || defaultCsrfToken;
                const selectedApplications = options.selectedApplications || [];
                window.invoiceServiceFeeUrl = options.getServiceFeeUrl || defaultGetServiceFeeUrl;
                window.invoiceServiceFeeToken = csrfToken;

                $(document).off('click.invoiceServiceAdd').on('click.invoiceServiceAdd', '.add-invoice-service', function () {
                    addInvoiceServiceRow(options);
                });

                $(document).off('click.invoiceServiceRemove').on('click.invoiceServiceRemove', '.remove-invoice-service', function () {
                    if ($('#invoice_services .invoice-service-row').length <= 1) {
                        return;
                    }

                    $(this).closest('.invoice-service-row').remove();
                    refreshRemoveButtons();

                    if (typeof options.onAmountChange === 'function') {
                        options.onAmountChange();
                    }
                });

                if (typeof options.onAmountChange === 'function') {
                    $(document).on('input', '.invoice-service-amount', options.onAmountChange);
                }

                refreshRemoveButtons();
                bindServiceDetailLookup($('#invoice_services .invoice-service-row').first(), options);

                if (!showApplication) {
                    $('#invoice_services .invoice-service-row').each(function () {
                        bindServiceDetailLookup($(this), options);
                    });

                    const subscriberSelector = options.subscriberSelector || '#subscriber';
                    if ($(subscriberSelector).length) {
                        $(subscriberSelector).off('change.invoiceServiceFees').on('change.invoiceServiceFees', function () {
                            $('#invoice_services .invoice-service-row').each(function () {
                                const $row = $(this);
                                const detail = ($row.find('.invoice-service-detail').val() || '').toString().trim();
                                if (detail) {
                                    bindServiceDetailLookup($row, options);
                                    lookupServiceFee(detail, $row, '', options);
                                }
                            });
                        });
                    }

                    return;
                }

                function loadApplications(clientId) {
                    if (!clientId) {
                        invoiceApplicationOptionsHtml = '<option value="">Select application / service type</option>';
                        populateApplicationSelects(invoiceApplicationOptionsHtml);
                        $('#invoice_services .invoice-service-detail, #invoice_services .invoice-service-amount').val('');
                        return;
                    }

                    $.ajax({
                        url: getApplicationUrl,
                        method: 'POST',
                        data: {
                            _token: csrfToken,
                            id: clientId,
                            comm: 'invoice',
                            ignore_invoice_id: options.ignoreInvoiceId || ''
                        },
                        success: function (data) {
                            invoiceApplicationOptionsHtml = data;
                            populateApplicationSelects(invoiceApplicationOptionsHtml, selectedApplications);
                            bindAllApplicationChanges(options);
                        }
                    });
                }

                $(clientSelector).off('change.invoiceServices').on('change.invoiceServices', function () {
                    loadApplications($(this).val());
                });

                bindAllApplicationChanges(options);

                const initialClient = $(clientSelector).val();
                if (initialClient) {
                    loadApplications(initialClient);
                }
            };
        })(jQuery);
    </script>
    @endpush
@endonce
